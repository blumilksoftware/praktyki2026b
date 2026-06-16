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


#### API Documentation

Interactive API documentation is available via [Stoplight Elements](https://stoplight.io/open-source/elements) at:

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
