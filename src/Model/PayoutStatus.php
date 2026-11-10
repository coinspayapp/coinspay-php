<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Values of PayoutStatus. A newer API may send a value that is not listed here.
 */
final class PayoutStatus
{
    const PENDING = 'pending';
    const WAITING_MATURITY = 'waiting_maturity';
    const SUBMITTED = 'submitted';
    const BROADCAST = 'broadcast';
    const CONFIRMED = 'confirmed';
    const FAILED = 'failed';
    const PARTIALLY_COMPLETED = 'partially_completed';
    const REJECTED = 'rejected';

    const ALL = ['pending', 'waiting_maturity', 'submitted', 'broadcast', 'confirmed', 'failed', 'partially_completed', 'rejected'];

    private function __construct()
    {
    }
}
