# cuongcds/ci3-open-sso

Generic OAuth-style "Login via SSO" client for CodeIgniter 3 apps: builds the
login redirect URL and handles the callback token exchange against any
provider that speaks this simple protocol (`GET /login?callback=...&client_id=...`
followed by a `GET /oauth/token` exchange). Extracted from a production CI3
app's SSO controller so the same flow can be reused across projects.

## Install

```bash
composer require cuongcds/ci3-open-sso
```

## Why a redirect store abstraction?

Some SSO providers do not forward query parameters placed on the callback
URL correctly — e.g. appending their own `?token=...` with a bare `?` instead
of `&`, so a callback URL that already has `?redirect=...` ends up corrupted
(`...?redirect=foo?token=bar`, which PHP parses as a single garbage
`redirect` value, silently dropping the token). Because of this, the "where
to send the user after login" value must never be carried on the callback
URL — it has to be stashed on your side (session, by default) before leaving
for the provider and read back on return. This is exactly what
`Ci3Open\Sso\Storage\RedirectStoreInterface` is for.

## Usage

```php
use Ci3Open\Sso\SsoClient;
use Ci3Open\Sso\SsoConfig;
use Ci3Open\Sso\Storage\SessionRedirectStore;

$config = new SsoConfig(
    providerHost: config_item('sso_provider_host'),
    clientId: config_item('sso_client_id'),
    clientSecret: config_item('sso_client_secret'),
    callbackUrl: base_url('portal/oauth/callback')
);

$sso = new SsoClient($config, new SessionRedirectStore($this->session));
```

### Starting login

```php
public function login()
{
    $redirectTo = $this->input->get('redirect') ?: null;
    redirect($sso->getLoginUrl($redirectTo));
}
```

### Handling the callback

```php
public function callback()
{
    $result = $sso->handleCallback($this->input->get('token'));

    if (!$result->success) {
        $this->session->set_flashdata('error_message', 'Lỗi khi đăng nhập qua SSO.');
        redirect('login');
    }

    // $result->user->email / ->name / ->displayName
    // Map to your own user table / session here (this SDK doesn't touch
    // your user model — it only speaks the provider's protocol).

    redirect($result->redirectTo ?: 'portal');
}
```

A full example controller is in [examples/oauth-controller.php](examples/oauth-controller.php).

### Forcing SSO-only login/register

In your login/register controller actions, before rendering the form:

```php
if (config_item('force_sso')) {
    $redirectTo = $this->input->get('redirect') ?: null;
    redirect($sso->getLoginUrl($redirectTo));
}
```

## Custom redirect storage

The default `SessionRedirectStore` wraps CI3's session library. Implement
`Ci3Open\Sso\Storage\RedirectStoreInterface` (`put()` / `pullAndClear()`) to
back it with something else (e.g. a signed cookie) if needed.

## Custom HTTP transport

The client uses a cURL-based transport by default. Swap it by implementing
`Ci3Open\Sso\Http\HttpClientInterface` and passing it as the third
constructor argument to `SsoClient`.

## Errors

- `Ci3Open\Sso\Exception\SsoException` — base class, also thrown on
  transport-level failures (network error, timeout, cURL init failure).
- `Ci3Open\Sso\Exception\TokenExchangeException` — the provider rejected the
  token, or returned a response with no usable email. Surfaced as
  `CallbackResult::failure($message)` from `handleCallback()`, not thrown.

## Reference

This SDK was extracted from a CI3 app's `Oauth` controller after diagnosing
a bug where the post-login redirect target was lost — see the "Why a
redirect store abstraction?" section above for the root cause.
