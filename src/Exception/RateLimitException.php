<?php

namespace CoinsPay\Exception;

/**
 * 429: too many requests.
 */
class RateLimitException extends ApiException
{
    /**
     * @return int|null Seconds to wait before retrying, if the API said so.
     */
    public function getRetryAfter()
    {
        $headers = $this->getHeaders();
        if (isset($headers['retry-after']) && preg_match('/^\s*(\d+)\s*$/', $headers['retry-after'], $match)) {
            return (int) $match[1];
        }
        $details = $this->getDetails();
        if (isset($details['retry_after']) && is_int($details['retry_after']) && $details['retry_after'] >= 0) {
            return $details['retry_after'];
        }
        return null;
    }
}
