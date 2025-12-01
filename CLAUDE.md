# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **hospital checkup management system** built with CodeIgniter 4 (PHP 8.1+). The system manages hospitals, administrators, checkup items, companies, checkup targets, and products. Key features include:

- Multi-tenant hospital/administrator management
- Checkup item and optional item configuration
- Company-hospital linkage system
- Checkup target/patient management with Excel import/export
- Checkup product management with selection groups
- Daily checkup scheduling with calendar view

## Common Development Commands

### Development Server
```bash
# Start PHP development server (from project root)
php spark serve

# Or run from the public directory
php -S localhost:8080 -t public/
```

### Database
```bash
# Run migrations
php spark migrate

# Rollback migrations
php spark migrate:rollback

# Create new migration
php spark make:migration MigrationName

# Seed database
php spark db:seed SeederName
```

### Testing
```bash
# Run all tests
composer test
# Or
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/path/to/TestFile.php
```

### Code Quality
```bash
# Format code with PHP CS Fixer
vendor/bin/php-cs-fixer fix app/

# Dry run (check without fixing)
vendor/bin/php-cs-fixer fix app/ --dry-run --diff
```

### Cache Management
```bash
# Clear cache
php spark cache:clear

# Clear route cache
php spark route:cache
```

## Architecture & Key Patterns

### MVC Structure

**Controllers** (`app/Controllers/`):
- Follow pattern: `{Feature}Controller` (e.g., `MngrMngController`, `HsptlMngController`)
- AJAX methods prefixed with `ajax_` (e.g., `ajax_list`, `ajax_create`, `ajax_update`, `ajax_delete`)
- All AJAX endpoints return JSON with CSRF token refreshed: `['status' => 'success/error', 'data' => ..., 'csrf_hash' => csrf_hash()]`
- Controllers use `BaseController` as parent class

