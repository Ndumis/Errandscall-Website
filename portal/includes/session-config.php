<?php
// Starts a session with hardened cookie attributes (HttpOnly, SameSite,
// and Secure when served over HTTPS). Use this everywhere instead of
// calling session_start() directly so all session cookies get the same
// protections.
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) == 443)
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => $is_https,
        ]);

        session_start();
    }
}
