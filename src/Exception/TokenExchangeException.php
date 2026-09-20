<?php

declare(strict_types=1);

namespace Ci3Open\Sso\Exception;

/**
 * Thrown when the SSO provider rejects the token exchange, or returns a
 * response that doesn't carry a usable remote user (e.g. missing email).
 */
class TokenExchangeException extends SsoException
{
}
