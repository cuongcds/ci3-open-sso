<?php

declare(strict_types=1);

namespace Ci3Open\Sso;

use Ci3Open\Sso\Exception\TokenExchangeException;
use Ci3Open\Sso\Http\CurlHttpClient;
use Ci3Open\Sso\Http\HttpClientInterface;
use Ci3Open\Sso\Storage\RedirectStoreInterface;

/**
 * Client for a generic OAuth-style "login via SSO" flow used across CI3
 * apps:
 *
 *   1. getLoginUrl($redirectTo) -> redirect the browser here to start login.
 *   2. The provider redirects back to your callback route with ?token=...
 *   3. handleCallback() exchanges the token for the remote user and returns
 *      the redirect target you originally asked for in step 1.
 *
 * The intended post-login redirect is deliberately never placed on the
 * callback URL query string — see Storage\RedirectStoreInterface for why.
 * Route the returned SsoUser into your own user/session model; this SDK
 * only speaks the provider's protocol, it does not know your app's user
 * schema.
 */
final class SsoClient
{
    private readonly HttpClientInterface $httpClient;

    public function __construct(
        private readonly SsoConfig $config,
        private readonly RedirectStoreInterface $redirectStore,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    /**
     * Build the URL to send the browser to in order to start SSO login.
     * Stashes $redirectTo (e.g. the originally requested URI) so it survives
     * the round trip to the provider and back.
     */
    public function getLoginUrl(?string $redirectTo = null): string
    {
        $this->redirectStore->put($redirectTo ?? '');

        $query = http_build_query([
            'callback' => $this->config->callbackUrl,
            'client_id' => $this->config->clientId,
        ]);

        return rtrim($this->config->providerHost, '/') . '/login?' . $query;
    }

    /**
     * Handle the browser landing back on your callback route.
     *
     * @param string|null $token the 'token' query param the provider sent back
     */
    public function handleCallback(?string $token): CallbackResult
    {
        // Read + clear before anything else, so it can't be lost to any
        // session writes the caller performs while acting on the result.
        $redirectTo = $this->redirectStore->pullAndClear();

        if ($token === null || $token === '') {
            return CallbackResult::failure('Missing SSO token');
        }

        try {
            $user = $this->exchangeToken($token);
        } catch (TokenExchangeException $e) {
            return CallbackResult::failure($e->getMessage());
        }

        return CallbackResult::success($user, $redirectTo);
    }

    /**
     * @throws TokenExchangeException
     */
    private function exchangeToken(string $token): SsoUser
    {
        try {
            // POST, not GET - client_secret must never end up in a URL
            // (access logs, browser history, proxies, Referer headers).
            $response = $this->httpClient->post(rtrim($this->config->providerHost, '/') . '/oauth/token', [
                'token' => $token,
                'client_id' => $this->config->clientId,
                'client_secret' => $this->config->clientSecret,
            ]);
        } catch (\Throwable $e) {
            throw new TokenExchangeException('Failed to reach SSO provider: ' . $e->getMessage(), 0, $e);
        }

        $data = $response->json();

        if ($response->statusCode >= 400 || empty($data['email'])) {
            throw new TokenExchangeException('SSO provider rejected the token or returned no user');
        }

        return SsoUser::fromArray($data);
    }
}
