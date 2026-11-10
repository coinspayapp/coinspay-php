<?php

namespace CoinsPay;

/**
 * One page of a list, newest first.
 *
 * @template T
 */
final class Page implements \IteratorAggregate, \Countable
{
    /** @var array<int, T> */
    public $data;

    /** @var string|null Pass it as "cursor" to get the next page; null when there are no more pages. */
    public $next_cursor;

    /**
     * @param array<int, T> $data
     * @param string|null $nextCursor
     */
    public function __construct(array $data, $nextCursor)
    {
        $this->data = $data;
        $this->next_cursor = is_string($nextCursor) ? $nextCursor : null;
    }

    /** @return bool */
    public function hasMore()
    {
        return $this->next_cursor !== null;
    }

    /** @return \ArrayIterator<int, T> */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /** @return int */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->data);
    }
}
