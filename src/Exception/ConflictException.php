<?php

namespace CoinsPay\Exception;

/**
 * 409: IDEMPOTENCY_CONFLICT, or INVOICE_NOT_OPEN (see details.status).
 */
class ConflictException extends ApiException
{
}
