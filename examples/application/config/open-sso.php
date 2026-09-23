<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Config for Ci3Open\Sso — read by Opensso_oauth (see ../controllers/Opensso_oauth.php).
 * Fill in real values, or override per-environment as usual for CI3 config files.
 */
$config['sso_provider_host'] = '';
$config['sso_client_id'] = '';
$config['sso_client_secret'] = '';

// If true, login/register actions should redirect straight to SSO instead of
// showing a local form. Check this yourself where relevant — the SDK doesn't.
$config['sso_force'] = false;
