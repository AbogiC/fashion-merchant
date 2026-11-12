<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Utils\GoogleOAuth;
use App\Utils\Hash;
use App\Utils\Jwt;
use App\Utils\Validator;

/**
 * Authentication endpoints: register, login, logout, refresh and me.
 *
 * Authentication is token based (JWT) stored in HttpOnly cookies so the SPA
 * never handles the raw token in JavaScript, which mitigates token theft via XSS.
 */
class AuthController
{
    /** Set both the short-lived access token and the long-lived refresh token. */
    private function issueTokens(array $user): void
    {
        $accessTtl  = Env::int('JWT_ACCESS_TTL', 3600);       // 1 hour
        $refreshTtl = Env::int('JWT_REFRESH_TTL', 604800);   // 7 days
        $secure    = Env::get('APP_ENV', 'development') === 'production';

        $claims = [
            'sub'   => (int) $user['id'],
            'email' => $user['email'],
            'name'  => $user['name'],
            'role'  => $user['role'] ?? 'user',
        ];

        $access = Jwt::issue($claims, $accessTtl);
        $refresh = Jwt::issue(['sub' => (int) $user['id'], 'type' => 'refresh'], $refreshTtl);

        // Access cookie is sent to all API routes.
        Response::setCookie('access_token', $access, $accessTtl, [
            'secure'   => $secure,
            'samesite' => 'Lax',
        ]);
        // Refresh cookie is scoped to the auth refresh route only.
        Response::setCookie('refresh_token', $refresh, $refreshTtl, [
            'secure'   => $secure,
            'samesite' => 'Lax',
            'path'     => '/api/auth',
        ]);
    }

    /** POST /api/auth/register */
    public function register(Request $request): void
    {
        $data = $request->body();

        $v = Validator::make($data)
            ->required('name')->max('name', 100)
            ->required('email')->email('email')->max('email', 255)
            ->required('password')->min('password', 8)->max('password', 72)
            ->confirmed('password', 'password_confirmation');

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        $email = strtolower(trim($data['email']));
        $name = trim($data['name']);

        if (User::findByEmail($email) !== null) {
            Response::error('An account with this email already exists.', 409);
        }

        $id = User::create($name, $email, Hash::make($data['password']));
        $user = User::findById($id);

        $this->issueTokens($user);
        Response::json(['message' => 'Account created.', 'user' => User::public($user)], 201);
    }

    /** POST /api/auth/login */
    public function login(Request $request): void
    {
        $data = $request->body();

        $v = Validator::make($data)
            ->required('email')->email('email')
            ->required('password');

        if ($v->fails()) {
            Response::error('The given data was invalid.', 422, $v->errors());
        }

        $email = strtolower(trim($data['email']));
        $user = User::findByEmail($email);

        // Use a single generic error so attackers cannot enumerate accounts.
        if ($user === null) {
            Response::error('These credentials do not match our records.', 401);
        }

        // Temporary lockout after too many failed attempts.
        if (!empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time()) {
            Response::error('Too many failed attempts. Please try again in a few minutes.', 423);
        }

        // Google-only accounts have no password hash and cannot use this endpoint.
        if (empty($user['password_hash']) || !Hash::verify($data['password'], $user['password_hash'])) {
            User::registerFailedAttempt((int) $user['id']);
            Response::error('These credentials do not match our records.', 401);
        }

        // Upgrade the stored hash if PHP now recommends a stronger algorithm.
        if (Hash::needsRehash($user['password_hash'])) {
            User::updatePassword((int) $user['id'], Hash::make($data['password']));
        }

        User::updateLastLogin((int) $user['id']);
        $this->issueTokens($user);

        Response::json(['message' => 'Logged in.', 'user' => User::public($user)]);
    }

    /** POST /api/auth/logout */
    public function logout(Request $request): void
    {
        $secure = Env::get('APP_ENV', 'development') === 'production';

        Response::clearCookie('access_token', ['secure' => $secure, 'samesite' => 'Lax']);
        Response::clearCookie('refresh_token', [
            'secure'   => $secure,
            'samesite' => 'Lax',
            'path'     => '/api/auth',
        ]);

        Response::json(['message' => 'Logged out.']);
    }

    /** POST /api/auth/refresh — exchange a valid refresh token for a new access token. */
    public function refresh(Request $request): void
    {
        $payload = Jwt::verify($request->cookie('refresh_token'));

        if ($payload === null || ($payload['type'] ?? '') !== 'refresh') {
            Response::error('Invalid or expired refresh token.', 401);
        }

        $user = User::findById((int) $payload['sub']);
        if ($user === null) {
            Response::error('Invalid or expired refresh token.', 401);
        }

        $this->issueTokens($user);
        Response::json(['message' => 'Token refreshed.', 'user' => User::public($user)]);
    }

