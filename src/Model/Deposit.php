<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class Deposit extends Model
{
    /**
     * Public id of the payment.
     *
     * @var string
     */
    public $id;

    /**
     * `seen`: the transaction is in the mempool (Bitcoin and Litecoin), not a payment yet;
     * `pending`: in a block, waiting for confirmations; `confirmed`: credited; `orphaned`:
     * the block was reorganized or the transaction disappeared. One of DepositStatus::ALL.
     *
     * @var string
     */
    public $status;

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
     * Payment amount in asset units, as a decimal string. Decimal string.
     *
     * @var string
     */
    public $amount;

    /**
     * The same amount in base units of the asset (satoshi, wei, sun) as an integer string.
     * Integer string in base units of the asset.
     *
     * @var string
     */
    public $amount_base;

    /**
     * Decimal places of the asset: how many base units make one coin.
     *
     * @var int
     */
    public $decimals;

    /**
     * Value in US dollars at the moment of crediting; `null` before crediting or without a
     * rate. Decimal string.
     *
     * @var string|null
     */
    public $amount_usd;

    /**
     * Amounts in all reference currencies fixed at crediting: `{ "USD": "100", "EUR":
     * "92.15" }`. `null` as for `amount_usd`.
     *
     * @var array<string, string>|null
     */
    public $amounts_fiat;

    /**
     * Shop address that received the payment.
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
     * Invoice the address belongs to; `null` if the payment is not for an invoice.
     *
     * @var string|null
     */
    public $invoice_id;

    /**
     * Your order number: comes back in payments and events. Up to 190 characters.
     *
     * @var string|null
     */
    public $reference;

    /**
     * Transaction hash in the network.
     *
     * @var string
     */
    public $tx_hash;

    /**
     * Output or log index inside the transaction: together with the hash it identifies the
     * payment. Negative for SOL on Solana: −(1 + account index in the transaction).
     *
     * @var int
     */
    public $output_index;

    /**
     * Sender if the network knows it; `null` in Bitcoin and Litecoin.
     *
     * @var string|null
     */
    public $from_address;

    /**
     * Block with the transaction; `null` while it is in the mempool.
     *
     * @var int|null
     */
    public $block_height;

    /**
     * Confirmations required for crediting.
     *
     * @var int
     */
    public $confirmations_required;

    /**
     * The payment arrived in a different EVM network than the address was issued for: it
     * is counted in the actual network.
     *
     * @var bool
     */
    public $unexpected_chain;

    /**
     * Network the address was issued for.
     *
     * @var string|null
     */
    public $expected_chain;

    /**
     * Bitcoin and Litecoin mempool only: the transaction is replaceable (RBF). Otherwise
     * `null`.
     *
     * @var bool|null
     */
    public $rbf;

    /**
     * Bitcoin mempool only: a conflicting spend of the same coins was seen. Otherwise
     * `null`.
     *
     * @var bool|null
     */
    public $double_spend;

    /**
     * When the platform first saw the payment, UTC. ISO 8601 date-time in UTC.
     *
     * @var string
     */
    public $created_at;

    /**
     * When the payment was credited; `null` if not yet. ISO 8601 date-time in UTC.
     *
     * @var string|null
     */
    public $confirmed_at;

    /**
     * When the payment was cancelled by a reorganization; `null` if never. ISO 8601
     * date-time in UTC.
     *
     * @var string|null
     */
    public $orphaned_at;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->id = self::value($data, 'id');
        $model->status = self::value($data, 'status');
        $model->chain = self::value($data, 'chain');
        $model->asset = self::value($data, 'asset');
        $model->amount = self::value($data, 'amount');
        $model->amount_base = self::value($data, 'amount_base');
        $model->decimals = self::value($data, 'decimals');
        $model->amount_usd = self::value($data, 'amount_usd');
        $model->amounts_fiat = self::value($data, 'amounts_fiat');
        $model->address = self::value($data, 'address');
        $model->address_id = self::value($data, 'address_id');
        $model->invoice_id = self::value($data, 'invoice_id');
        $model->reference = self::value($data, 'reference');
        $model->tx_hash = self::value($data, 'tx_hash');
        $model->output_index = self::value($data, 'output_index');
        $model->from_address = self::value($data, 'from_address');
        $model->block_height = self::value($data, 'block_height');
        $model->confirmations_required = self::value($data, 'confirmations_required');
        $model->unexpected_chain = self::value($data, 'unexpected_chain');
        $model->expected_chain = self::value($data, 'expected_chain');
        $model->rbf = self::value($data, 'rbf');
        $model->double_spend = self::value($data, 'double_spend');
        $model->created_at = self::value($data, 'created_at');
        $model->confirmed_at = self::value($data, 'confirmed_at');
        $model->orphaned_at = self::value($data, 'orphaned_at');
        return $model;
    }
}
