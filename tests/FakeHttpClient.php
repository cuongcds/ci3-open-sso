<?php

declare(strict_types=1);

namespace Ci3Open\Sso\Tests;

use Ci3Open\Sso\Http\HttpClientInterface;
use Ci3Open\Sso\Http\HttpResponse;

final class FakeHttpClient implements HttpClientInterface
{
    public ?string $lastUrl = null;

    /** @var array<string, mixed>|null */
    public ?array $lastQuery = null;

    public function __construct(private readonly int $statusCode, private readonly string $body)
    {
    }

    public function get(string $url, array $query): HttpResponse
    {
        $this->lastUrl = $url;
        $this->lastQuery = $query;

        return new HttpResponse($this->statusCode, $this->body);
    }
}