    /** GET /api/auth/me — return the authenticated user. */
    public function me(Request $request): void
    {
        $claims = $request->user() ?? [];
        $user = User::findById((int) ($claims['sub'] ?? 0));

        if ($user === null) {
            Response::error('User not found.', 404);
        }

        Response::json(['user' => User::public($user)]);
    }

    /**
     * GET /api/auth/google/redirect
     * Start the OAuth flow: store a single-use state and send the user to Google.
     */
    public function googleRedirect(Request $request): void
    {
        header('Cache-Control: no-store');

        if (!GoogleOAuth::isConfigured()) {
            $this->googleError('Google sign-in is not configured.');
        }

        $state = bin2hex(random_bytes(32));

        Response::setCookie('google_oauth_state', $state, 600, [
            'secure'   => Env::get('APP_ENV', 'development') === 'production',
            'samesite' => 'Lax',
            'path'     => $this->authCookiePath(),
        ]);

        Response::redirect(GoogleOAuth::authUrl($state));
    }

    /**
     * GET /api/auth/google/callback
     * Validate the state, exchange the code and sign the user in.
     */
    public function googleCallback(Request $request): void
    {
        header('Cache-Control: no-store');

        $secure = Env::get('APP_ENV', 'development') === 'production';
        $cookieOptions = [
            'secure'   => $secure,
            'samesite' => 'Lax',
            'path'     => $this->authCookiePath(),
        ];

        $expectedState = (string) $request->cookie('google_oauth_state', '');
        $state = (string) $request->query('state', '');
        Response::clearCookie('google_oauth_state', $cookieOptions);

        if ($request->query('error') !== null) {
            $this->googleError('Google sign-in was cancelled.');
        }

        if (
            $expectedState === ''
            || $state === ''
            || !hash_equals($expectedState, $state)
        ) {
            $this->googleError('The sign-in request could not be verified. Please try again.');
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            $this->googleError('Google did not return an authorization code.');
        }

        try {
            $tokens = GoogleOAuth::exchangeCode($code);
            $profile = GoogleOAuth::userInfo((string) $tokens['access_token']);
        } catch (\Throwable $e) {
            error_log('Google OAuth error: ' . $e->getMessage());
            $this->googleError('Unable to complete Google sign-in. Please try again.');
        }

        $googleId = (string) ($profile['sub'] ?? '');
        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        $emailVerified = filter_var(
            $profile['email_verified'] ?? false,
            FILTER_VALIDATE_BOOLEAN,
        );
        $name = trim((string) ($profile['name'] ?? ''));
        $avatar = isset($profile['picture']) ? (string) $profile['picture'] : null;

        if ($googleId === '' || $email === '' || !$emailVerified) {
            $this->googleError('Google did not provide a verified email address.');
        }

        if ($name === '') {
            $name = (string) strstr($email, '@', true);
        }

        $user = User::findByGoogleId($googleId);

        if ($user === null) {
            $existing = User::findByEmail($email);

            if ($existing !== null) {
                if (!empty($existing['google_id']) && $existing['google_id'] !== $googleId) {
                    $this->googleError('This email is already linked to another Google account.');
                }
                User::linkGoogle((int) $existing['id'], $googleId, $avatar);
                $user = User::findById((int) $existing['id']);
            } else {
                $id = User::createFromGoogle($name, $email, $googleId, $avatar);
                $user = User::findById($id);
            }
        } else {
            User::updateGoogleProfile((int) $user['id'], $name, $avatar);
            $user = User::findById((int) $user['id']);
        }

        if ($user === null) {
            $this->googleError('Unable to create your account.');
        }

        User::updateLastLogin((int) $user['id']);
        $this->issueTokens($user);

        Response::redirect($this->frontendUrl('/login?oauth=success'));
    }

    /** Send the browser back to the frontend with a human-readable error. */
    private function googleError(string $message): never
    {
        $query = http_build_query(['oauth' => 'error', 'message' => $message]);
        Response::redirect($this->frontendUrl('/login?' . $query));
    }

    private function frontendUrl(string $path): string
    {
        return rtrim(Env::get('FRONTEND_URL', 'http://localhost:5173'), '/') . $path;
    }

    private function authCookiePath(): string
    {
        return rtrim(Env::get('API_PREFIX', '/api'), '/') . '/auth';
    }
}
