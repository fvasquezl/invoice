# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Backend (PHP/Laravel)
```bash
composer run dev        # Start all services: Laravel server, queue, log tail, Vite
composer run setup      # First-time setup: install deps, generate key, migrate, build frontend
composer run test       # Run PHPUnit test suite
./vendor/bin/pint       # Format PHP code
./vendor/bin/phpstan analyse  # Run static analysis
php artisan migrate     # Run database migrations
php artisan tinker      # Open REPL
```

### Frontend
```bash
npm run dev             # Start Vite dev server (included in composer run dev)
npm run build           # Build frontend assets for production
```

### Running a single test
```bash
php artisan test --filter=TestName
./vendor/bin/phpunit tests/path/to/TestFile.php
```

## Architecture

This is a **Laravel 12 invoice generator** with multiple template designs, PDF generation, and user authentication.

### Stack
- **Backend:** Laravel 12, PHP 8.2+, Eloquent ORM
- **Admin/Forms:** Filament 5.0 (panel + form schemas)
- **Reactive UI:** Livewire (components, form binding)
- **Frontend:** Tailwind CSS 4, Alpine.js, Vite 7
- **PDF:** DomPDF via `InvoicePdfService`
- **Database:** SQLite (default), migrations with cascade deletes

### Core Models
- `User` → has many `Invoice`
- `Invoice` → has many `InvoiceItem`, belongs to `Template`; auto-generates invoice numbers (`INV-YYYY-XXXX`); `calculateTotals()` method aggregates line items
- `InvoiceItem` → auto-calculates `total` (`quantity × unit_price`) on save via model events
- `Template` → stores template metadata; settings stored as JSON

### Routing
- `routes/web.php` — public-facing routes (landing page, invoice preview, template switching)
- `routes/invoice.php` — Livewire invoice creation route
- `routes/console.php` — Artisan commands

### Invoice Creation Flow
1. User lands on public page (`welcome.blade.php`)
2. Invoice form uses a Livewire component (`pages/invoice/create.blade.php`) backed by Filament Schemas
3. Session stores form data to survive auth redirects
4. Template selection stored on the `Invoice` model via `template_id`
5. Preview rendered by `InvoiceTemplate` view component
6. PDF generated via `InvoicePdfService` using DomPDF

### Template System
Four invoice templates live in `resources/views/components/templates/`:
- Modern Minimalist
- Classic Business
- Creative Agency
- Corporate Blue

Template rendering flows through `invoice-template.blade.php` → `invoice-renderer.blade.php`, selecting the appropriate template Blade component based on `Invoice->template_id`.

### Authentication
- Standard Laravel auth for user accounts
- Filament panel has its own auth
- `auth-modal.blade.php` (Livewire) handles login/register on public pages without full-page redirect

### Layouts
- `layouts/app.blade.php` — authenticated/admin layout with Filament
- `layouts/public.blade.php` — public layout with navigation and auth modal
