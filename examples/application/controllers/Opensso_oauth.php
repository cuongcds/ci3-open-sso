<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Ci3Open\Sso\SsoClient;
use Ci3Open\Sso\SsoConfig;
use Ci3Open\Sso\Storage\SessionRedirectStore;

/**
 * Wires Ci3Open\Sso into a CI3 app. "Opensso_" prefix keeps SDK-provided
 * controllers apart from the app's own controllers.
 *
 * Requires require_once FCPATH . 'vendor/autoload.php' to be loaded (e.g. via
 * $config['composer_autoload'] in application/config/config.php) and routes
 * for `login` and your callback URL — see ../config/routes-snippet.php.
 */
class Opensso_oauth extends CI_Controller
{
    private const ACCOUNT_KEY = 'app_account';

    private SsoClient $sso;

    public function __construct()
    {
        parent::__construct();
        $this->load->config('open-sso');
        $this->load->library('session');

        $config = new SsoConfig(
            providerHost: config_item('sso_provider_host'),
            clientId: config_item('sso_client_id'),
            clientSecret: config_item('sso_client_secret'),
            callbackUrl: base_url('portal/oauth/callback')
        );

        $this->sso = new SsoClient($config, new SessionRedirectStore($this->session));
    }

    public function login()
    {
        $redirectTo = $this->input->get('redirect') ?: null;
        redirect($this->sso->getLoginUrl($redirectTo));
    }

    public function callback()
    {
        $result = $this->sso->handleCallback($this->input->get('token'));

        if (!$result->success) {
            $this->session->set_flashdata('error_message', 'Lỗi khi đăng nhập qua SSO.');
            $this->load->view('plugins/open-sso/error', ['message' => $result->errorMessage]);
            return;
        }

        $email = $result->user->email;

        // Replace with your own user table lookup/insert.
        // $account = $this->User_model->getByEmail($email);
        // if ($account === null) {
        //     $accountId = $this->User_model->insert([
        //         'email' => strtolower($email),
        //         'name' => $result->user->name,
        //         'display_name' => $result->user->displayName ?? $result->user->name,
        //         'avatar' => $result->user->avatar,
        //     ]);
        // } else {
        //     $accountId = $account['id'];
        // }
        // $this->session->set_userdata(self::ACCOUNT_KEY, ['id' => $accountId]);

        redirect($result->redirectTo ?: 'portal');
    }
}
