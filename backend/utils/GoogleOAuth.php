<?php

declare(strict_types=1);

namespace App\Utils;

use App\Core\Env;
use RuntimeException;

/**
 * Minimal Google OAuth 2.0 (OpenID Connect) client.
 *
 * Implements the server-side Authorization Code flow so the client secret never
 * reaches the browser. After Google redirects back with a code we exchange it
 * for tokens and read the verified profile from the userinfo endpoint.
 */
class GoogleOAuth
{
    private const AUTH_URL     = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL    = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public static function isConfigured(): bool
    {
        return Env::get('GOOGLE_CLIENT_ID') !== ''
            && Env::get('GOOGLE_CLIENT_SECRET') !== '';
    }

    public static function redirectUri(): string
    {
        $uri = Env::get('GOOGLE_REDIRECT_URI', '');
        if ($uri !== '') {
            return $uri;
        }

        return rtrim(Env::get('APP_URL', 'http://localhost:8000'), '/')
            . rtrim(Env::get('API_PREFIX', '/api'), '/')
            . '/auth/google/callback';
    }

    /** Build the Google consent screen URL. */
    public static function authUrl(string $state): string
    {
        $params = [
            'client_id'     => Env::get('GOOGLE_CLIENT_ID'),
            'redirect_uri'  => self::redirectUri(),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ];

        return self::AUTH_URL . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /** Exchange an authorization code for an access token. */
    public static function exchangeCode(string $code): array
    {
        $body = http_build_query([
            'code'          => $code,
            'client_id'     => Env::get('GOOGLE_CLIENT_ID'),
            'client_secret' => Env::get('GOOGLE_CLIENT_SECRET'),
            'redirect_uri'  => self::redirectUri(),
            'grant_type'    => 'authorization_code',
        ], '', '&', PHP_QUERY_RFC3986);

        $data = json_decode(self::request(self::TOKEN_URL, $body, true), true);

        if (!is_array($data) || empty($data['access_token'])) {
            throw new RuntimeException('Google token exchange failed.');
        }

        return $data;
    }

    /** Read the authenticated user's profile from Google. */
    public static function userInfo(string $accessToken): array
    {
        $data = json_decode(
            self::request(self::USERINFO_URL, null, false, $accessToken),
            true,
        );

        if (!is_array($data) || empty($data['sub'])) {
            throw new RuntimeException('Unable to read the Google profile.');
        }

        return $data;
    }

    private static function request(
        string $url,
        ?string $body,
        bool $post,
        ?string $accessToken = null,
    ): string {
        $headers = ['Accept: application/json'];
        if ($post) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        if ($accessToken !== null) {
            $headers[] = 'Authorization: Bearer ' . $accessToken;
        }

        $caBundle = self::caBundle();

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POST           => $post,
            ]);
            if ($caBundle !== null) {
                curl_setopt($ch, CURLOPT_CAINFO, $caBundle);
            }
            if ($post && $body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $result = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            if ($result === false || $status < 200 || $status >= 300) {
                throw new RuntimeException(
                    'Google API request failed (HTTP ' . $status . ').'
                    . self::errorDetail($result === false ? $curlError : (string) $result),
                );
            }

            return (string) $result;
        }

        // Fallback for environments without the cURL extension.
        $ssl = [];
        if ($caBundle !== null) {
            $ssl['cafile'] = $caBundle;
        }

        $context = stream_context_create([
            'http' => [
                'method'        => $post ? 'POST' : 'GET',
                'header'        => implode("\r\n", $headers),
                'content'       => $post ? (string) $body : '',
                'timeout'       => 15,
                'ignore_errors' => true,
            ],
            'ssl' => $ssl,
        ]);

        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            throw new RuntimeException('Google API request failed.');
        }

        $status = 0;
        if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
            $status = (int) $m[1];
        }

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException(
                'Google API request failed (HTTP ' . $status . ').' . self::errorDetail((string) $result),
            );
        }

        return $result;
    }

    /**
     * Locate a CA bundle for TLS verification.
     *
     * Windows PHP builds often ship without a CA store, which makes cURL fail
     * with "unable to get local issuer certificate". Prefer an explicit env
     * path, then the project-local bundle, then PHP's own configuration.
     */
    private static function caBundle(): ?string
    {
        foreach ([Env::get('GOOGLE_CA_BUNDLE'), Env::get('CURL_CA_BUNDLE')] as $path) {
            if ($path !== '' && is_file($path)) {
                return $path;
            }
        }

        $bundled = dirname(__DIR__) . '/storage/cacert.pem';
        return is_file($bundled) ? $bundled : null;
    }

    /** Extract Google's error code/description without leaking anything sensitive. */
    private static function errorDetail(string $body): string
    {
        $data = json_decode($body, true);
        if (is_array($data)) {
            $detail = (string) ($data['error'] ?? '');
            if (isset($data['error_description'])) {
                $detail .= ($detail !== '' ? ': ' : '') . $data['error_description'];
            }
            if ($detail !== '') {
                return ' ' . $detail;
            }
        }

        $body = trim($body);
        return $body === '' ? '' : ' ' . substr($body, 0, 300);
    }
}
