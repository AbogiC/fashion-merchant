<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Password hashing helpers built on PHP's password_* API (bcrypt by default,
 * automatically upgraded when PHP supports Argon2).
 */
class Hash
{
    public static function make(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /** True when the hash uses an outdated algorithm and should be rehashed. */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
