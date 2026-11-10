<?php

namespace CoinsPay\Service;

use CoinsPay\Model\CurrencyList;

/**
 * Currencies an invoice may be priced in.
 */
final class CurrencyService extends AbstractService
{
    /**
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return CurrencyList
     */
    public function list(array $options = [])
    {
        return $this->client->call('listCurrencies', ['options' => $options]);
    }
}
