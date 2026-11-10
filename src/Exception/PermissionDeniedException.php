<?php

namespace CoinsPay\Exception;

/**
 * 403: the key may not do this: KEY_ACCESS_DENIED (an invoices-only key),
 * TEST_MODE_UNSUPPORTED (a test key), IP_NOT_ALLOWED (see details.ip), SHOP_DISABLED.
 */
class PermissionDeniedException extends ApiException
{
}
