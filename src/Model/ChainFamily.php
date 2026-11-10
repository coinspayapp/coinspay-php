<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Values of ChainFamily. A newer API may send a value that is not listed here.
 */
final class ChainFamily
{
    const EVM = 'evm';
    const TRON = 'tron';
    const BITCOIN = 'bitcoin';
    const LITECOIN = 'litecoin';
    const SOLANA = 'solana';

    const ALL = ['evm', 'tron', 'bitcoin', 'litecoin', 'solana'];

    private function __construct()
    {
    }
}
