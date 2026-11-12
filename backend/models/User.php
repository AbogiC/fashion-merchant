<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * User model – all access to the users table goes through here so SQL is
 * centralized and always parameterized.
 */
class User
{
    /** Maximum login attempts before the account is temporarily locked. */
    public const MAX_FAILED_ATTEMPTS = 5;

    public static function findByEmail(string $email): ?array
    {
        $row = Database::query(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [strtolower($email)],
        )->fetch();

        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $row = Database::query('SELECT * FROM users WHERE id = ? LIMIT 1', [$id])->fetch();
        return $row ?: null;
    }

    public static function findByGoogleId(string $googleId): ?array
    {
        $row = Database::query(
            'SELECT * FROM users WHERE google_id = ? LIMIT 1',
            [$googleId],
        )->fetch();

        return $row ?: null;
    }

    public static function create(string $name, string $email, string $passwordHash): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)',
        )->execute([$name, $email, $passwordHash]);

        return (int) $pdo->lastInsertId();
    }

    /** Create an account that signs in exclusively with Google (no password). */
    public static function createFromGoogle(
        string $name,
        string $email,
        string $googleId,
        ?string $avatarUrl = null,
    ): int {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO users (name, email, google_id, avatar_url)
             VALUES (?, ?, ?, ?)',
        )->execute([$name, $email, $googleId, $avatarUrl]);

        return (int) $pdo->lastInsertId();
    }

    /** Attach a Google identity to an existing password account. */
    public static function linkGoogle(int $id, string $googleId, ?string $avatarUrl = null): void
    {
        Database::query(
            'UPDATE users SET google_id = ?, avatar_url = COALESCE(?, avatar_url) WHERE id = ?',
            [$googleId, $avatarUrl, $id],
        );
    }

    /** Refresh the profile fields Google owns on every sign-in. */
    public static function updateGoogleProfile(int $id, string $name, ?string $avatarUrl = null): void
    {
        Database::query(
            'UPDATE users SET name = ?, avatar_url = COALESCE(?, avatar_url) WHERE id = ?',
            [$name, $avatarUrl, $id],
        );
    }

    public static function updateLastLogin(int $id): void
    {
        Database::query(
            'UPDATE users
                SET last_login_at = NOW(),
                    failed_login_attempts = 0,
                    locked_until = NULL
              WHERE id = ?',
            [$id],
        );
    }

    /** Record a failed login and lock the account if the threshold is reached. */
    public static function registerFailedAttempt(int $id): void
    {
        Database::query(
            'UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?',
            [$id],
        );
        Database::query(
            'UPDATE users
                SET locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
              WHERE id = ? AND failed_login_attempts >= ?',
            [$id, self::MAX_FAILED_ATTEMPTS],
        );
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        Database::query(
            'UPDATE users SET password_hash = ? WHERE id = ?',
            [$passwordHash, $id],
        );
    }

    /** Strip sensitive columns before returning a user to the client. */
    public static function public(array $user): array
    {
        return [
            'id'           => (int) $user['id'],
            'name'         => $user['name'],
            'email'        => $user['email'],
            'role'         => $user['role'] ?? 'user',
            'avatar_url'   => $user['avatar_url'] ?? null,
            'has_password' => !empty($user['password_hash']),
            'has_google'   => !empty($user['google_id']),
            'created_at'   => $user['created_at'] ?? null,
        ];
    }
}
