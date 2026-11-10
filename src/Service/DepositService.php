<?php

namespace CoinsPay\Service;

use CoinsPay\Model\Deposit;
use CoinsPay\Page;

/**
 * Payments that came to the shop addresses.
 */
final class DepositService extends AbstractService
{
    /**
     * One page, newest first.
     *
     * @param array<string, mixed> $params status, chain, asset, reference, address, tx_hash, cursor, limit
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Page<Deposit>
     */
    public function list(array $params = [], array $options = [])
    {
        return $this->client->call('listDeposits', ['params' => $params, 'options' => $options]);
    }

    /**
     * All records that match, page after page: foreach ($client->deposits->iterate() as $deposit).
     *
     * @param array<string, mixed> $params status, chain, asset, reference, address, tx_hash, cursor, limit
     * @param array<string, mixed> $options
     * @return \Generator<int, Deposit>
     */
    public function iterate(array $params = [], array $options = [])
    {
        return $this->iterateList('listDeposits', $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Deposit
     */
    public function get($id, array $options = [])
    {
        return $this->client->call('getDeposit', ['id' => $id, 'options' => $options]);
    }
}
