<?php

declare(strict_types=1);

namespace Ci3Open\Sso\Http;

use Ci3Open\Sso\Exception\SsoException;

final class CurlHttpClient implements HttpClientInterface
{
    public function __construct(private readonly int $timeoutSeconds = 10)
    {
    }

    public function get(string $url, array $query): HttpResponse
    {
        $fullUrl = $query === [] ? $url : $url . '?' . http_build_query($query);
        $handle = curl_init($fullUrl);

        if ($handle === false) {
            throw new SsoException('Failed to initialize cURL session');
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);

        $body = curl_exec($handle);

        if ($body === false) {
            $error = curl_error($handle);
            curl_close($handle);

            throw new SsoException("HTTP request failed: {$error}");
        }

        $statusCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return new HttpResponse($statusCode, (string) $body);
    }
}
