<?php

declare(strict_types=1);

namespace Ci3Open\Sso;

/**
 * Outcome of SsoClient::handleCallback(): either a resolved remote user with
 * a redirect target to send the browser to, or a failure to display.
 */
final class CallbackResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?SsoUser $user,
        public readonly ?string $redirectTo,
        public readonly ?string $errorMessage
    ) {
    }

    public static function success(SsoUser $user, ?string $redirectTo): self
    {
        return new self(true, $user, $redirectTo, null);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, null, $errorMessage);
    }
}
