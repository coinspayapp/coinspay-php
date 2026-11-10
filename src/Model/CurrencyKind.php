<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Values of CurrencyKind. A newer API may send a value that is not listed here.
 */
final class CurrencyKind
{
    const FIAT = 'fiat';
    const CRYPTO = 'crypto';

    const ALL = ['fiat', 'crypto'];

    private function __construct()
    {
    }
}
