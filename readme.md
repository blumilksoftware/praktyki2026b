## @blumilksoftware/AppLikuj
### About application
> placeholder

### Local development
```
cp .env.example .env
task init
task vite
```
Application will be running under [localhost:63851](localhost:63851) and [http://AppLikuj.blumilk.localhost/](http://AppLikuj.blumilk.localhost/) in Blumilk traefik environment. If you don't have a Blumilk traefik environment set up yet, follow the instructions from this [repository](https://github.com/blumilksoftware/environment).

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


#### Containers

| service    | container name            | default host port               |
|:-----------|:--------------------------|:--------------------------------|
| `app`      | `AppLikuj-app-dev`     | [63851](http://localhost:63851) |
| `database` | `AppLikuj-db-dev`      | 63853                           |
| `redis`    | `AppLikuj-redis-dev`   | 63852                           |
| `mailpit`  | `AppLikuj-mailpit-dev` | 63854                           |
