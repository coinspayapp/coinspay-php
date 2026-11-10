<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Values of PaymentEventType. A newer API may send a value that is not listed here.
 */
final class PaymentEventType
{
    const PAYMENT_SEEN = 'payment.seen';
    const PAYMENT_DETECTED = 'payment.detected';
    const PAYMENT_CONFIRMED = 'payment.confirmed';
    const PAYMENT_ORPHANED = 'payment.orphaned';

    const ALL = ['payment.seen', 'payment.detected', 'payment.confirmed', 'payment.orphaned'];

    private function __construct()
    {
    }
}
