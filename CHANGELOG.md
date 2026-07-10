# Changelog

## Unreleased

### Added

- Follow-up reminder digest command with `--today` and `--overdue` filters.
- Daily scheduler registration for the default reminder digest at `08:00`.
- Quotation expiry command with `--dry-run` support.
- Daily scheduler registration for quotation expiry at `00:30`.
- Quotation action for marking approved generated quotations as sent.
- Quotation outcome actions for marking sent quotations as accepted or declined.

### Verification

- Full PHPUnit feature/unit suite passed with 105 tests and 318 assertions.
- Laravel Pint style check passed with `vendor/bin/pint --test`.
- Production frontend build passed with `npm run build`.

## 1.1.1 - Report Export Route Patch

This patch aligns report export URLs with the project route plan while preserving backward-compatible redirects.

### Changed

- Canonical prospect export route changed to `/reports/export/prospects`.
- Canonical quotation export route changed to `/reports/export/quotations`.
- Legacy `/reports/exports/...` URLs now redirect to the canonical export paths.

### Verification

- Full PHPUnit feature/unit suite passed with 93 tests and 261 assertions.
- Laravel Pint style check passed with `vendor/bin/pint --test`.

## 1.1.0 - Portfolio Maintenance Release

This release adds repository automation, GitHub contribution templates, and report CSV exports after the initial portfolio release.

### Added

- GitHub Actions CI workflow for Laravel tests, style checks, dependency audit, and frontend build verification.
- Dependabot configuration for Composer, npm, and GitHub Actions updates.
- README status badges for CI, Laravel version, and release reference.
- GitHub issue templates for bug reports and feature requests.
- Pull request template with verification and scope checks.
- CSV exports for period-filtered prospect and quotation report data.

### Verification

- Full PHPUnit feature/unit suite passed with 92 tests and 257 assertions.
- Laravel Pint style check passed with `vendor/bin/pint --test`.
- Production frontend build passed with `npm run build`.

## 1.0.0 - Portfolio Release

This release completes the milestone plan for the B2B Fleet Rental CRM & Quotation System.

### Added

- Laravel 12 application scaffold with Breeze authentication.
- Role-based access for Admin, Sales, Sales Manager, and Finance.
- Internal user management with active/inactive controls.
- Prospect CRM with contacts, primary PIC handling, pipeline status, priority, and follow-up scheduling.
- Follow-up activity timeline plus today and overdue reminder views.
- Vehicle and rental package master data.
- Quotation draft creation with server-side line item, subtotal, discount, tax, and grand total calculation.
- Quotation approval workflow with submit, approve, reject, rejection reason, and approval history.
- Approved quotation PDF generation and private local file storage.
- Dashboard metrics, pipeline snapshot, recent quotation list, overdue follow-up list, and role-specific work queues.
- Reports page with date filters, prospect funnel, quotation values by status, follow-up outcomes, and sales performance.
- Audit logging for sensitive CRM and quotation actions.
- Session hardening that logs out users whose account is deactivated after login.
- Idempotent portfolio demo seeder with users, prospects, contacts, follow-ups, vehicles, packages, quotations, approvals, and audit sample data.
- Portfolio demo and screenshot guide in `docs/portfolio.md`.

### Verification

- Full PHPUnit feature/unit suite covers authentication, roles, users, prospects, follow-ups, master data, quotation draft/calculation, approval, PDF generation, dashboard, reports, audit logs, and seed data.
- Production frontend build verified with `npm run build`.
- Database migrations verified with `php artisan migrate:status`.
