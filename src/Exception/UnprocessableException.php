<?php

namespace CoinsPay\Exception;

/**
 * 422: the payout cannot be made: INSUFFICIENT_BALANCE, FUNDS_NOT_MATURE, BELOW_MINIMUM and others.
 */
class UnprocessableException extends ApiException
{
}
