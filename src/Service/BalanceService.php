<?php

namespace CoinsPay\Service;

use CoinsPay\Model\Balance;

/**
 * Balances of the shop addresses.
 */
final class BalanceService extends AbstractService
{
    /**
     * Balances by network and asset.
     *
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Balance[]
     */
    public function list(array $options = [])
    {
        return $this->client->call('listBalances', ['options' => $options]);
    }
}
