# AIRO Travel Insurance Quotation App

A Laravel API with a Blade frontend that calculates travel insurance quotes. Users authenticate with their own credentials via JWT, submit traveller details, and receive a calculated premium stored in the database.

---

## Stack

- **Backend:** Laravel 13, PHP 8.3, MySQL 8.4
- **Auth:** `php-open-source-saver/jwt-auth` (user-level JWT)
- **Frontend:** Blade + vanilla JS + plain CSS (no build pipeline)
- **Infrastructure:** Docker via Laravel Sail

---

## How It Works

### Authentication
The app uses **user-level JWT authentication**. Each user logs in with their own credentials and receives a personal token scoped to their identity. The quotation form is gated behind this login step — there is no public access to the API.

### Quotation Flow
1. User visits `/` and logs in with email + password
2. On success, a JWT token is stored in `localStorage`
3. User fills in the quotation form (traveller ages, currency, travel dates)
4. The frontend sends `POST /api/quotation` with the token in the `Authorization` header
5. The API calculates the premium, persists the record, and returns the result
6. The result is displayed on the page — no page reload

### Premium Formula

```
Total = SUM over each traveller: 3 × age_load × trip_length_in_days
Trip length = (end_date - start_date) + 1  (inclusive)
```

| Age Range | Load |
|-----------|------|
| 18–30     | 0.6  |
| 31–40     | 0.7  |
| 41–50     | 0.8  |
| 51–60     | 0.9  |
| 61–70     | 1.0  |

**Example:** ages `28,35`, dates `2020-10-01` to `2020-10-30` (30 days), currency `EUR`
```
(3 × 0.6 × 30) + (3 × 0.7 × 30) = 54 + 63 = 117.00 EUR
```

---

## Project Structure

```
app/
  Http/
    Controllers/Api/
      AuthController.php        — POST /api/auth/login
      QuotationController.php   — POST /api/quotation
    Requests/
      StoreQuotationRequest.php — validation rules + custom age range check
  Models/
    Quotation.php
    User.php
  Services/
    QuotationCalculator.php     — age load + trip length business logic

database/
  migrations/
    xxxx_create_quotations_table.php
  seeders/
    UserSeeder.php
    DatabaseSeeder.php

routes/
  api.php                       — /auth/login + /quotation (JWT protected)
  web.php                       — / → quotation Blade view

resources/views/
  quotation.blade.php           — HTML structure only

public/
  css/quotation.css             — all styles
  js/quotation.js               — all JS (fetch, token management, form logic)

bootstrap/
  app.php                       — API JSON exception handler + route registration
```

---

## Getting Started (after cloning)

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) or [Rancher Desktop](https://rancherdesktop.io/) (set to **dockerd/moby** engine)
- [Composer](https://getcomposer.org/)

### 1. Install PHP dependencies

```bash
composer install
```

### 2. Set up environment

```bash
cp .env.example .env
```

Key values to set in `.env`:

| Variable | Value | Note |
|----------|-------|------|
| `APP_NAME` | `"Airo App"` | Quotes required — value has a space |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST` | `mysql` | |
| `DB_DATABASE` | `laravel` | |
| `DB_USERNAME` | `sail` | |
| `DB_PASSWORD` | `password` | |
| `FORWARD_DB_PORT` | `3340` | Only if port 3306 is already in use |
| `FORWARD_REDIS_PORT` | `6399` | Only if port 6379 is already in use |

### 3. Generate app key

```bash
./vendor/bin/sail artisan key:generate
```

### 4. Start containers

```bash
./vendor/bin/sail up -d
```

All containers should show **running** in Docker/Rancher Desktop.

### 5. Generate JWT secret

```bash
./vendor/bin/sail artisan jwt:secret
```

### 6. Run migrations and seed

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

This creates the `users` and `quotations` tables and seeds the login user.

### 7. Open the app

Visit [http://localhost](http://localhost)

---

## Login Credentials

The seeder creates one user:

| Field    | Value                     |
|----------|---------------------------|
| Email    | `valentino@trencev.com`   |
| Password | `valentino123`            |

---

## API Reference

All routes are prefixed with `/api`.

### `POST /api/auth/login`

Public — no token required.

**Request:**
```json
{ "email": "valentino@trencev.com", "password": "valentino123" }
```

**Response `200`:**
```json
{ "token": "<jwt>", "token_type": "bearer" }
```

**Response `401`:** Invalid credentials
```json
{ "message": "Invalid credentials" }
```

---

### `POST /api/quotation`

Protected — requires `Authorization: Bearer <token>` header.

**Request:**
```json
{
  "age": "28,35",
  "currency_id": "EUR",
  "start_date": "2025-06-01",
  "end_date": "2025-06-30"
}
```

| Field | Rules |
|-------|-------|
| `age` | Required. Comma-separated integers, each between 18 and 70 |
| `currency_id` | Required. One of: `EUR`, `GBP`, `USD` |
| `start_date` | Required. Format: `YYYY-MM-DD`. Must be before or equal to `end_date` |
| `end_date` | Required. Format: `YYYY-MM-DD`. Must be after or equal to `start_date` |

**Response `201`:**
```json
{
  "quotation_id": 1,
  "total": 117.00,
  "currency_id": "EUR"
}
```

**Error responses:**

| Status | Meaning |
|--------|---------|
| `401` | Missing or invalid JWT token |
| `422` | Validation failure — field-level errors returned in `errors` object |
| `500` | Unexpected server error |

---

## Running Commands Inside Sail

All `artisan` and `composer` commands must run inside the Sail container:

```bash
./vendor/bin/sail artisan <command>
./vendor/bin/sail composer <command>
```

**Common commands:**

```bash
# Re-run all migrations and seed from scratch
./vendor/bin/sail artisan migrate:fresh --seed

# Check registered routes
./vendor/bin/sail artisan route:list

# Open a PHP REPL inside the container
./vendor/bin/sail artisan tinker
```
