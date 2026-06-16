# Project Decisions

This document lists the architectural and design decisions made during the development of Applikuj.

## Internationalisation (i18n)

Polish (`pl`) is the default and only locale. The architecture supports adding languages later without restructuring.

### Backend i18n
We use Laravel `lang/` files (PHP arrays, loaded via `__()`):
- `lang/pl/auth.php` - Authentication and verification messages
- `lang/pl/emails.php` - Email subject lines and body strings
- `lang/pl/validation.php` - Validation error messages and attribute names

**Usage:** `__('emails.verification.subject')` or `__('auth.verification.invalid_link')`
**Key naming convention:** Use `snake_case` for backend keys (e.g., `auth.verification.invalid_link`).

### Frontend i18n
We use `vue-i18n` with a single JSON file per locale:
- `resources/js/lang/pl.json` - All Polish frontend strings

**Key naming convention:** Use `camelCase` for frontend keys (e.g., `auth.verification.invalidLink`).

### General Key Rules
- Keys are grouped by domain (feature area), not by file or component.
- Nest up to two levels: `domain.subDomain.key` (frontend) / `domain.sub_domain.key` (backend).
- Backend and frontend mirror the same domain groupings.
- **Rule:** No hardcoded user-facing strings anywhere — every string must go through `__()` (backend) or `t()` (frontend).
