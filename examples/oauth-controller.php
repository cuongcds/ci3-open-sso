<?php

/**
 * Example CI3 controller wiring for Ci3Open\Sso, mirroring the flow this SDK
 * was extracted from. Not autoloaded — copy the relevant parts into your
 * app's controller.
 */

defined('BASEPATH') or exit('No direct script access allowed');

use Ci3Open\Sso\SsoClient;
use Ci3Open\Sso\SsoConfig;
use Ci3Open\Sso\Storage\SessionRedirectStore;

require_once 'application/core/App_Controller.php';
require_once './vendor/autoload.php';

class Oauth extends App_Controller
{
    private SsoClient $sso;

    public function __construct()
    {
        parent::__construct();
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
            redirect('login');
        }

        $email = $result->user->email;
        $account = $this->user_model->getByEmail($email);

        if ($account == null) {
            $newUserId = $this->user_model->insert([
                'email' => strtolower($email),
                'name' => $result->user->name,
                'display_name' => $result->user->displayName ?? $result->user->name,
                'password' => null,
                'need_reset_password' => 0,
                'user_kind' => USER_KIND_CUSTOMER,
                'created_at' => time(),
            ]);
            $this->session->set_userdata(self::ACCOUNT_KEY, ['id' => $newUserId, 'user_kind' => USER_KIND_CUSTOMER]);
        } else {
            $this->session->set_userdata(self::ACCOUNT_KEY, ['id' => $account['id'], 'user_kind' => $account['user_kind']]);
        }

        $this->redirect($result->redirectTo ?: 'portal');
    }
}
