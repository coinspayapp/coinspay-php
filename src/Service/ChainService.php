<?php

namespace CoinsPay\Service;

use CoinsPay\Model\ChainList;

/**
 * Networks and assets.
 */
final class ChainService extends AbstractService
{
    /**
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return ChainList
     */
    public function list(array $options = [])
    {
        return $this->client->call('listChains', ['options' => $options]);
    }
}
