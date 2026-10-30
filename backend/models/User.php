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

    public static function create(string $name, string $email, string $passwordHash): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)',
        )->execute([$name, $email, $passwordHash]);

        return (int) $pdo->lastInsertId();
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
            'id'         => (int) $user['id'],
            'name'       => $user['name'],
            'email'      => $user['email'],
            'role'       => $user['role'] ?? 'user',
            'created_at' => $user['created_at'] ?? null,
        ];
    }
}
