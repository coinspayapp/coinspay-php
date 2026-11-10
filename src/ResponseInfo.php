<?php

namespace CoinsPay;

/**
 * What the API answered to the last request, besides the body.
 */
final class ResponseInfo
{
    /** @var int */
    public $status;

    /** @var array<string, string> Lowercase header names. */
    public $headers;

    /**
     * @var bool True when a create request repeated an earlier one with the same
     *      idempotency key: the API returned the existing object (status 200) in its
     *      current state instead of creating a new one (201).
     */
    public $replayed;

    /**
     * @param int $status
     * @param array<string, string> $headers
     * @param bool $replayed
     */
    public function __construct($status, array $headers, $replayed)
    {
        $this->status = (int) $status;
        $this->headers = $headers;
        $this->replayed = (bool) $replayed;
    }
}
