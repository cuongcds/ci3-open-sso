<?php

declare(strict_types=1);

namespace Ci3Open\Sso\Storage;

/**
 * Default store, backed by CodeIgniter's session library
 * (`$CI->load->library('session')` must already be loaded by the caller).
 *
 * Usage: new SessionRedirectStore(get_instance()->session)
 */
final class SessionRedirectStore implements RedirectStoreInterface
{
    /**
     * @param object $ciSession the CI_Session instance (duck-typed: only
     *                          set_userdata/userdata/unset_userdata are used)
     */
    public function __construct(
        private readonly object $ciSession,
        private readonly string $sessionKey = 'sso_pending_redirect'
    ) {
    }

    public function put(string $redirectTo): void
    {
        if ($redirectTo === '') {
            $this->ciSession->unset_userdata($this->sessionKey);

            return;
        }

        $this->ciSession->set_userdata($this->sessionKey, $redirectTo);
    }

    public function pullAndClear(): ?string
    {
        $value = $this->ciSession->userdata($this->sessionKey);
        $this->ciSession->unset_userdata($this->sessionKey);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
