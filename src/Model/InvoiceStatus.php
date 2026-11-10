<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Values of InvoiceStatus. A newer API may send a value that is not listed here.
 */
final class InvoiceStatus
{
    const OPEN = 'open';
    const PAID = 'paid';
    const EXPIRED = 'expired';
    const CANCELLED = 'cancelled';

    const ALL = ['open', 'paid', 'expired', 'cancelled'];

    private function __construct()
    {
    }
}
