# Upgrade `dejwcake/admin-generator` v1 → v2

This document covers upgrading the `admin-generator` package from the **v1** branch (`v1`, the legacy `1.x` line) to **v2** (this branch / the `v2-dev` line).

Most consumers only invoke artisan commands and won't touch `admin-generator` source code — for them the upgrade is a `composer.json` bump plus a stack version bump. Plugin authors who *extend* generator classes have more work because the namespaces moved.

## Platform requirements

| Requirement | v1 | v2 |
|---|---|---|
| PHP | `^8.2` | `^8.5` |
| Laravel (`illuminate/console`, `illuminate/support`) | `^12.0` | `^13.0` |
| `doctrine/dbal` | required (`^4.2.2`) | **removed** — v2 uses Laravel's native Schema |
| PHPUnit (dev) | `^11.5.9` | `^13.0` |
| `orchestra/testbench` (dev) | `^10.0` | `^11.0` |
| `mockery/mockery` (dev) | `^1.6` | **removed** — v2 tests use real DI |
| `composer.json` `type` | `"project"` | `"library"` |

## Public artisan API — what stayed the same

Top-level commands keep their names. Calling these from CI / Make targets / docs continues to work:

- `admin:generate <table>`
- `admin:generate:user`
- `admin:generate:admin-user`
- `admin:generate:admin-user:profile`
- `admin:generate:controller`
- `admin:generate:model`
- `admin:generate:routes`
- `admin:generate:lang`
- `admin:generate:factory`
- `admin:generate:export`
- `admin:generate:permissions`
- `admin:generate:request:{index,store,update,destroy,bulk-destroy,export,impersonal-login}`

The `Brackets\AdminGenerator\AdminGeneratorServiceProvider` auto-discovery contract is unchanged — Laravel still picks it up automatically; nothing to register manually.

## Public artisan API — breaking changes

### `admin:generate:index` and `admin:generate:form` were split

v1 had monolithic view generators. v2 splits them by output type:

| v1 command | v2 replacement(s) |
|---|---|
| `admin:generate:index` | `admin:generate:blade-index` + `admin:generate:vue-listing` |
| `admin:generate:form` | `admin:generate:blade-create` + `admin:generate:blade-edit` + `admin:generate:blade-form` + `admin:generate:vue-form` |

If you call these sub-commands directly from a custom script, switch to the new ones. If you only call the top-level `admin:generate*` orchestrators, no change needed — they were updated internally.

### New options

| Command | New option | Purpose |
|---|---|---|
| `admin:generate:user` | `--force-permissions` | Generate the permissions migration even when `Brackets\Craftable\CraftableServiceProvider` isn't installed (already present on `admin:generate` in v1; new on `:user` in v2) |
| `admin:generate:user` | `--without-bulk`, `--media`, `-M`, `--generate-model` (kept from v1) | Already in v1; explicit list for clarity |
| `admin:generate:user` | (no change to defaults) | The chained call to `admin:generate:request:impersonal-login` is **always** added in v2 — see "Generated output" below |
| `admin:generate:permissions` | `--with-impersonal-login` | Add `admin.{model}.impersonal-login` to the generated permissions migration. `admin:generate:user` now passes this automatically |

### Removed / renamed commands

None at the top level. The internal sub-command renames listed above are the only public artisan break.

## Class renames (impacts plugin authors / subclassers)

The whole `src/` tree was reorganised. If you `extends` or reference any of these classes by FQN:

### Top-level commands

| v1 FQN | v2 FQN |
|---|---|
| `Brackets\AdminGenerator\GenerateAdmin` | `Brackets\AdminGenerator\Generators\Generate` |
| `Brackets\AdminGenerator\GenerateAdminProfile` | `Brackets\AdminGenerator\Generators\GenerateAdminUserProfile` |
| `Brackets\AdminGenerator\GenerateAdminUser` | `Brackets\AdminGenerator\Generators\GenerateAdminUser` |
| `Brackets\AdminGenerator\GenerateUser` | `Brackets\AdminGenerator\Generators\GenerateUser` |

The artisan command names themselves are unchanged — only the PHP FQNs moved.

### Sub-generators (one-file generators)

The flat `Brackets\AdminGenerator\Generate\*` namespace was split by output category:

