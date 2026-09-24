<?php

declare(strict_types=1);

namespace Ci3Open\Sso\Http;

use Ci3Open\Sso\Exception\SsoException;

interface HttpClientInterface
{
    /**
     * Send a GET request with query string parameters.
     *
     * @param array<string, mixed> $query
     *
     * @throws SsoException on transport-level failure
     */
    public function get(string $url, array $query): HttpResponse;

    /**
     * Send a POST request with a form-urlencoded body.
     *
     * @param array<string, mixed> $fields
     *
     * @throws SsoException on transport-level failure
     */
    public function post(string $url, array $fields): HttpResponse;
}
