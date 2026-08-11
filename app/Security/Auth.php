<?php

declare(strict_types=1);

namespace App\Security;

use App\Support\Response;
use RuntimeException;

final class Auth
{
    public function __construct(private readonly array $config)
    {
    }

    public function authorizationUrl(): string
    {
        $client = $this->client();
        $_SESSION['oauth_state'] = bin2hex(random_bytes(32));
        $client->setState($_SESSION['oauth_state']);
        return $client->createAuthUrl();
    }

    public function authenticate(string $code, ?string $state): void
    {
        if (!is_string($state) || !isset($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
            throw new RuntimeException('Invalid OAuth state.');
        }
        unset($_SESSION['oauth_state']);

        $client = $this->client();
        $token = $client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            throw new RuntimeException('OAuth token exchange failed.');
        }

        $identity = $client->verifyIdToken($token['id_token'] ?? '');
        if (!is_array($identity)) {
            throw new RuntimeException('Invalid identity token.');
        }

        $issuer = $identity['iss'] ?? '';
        $audience = $identity['aud'] ?? '';
        $email = strtolower(trim((string) ($identity['email'] ?? '')));
        $verified = filter_var($identity['email_verified'] ?? false, FILTER_VALIDATE_BOOL);

        if (!in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)
            || $audience !== (string) ($this->config['google_client_id'] ?? '')
            || (int) ($identity['exp'] ?? 0) <= time()
            || $email === ''
            || !$verified
            || !in_array($email, $this->adminEmails(), true)
        ) {
            throw new RuntimeException('Unauthorized Google identity.');
        }

        session_regenerate_id(true);
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_sub'] = (string) ($identity['sub'] ?? '');
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['admin_email'], $_SESSION['admin_sub']);
    }

    public function requireAdmin(): void
    {
        if (!$this->isAuthenticated()) {
            Response::redirect('/admin/login');
        }

        if (!in_array(strtolower((string) $_SESSION['admin_email']), $this->adminEmails(), true)) {
            Response::text('Forbidden.', 403);
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
    }

    private function client(): object
    {
        if (!class_exists(\Google\Client::class)) {
            throw new RuntimeException('Google API Client is not installed.');
        }

        $client = new \Google\Client();
        $client->setClientId((string) ($this->config['google_client_id'] ?? ''));
        $client->setClientSecret((string) ($this->config['google_client_secret'] ?? ''));
        $client->setRedirectUri((string) ($this->config['google_redirect_uri'] ?? ''));
        $client->setScopes(['openid', 'email', 'profile']);
        return $client;
    }

    private function adminEmails(): array
    {
        return array_values(array_filter(array_map(
            static fn (string $email): string => strtolower(trim($email)),
            explode(',', (string) ($this->config['admin_emails'] ?? ''))
        )));
    }
}
