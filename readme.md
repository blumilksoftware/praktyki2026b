## @blumilksoftware/Applikuj
### About application
> placeholder

### Local development
```
cp .env.example .env
task init
task vite
```
Application will be running under [localhost:63851](localhost:63851) and [https://applikuj.blumilk.local.env/](https://applikuj.blumilk.local.env/) in Blumilk traefik environment. If you don't have a Blumilk traefik environment set up yet, follow the instructions from this [repository](https://github.com/blumilksoftware/environment).

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


#### Project decisions

Architectural and design decisions regarding Internationalisation (i18n) are documented in the [projects-decisions.md](projects-decisions.md) file.

#### Containers

| service    | container name            | default host port               |
|:-----------|:--------------------------|:--------------------------------|
| `app`      | `Applikuj-app-dev`     | [63851](http://localhost:63851) |
| `database` | `Applikuj-db-dev`      | 63853                           |
| `redis`    | `Applikuj-redis-dev`   | 63852                           |
| `mailpit`  | `Applikuj-mailpit-dev` | 63854                           |

Working with encrypted data

To encrypt/decrypt environment secrets files, you can use task commands:
E.g.: task secops:decrypt-dev-secrets

    secops:decrypt-dev-secrets: Decrypt app dev secrets
    secops:encrypt-dev-secrets: Encrypt app dev secrets

Remember that decryption requires private key (e.g. SOPS_AGE_DEV_SECRET_KEY for dev environment) which should be set in **.env** file.
Age secret key can be found in [Infisical](https://infisical.blumilk.pl/projects/secret-management/ae82a23a-eb53-470e-8f6a-edcfc4021f53/overview?secretPath=%2Fdeployment).
Encryption uses public key which is added in **.sops.yaml** file.
