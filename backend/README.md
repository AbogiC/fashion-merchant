# Fashion Merchant – Backend API

A small, dependency-free PHP 8 + MySQL authentication API with a middleware
pipeline. It runs without Composer thanks to a tiny PSR-4 style autoloader.

## Requirements

- PHP **8.0+** (uses `str_contains`, `str_starts_with`, named args)
- A MySQL / MariaDB server
- The PDO MySQL extension (enabled by default in most PHP builds)

## Project structure

```
backend/
├── public/              Web document root (the ONLY folder served to the web)
│   ├── index.php        Front controller / router entry point
│   └── .htaccess        Rewrites all requests to index.php
├── core/                Framework primitives (Router, Request, Response, Database, Env)
├── middleware/          Reusable request middleware (security focused)
│   ├── CorsMiddleware.php
│   ├── SecurityHeadersMiddleware.php
│   ├── EnsureAjaxMiddleware.php   (CSRF header guard)
│   ├── RateLimitMiddleware.php    (brute-force protection)
│   └── AuthMiddleware.php         (requires a valid JWT)
├── controllers/         Request handlers
├── models/              Data access (parameterized queries only)
├── utils/               JWT, password hashing, validation
├── routes/api.php       Route definitions + per-route middleware
├── database/schema.sql  The users table
└── storage/             Runtime data (rate-limit buckets, logs) – not web accessible
```

## Security layers

1. **CORS** – explicit allowed origin + credentials for the SPA.
2. **Security headers** – `X-Content-Type-Options`, `X-Frame-Options`, etc.
3. **AJAX / CSRF guard** – state-changing methods must send an `X-Requested-With`
   header, which cross-site form posts cannot set without a CORS preflight.
4. **Rate limiting** – `register` / `login` / `refresh` are throttled per IP.
5. **Auth middleware** – protected routes require a valid JWT access token.
6. **Account lockout** – after 5 failed logins the account is locked for 15 min.
7. **HttpOnly, SameSite cookies** – tokens never touch JavaScript, mitigating
   token theft via XSS. Passwords are hashed with PHP's `password_hash()`.

## Endpoints

| Method | Path             | Auth        | Description                      |
|--------|------------------|-------------|----------------------------------|
| POST   | `/api/auth/register` | guest  | Create an account + issue tokens |
| POST   | `/api/auth/login`    | guest  | Authenticate + issue tokens      |
| POST   | `/api/auth/refresh`  | refresh cookie | New access token           |
| POST   | `/api/auth/logout`   | logged in | Clear auth cookies           |
| GET    | `/api/auth/me`       | logged in | Current user profile         |
| GET    | `/api/auth/google/redirect` | guest | Start Google OAuth flow |
| GET    | `/api/auth/google/callback` | guest | Google OAuth callback (sets cookies) |

## Google sign-in

Google OAuth uses the server-side Authorization Code flow, so the client
secret stays on the server and the SPA only receives the usual HttpOnly
session cookies.

1. Open the [Google Cloud Console](https://console.cloud.google.com/) and
   create (or select) a project.
2. Configure the **OAuth consent screen** (External is fine for testing; add
   your own Google account under *Test users* while the app is unverified).
3. Go to **APIs & Services → Credentials → Create credentials → OAuth client ID**,
   choose **Web application**, and add an **Authorized redirect URI**:
   - Local: `http://localhost:8000/api/auth/google/callback`
   - Production: `https://api.your-domain.com/api/auth/google/callback`
4. Copy the **Client ID** and **Client secret** into `backend/.env`:
   ```env
   GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=xxxxxxxx
   GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
   FRONTEND_URL=http://localhost:5173
   ```
   `GOOGLE_REDIRECT_URI` must match the value registered in Google Cloud
   exactly. `FRONTEND_URL` is where the user is sent after sign-in.

The first Google sign-in creates a passwordless account (or links Google to an
existing account with the same verified email). Users can then sign in with
either method.

### Troubleshooting Google sign-in

- **`redirect_uri_mismatch`** – the redirect URI registered in Google Cloud must
  match `GOOGLE_REDIRECT_URI` character-for-character (`http`, `localhost`,
  port `8000`, no trailing slash).
- **`Unable to complete Google sign-in`** – check the PHP error log. A common
  cause on Windows is `unable to get local issuer certificate`, meaning PHP has
  no CA store. Download <https://curl.se/ca/cacert.pem> to
  `backend/storage/cacert.pem` (used automatically), or set `GOOGLE_CA_BUNDLE`
  / `curl.cainfo` in `php.ini`.
- **`invalid_client`** – the client ID/secret pair in `.env` is wrong or belongs
  to a different OAuth client.

## Setup

1. **Create the database** (run the schema):
   ```bash
   mysql -u root -p < backend/database/schema.sql
   ```
   Upgrading an existing database? Run the Google migration once:
   ```bash
   mysql -u root -p fashion_merchant < backend/database/migrations/2026_11_12_add_google_oauth.sql
   ```
2. **Configure environment:**
   ```bash
   cp backend/.env.example backend/.env
   ```
   Edit `DB_*` credentials and set a strong `JWT_SECRET`
   (`php -r "echo bin2hex(random_bytes(32));"` to generate one), then fill in
   the Google OAuth values described above.
3. **Serve the API** with PHP's built-in server (development only):
   ```bash
   php -S localhost:8000 -t backend/public
   ```
   For production, point Apache/Nginx's document root at `backend/public/`.

The Vue dev server proxies `/api` to `http://localhost:8000` (see
`frontend/vite.config.js`), so the frontend calls `/api/auth/...` as same-origin.
