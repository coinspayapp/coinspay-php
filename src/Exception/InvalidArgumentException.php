<?php

namespace CoinsPay\Exception;

/**
 * A wrong argument, found before anything was sent: an id that is not a UUID,
 * a number instead of a decimal string, an unknown parameter.
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
