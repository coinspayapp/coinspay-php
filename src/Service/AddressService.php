<?php

namespace CoinsPay\Service;

use CoinsPay\Model\Address;
use CoinsPay\Page;

/**
 * Permanent receiving addresses of the shop.
 */
final class AddressService extends AbstractService
{
    /**
     * Issues a receiving address. This request has no idempotency key: after an
     * ApiConnectionException that wasSent(), look the address up by reference first.
     *
     * @param array<string, mixed> $params chain, reference, payout
     * @param array<string, mixed> $options extra, timeout, max_retries, headers
     * @return Address
     */
    public function create(array $params, array $options = [])
    {
        return $this->client->call('createAddress', ['params' => $params, 'options' => $options]);
    }

    /**
     * One page, newest first.
     *
     * @param array<string, mixed> $params chain, reference, address, cursor, limit
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Page<Address>
     */
    public function list(array $params = [], array $options = [])
    {
        return $this->client->call('listAddresses', ['params' => $params, 'options' => $options]);
    }

    /**
     * All records that match, page after page: foreach ($client->addresses->iterate() as $address).
     *
     * @param array<string, mixed> $params chain, reference, address, cursor, limit
     * @param array<string, mixed> $options
     * @return \Generator<int, Address>
     */
    public function iterate(array $params = [], array $options = [])
    {
        return $this->iterateList('listAddresses', $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Address
     */
    public function get($id, array $options = [])
    {
        return $this->client->call('getAddress', ['id' => $id, 'options' => $options]);
    }
}
