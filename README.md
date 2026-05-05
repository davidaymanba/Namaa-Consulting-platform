# Namaa Insurance Core System (Laravel 11)

Enterprise-ready insurance management platform for **Namaa Consulting Company (Saudi Arabia)**.

## Implemented Modules

- Authentication with Laravel Breeze (session-based)
- Role-ready architecture (`SUPER_ADMIN`, `BRANCH_MANAGER`, `UNDERWRITER`, `CLAIMS_OFFICER`, `ACCOUNTANT`, `BROKER`, `CLIENT`)
- Arabic RTL default UI + English toggle
- Dark / Light mode toggle
- Dashboard with real DB KPIs + ApexCharts
- Policy Management (Car issuance end-to-end)
  - Premium auto-calculation from tariff rules
  - Installment schedule generator
  - Reinsurance auto-distribution
  - Auto journal entry on issuance
  - Policy PDF export via `barryvdh/laravel-dompdf`
- Claims Management
  - Registration linked to policy
  - Workflow status updates
  - Escalation for large claims
  - Alert for stale claims (>30 days without update)
  - Auto accounting entry on paid claims
- Reinsurance module
  - Bordereaux listing
  - Excel export via `maatwebsite/laravel-excel`
- Finance module
  - Journal overview and summary KPIs
- Audit trail logging service

## Tech Stack

- Laravel 11
- Blade + Alpine.js + Tailwind CSS
- MySQL 8
- ApexCharts CDN
- `barryvdh/laravel-dompdf`
- `maatwebsite/laravel-excel`

## Quick Start

1. Install dependencies:

```bash
composer install
npm install
```

2. Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure DB in `.env` (`DB_CONNECTION=mysql`) then run:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve
```

4. Login (seeded):

- `admin@namaa.sa` / `Password@123`
- `claims@namaa.sa` / `Password@123`

## Main Routes

- `/dashboard`
- `/policies`
- `/claims`
- `/reinsurance`
- `/finance`

## Notes

- Seeded data is realistic Arabic dummy data for the Saudi market.
- Default locale is Arabic (`APP_LOCALE=ar`).
- To switch language, use the topbar toggle in the UI.
