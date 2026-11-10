<?php

namespace CoinsPay\Exception;

/**
 * No answer within the timeout; the request may have been processed.
 */
class ApiTimeoutException extends ApiConnectionException
{
    /**
     * @param string $message
     * @param \Throwable|null $previous
     */
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, true, $previous);
    }
}
