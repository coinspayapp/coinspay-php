<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Values of DepositStatus. A newer API may send a value that is not listed here.
 */
final class DepositStatus
{
    const SEEN = 'seen';
    const PENDING = 'pending';
    const CONFIRMED = 'confirmed';
    const ORPHANED = 'orphaned';

    const ALL = ['seen', 'pending', 'confirmed', 'orphaned'];

    private function __construct()
    {
    }
}
