<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Values of InvoiceEventType. A newer API may send a value that is not listed here.
 */
final class InvoiceEventType
{
    const INVOICE_PAID = 'invoice.paid';
    const INVOICE_PARTIALLY_PAID = 'invoice.partially_paid';
    const INVOICE_EXPIRED = 'invoice.expired';
    const INVOICE_CANCELLED = 'invoice.cancelled';

    const ALL = ['invoice.paid', 'invoice.partially_paid', 'invoice.expired', 'invoice.cancelled'];

    private function __construct()
    {
    }
}
