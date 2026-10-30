<?php

declare(strict_types=1);

namespace App\Utils;

use App\Core\Env;
use RuntimeException;

/**
 * Minimal, dependency-free HS256 JWT implementation.
 *
 * Tokens carry a "sub" (user id) plus standard iat/exp claims. Use
 * Jwt::issue() to create a token and Jwt::verify() to validate one and read
 * back its claims (returns null when invalid or expired).
 */
class Jwt
{
    public static function issue(array $payload, int $ttl): string
    {
        $now = time();
        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $ttl,
        ]);

        $header  = ['alg' => 'HS256', 'typ' => 'JWT'];
        $segments = [
            self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];
        $signingInput = implode('.', $segments);
        $signature = self::base64UrlEncode(
            hash_hmac('sha256', $signingInput, self::secret(), true),
        );

        return $signingInput . '.' . $signature;
    }

    public static function verify(?string $token): ?array
    {
        if ($token === null || $token === '') {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$headerEncoded, $payloadEncoded, $signature] = $parts;

        $expected = self::base64UrlEncode(
            hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::secret(), true),
        );
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        if (!is_array($payload)) {
            return null;
        }

        $exp = $payload['exp'] ?? 0;
        if (!is_int($exp) || $exp < time()) {
            return null;
        }

        return $payload;
    }

    private static function secret(): string
    {
        $secret = Env::get('JWT_SECRET', '');
        if ($secret === '') {
            throw new RuntimeException('JWT_SECRET is not configured. Set it in backend/.env');
        }
        if (strlen($secret) < 32) {
            throw new RuntimeException('JWT_SECRET must be at least 32 characters long.');
        }
        return $secret;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
