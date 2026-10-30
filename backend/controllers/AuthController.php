<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
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

        if (!Hash::verify($data['password'], $user['password_hash'])) {
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
}
