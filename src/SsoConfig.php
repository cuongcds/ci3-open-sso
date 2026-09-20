<?php

declare(strict_types=1);

namespace Ci3Open\Sso;

/**
 * Static configuration for one OAuth-style SSO provider/client. Immutable —
 * build once from your app config and reuse across requests.
 */
final class SsoConfig
{
    public function __construct(
        public readonly string $providerHost,
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $callbackUrl
    ) {
    }

    public function withCallbackUrl(string $callbackUrl): self
    {
        return new self($this->providerHost, $this->clientId, $this->clientSecret, $callbackUrl);
    }
}
