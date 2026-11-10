<?php

namespace CoinsPay\Exception;

/**
 * The webhook signature is missing, malformed, too old or does not match.
 */
class SignatureVerificationException extends CoinsPayException
{
    const MISSING = 'missing';
    const MALFORMED = 'malformed';
    const STALE = 'stale';
    const MISMATCH = 'mismatch';

    const MESSAGES = [
        'missing' => 'No x-cpay-signature header',
        'malformed' => 'The x-cpay-signature header is malformed',
        'stale' => 'The webhook signature is too old or from the future',
        'mismatch' => 'The webhook signature does not match the body and secret',
    ];

    /** @var string */
    private $reason;

    /**
     * @param string $reason One of the class constants.
     */
    public function __construct($reason)
    {
        parent::__construct(self::MESSAGES[$reason]);
        $this->reason = $reason;
    }

    /** @return string missing, malformed, stale or mismatch. */
    public function getReason()
    {
        return $this->reason;
    }
}
