<?php

namespace CoinsPay\Http;

/**
 * An HTTP answer as received.
 */
final class Response
{
    /** @var int */
    public $status;

    /** @var array<string, string> Lowercase header names. */
    public $headers;

    /** @var string */
    public $body;

    /**
     * @param int $status
     * @param array<string, string> $headers
     * @param string $body
     */
    public function __construct($status, array $headers, $body)
    {
        $this->status = (int) $status;
        $this->headers = array_change_key_case($headers, CASE_LOWER);
        $this->body = (string) $body;
    }
}
