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

## Setup

1. **Create the database** (run the schema):
   ```bash
   mysql -u root -p < backend/database/schema.sql
   ```
2. **Configure environment:**
   ```bash
   cp backend/.env.example backend/.env
   ```
   Edit `DB_*` credentials and set a strong `JWT_SECRET`
   (`php -r "echo bin2hex(random_bytes(32));"` to generate one).
3. **Serve the API** with PHP's built-in server (development only):
   ```bash
   php -S localhost:8000 -t backend/public
   ```
   For production, point Apache/Nginx's document root at `backend/public/`.

The Vue dev server proxies `/api` to `http://localhost:8000` (see
`frontend/vite.config.js`), so the frontend calls `/api/auth/...` as same-origin.
