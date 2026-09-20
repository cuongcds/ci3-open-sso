<?php

declare(strict_types=1);

namespace Ci3Open\Sso\Tests;

use Ci3Open\Sso\SsoClient;
use Ci3Open\Sso\SsoConfig;
use PHPUnit\Framework\TestCase;

final class SsoClientTest extends TestCase
{
    private function makeConfig(): SsoConfig
    {
        return new SsoConfig(
            providerHost: 'https://accounts.example.com',
            clientId: 'my-app',
            clientSecret: 'secret',
            callbackUrl: 'https://app.example.com/portal/oauth/callback'
        );
    }

    public function testLoginUrlNeverCarriesRedirectAsQueryParam(): void
    {
        $store = new ArrayRedirectStore();
        $http = new FakeHttpClient(200, '{}');
        $client = new SsoClient($this->makeConfig(), $store, $http);

        $url = $client->getLoginUrl('portal/website');

        self::assertStringNotContainsString('redirect', $url);
        self::assertSame(
            'https://accounts.example.com/login?callback=https%3A%2F%2Fapp.example.com%2Fportal%2Foauth%2Fcallback&client_id=my-app',
            $url
        );
        self::assertSame('portal/website', $store->pullAndClear());
    }

    public function testHandleCallbackResolvesUserAndOriginalRedirect(): void
    {
        $store = new ArrayRedirectStore();
        $store->put('portal/website');
        $http = new FakeHttpClient(200, '{"email":"a@b.com","name":"A","display_name":"Mr A"}');
        $client = new SsoClient($this->makeConfig(), $store, $http);

        $result = $client->handleCallback('tok-123');

        self::assertTrue($result->success);
        self::assertSame('a@b.com', $result->user->email);
        self::assertSame('Mr A', $result->user->displayName);
        self::assertSame('portal/website', $result->redirectTo);
        self::assertSame(['token' => 'tok-123', 'client_id' => 'my-app', 'client_secret' => 'secret'], $http->lastQuery);

        // stashed value must be cleared so it can't leak into a later login
        self::assertNull($store->pullAndClear());
    }

    public function testHandleCallbackWithoutTokenFailsWithoutHttpCall(): void
    {
        $store = new ArrayRedirectStore();
        $store->put('portal/website');
        $http = new FakeHttpClient(200, '{}');
        $client = new SsoClient($this->makeConfig(), $store, $http);

        $result = $client->handleCallback(null);

        self::assertFalse($result->success);
        self::assertNull($http->lastUrl);
    }

    public function testHandleCallbackWithNoEmailInResponseFails(): void
    {
        $store = new ArrayRedirectStore();
        $http = new FakeHttpClient(200, '{"name":"A"}');
        $client = new SsoClient($this->makeConfig(), $store, $http);

        $result = $client->handleCallback('tok-123');

        self::assertFalse($result->success);
        self::assertNotNull($result->errorMessage);
    }

    public function testRedirectSurvivesEvenIfCallbackUrlItselfCarriesGarbageQueryString(): void
    {
        // Simulates a provider appending '?token=' onto a callback URL that
        // already had '?redirect=...' -- the garbage lands in $_GET, but
        // since this SDK never reads $_GET directly (the caller passes
        // $token in), and the redirect target came from the store instead,
        // it is unaffected.
        $store = new ArrayRedirectStore();
        $store->put('portal/website');
        $http = new FakeHttpClient(200, '{"email":"a@b.com","name":"A"}');
        $client = new SsoClient($this->makeConfig(), $store, $http);

        $result = $client->handleCallback('tok-123');

        self::assertSame('portal/website', $result->redirectTo);
    }
}
