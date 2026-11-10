<?php

namespace CoinsPay;

use CoinsPay\Exception\CoinsPayException;
use CoinsPay\Exception\InvalidArgumentException;
use CoinsPay\Exception\SignatureVerificationException;
use CoinsPay\Internal\Operations;
use CoinsPay\Model\Model;
use CoinsPay\Model\UnknownEvent;

/**
 * Webhooks: checks the signature of an event and turns it into an object.
 *
 *     $event = \CoinsPay\Webhook::constructEvent(
 *         file_get_contents('php://input'),
 *         \CoinsPay\Webhook::headerFromGlobals(),
 *         getenv('COINSPAY_WEBHOOK_SECRET')
 *     );
 *     if ($event instanceof \CoinsPay\Model\InvoiceEvent && $event->type === 'invoice.paid') { ... }
 */
final class Webhook
{
    /** Header with the signature: t=<unix>,v1=<hex>. */
    const SIGNATURE_HEADER = 'x-cpay-signature';

    /** Header with the event type. */
    const EVENT_HEADER = 'x-cpay-event';

    /** Header with the event id; a repeated delivery carries the same id. */
    const DELIVERY_HEADER = 'x-cpay-delivery';

    /** Allowed clock difference in seconds, in both directions. */
    const DEFAULT_TOLERANCE = 300;

    private function __construct()
    {
    }

    /**
     * Checks the x-cpay-signature header and throws SignatureVerificationException if it does not hold.
     *
     * $payload is the request body exactly as received (file_get_contents('php://input')), before
     * json_decode(): the signature covers the bytes. $secret may be a list: the old secret stops
     * working at once when you replace it in the dashboard, so pass both while you roll the new one out.
     *
     * @param string $payload
     * @param string|string[]|null $header
     * @param string|string[] $secret
     * @param int $tolerance Allowed clock difference in seconds.
     * @param int|null $now Current unix time, for tests.
     */
    public static function verifySignature($payload, $header, $secret, $tolerance = self::DEFAULT_TOLERANCE, $now = null)
    {
        // NAN or a negative tolerance would turn the age check off.
        if (!is_int($tolerance) && !is_float($tolerance) || !is_finite((float) $tolerance) || $tolerance < 0) {
            throw new InvalidArgumentException('The tolerance must be a non-negative number of seconds');
        }
        if (is_array($header)) {
            $header = implode(',', $header);
        }
        if (!is_string($header) || $header === '') {
            throw new SignatureVerificationException(SignatureVerificationException::MISSING);
        }
        if (!is_string($payload)) {
            throw new InvalidArgumentException('The webhook payload must be the raw body string');
        }

        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $piece) {
            $eq = strpos($piece, '=');
            if ($eq === false || $eq === 0) {
                throw new SignatureVerificationException(SignatureVerificationException::MALFORMED);
            }
            $key = trim(substr($piece, 0, $eq));
            $value = trim(substr($piece, $eq + 1));
            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }
        // preg_match, not ctype_digit: ext-ctype is optional, on Alpine it is a separate package.
        if ($timestamp === null || !preg_match('/^[0-9]+$/D', $timestamp) || $signatures === []) {
            throw new SignatureVerificationException(SignatureVerificationException::MALFORMED);
        }
        foreach ($signatures as $signature) {
            if (!preg_match('/^[0-9a-f]{64}$/D', $signature)) {
                throw new SignatureVerificationException(SignatureVerificationException::MALFORMED);
            }
        }

        // Compared as numbers, so a timestamp too long for an int is simply stale.
        $t = (float) $timestamp;
        $now = $now === null ? time() : $now;
        if (abs($now - $t) > $tolerance) {
            throw new SignatureVerificationException(SignatureVerificationException::STALE);
        }

        $secrets = is_array($secret) ? array_values($secret) : [$secret];
        if ($secrets === []) {
            throw new InvalidArgumentException('The webhook secret is empty');
        }
        foreach ($secrets as $item) {
            if (!is_string($item) || $item === '') {
                throw new InvalidArgumentException('The webhook secret is empty');
            }
        }
        $signed = ltrim($timestamp, '0');
        $signed = $signed === '' ? '0' : $signed;
        foreach ($secrets as $item) {
            $expected = hash_hmac('sha256', $signed . '.' . $payload, $item);
            foreach ($signatures as $signature) {
                if (hash_equals($expected, $signature)) {
                    return;
                }
            }
        }
        throw new SignatureVerificationException(SignatureVerificationException::MISMATCH);
    }

    /**
     * Verifies the signature and returns the event: PaymentEvent, PayoutEvent, InvoiceEvent,
     * TestEvent, or UnknownEvent for a type this version of the library does not know.
     *
     * @param string $payload
     * @param string|string[]|null $header
     * @param string|string[] $secret
     * @param int $tolerance
     * @param int|null $now
     * @return Model
     */
    public static function constructEvent($payload, $header, $secret, $tolerance = self::DEFAULT_TOLERANCE, $now = null)
    {
        self::verifySignature($payload, $header, $secret, $tolerance, $now);
        $data = json_decode($payload, true, 512, JSON_BIGINT_AS_STRING);
        if (!is_array($data) || !isset($data['type']) || !is_string($data['type'])) {
            throw new CoinsPayException('The webhook body is not a CoinsPay event');
        }
        $class = isset(Operations::EVENTS[$data['type']]) ? Operations::EVENTS[$data['type']] : UnknownEvent::class;
        return $class::fromArray($data);
    }

    /**
     * A valid x-cpay-signature value, for testing your webhook handler.
     *
     * @param string $payload
     * @param string $secret
     * @param int|null $timestamp
     * @return string
     */
    public static function signatureHeader($payload, $secret, $timestamp = null)
    {
        $timestamp = $timestamp === null ? time() : (int) $timestamp;
        return 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
    }

    /**
     * The x-cpay-signature header of the current request, from $_SERVER.
     *
     * @return string|null
     */
    public static function headerFromGlobals()
    {
        if (isset($_SERVER['HTTP_X_CPAY_SIGNATURE'])) {
            return $_SERVER['HTTP_X_CPAY_SIGNATURE'];
        }
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strtolower($name) === self::SIGNATURE_HEADER) {
                    return $value;
                }
            }
        }
        return null;
    }
}
