<?php

namespace CoinsPay\Internal;

/**
 * Holds the API key so that var_dump(), print_r(), var_export(), an array cast and
 * serialize() of the client never show it.
 *
 * @internal
 */
final class Secret
{
    /** @var \Closure */
    private $value;

    /**
     * @param string $value
     */
    public function __construct(
        #[\SensitiveParameter]
        $value
    ) {
        $this->value = function () use ($value) {
            return $value;
        };
    }

    /** @return string */
    public function reveal()
    {
        return call_user_func($this->value);
    }

    /** @return array<string, string> */
    public function __debugInfo()
    {
        return ['value' => '***'];
    }
}
