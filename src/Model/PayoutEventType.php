<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Values of PayoutEventType. A newer API may send a value that is not listed here.
 */
final class PayoutEventType
{
    const PAYOUT_BROADCAST = 'payout.broadcast';
    const PAYOUT_CONFIRMED = 'payout.confirmed';
    const PAYOUT_FAILED = 'payout.failed';

    const ALL = ['payout.broadcast', 'payout.confirmed', 'payout.failed'];

    private function __construct()
    {
    }
}