**Models** (`app/Models/`):
- Follow pattern: `{TableName}Model` (e.g., `MngrMngModel` for `MNGR_MNG` table)
- Use CodeIgniter's Query Builder exclusively (never raw SQL)
- Implement soft deletes using `DEL_YN` column pattern (not CI4's built-in soft deletes)
- Password hashing handled in `beforeInsert`/`beforeUpdate` callbacks
- Custom validation rules defined per operation via `buildValidationRules()` method

**Views** (`app/Views/`):
- Located in feature-specific subdirectories: `mngr/{feature}/index.php`
- Always use `esc()` function for XSS prevention when outputting user data
- Include `<?= csrf_field() ?>` in all forms
- Use CodeIgniter helpers: `base_url()`, `site_url()`, `form_open()`

### Database Naming Conventions

**CRITICAL**: This project uses **uppercase snake_case** for ALL database columns:
- Primary keys: `{TABLE}_SN` (e.g., `MNGR_SN`, `HSPTL_SN`)
- Column names: `MNGR_ID`, `HSPTL_NM`, `CKUP_ARTCL_NM`, etc.
- Soft delete flag: `DEL_YN` (values: 'Y' or 'N')
- Foreign keys: Match referenced table's primary key name

Common table abbreviations:
- `MNGR` = Manager
- `HSPTL` = Hospital
- `CKUP` = Checkup
- `ARTCL` = Article/Item
- `TRGT` = Target
- `CHC` = Choice
- `GDS` = Goods
- `CO` = Company
- `SN` = Serial Number (auto-increment ID)

### Routing Pattern

Routes are organized in `app/Config/Routes.php` using grouped RESTful-style patterns:

```php
$routes->group('mngr', static function ($routes) {
    $routes->group('mngrMng', static function ($routes) {
        $routes->get('/', 'MngrMngController::index');              // List view
        $routes->post('ajax_list', 'MngrMngController::ajax_list'); // AJAX data
        $routes->get('ajax_get_mngr/(:num)', 'MngrMngController::ajax_get_mngr/$1');
        $routes->post('ajax_create', 'MngrMngController::ajax_create');
        $routes->post('ajax_update', 'MngrMngController::ajax_update');
        $routes->post('ajax_delete/(:num)', 'MngrMngController::ajax_delete/$1');
    });
});
```

URL pattern: `/mngr/{feature}/{action}`

**Dual Route Structure**: The application has two separate route hierarchies:
- `/mngr/*` - Manager/admin interface (managed by hospital administrators)
- `/user/*` - User/patient interface (for checkup targets to view and manage their own data)

Both hierarchies may have similarly named controllers (e.g., `CkupTrgtController` vs `UserCkupTrgtController`) but serve different purposes and user types.

### Authentication & Authorization

**Session-based authentication** using `MngrAuthController`/`UserAuthController` and `AuthGuard` filter:
- Login creates session with: `is_logged_in`, `user_id`, `mngr_sn`, `hsptl_sn`, `user_type`
- `user_type` values: `'S'` (super admin), `'H'` (hospital manager), or `'M'` (regular user/patient)
- Protected routes use `AuthGuard` filter (configured in `app/Config/Filters.php`)
- Passwords hashed with `password_hash()` and verified with `password_verify()`
- Two separate authentication flows: manager (`/mngr/login`) and user (`/user/login`)

**IMPORTANT**: All AJAX requests must:
1. Include CSRF token in POST data
2. Check `$this->request->isAJAX()` in controller
3. Return updated CSRF hash in response

### Front-end Stack

- **jQuery** for AJAX and DOM manipulation
- **DataTables** for listing pages with server-side processing
  - List pages use `ajax_list` controller methods that return JSON data arrays
  - Typical pattern: Controller renders initial view → DataTable calls `ajax_list` → Returns rows as nested arrays
  - Action buttons rendered via partial views (e.g., `action_buttons.php`)
- **Bootstrap** for UI components (Velzon admin template v4.3.0)
- **SweetAlert2** for confirmations and alerts
- **FullCalendar** for calendar views (see `DayCkupMngController`)
- **PHPSpreadsheet** for Excel import/export

### Soft Delete Pattern

**IMPORTANT**: Most tables use `DEL_YN` column instead of CI4's built-in soft deletes:

```php
// Query only active records
$this->model->where('DEL_YN', 'N')->findAll();

// Soft delete
$this->model->update($id, ['DEL_YN' => 'Y']);
```

**Exception**: `MNGR_MNG` table uses **physical deletion** (permanent delete without soft delete).

### Join Pattern for Related Data

Models often have methods that join related tables:

```php
public function getManagersWithHospitalDetails()
{
    return $this->select('MNGR_MNG.*, HSPTL_MNG.HSPTL_NM')
                ->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = MNGR_MNG.HSPTL_SN')
                ->where('MNGR_MNG.DEL_YN', 'N')
                ->orderBy('MNGR_MNG.MNGR_SN', 'DESC')
                ->findAll();
}
```

## Key Features Implementation Notes

### Excel Import/Export
- Implemented in `CkupTrgtController::excel_upload()` and `excel_download()`
- Uses PHPSpreadsheet library
- Template-based import with column validation
- Export includes all filtered/searched data

### Dynamic Validation Rules
Models implement `buildValidationRules()` to handle different validation for create vs update:
- Create: all required fields including password
- Update: `is_unique` excludes current record, password is `permit_empty`

### Multi-level Selection System
Checkup products (`CKUP_GDS_MNG`) have complex selection hierarchy:
- Choice groups (`CKUP_GDS_CHC_GROUP`) contain multiple choice items
- Items can be checkup articles (`CKUP_ARTCL_MNG`) or choice articles (`CHC_ARTCL_MNG`)
- Additional choices (`CKUP_GDS_ADD_CHC`) for supplementary options
- See `CkupGdsController` for implementation

**Excel-based Checkup Goods**: A newer Excel-focused variant exists at `/mngr/ckupGdsExcel/*`:
- Uses separate models: `CkupGdsExcelMngModel`, `CkupGdsExcelArtclModel`, `CkupGdsExcelChcGroupModel`, etc.
- Designed for Excel import/export workflows
- Managed via `mngr\CkupGdsExcelController` (note: uses namespace separator in routes)

### Daily Checkup Management
`DayCkupMngController` implements two views:
- List view with search/filter
- Calendar view with FullCalendar integration
- Stores daily limits and bookings per hospital

## Security Best Practices

1. **Always escape output**: `<?= esc($data) ?>`
2. **CSRF protection**: Include `csrf_field()` in forms, refresh token in AJAX responses
3. **XSS prevention**: Use Laminas Escaper via `esc()` helper
4. **SQL Injection**: Use Query Builder, never concatenate SQL
5. **Password handling**: `password_hash()` and `password_verify()` only
6. **Input validation**: Define rules in model or controller validation

## Common Gotchas

1. **Database column case**: ALL database columns are UPPERCASE. `$row['mngr_id']` will fail; use `$row['MNGR_ID']`
2. **Soft deletes**: Don't use CI4's `$useSoftDeletes = true`. Use `DEL_YN` column pattern (except `MNGR_MNG`)
3. **CSRF in AJAX**: Always return `csrf_hash()` in AJAX responses and update the hidden input
4. **Primary keys**: Named `{TABLE}_SN`, not `id`
5. **Type hinting**: PHP 8.1+ features required. Use typed properties and union types (`array|false`)
6. **Korean comments**: This project uses Korean comments extensively - this is intentional
7. **User types**: Check session `user_type` carefully - `'S'` = super admin, `'H'` = hospital manager, `'M'` = patient/user
8. **Route groups**: Manager routes use `/mngr/*` prefix, user routes use `/user/*` prefix

## Code Style Requirements

- **Indentation**: 4 spaces (no tabs)
- **Braces**: K&R style (opening brace on same line)
- **Arrays**: Short syntax `[]` not `array()`
- **Naming**:
  - Classes: PascalCase
  - Methods/variables: camelCase
  - Constants: UPPER_SNAKE_CASE
  - Database columns: UPPER_SNAKE_CASE
- **Type hints**: Required for all method parameters and return types
- **PHPDoc**: Required for all public methods
- **Validation**: CodeIgniter Coding Standard via PHP CS Fixer

## Environment Configuration

Copy `env` to `.env` and configure:
- `CI_ENVIRONMENT` (development/production)
- `app.baseURL`
- Database credentials under `database.default.*`
- `encryption.key` for sessions

The app entry point is `public/index.php` (not root `index.php`). Configure web server to point to `public/` directory.
