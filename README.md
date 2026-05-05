# B2B Fleet Rental CRM & Quotation System

Laravel portfolio project for a B2B vehicle rental company. The application will cover CRM prospect tracking, PIC/contact management, sales follow-up, quotation drafting, manager approval, PDF quotation output, dashboard reporting, and audit logging.

## Current Milestone

Milestone 3: Prospect and contact management.

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

For active frontend development, run:

```bash
npm run dev
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

## Planned Milestones

1. Authentication and role-based dashboard layout.
2. User management.
3. Prospect and contact management.
4. Follow-up activity tracking.
5. Vehicle and rental package masters.
6. Quotation draft and calculation service.
7. Quotation approval workflow.
8. Approved quotation PDF generation.
9. Dashboard and reports.
10. Audit log and security hardening.
11. Testing, seeders, screenshots, and portfolio polish.
