# AGENTS.md — dejwcake/admin-generator

Artisan CRUD generator for Craftable admin panels: from an existing, migrated table it generates
the model, controller, form requests, export, factory, permissions migration, Blade/Vue views,
routes and translations. Composer `dejwcake/admin-generator`, namespace `Brackets\AdminGenerator`
(fork of `brackets/admin-generator`). Part of the Craftable ecosystem — see README.md.

## Layout

- `src/Generators/` — one Artisan command per generated artefact (`Classes/`, `Resources/`,
  `Routes/`, `FileAppenders/`); `Generate*` commands orchestrate them (`admin:generate`,
  `admin:generate:admin-user`, `admin:generate:admin-user:profile`, `admin:generate:user`).
- `src/Builders/` — read the DB schema into column/relation/media/rules DTOs (`src/Dtos/`).
- `resources/views/` — Blade templates that **render the generated PHP/Vue source**
  (`classes/`, `resources/`, `routes/`, `file-appenders/`), namespace `brackets/admin-generator::`.

## Commands

Everything runs in Docker from the package root — never against a host PHP. The full,
copy-pasteable list (composer, every QA tool, both databases, snapshot regeneration and
the "whole PHP suite" one-liner) is in **README.md → "How to develop this project"**.
The ones you need most:

```shell
docker compose run --rm test composer update
docker compose run --rm test ./vendor/bin/phpunit                         # MariaDB (default)
docker compose run --rm -e DB_CONNECTION=pgsql test ./vendor/bin/phpunit   # PostgreSQL
docker compose run --rm -e UPDATE_SNAPSHOTS=true test ./vendor/bin/phpunit
docker compose run --rm php-qa phpcs -s --colors --extensions=php
docker compose run --rm php-qa phpcbf -s --colors --extensions=php       # auto-fix style
docker compose run --rm php-qa phpstan analyse --configuration=phpstan.neon
docker compose run --rm php-qa phpmd ./src,./tests ansi phpmd.xml --suffixes php --baseline-file phpmd.baseline.xml
docker compose run --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache
docker compose run --rm php-qa composer normalize
```

A change is done when phpcs, phpstan, phpmd and the test suite are green.

## Code conventions

- PHP `^8.5`, Laravel 13. Every file starts with `declare(strict_types=1);`.
- **No Facades** — inject contracts through the constructor.
- **No helpers**, with these exceptions: `trans()` / `__()` are allowed everywhere; `app()` only in
  models, traits and places where DI is genuinely hard to provide.
- Constructor property promotion. `final` classes and `readonly` wherever possible — prefer a
  `final readonly class`, otherwise readonly properties. A readonly property is public rather than
  hidden behind a getter.
- Always import with `use`; never inline `\Fully\Qualified\Names`.
- Alias the colliding `Repository` contracts:
  `use Illuminate\Contracts\Config\Repository as Config;`,
  `use Illuminate\Contracts\Cache\Repository as Cache;`.
- Name a property after its type: `TranslationImportService $translationImportService`, not `$service`.
- Build strings with `sprintf()` — no `"{$var}"` interpolation and no `.` concatenation.
- Mark overrides with `#[Override]` — **except** a method that overrides a *trait* method
  (e.g. `HasFactory::newFactory()`): PHP 8.5.3 segfaults on that.
- Before adding a native type to an overriding property/parameter, check the parent. If the parent
  is untyped (Laravel's `$fillable`, `$hidden`, a command's `$description`, …) the child must stay
  untyped too.
- Fix new phpstan/phpmd findings in code. Baselines are for accepted, existing debt only — inspect
  the baseline diff before committing it.

## Testing conventions

- PHPUnit 13 + Orchestra Testbench 11. Test namespaces mirror `src/`.
- Several tested methods of one class → a directory named after the class with one
  `<Method>Test.php` per method.
- Feature tests when several real classes collaborate; Unit tests for isolated logic (mock the
  rest). Don't write tests for service providers or install commands.
- PHPUnit assertions are static: `self::assert*()`. Laravel's instance assertions
  (`$this->assertDatabaseHas()`, response asserts) stay on `$this`.
- Resolve services with `$this->app->make()`, never `app()`.
- Test-only models and stubs live in the `tests/` root.

## Package notes

- Each generator keeps its template name in `protected string $view = '<dir>.<name>';` and renders
  `sprintf('brackets/admin-generator::%s', $this->view)`. Keep that shape — a literal view name
  fails larastan's `view-string` check because the package's view namespace isn't registered
  during analysis.
- `Generator::option()` returns `null` for options a command doesn't define, so the shared base
  code may read options that only some subcommands declare. That's intentional.
- Generated output is covered by snapshot tests (`tests/Feature/Generators/**/__snapshots__/`).
  Changing a template means regenerating snapshots with `UPDATE_SNAPSHOTS=true` and reviewing the
  diff — the snapshot *is* the spec for the generated code, and it must follow the conventions below.
- Generated code must itself follow these conventions (DI, `final`, `sprintf`, …) — it lands in
  consumer projects as-is.

## Versioning

The package is on **2.x** and stays there through the Laravel 13 / PHP 8.5 upgrade — don't add
v3 upgrade sections or bump the `branch-alias`. User-facing changes go to `UPGRADE.md` when
consumers have to act.
