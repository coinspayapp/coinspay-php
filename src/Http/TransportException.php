<?php

namespace CoinsPay\Http;

/**
 * An HttpClientInterface got no answer. The client turns it into
 * ApiConnectionException or ApiTimeoutException.
 */
class TransportException extends \RuntimeException
{
    /** @var bool */
    private $sent;

    /** @var bool */
    private $timeout;

    /** @var bool */
    private $retryable;

    /**
     * @param string $message
     * @param bool $sent The request may have reached the server.
     * @param bool $timeout No answer within the timeout.
     * @param bool $retryable False for failures a retry cannot fix, such as a bad TLS certificate.
     * @param \Throwable|null $previous
     */
    public function __construct($message, $sent, $timeout = false, $retryable = true, $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->sent = (bool) $sent;
        $this->timeout = (bool) $timeout;
        $this->retryable = (bool) $retryable;
    }

    /** @return bool */
    public function wasSent()
    {
        return $this->sent;
    }

    /** @return bool */
    public function isTimeout()
    {
        return $this->timeout;
    }

    /** @return bool */
    public function isRetryable()
    {
        return $this->retryable;
    }
}
