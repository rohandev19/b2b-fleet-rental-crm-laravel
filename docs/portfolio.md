# Portfolio Guide

This CRM is built as a milestone-based Laravel portfolio project for a B2B fleet rental business.

## Demo Flow

1. Sign in as `admin@example.com` with password `password`.
2. Review live metrics on the dashboard.
3. Open Prospects and inspect `PT Nusantara Retailindo`.
4. Open Quotations and review the approved quotation ending in `9001`.
5. Generate the quotation PDF from the approved quotation detail page.
6. Mark the approved generated quotation as sent.
7. Mark the sent quotation as accepted or declined to show customer outcome tracking.
8. Open Reports to view the funnel, quotation value, sales performance, and CSV export actions.
9. Export prospect or quotation CSV data for the selected report period.
10. Run `php artisan crm:follow-up-reminders` to show due and overdue follow-up reminders.
11. Run `php artisan crm:expire-quotations --dry-run` to preview quotation expiry automation.
12. Open Audit Logs to inspect recorded changes.

## Demo Accounts

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@example.com | password |
| Sales | sales@example.com | password |
| Sales Manager | manager@example.com | password |
| Finance | finance@example.com | password |

## Screenshot Checklist

Capture these screens for the portfolio README or project showcase:

1. Dashboard as Sales Manager.
2. Prospect detail with contacts and follow-up timeline.
3. Quotation detail with approval history and PDF actions.
4. Generated quotation PDF preview or download.
5. Reports page with period filter and export actions visible.
6. Audit Logs page showing tracked actions.

## Local Reset

Use the standard setup commands, then run:

```bash
php artisan migrate:fresh --seed
php artisan crm:follow-up-reminders
php artisan crm:expire-quotations --dry-run
npm run build
php artisan serve
```
