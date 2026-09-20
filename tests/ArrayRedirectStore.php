<?php

declare(strict_types=1);

namespace Ci3Open\Sso\Tests;

use Ci3Open\Sso\Storage\RedirectStoreInterface;

/**
 * In-memory RedirectStoreInterface for tests, standing in for a real session.
 */
final class ArrayRedirectStore implements RedirectStoreInterface
{
    private ?string $value = null;

    public function put(string $redirectTo): void
    {
        $this->value = $redirectTo === '' ? null : $redirectTo;
    }

    public function pullAndClear(): ?string
    {
        $value = $this->value;
        $this->value = null;

        return $value;
    }
}
