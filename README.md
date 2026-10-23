# Admin Generator

Admin Generator is a Laravel package that quickly scaffolds full CRUD administration for your existing, migrated database tables. It generates controllers, form requests, models, views, routes, and translations so you can get a production-ready admin interface in minutes instead of wiring everything by hand.

It builds on the admin UI template from our `dejwCake/admin-ui` package and is shipped as part of the Craftable ecosystem, making it a good fit for new and existing Craftable-based projects.

Example of an administration interface generated with this package:
![Craftable administration area example](https://docs.getcraftable.com/assets/posts-crud.png "Craftable administration area example")

This package is part of [Craftable](https://github.com/dejwCake/craftable) (`dejwCake/craftable`), an administration starter kit for Laravel 12, forked from [Craftable](https://github.com/BRACKETS-by-TRIAD/craftable) (`brackets/craftable`).

## Documentation
You can find full documentation at https://docs.getcraftable.com/#/admin-generator

## Choosing which actions to generate

By default `admin:generate` scaffolds every action except export. Use `--only` or `--except` with a
comma-separated list to choose the actions for a resource:

```shell
php artisan admin:generate posts --only=show,export      # a read-only resource with export
php artisan admin:generate posts --except=delete,bulk-delete
```

| Action | What it generates |
|---|---|
| `create` | `store` request, `create`/`store` controller methods and routes, create page, form component, the listing's create button |
| `edit` | `update` request, `edit`/`update` controller methods and routes, edit page, form component, the listing's edit button |
| `show` | `show` controller method and route, show page and component, the listing's show button |
| `delete` | `destroy` request, controller method and route, the listing's delete button |
| `bulk-delete` | `bulk-destroy` request, controller method and route, the listing's bulk-operations bar |
| `export` | `export` request, controller method and route, the `Export` class, the listing's export button |

- **`--only`** generates exactly the listed actions.
- **`--except`** generates every action except the listed ones. It starts from *all* actions,
  export included, so `--except=delete` turns export on, while omitting both options leaves it off.
- The two options can't be combined, and each needs at least one action. An unknown action name is
  an error.
- The form component is generated when the resource has `create` or `edit`.

**`index` is always generated and can't be listed.** The listing is the resource's admin page: the
menu link points to it, and it hosts the buttons for every other action. A resource without it would
have nothing to navigate to, so `--only=index,…` is rejected rather than silently ignored.

**What's generated regardless of the selection:** the model and factory, the controller with its
`index` method, the `index` request, the listing (Blade page and Vue component), routes, translations
and the permissions migration. The routes, translations and listing only contain entries for the
selected actions.

**The permissions migration is always complete**, with every action's permission. Its rows are
referenced by `role_has_permissions`, so regenerating a resource with a narrower set would delete
grants roles already have. A permission for an action that isn't generated is harmless.

### Deprecated flags

`--with-export` (`-e`) and `--without-bulk` (`-wb`) still work, and are applied on top of
`--only`/`--except`. `--with-export` can only add export, and `--without-bulk` can only remove
bulk-delete. `admin:generate:permissions` accepts `--without-bulk` but ignores it.

`admin:generate:user` and `admin:generate:admin-user` accept the same options.
`admin:generate:admin-user:profile` does not take them.

The generators whose output depends on the selection (`admin:generate:controller`,
`admin:generate:routes`, `admin:generate:blade-index`, `admin:generate:vue-listing` and
`admin:generate:lang`) accept the same options, so each one can be run on its own. The others
generate one action's files and are simply not called when that action is left out.

## Issues
Where do I report issues?
If something is not working as expected, please open an issue in the main repository https://github.com/dejwCake/craftable.

## How to develop this project

### Composer

Update dependencies:
```shell
docker compose run -it --rm test composer update
```

Composer normalization:
```shell
docker compose run -it --rm php-qa composer normalize
```

### Run code analysis tools (php-qa)

PHP compatibility:
```shell
docker compose run -it --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache
```

Code style:
```shell
docker compose run -it --rm php-qa phpcs -s --colors --extensions=php
```

Fix style issues:
```shell
docker compose run -it --rm php-qa phpcbf -s --colors --extensions=php
```

Static analysis (phpstan):
```shell
docker compose run -it --rm php-qa phpstan analyse --configuration=phpstan.neon
```

Regenerate the phpstan baseline:
```shell
docker compose run -it --rm php-qa phpstan analyse --configuration=phpstan.neon --generate-baseline=phpstan-baseline.neon
```

Mess detector (phpmd):
```shell
docker compose run -it --rm php-qa phpmd analyze
```

Regenerate the phpmd baseline:
```shell
docker compose run -it --rm php-qa phpmd analyze --generate-baseline
```

### Run tests

Run tests against mariadb:
```shell
docker compose run -it --rm -e DB_CONNECTION=mariadb test ./vendor/bin/phpunit
```

Run tests against postgresql:
```shell
docker compose run -it --rm -e DB_CONNECTION=pgsql test ./vendor/bin/phpunit
```

Run tests with coverage:
```shell
docker compose run -it --rm test ./vendor/bin/phpunit --coverage-text
```

To regenerate snapshots use:
```shell
docker compose run -it --rm -e UPDATE_SNAPSHOTS=true test ./vendor/bin/phpunit
```

### Run the whole PHP suite

Run every PHP check and the test suite against both databases in sequence (stops at the first failure):
```shell
docker compose run -it --rm test composer update \
  && docker compose run -it --rm php-qa composer normalize \
  && docker compose run -it --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache \
  && docker compose run -it --rm php-qa phpcs -s --colors --extensions=php \
  && docker compose run -it --rm php-qa phpstan analyse --configuration=phpstan.neon \
  && docker compose run -it --rm php-qa phpmd analyze \
  && docker compose run -it --rm -e DB_CONNECTION=mariadb test ./vendor/bin/phpunit \
  && docker compose run -it --rm -e DB_CONNECTION=pgsql test ./vendor/bin/phpunit
```
