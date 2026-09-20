<?php

declare(strict_types=1);

namespace Ci3Open\Sso\Http;

final class HttpResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $body
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        $decoded = json_decode($this->body, true);

        return is_array($decoded) ? $decoded : [];
    }
}
