<?php

namespace CoinsPay\Exception;

/**
 * The answer was not the JSON the API sends, for example an HTML error page of a proxy or a redirect.
 */
class InvalidResponseException extends CoinsPayException
{
    /** @var int */
    private $status;

    /**
     * @param string $message
     * @param int $status
     */
    public function __construct($message, $status)
    {
        parent::__construct($message);
        $this->status = (int) $status;
    }

    /** @return int HTTP status, 0 when there was none. */
    public function getStatus()
    {
        return $this->status;
    }
}
