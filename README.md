# WPS-SME

Web-based **Telecel data bundle reselling platform**: wallet-based, role-driven, supplier-controlled. Orders are fulfilled manually by the supplier; no automated API delivery.

## Stack

- **Laravel 11** (PHP 8.2+)
- **MySQL** (or SQLite for local dev)
- **Laravel Sanctum** (API token auth for external sites)
- **Spatie Laravel Permission** (roles & permissions)

## Project layout

- **`wps-app/`** — Laravel application (all code, migrations, views).
- **Document root for hosting:** `wps-app/public`.

## Local setup

```bash
cd wps-app
cp .env.example .env
# Edit .env: for MySQL set DB_*; or keep SQLite and create database/database.sqlite
php artisan key:generate
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

- **Login (web):** http://localhost:8000/login  
- **Default admin:** `admin@wps-sme.local` / `password` — change in production.

## API for external websites

- **Base URL:** `https://your-domain.com/api`
- **Login:** `POST /api/login` — body: `{ "email": "...", "password": "..." }` → returns `token`.
- **Authenticated requests:** header `Authorization: Bearer <token>`.
- **Endpoints (v1):**  
  `GET /api/v1/me` — current user and wallet balance  
  `GET /api/v1/wallet` — wallet balance  
  `GET /api/v1/wallet/transactions` — ledger  
  `GET /api/v1/plans` — reseller data plans  
  `GET /api/v1/subscriptions` — subscriptions  
  `GET /api/v1/orders` — list orders (query: status, from_date, to_date)  
  `POST /api/v1/orders` — place order (phone_number, reseller_plan_id, confirmation_checked)  
  `GET /api/v1/orders/{id}` — order detail with status history  

## Deployment on Plesk

See **[DEPLOYMENT-PLESK.md](DEPLOYMENT-PLESK.md)** for database creation, document root (`wps-app/public`), `.env`, permissions, and post-deploy steps.

## Roles

- **Supplier** — Admin: orders, wallets, users, bundles, notifications, roles, system settings.
- **Wholesaler / Retailer** — Place orders, manage own reseller plans and subscriptions, view wallet and transactions.

## Features

- Wallet (credit/debit/refund, immutable ledger)
- Order lifecycle: PENDING → PROCESSING → SENT | FAILED → REFUNDED
- Telecel bundle subscriptions (admin) and reseller data plans (per user)
- Broadcast notifications (admin → all or by role), in-app popup
- Role-based access control; configurable permissions per role
- System setting: max pending orders per user
