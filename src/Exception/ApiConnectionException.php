<?php

namespace CoinsPay\Exception;

/**
 * The request did not get an answer. When wasSent() is false it never reached
 * the API (connection refused, DNS failure), so any request is safe to retry.
 * When it is true the request may have been processed.
 */
class ApiConnectionException extends CoinsPayException
{
    /** @var bool */
    private $sent;

    /**
     * @param string $message
     * @param bool $sent
     * @param \Throwable|null $previous
     */
    public function __construct($message, $sent, $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->sent = (bool) $sent;
    }

    /** @return bool Whether the request may have reached the API. */
    public function wasSent()
    {
        return $this->sent;
    }
}
