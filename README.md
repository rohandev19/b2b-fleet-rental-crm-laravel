# B2B Fleet Rental CRM & Quotation System

[![CI](https://github.com/rohandev19/b2b-fleet-rental-crm-laravel/actions/workflows/ci.yml/badge.svg)](https://github.com/rohandev19/b2b-fleet-rental-crm-laravel/actions/workflows/ci.yml)
[![Release](https://img.shields.io/badge/release-v1.1.1-111827)](https://github.com/rohandev19/b2b-fleet-rental-crm-laravel/releases/tag/v1.1.1)
[![Laravel](https://img.shields.io/badge/Laravel-12-ff2d20)](https://laravel.com)

Laravel portfolio project for a B2B vehicle rental company. The application will cover CRM prospect tracking, PIC/contact management, sales follow-up, quotation drafting, manager approval, PDF quotation output, dashboard reporting, and audit logging.

## Quick Links

- Portfolio guide: [`docs/portfolio.md`](docs/portfolio.md)
- Release history: [`CHANGELOG.md`](CHANGELOG.md)
- CI workflow: [`.github/workflows/ci.yml`](.github/workflows/ci.yml)

## Project Status

Milestones 1-11 complete. The project is ready for portfolio review, demo seeding, screenshots, and further extension.

- Laravel 12 application scaffolded.
- Laravel Breeze Blade authentication installed.
- Tailwind/Vite frontend build configured.
- SQLite local database prepared for quick development.
- Base migrations can run successfully.
- Public registration disabled for internal CRM usage.
- User role enum and role middleware added.
- Role-based dashboard shell with sidebar/topbar added.
- Demo users can be seeded.
- Admin-only user management added.
- Admin can create, edit, filter, and activate/deactivate users.
- Inactive users cannot sign in.
- Prospect CRUD with search/filter/pagination added.
- Prospect detail page includes company profile, pipeline status, sales owner, and PIC contacts.
- Admin/Sales can manage prospects and contacts; Manager/Finance can view prospects.
- Primary PIC rule keeps only one primary contact per prospect.
- Follow-up activity timeline added to prospect detail.
- Admin/Sales can add, edit, and delete follow-up activities.
- Today and overdue follow-up reminder pages added.
- Follow-up reminder digest command added for manual and scheduled checks.
- Lost prospects reject new follow-up activities.
- Vehicle master CRUD added with type, transmission, fuel, seat capacity, base monthly price, and active status.
- Rental package CRUD added with duration and included services.
- Admin can manage master data; Manager/Finance can view master data.
- Quotation draft creation added with prospect, PIC, vehicle, package, and multiple line items.
- Quotation number is generated automatically using `QTN/HAN/YYYY/MM/0001` format.
- Subtotal, discount, tax, and grand total are calculated server-side.
- Active vehicle/package validation added for new quotation drafts.
- Sales/Admin can submit draft quotations for approval.
- Manager can approve or reject submitted quotations.
- Rejection reason is required and approval history is recorded.
- Sales cannot approve or reject their own quotations.
- Approved quotations can be generated into PDF using DomPDF.
- Quotation PDFs are stored on the private local disk.
- Admin/Sales/Manager can generate approved quotation PDFs.
- Admin/Sales/Manager/Finance can download generated approved quotation PDFs.
- Admin/Sales/Manager can mark approved generated quotations as sent.
- Dashboard metrics now use live CRM, follow-up, and quotation data.
- Dashboard includes pipeline snapshot, recent quotations, overdue follow-ups, and role-specific work queue.
- Reports page added for Admin/Manager/Finance with period filters.
- Reports cover prospect funnel, quotation status value, follow-up outcomes, and sales performance.
- Reports can export period-filtered prospect and quotation data as CSV files.
- Audit log table, logger service, and Admin/Manager audit log viewer added.
- User, prospect, contact, follow-up, quotation approval, and PDF generation actions are audited.
- Audit logs capture actor, action, record, changed values, IP address, and user agent.
- Active-session hardening logs out users whose account is deactivated after login.
- Portfolio demo dataset seeder added for prospects, contacts, follow-ups, vehicles, packages, quotations, approvals, and audit logs.
- Seeder is idempotent for demo records and covered by feature tests.
- Portfolio guide and screenshot checklist added in [`docs/portfolio.md`](docs/portfolio.md).
- Full feature test suite covers the core CRM, quotation, PDF, reporting, audit, and seeder flows.

## Tech Stack

- Laravel 12
- PHP 8.2+
- Blade
- Tailwind CSS 4
- Alpine.js
- SQLite for local development
- PHPUnit

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan serve
```

To reset with the portfolio demo dataset:

```bash
php artisan migrate:fresh --seed
```

For active frontend development, run:

```bash
npm run dev
```

## Scheduled Jobs

The CRM includes a follow-up reminder digest command:

```bash
php artisan crm:follow-up-reminders
php artisan crm:follow-up-reminders --today
php artisan crm:follow-up-reminders --overdue
```

The CRM can also expire quotations that are past their validity date:

```bash
php artisan crm:expire-quotations
php artisan crm:expire-quotations --dry-run
```

Laravel's scheduler runs the default reminder digest daily at `08:00` and quotation expiry daily at `00:30`. In production, configure the standard scheduler cron entry:

```bash
* * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1
```

## Demo Accounts

All demo accounts use the password `password`.

| Role | Email |
| --- | --- |
| Admin | admin@example.com |
| Sales | sales@example.com |
| Sales Manager | manager@example.com |
| Finance | finance@example.com |

## Development Approach

This project is built milestone by milestone with small commits. Each milestone should be runnable and verified before moving to the next feature area.

## Completed Milestones

1. Authentication and role-based dashboard layout.
2. User management.
3. Prospect and contact management.
4. Follow-up activity tracking and reminder digest.
5. Vehicle and rental package masters.
6. Quotation draft and calculation service.
7. Quotation approval and sent workflow.
8. Approved quotation PDF generation.
9. Dashboard, reports, and CSV exports.
10. Audit log and security hardening.
11. Testing, seeders, screenshots, and portfolio polish.

## Portfolio Reference

- Demo and screenshot guide: [`docs/portfolio.md`](docs/portfolio.md)
- Release history: [`CHANGELOG.md`](CHANGELOG.md)
