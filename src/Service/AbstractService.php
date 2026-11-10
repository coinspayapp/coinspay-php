<?php

namespace CoinsPay\Service;

use CoinsPay\Client;

/**
 * Base of the API resources.
 */
abstract class AbstractService
{
    /** @var Client */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * All records of a list, page after page.
     *
     * @param string $operation
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return \Generator
     */
    protected function iterateList($operation, array $params, array $options)
    {
        $cursor = isset($params['cursor']) ? $params['cursor'] : null;
        do {
            if ($cursor !== null) {
                $params['cursor'] = $cursor;
            }
            $page = $this->client->call($operation, ['params' => $params, 'options' => $options]);
            foreach ($page->data as $item) {
                yield $item;
            }
            $cursor = $page->next_cursor;
        } while ($cursor !== null);
    }
}
