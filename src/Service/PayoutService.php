<?php

namespace CoinsPay\Service;

use CoinsPay\Exception\InvalidArgumentException;
use CoinsPay\Internal\Requestor;
use CoinsPay\Model\Payout;
use CoinsPay\Model\PayoutQuote;
use CoinsPay\Page;

/**
 * Payouts from the shop addresses to your wallets.
 */
final class PayoutService extends AbstractService
{
    /**
     * What a payout would send to each recipient, without sending anything.
     *
     * @param array<string, mixed> $params address_id or address, chain, asset, amount, recipients
     * @param array<string, mixed> $options extra, timeout, max_retries, headers
     * @return PayoutQuote
     */
    public function quote(array $params, array $options = [])
    {
        return $this->client->call('quotePayout', ['params' => $params, 'options' => $options]);
    }

    /**
     * Sends a payout. The idempotency key is required: take it from your own
     * payout record, so that repeating the call after a failure never sends the
     * money twice.
     *
     * @param array<string, mixed> $params address_id or address, chain, asset, amount, recipients
     * @param array<string, mixed> $options idempotency_key (required), extra, timeout, max_retries, headers
     * @return Payout
     */
    public function create(array $params, array $options)
    {
        if (!isset($options['idempotency_key'])) {
            throw new InvalidArgumentException('payouts->create() needs the idempotency_key option');
        }
        $key = Requestor::checkIdempotencyKey($options['idempotency_key']);
        return $this->client->call('createPayout', ['params' => $params, 'idempotency_key' => $key, 'options' => $options]);
    }

    /**
     * One page, newest first.
     *
     * @param array<string, mixed> $params status, chain, address_id, cursor, limit
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Page<Payout>
     */
    public function list(array $params = [], array $options = [])
    {
        return $this->client->call('listPayouts', ['params' => $params, 'options' => $options]);
    }

    /**
     * All records that match, page after page: foreach ($client->payouts->iterate() as $payout).
     *
     * @param array<string, mixed> $params status, chain, address_id, cursor, limit
     * @param array<string, mixed> $options
     * @return \Generator<int, Payout>
     */
    public function iterate(array $params = [], array $options = [])
    {
        return $this->iterateList('listPayouts', $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Payout
     */
    public function get($id, array $options = [])
    {
        return $this->client->call('getPayout', ['id' => $id, 'options' => $options]);
    }
}
