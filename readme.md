## @blumilksoftware/Applikuj
### About application
> placeholder

### Local development
```
cp .env.example .env
task init
task vite
```
Application will be running under [localhost:63851](localhost:63851) and [http://Applikuj.blumilk.localhost/](http://Applikuj.blumilk.localhost/) in Blumilk traefik environment. If you don't have a Blumilk traefik environment set up yet, follow the instructions from this [repository](https://github.com/blumilksoftware/environment).

#### Commands
Before running any of the commands below, you must run shell:
```
task shell
```

| Command                 | Task                                        |
|:------------------------|:--------------------------------------------|
| `composer <command>`    | Composer                                    |
| `composer test`         | Runs backend tests                          |
| `composer analyse`      | Runs Larastan analyse for backend files     |
| `composer cs`           | Lints backend files                         |
| `composer csf`          | Lints and fixes backend files               |
| `php artisan <command>` | Artisan commands                            |
| `npm run dev`           | Compiles and hot-reloads for development    |
| `npm run build`         | Compiles and minifies for production        |
| `npm run lint`          | Lints frontend files                        |
| `npm run lintf`         | Lints and fixes frontend files              |
| `npm run tsc`           | Runs TypeScript checker                     |


#### Internationalisation (i18n)

Polish (`pl`) is the default and only locale. The architecture supports adding languages later without restructuring.

**Backend** — Laravel `lang/` files (PHP arrays, loaded via `__()`):

| File | Purpose |
|:-----|:--------|
| `lang/pl/auth.php` | Authentication and verification messages |
| `lang/pl/emails.php` | Email subject lines and body strings |
| `lang/pl/validation.php` | Validation error messages and attribute names |

Usage in PHP: `__('emails.verification.subject')` or `__('auth.verification.invalid_link')`

**Frontend** — vue-i18n with a single JSON file per locale:

| File | Purpose |
|:-----|:--------|
| `resources/js/lang/pl.json` | All Polish frontend strings |

Usage in Vue (Composition API):
```ts
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
// in template: {{ t('welcome.documentation_title') }}
```

**Key naming convention:**

- Keys are grouped by domain (feature area), not by file or component
- Use `snake_case` for all key names
- Nest up to two levels: `domain.sub_domain.key` (e.g. `auth.verification.invalid_link`, `emails.registration.subject`)
- Backend and frontend mirror the same domain groupings

**Rule:** no hardcoded user-facing strings anywhere — every string must go through `__()` (backend) or `t()` (frontend).

#### API Documentation

Interactive API documentation is available at:

- **UI:** `GET /documentation` — browse and test endpoints interactively
- **Raw spec:** `GET /documentation/raw` — OpenAPI 3.0 JSON spec

The specification source lives in `resources/openapi/openapi.yml`. To validate it locally, run:
```
php artisan openapi:validate openapi
```

#### Containers

| service    | container name            | default host port               |
|:-----------|:--------------------------|:--------------------------------|
| `app`      | `Applikuj-app-dev`     | [63851](http://localhost:63851) |
| `database` | `Applikuj-db-dev`      | 63853                           |
| `redis`    | `Applikuj-redis-dev`   | 63852                           |
| `mailpit`  | `Applikuj-mailpit-dev` | 63854                           |
