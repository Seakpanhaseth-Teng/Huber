<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?style=for-the-badge&logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Sanctum-Auth-FF2D20?style=for-the-badge" alt="Sanctum">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=tailwindcss" alt="Tailwind CSS 3">
  <img src="https://img.shields.io/badge/Vite-6-646CFF?style=for-the-badge&logo=vite" alt="Vite 6">
</p>

<h1 align="center">Huber — Ride-Sharing Platform</h1>

<p align="center">
  A full-featured ride-sharing platform built with Laravel. Passengers can search and book rides; drivers can create rides, manage bookings, and track earnings; admins oversee the entire system.
</p>

---

## Table of Contents

- [Project Structure](#project-structure)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Security](#security)
- [License](#license)

---

## Project Structure

This project is split into **three independent Laravel installations** within a single monorepo:

```
huber/
├── api/          # API backend — Sanctum token auth, JSON endpoints
├── admin/        # Admin panel — Blade views, session auth
├── frontend/     # Passenger & driver web app — Blade views, session auth
```

Each app shares a similar codebase (models, controllers, migrations) but runs independently with its own `.env`, database config, and dependencies.

### Why three apps?

| App | Purpose | Auth |
|-----|---------|------|
| **api/** | Mobile app / third-party integration backend | Sanctum tokens |
| **admin/** | Admin dashboard for platform operators | Session + Admin guard |
| **frontend/** | Web app for passengers and drivers | Session |

---

## Features

### For Passengers
- **User registration & authentication** — Register as a passenger, log in/out
- **Ride search** — Search available rides by date, location, price range, departure time
- **Seat selection** — Interactive seat picker for shared rides
- **Booking** — Book shared (per-seat) or exclusive (whole-vehicle) rides with one-way or round-trip options
- **Payment** — Credit card and QR code payment support
- **Booking management** — View booking history, receipts, and confirmation details
- **Reviews & ratings** — Rate drivers on multiple criteria (overall, driver, vehicle, punctuality, safety, comfort)

### For Drivers
- **Driver registration** — Register with license, vehicle info, insurance documents
- **Document upload** — Upload driver license, vehicle registration, insurance, and vehicle photos
- **Ride management** — Create, edit, and manage rides (shared or exclusive, one-way or round-trip)
- **Earnings dashboard** — Track total earnings, completed rides, booking history
- **Customer management** — View passenger lists per ride with seat maps
- **Ride lifecycle** — Mark rides as ongoing or completed
- **Review visibility** — View all reviews and ratings received

### For Admins
- **Dashboard** — At-a-glance stats: total users, drivers, verified drivers, rides, earnings
- **User management** — CRUD for all users
- **Driver management** — CRUD for drivers, view driver stats and earnings
- **Ride management** — CRUD for all rides, view passenger lists per ride
- **Driver verification** — Approve/reject driver document submissions
- **Admin authentication** — Separate admin login system

---

## Tech Stack

### Backend
| Layer | Technology |
|-------|-----------|
| **Framework** | Laravel 11 |
| **Language** | PHP ^8.2 |
| **Database** | MySQL / MariaDB (SQLite for testing) |
| **API Auth** | Laravel Sanctum (token-based) |
| **Mail** | Resend |

### Frontend
| Layer | Technology |
|-------|-----------|
| **Template Engine** | Laravel Blade |
| **CSS Framework** | Tailwind CSS 3.4 (via Vite) |
| **Build Tool** | Vite 6 |
| **JavaScript** | Vanilla JS |

### Infrastructure
| Component | Technology |
|-----------|-----------|
| **Queue** | Database-backed |
| **Cache** | Database-backed |
| **Session** | Database-backed |
| **Storage** | Local filesystem |

---

## Prerequisites

- PHP ^8.2 with extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `xml`
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+ or MariaDB 10.5+ (or SQLite for local development)

---

## Quick Start — Getting the Project Running Locally

This guide walks you through setting up the Huber project on your local machine from scratch.

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/huber.git
cd huber
```

### Step 2: Verify System Requirements

Ensure you have the required tools installed:

```bash
# Check PHP version and extensions
php -v
php -m | grep -E "bcmath|ctype|fileinfo|json|mbstring|openssl|pdo|xml"

# Check Composer
composer --version

# Check Node.js and npm
node --version
npm --version

# Verify MySQL is running (you should be able to connect)
mysql -u root -p -e "SELECT VERSION();"
```

If you're missing any requirements, install them before proceeding.

### Step 3: Set Up Each Application

Run the following commands from the **project root** (`huber/`). These will set up all three apps with dependencies and environment files.

#### 3a. Install PHP Dependencies

```bash
cd api && composer install && cd ..
cd admin && composer install && cd ..
cd frontend && composer install && cd ..
```

#### 3b. Install Node.js Dependencies

```bash
cd api && npm install && cd ..
cd admin && npm install && cd ..
cd frontend && npm install && cd ..
```

#### 3c. Generate Application Keys and Set Up Environment Files

```bash
cd api && cp .env.example .env && php artisan key:generate && cd ..
cd admin && cp .env.example .env && php artisan key:generate && cd ..
cd frontend && cp .env.example .env && php artisan key:generate && cd ..
```

### Step 4: Configure Databases

**Option A: Using MySQL (Recommended for Production-like Setup)**

Create three separate databases:

```sql
CREATE DATABASE huber_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE huber_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE huber_frontend CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then update each `.env` file with your MySQL credentials. Edit each of these files:
- `api/.env`
- `admin/.env`
- `frontend/.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=huber_api          # (use huber_admin for admin/, huber_frontend for frontend/)
DB_USERNAME=root               # (your MySQL username)
DB_PASSWORD=                   # (your MySQL password)
```

**Option B: Using SQLite (Quick Local Testing)**

SQLite requires no configuration. The default `.env` files already use SQLite. Just make sure the database file is created:

```bash
cd api && touch database/database.sqlite && cd ..
cd admin && touch database/database.sqlite && cd ..
cd frontend && touch database/database.sqlite && cd ..
```

### Step 5: Configure Admin Credentials and Mail

In each app's `.env` file, set admin credentials and other important config:

```env
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=secure-password-123
MAIL_FROM_ADDRESS=noreply@huber.local
```

### Step 6: Run Database Migrations and Seeders

Migrate the database schema and seed test data:

```bash
cd api && php artisan migrate --seed && cd ..
cd admin && php artisan migrate --seed && cd ..
cd frontend && php artisan migrate --seed && cd ..
```

This creates:
- Base schema for users, drivers, rides, bookings, etc.
- Test user: `test@example.com` / `password`
- Admin account: credentials from your `.env` file

### Step 7: Build Frontend Assets

Compile CSS and JavaScript using Vite:

```bash
cd api && npm run build && cd ..
cd admin && npm run build && cd ..
cd frontend && npm run build && cd ..
```

### Step 8: Start the Application

You'll need **three terminal windows** — one for each app.

**Terminal 1 — API Backend (Port 8000):**

```bash
cd api
php artisan serve --port=8000
```

**Terminal 2 — Admin Panel (Port 8001):**

```bash
cd admin
php artisan serve --port=8001
```

**Terminal 3 — Frontend (Port 8002):**

```bash
cd frontend
php artisan serve --port=8002
```

*(Optional: In a fourth terminal, run `npm run dev` in each app directory for hot-reloading during development)*

### Step 9: Access the Applications

Open your browser and navigate to:

| App | URL | Login |
|-----|-----|-------|
| **API** | http://localhost:8000 | N/A (API only) |
| **Admin** | http://localhost:8001 | admin@example.com / your password |
| **Frontend** | http://localhost:8002 | test@example.com / password |

### Step 10: Verify Setup

1. **Admin Panel:** Log in at http://localhost:8001 — you should see the dashboard with stats
2. **Frontend:** Log in at http://localhost:8002 — you should see the passenger/driver home page
3. **API:** Visit http://localhost:8000/api — you should see the API welcome message

If any app shows a database error or blank page, check the logs in `storage/logs/laravel.log` within each app directory.

---

### Common Issues & Troubleshooting

| Issue | Solution |
|-------|----------|
| **"SQLSTATE[HY000] [1045] Access denied"** | Check DB credentials in `.env` file; verify MySQL is running |
| **"Class not found" or "Failed to locate config"** | Run `composer install` again; clear cache with `php artisan config:clear` |
| **Migrations fail with "table already exists"** | Run `php artisan migrate:fresh --seed` to reset the database |
| **"npm command not found"** | Reinstall Node.js; verify npm is in your PATH |
| **CSS/JS not loading (styling looks broken)** | Run `npm run build` in each app; ensure Vite dev server is running if in dev mode |
| **"No application encryption key has been generated"** | Run `php artisan key:generate` in each app |
| **Can't log in after setup** | Check `.env` for `ADMIN_EMAIL` and `ADMIN_PASSWORD`; verify migrations ran with test user |

---

## Installation

Each app needs its own dependencies and environment setup.

### 1. Install PHP dependencies

```bash
cd api      && composer install
cd ../admin && composer install
cd ../frontend && composer install
```

### 2. Install Node dependencies

```bash
cd api      && npm install
cd ../admin && npm install
cd ../frontend && npm install
```

### 3. Set up environment files

```bash
cd api      && cp .env.example .env && php artisan key:generate
cd ../admin && cp .env.example .env && php artisan key:generate
cd ../frontend && cp .env.example .env && php artisan key:generate
```

### 4. Configure `.env` files

Edit each `.env` with your database credentials. At minimum, set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=huber_api    # or huber_admin, huber_frontend
DB_USERNAME=root
DB_PASSWORD=

ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=your-secure-password-here
```

**Important:** Use a separate database for each app, or use table prefixes. If sharing one database, set `SESSION_DRIVER=file` and `CACHE_STORE=file` to avoid session/cache collisions.

### 5. Build frontend assets

```bash
cd api      && npm run build
cd ../admin && npm run build
cd ../frontend && npm run build
```

---

## Database Setup

### 1. Create databases

```sql
CREATE DATABASE huber_api      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE huber_admin    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE huber_frontend CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Run migrations

```bash
cd api      && php artisan migrate
cd ../admin && php artisan migrate
cd ../frontend && php artisan migrate
```

### 3. Seed the database

```bash
cd api      && php artisan db:seed
cd ../admin && php artisan db:seed
cd ../frontend && php artisan db:seed
```

Each seeder creates:
- A test user: `test@example.com`
- An admin account using credentials from `.env` (`ADMIN_EMAIL` / `ADMIN_PASSWORD`)

---

## Running the Application

Each app runs on a different port.

### Development Servers

```bash
# Terminal 1 — API backend
cd api && php artisan serve --port=8000

# Terminal 2 — Admin panel
cd admin && php artisan serve --port=8001

# Terminal 3 — Frontend
cd frontend && php artisan serve --port=8002
```

### Queue Worker (for email dispatch, run in each app that needs it)

```bash
php artisan queue:listen --tries=1
```

### Vite Dev Server (hot-reloading, run alongside each app)

```bash
npm run dev
```

---

## API Documentation

The API is token-authenticated via Laravel Sanctum. Obtain a token by POSTing to `/api/login`.

### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Authenticate and receive Sanctum token |
| POST | `/api/logout` | Revoke current token |
| POST | `/api/register` | Register as a passenger |
| POST | `/api/register-driver` | Register as a driver |
| POST | `/api/register-driver-docs` | Upload driver documents |
| GET | `/api/` | API welcome endpoint |
| POST | `/api/admin/login` | Admin login |
| POST | `/api/admin/logout` | Admin logout |

### Protected Endpoints

All protected endpoints require `Authorization: Bearer <token>` header.  
**Rate limit:** 60 requests per minute. Login: 5 requests per minute.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/PUT | `/api/profile` | View / Update profile |
| GET/PUT | `/api/password/change` | Change password |
| GET/PUT | `/api/driver/profile` | Driver profile |
| PUT | `/api/driver/vehicle-photos` | Upload vehicle photos |
| GET | `/api/driver/profile/{id}` | Public driver profile |
| GET/POST/PUT | `/api/driver/rides*` | Full ride management CRUD |
| GET | `/api/driver/earnings` | View earnings |
| GET | `/api/find-rides` | Search available rides |
| GET/POST | `/api/booking/*` | Booking flow |
| GET/POST | `/api/payment/*` | Payment flow |
| GET | `/api/user/bookings*` | User booking history |
| POST | `/api/driver/rides/{id}/ongoing` | Mark ride ongoing |
| POST | `/api/driver/rides/{id}/complete` | Mark ride completed |
| GET/POST | `/api/user/bookings/{id}/review` | Submit review |

### Admin Endpoints

All admin endpoints are prefixed with `/api/admin/`.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard` | Dashboard statistics |
| GET/POST | `/users` | List / Create users |
| GET/PUT/DELETE | `/users/{id}` | Show / Update / Delete user |
| GET/POST | `/drivers` | List / Create drivers |
| GET/PUT/DELETE | `/drivers/{id}` | Show / Update / Delete driver |
| GET/POST | `/rides` | List / Create rides |
| GET/PUT/DELETE | `/rides/{id}` | Show / Update / Delete ride |
| GET | `/rides/{id}/passengers` | View ride passengers |
| GET/POST/PUT/DELETE | `/driver-documents*` | Document management |
| POST | `/driver-documents/{id}/verify` | Verify driver |
| POST | `/driver-documents/{id}/unverify` | Unverify driver |

---

## Testing

### Run all tests (within each app)

```bash
php artisan test
```

### Run a specific test file

```bash
php artisan test --filter=BookingTest
```

### Run with PHPUnit directly

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/Feature/BookingTest.php
```

---

## Security

- **Authentication** — API uses Laravel Sanctum token auth; web routes use PHP sessions
- **Rate limiting** — Brute-force protection on login (5 req/min) and API (60 req/min)
- **Admin credentials** — Read from environment variables, not hardcoded
- **CSRF** — Web routes use CSRF protection; API routes are stateless
- **SQL injection** — All queries use parameter binding via Eloquent ORM
- **Input validation** — All inputs validated before processing
- **File uploads** — Driver documents validated by type and size
- **Mass assignment** — Model creation uses only validated data, not raw request input
- **Password policy** — Minimum 8 characters across all user types
- **PCI compliance** — No credit card data stored; only payment references

> **Never commit `.env` to version control.** Rotate admin passwords after deployment.

---

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
