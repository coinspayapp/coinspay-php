<?php

namespace CoinsPay\Service;

use CoinsPay\Model\ShopDetails;

/**
 * The shop of the API key.
 */
final class ShopService extends AbstractService
{
    /**
     * The shop and what the key may do: $details->key->test, $details->key->access.
     *
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return ShopDetails
     */
    public function get(array $options = [])
    {
        return $this->client->call('getShop', ['options' => $options]);
    }
}