| v1 FQN | v2 FQN |
|---|---|
| `Generate\Model` | `Generators\Classes\Model` |
| `Generate\Controller` | `Generators\Classes\Controller` |
| `Generate\Factory` | `Generators\Classes\Factory` |
| `Generate\Export` | `Generators\Classes\Export` |
| `Generate\Permissions` | `Generators\Classes\Permissions` |
| `Generate\IndexRequest` | `Generators\Classes\IndexRequest` |
| `Generate\StoreRequest` | `Generators\Classes\StoreRequest` |
| `Generate\UpdateRequest` | `Generators\Classes\UpdateRequest` |
| `Generate\DestroyRequest` | `Generators\Classes\DestroyRequest` |
| `Generate\BulkDestroyRequest` | `Generators\Classes\BulkDestroyRequest` |
| `Generate\ExportRequest` | `Generators\Classes\ExportRequest` |
| `Generate\ImpersonalLoginRequest` | `Generators\Classes\ImpersonalLoginRequest` |
| `Generate\Lang` | `Generators\FileAppenders\Lang` |
| `Generate\Routes` | `Generators\FileAppenders\Routes` |
| `Generate\ViewIndex` | **removed** — split into `Generators\Resources\BladeIndex` + `Generators\Resources\VueListing` |
| `Generate\ViewForm` | **removed** — split into `Generators\Resources\BladeForm` + `Generators\Resources\BladeCreate` + `Generators\Resources\BladeEdit` + `Generators\Resources\VueForm` |
| `Generate\ViewFullForm` | **removed** — folded into the resources above |
| `Generate\ViewGenerator` | `Generators\Resources\ResourceGenerator` |
| `Generate\FileAppender` | `Generators\FileAppenders\FileAppender` |
| `Generate\ClassGenerator` | `Generators\Classes\ClassGenerator` |
| `Generate\Traits\Names` (trait) | folded into `Generators\Generator` base class |

### Top-level `Generators\Generator` base class

`Generator` is now an `abstract` base for all generators (commands), exposing `initCommonNames()` (model/controller naming derivation), `$tableName`, `$modelBaseName`, `$modelFullName`, `$modelPlural`, `$modelVariableName`, `$modelRouteAndViewName`, `$modelNamespace`, `$modelWithNamespaceFromDefault`, `$modelViewsDirectory`, `$modelDotNotation`, `$modelJSName`, `$modelLangFormat`, `$resource`, `$exportBaseName`, `$titleSingular`, `$titlePlural`, `$controllerFullName`, `$controllerWithNamespaceFromDefault`, `$relations`, `$mediaCollections`. v1 had this logic in the `Generate\Traits\Names` trait + `Generate\ClassGenerator`.

### All concrete classes are `final`

Every concrete generator and DTO is now declared `final` (abstract bases are `abstract`). If you previously **extended** a v1 generator class, that extension will fail in v2 — copy the class instead, or open a PR to make the extension point pluggable.

## New internal architecture (informational)

If you only consume artisan commands you can skip this. For plugin authors / contributors:

- `src/Dtos/**` (new) — `final readonly class` value objects: `Column`, `ColumnCollection`, `BelongsTo`, `BelongsToMany`, `HasMany`, `RelationCollection`, `MediaCollection`, `MediaCollectionDisk` (enum), `MediaCollectionType` (enum), and 13 validation-rule classes under `Dtos/Columns/Rules/`.
- `src/Builders/**` (new) — pure builders that turn raw DB schema into the DTOs above: `ColumnBuilder`, `ColumnCollectionBuilder`, `RelationBuilder`, `BelongsToBuilder`, `HasManyBuilder`, `BelongsToManyBuilder`, `MediaCollectionBuilder`, `FrontendRulesBuilder`, `ServerStoreRulesBuilder`, `ServerUpdateRulesBuilder`. Stateless builders are `final readonly class`; the rules-collection builders keep instance scratchpads (deferred refactor).
- `src/Naming.php` (new) — extracted utility for table-name → model-name conversion (lifted out of the old `Generate\Traits\Names`).
- `Generators/Generator.php` is now constructor-injected: `Filesystem`, `ColumnCollectionBuilder`, `MediaCollectionBuilder`, `RelationBuilder`, `Illuminate\Contracts\View\Factory`. Subclasses receive these via `$this->files`, `$this->columnCollectionBuilder`, etc.

## Generated user code — what consumers will see when regenerating

Running `php artisan admin:generate:* --force` against a v2 install produces meaningfully different output from v1. Highlights:

