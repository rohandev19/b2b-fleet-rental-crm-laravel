# Changelog

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
- CSV exports for period-filtered prospect and quotation report data.
- Audit logging for sensitive CRM and quotation actions.
- Session hardening that logs out users whose account is deactivated after login.
- Idempotent portfolio demo seeder with users, prospects, contacts, follow-ups, vehicles, packages, quotations, approvals, and audit sample data.
- Portfolio demo and screenshot guide in `docs/portfolio.md`.

### Verification

- Full PHPUnit feature/unit suite covers authentication, roles, users, prospects, follow-ups, master data, quotation draft/calculation, approval, PDF generation, dashboard, reports, CSV exports, audit logs, and seed data.
- Production frontend build verified with `npm run build`.
- Database migrations verified with `php artisan migrate:status`.
