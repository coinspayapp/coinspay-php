<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class PayoutQuote extends Model
{
    /**
     * Network: `tron`, `ethereum`, `bsc`… All values are in [Networks and
     * coins](/docs/networks) and in the `GET /chains` response.
     *
     * @var string
     */
    public $chain;

    /**
     * Asset: the network coin (`ETH`, `TRX`, `BTC`…) or a token (`USDT`, `USDC`…). The
     * assets of each network are in [Networks and coins](/docs/networks).
     *
     * @var string
     */
    public $asset;

    /**
     * Decimal places of the asset: how many base units make one coin.
     *
     * @var int
     */
    public $decimals;

    /**
     * Amount that will leave the address. Decimal string.
     *
     * @var string
     */
    public $amount;

    /**
     * Shop address the payout will be sent from.
     *
     * @var string
     */
    public $address;

    /**
     * id of the shop receiving address.
     *
     * @var string
     */
    public $address_id;

    /**
     * What each recipient will get.
     *
     * @var PayoutQuoteRecipient[]
     */
    public $recipients;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->chain = self::value($data, 'chain');
        $model->asset = self::value($data, 'asset');
        $model->decimals = self::value($data, 'decimals');
        $model->amount = self::value($data, 'amount');
        $model->address = self::value($data, 'address');
        $model->address_id = self::value($data, 'address_id');
        $model->recipients = self::listOf($data, 'recipients', PayoutQuoteRecipient::class);
        return $model;
    }
}
