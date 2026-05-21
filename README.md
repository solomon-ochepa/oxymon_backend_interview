# Loan App API

A simple loan-management REST API built with **Laravel 13 / PHP 8.5**, used for interviewing
candidates on PHP, Laravel, and backend fundamentals.

Features token-based authentication (Laravel Sanctum), per-user loan ownership, request
validation, API Resources, and interactive Swagger/OpenAPI documentation.

---

## Stack

| | |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.5 |
| Database | SQLite (zero setup; `pdo_mysql` is also bundled if you want to switch) |
| Auth | Laravel Sanctum (personal access tokens) |
| API docs | `darkaonline/l5-swagger` (OpenAPI 3) |
| Runtime | Docker (PHP built-in server) |

---

## Setup with Docker (recommended)

Prerequisites: **Docker Desktop** running.

```bash
# 1. Build the image (installs PHP extensions + Composer dependencies)
docker compose build

# 2. Start the app (entrypoint runs key:generate, migrate, and generates Swagger docs)
docker compose up -d

# 3. Tail logs (optional)
docker compose logs -f app
```

The API is now available at **http://localhost:8000**.

```bash
# Stop
docker compose down

# Run any artisan command inside the container
docker compose exec app php artisan <command>
```

> **Note:** the project source is bind-mounted, so code edits are picked up live.
> `vendor/` is a named volume seeded from the image. If you change Composer
> dependencies, run `docker compose exec app composer require <pkg>` **and**
> rebuild (`docker compose build`) so the image stays reproducible.
>
> **Port 8000 gotcha:** if you also ran `php artisan serve` locally, that host
> process can shadow the container on port 8000. Clear it with
> `pkill -f "artisan serve"` if responses look wrong.

---

## Setup without Docker (alternative)

Prerequisites: PHP 8.5+, Composer.

```bash
composer install
cp .env.example .env          # if .env is missing
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan l5-swagger:generate
php artisan serve             # http://localhost:8000
```

---

## API Documentation (Swagger)

Interactive docs (with an **Authorize** button for the bearer token):

- **Swagger UI:** http://localhost:8000/api/documentation
- **OpenAPI JSON:** http://localhost:8000/docs

Regenerate after changing annotations:

```bash
docker compose exec app php artisan l5-swagger:generate
```

---

## Authentication

All `/api/loans*` endpoints require a Sanctum bearer token. This is an
API-only app — every `/api/*` request is treated as JSON, so auth failures
return a clean `401` (not a redirect).

```bash
# 1. Register (or POST /api/login with existing credentials)
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Ada","email":"ada@example.com","password":"Password123!","password_confirmation":"Password123!"}'
# -> { "user": {...}, "token": "<TOKEN>" }

# 2. Use the token on protected endpoints
curl http://localhost:8000/api/loans \
  -H "Authorization: Bearer <TOKEN>"
```

In Postman: **Authorization** tab → **Bearer Token** → paste the token.

---

## Endpoints

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/api/register` | public | Create user, returns user + token |
| POST | `/api/login` | public | Authenticate, returns user + token |
| GET | `/api/me` | bearer | Current authenticated user |
| POST | `/api/logout` | bearer | Revoke the current token |
| GET | `/api/loans` | bearer | All loans (paginated) |
| GET | `/api/loans/me` | bearer | Loans owned by the authenticated user |
| POST | `/api/loans` | bearer | Create a loan (owned by the caller) |
| GET | `/api/loans/{id}` | bearer | Retrieve a single loan |
| PUT/PATCH | `/api/loans/{id}` | bearer | Update a loan |
| DELETE | `/api/loans/{id}` | bearer | Delete a loan |

**Loan fields:** `user_id`, `amount`, `interest`, `term` (months),
`status` (`pending` \| `approved` \| `active` \| `paid` \| `rejected`, defaults to `pending`).

**User roles:** `users.role` is `user` (default) or `admin`. Not mass-assignable
via registration — admins are created via seeder/tinker.

---

## Resetting the database

Wipes all data and re-runs every migration (schema preserved):

```bash
docker compose exec app php artisan migrate:fresh --force
```

---

## Project layout

```
app/Http/Controllers/   AuthController, LoanController, Controller (OpenAPI base)
app/Http/Requests/      Register/Login + Store/UpdateLoan form requests (validation)
app/Http/Resources/     LoanResource (response shaping)
app/Http/Middleware/    ForceJsonRequest (forces JSON on /api/*)
app/Models/             User, Loan
bootstrap/app.php        Routing, middleware, JSON exception rendering
routes/api.php           All API routes
database/migrations/     Schema (loans, users.role, loans.user_id, Sanctum tokens)
Dockerfile, docker-compose.yml, docker/entrypoint.sh
```