- **Controllers**: `final` class, fully constructor-injected (`private readonly Gate $gate`, `private readonly Redirector $redirector`, `private readonly UrlGenerator $urlGenerator`, `private readonly ViewFactory $viewFactory`, `private readonly ListingBuilder $listingBuilder`, `private readonly ListingQueryBuilder $listingQueryBuilder`, `private readonly Config $config`). No `redirect()`, `route()`, `view()`, `auth()`, `Auth::`, `URL::`, `View::` calls.
- **Models**: `final class {Model} extends Model`. Helper calls (`app(...)`) are still allowed in models per project convention; everything else is DI.
- **Requests**: `final class` + native return types on `rules()`, `untranslatableRules()`, `getModifiedData()`, etc.
- **Factories**: `final class` + uses `CarbonImmutable::now()` for date defaults.
- **Exports**: `final class` + native `collection(): Collection` return type (v2 dropped the `@phpcsSuppress` annotations).
- **Routes file**: kept Laravel-canonical `Route::middleware([...])->group(...)` Facade form (intentional — the alternative is awkward in route files).
- **Carbon**: emitted type-hint string is `'CarbonInterface'`; `CarbonImmutable::now()` everywhere `Carbon::now()` was used.

### `admin:generate:user` now produces impersonate UI

v1 only had impersonate in `admin:generate:admin-user`. v2 ports the same flow to `admin:generate:user`:

- New `app/Http/Requests/Admin/{Model}/ImpersonalLogin{Model}.php` request.
- Controller gains `impersonalLogin(...)` method, `'impersonalLoginUrlTemplate'` + `'canImpersonalLogin'` listing data.
- Routes get a `get('/{model}/impersonal-login')` line.
- Blade index gets `:impersonal-login-url-template` + `:can-impersonal-login` props.
- Vue listing gets the impersonate button and matching props.
- Permissions migration gets `admin.{model}.impersonal-login` (via the new `--with-impersonal-login` option that `GenerateUser` passes automatically).

If you regenerate over an existing v1 user CRUD without `--force`, the `ImpersonalLogin{Model}.php` request file will be newly created, but the controller / routes / blade / Vue won't update. Use `--force` to overwrite (which deletes the existing files in those four categories first), then review the diff.

## Suggested upgrade procedure

1. **Bump platform versions.** Update `composer.json`:
   ```json
   "require": {
       "php": "^8.5",
       "laravel/framework": "^13.0",
       "dejwcake/admin-generator": "^2.0"
   }
   ```
   Drop `doctrine/dbal` if your `require` only had it for admin-generator (Laravel 11+ also doesn't need it for migrations).

2. **`composer update`.** Resolve any other Laravel 13 / PHP 8.5 incompatibilities your app has (out of scope here).

3. **Run the test suite + manually check the admin.** Generated code from v1 still works at runtime under v2 admin-generator — only your in-repo CRUD output keeps the v1 style until you regenerate.

4. **(Optional) Replace direct calls to the split sub-commands.** If your CI / Make / `composer scripts` run `admin:generate:index` or `admin:generate:form`, swap them for the new commands listed above.

5. **(Optional) Replace v1 FQN references.** If you `use Brackets\AdminGenerator\Generate\<Foo>` anywhere in your app/package code (uncommon — most consumers don't), rename to the v2 path.

6. **(Optional) Regenerate scaffolds.** Pick a feature (e.g. one CRUD module), run `php artisan admin:generate <table> --force`, and review the diff. The new output uses `final readonly class`, full DI, native types, and the new impersonate flow for users. Roll out per module — there's no big-bang requirement.

7. **(Optional) Adopt new options.**
   - `admin:generate:permissions --with-impersonal-login` if you want the permission seeded.
   - `admin:generate:user --force-permissions` if Craftable isn't installed but you still want the permissions migration generated.

## Things that did NOT change (and you can rely on)

- Service-provider auto-discovery.
- Top-level artisan command names (`admin:generate`, `admin:generate:user`, `admin:generate:admin-user`, `admin:generate:admin-user:profile`).
- Blade view publishing tag / namespace (`brackets/admin-generator`).
- Output paths (controllers in `app/Http/Controllers/Admin/...`, requests in `app/Http/Requests/Admin/...`, views in `resources/views/admin/...`, Vue in `resources/js/admin/...`, factory in `database/factories/...`).
- Permission migration filename pattern (`fill_permissions_for_<model>.php`).

## Reference

- Project-level guidance: see `CLAUDE.md` in this directory for current architecture / conventions / test layout.
- Outstanding work: see `TODO.md`.
- Commit history from v1 to v2: `git log v1..HEAD --oneline -- packages/admin-generator` (50 commits, March 2025 → April 2026).
