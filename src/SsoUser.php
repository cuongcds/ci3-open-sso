<?php

declare(strict_types=1);

namespace Ci3Open\Sso;

/**
 * The remote user resolved from a provider token exchange.
 */
final class SsoUser
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly ?string $displayName = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: (string) ($data['email'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            displayName: isset($data['display_name']) && $data['display_name'] !== ''
                ? (string) $data['display_name']
                : null
        );
    }
}
