# Example: copying into an existing CI3 app

Everything under [application/](application/) here is meant to be copied
as-is into your app's own `application/` directory — nothing else. No demo
project, no vendored CodeIgniter core.

## Copy the files

After `composer require cuongcds/ci3-open-sso`, run the copy script the
package ships (make sure `$config['composer_autoload']` in
`application/config/config.php` points at `vendor/autoload.php` first):

```bash
composer require cuongcds/ci3-open-sso
php vendor/bin/install-files.php
```

This copies `application/config/open-sso.php`,
`application/controllers/Opensso_oauth.php` and
`application/views/plugins/open-sso/` into your project's `application/`
directory, without overwriting files that already exist there (pass
`--force` to overwrite). Equivalent to, but safer than, doing it by hand:

```bash
cp sdks/ci3-open-sso/examples/application/config/open-sso.php application/config/open-sso.php
cp sdks/ci3-open-sso/examples/application/controllers/Opensso_oauth.php application/controllers/Opensso_oauth.php
cp -r sdks/ci3-open-sso/examples/application/views/plugins/open-sso application/views/plugins/open-sso
```

Then:

1. Fill in `application/config/open-sso.php` (`sso_provider_host`,
   `sso_client_id`, `sso_client_secret`).
2. Add routes for `login` and your callback URL in
   `application/config/routes.php`:

   ```php
   $route['login'] = 'opensso_oauth/login';
   $route['portal/oauth/callback'] = 'opensso_oauth/callback';
   ```

3. In `Opensso_oauth::callback()`, uncomment and adapt the block that maps
   `$result->user` to your own user table — the SDK only speaks the
   provider's protocol, it doesn't touch your users.

## What's here

- `application/config/open-sso.php` — this SDK's own config, kept separate
  from your app's `config.php` so it's a clean drop-in.
- `application/controllers/Opensso_oauth.php` — login/callback controller.
  Kept under the `Opensso_` prefix so it's distinguishable from your app's
  own controllers.
- `application/views/plugins/open-sso/error.php` — the only view the
  controller renders itself (a failed-login page); everything else is your
  own redirect targets (`login`, `portal`, ...).
