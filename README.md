# Product Labels for Shopware 6.7 (`Conventions` plugin)

Shop administrators assign labels such as **"New"**, **"Sale"** or **"Limited Edition"** to products. Active labels that are valid at the current date are shown on the product detail page and on product boxes in listings, highest priority first.

| | |
|---|---|
| Shopware | 6.7.14.0 |
| PHP | 8.4+ |
| Database | MariaDB 11.8 |
| Plugin | [`custom/plugins/Conventions`](custom/plugins/Conventions) |
| CI | [`.github/workflows/ci.yml`](.github/workflows/ci.yml) |

## Contents

1. [Features](#features)
2. [Setup](#setup)
3. [Quality tools and tests](#quality-tools-and-tests)
4. [CI/CD](#cicd)
5. [Design decisions](#design-decisions)
6. [What I would improve with more time](#what-i-would-improve-with-more-time)

---

## Features

| Area | What it does |
|---|---|
| **Data model** | Entity `product_label` with `name` (translated, required), `color` (hex `#RRGGBB`, required), `priority` (default `0`), `active` (default `true`), `validFrom` / `validTo` (optional). ManyToMany association with `product`. |
| **Administration** | *Catalogues → Product labels*: a list with search, language switch, sorting and inline editing, and a detail page for creating and editing labels, including translations, colour picker and validity period. A **Labels** tab on the product detail page assigns and removes labels. Snippets are provided for `en-GB` and `de-DE`. |
| **Storefront** | Labels appear below the product name on the detail page and on product boxes in listings, search, sliders and cross-selling. Only labels that are active and inside their validity period are shown, sorted by priority. The styling is a reusable SCSS component. |
| **Validation** | Required fields, a valid hex colour and a valid date range are enforced for every write: from the administration, the API and imports. |
| **Bonus: expired labels** | A scheduled task (hourly) and a console command set labels whose `validTo` is in the past to inactive. |
| **Bonus: cache invalidation** | When a label is created, changed, translated, deleted, assigned or removed, the cached pages of the affected products are invalidated. |

## Setup

Requirements: Docker with Docker Compose v2.24 or newer.

```bash
git clone https://github.com/yusufabo/shopware-conventions.git
cd shopware-conventions

# 1. Start the containers
docker compose up -d

# 2. Install PHP dependencies and Shopware
docker compose exec web composer install
docker compose exec web bin/console system:install --create-database --basic-setup --force

# 3. Install and activate the plugin
docker compose exec web bin/console plugin:refresh
docker compose exec web bin/console plugin:install --activate Conventions

# 4. Build the administration and storefront assets
docker compose exec web bin/build-administration.sh
docker compose exec web bin/console theme:compile
docker compose exec web bin/console cache:clear
```

| Service | URL |
|---|---|
| Storefront | http://localhost:8000 |
| Administration | http://localhost:8000/admin (login `admin` / `shopware`) |
| Adminer (database) | http://localhost:9080 (server `database`, user `root`, password `root`) |
| Mailpit | http://localhost:8025 |

The database is also reachable from the host at `127.0.0.1:33306`. For local overrides, create a `.env.local` file (optional, not committed).

### Try it

1. *Administration → Catalogues → Product labels*: create a label, for example "Sale" in red with priority 10.
2. Open a product, go to the **Labels** tab, assign the label, and save the product.
3. Open the product in the storefront. The label is shown below the product name and on the product box in the category listing.
4. Give a label a "Valid to" date in the past, then run:
   ```bash
   docker compose exec web bin/console conventions:product-label:deactivate-expired
   ```
   The label becomes inactive and disappears from the storefront.

### Uninstall

```bash
docker compose exec web bin/console plugin:uninstall Conventions
```

This removes all plugin tables. To keep the data, use `--keep-user-data`.

## Quality tools and tests

```bash
docker compose exec web vendor/bin/phpstan analyse                     # level max (phpstan.dist.neon)
docker compose exec web vendor/bin/php-cs-fixer fix --dry-run --diff   # Shopware ruleset (.php-cs-fixer.dist.php)
docker compose exec web vendor/bin/phpunit --testdox                   # 11 tests (phpunit.dist.xml)
```

| Test | Type | What it checks |
|---|---|---|
| `Integration/.../ProductLabelRepositoryTest` | Integration | A label can be written and read through the DAL repository. |
| `Unit/Subscriber/ProductLabelCriteriaSubscriberTest` | Unit | The storefront subscriber adds the `labels` association with the active and date filters and the priority sorting, and it listens to all product criteria events. |
| `Unit/.../ProductLabelEntityTest` | Unit | `hasLightColor()` decides correctly between dark and light text. |
| `Integration/.../Service/ExpiredProductLabelDeactivatorTest` | Integration | Only active labels whose `validTo` is in the past are deactivated. |
| `Integration/.../Cache/ProductLabelCacheInvalidatorTest` | Integration | Updating and deleting a label invalidates the cache of its products. |

How the tests boot:

- `phpunit.dist.xml` uses `tests/bootstrap.php`, which runs the plugin's `tests/TestBootstrap.php`, which uses Shopware's `TestBootstrapper`.
- The bootstrapper uses a **separate test database** (`DATABASE_URL` + `_test`). It installs the plugin there, so Shopware's plugin loader registers the `Conventions\` namespace, and it registers `Conventions\Tests\` from the plugin's `autoload-dev`.
- `APP_ENV=test` and `KERNEL_CLASS=Shopware\Core\Kernel` are forced in the PHPUnit configuration.
- Integration tests use `IntegrationTestBehaviour`, so every test runs in a transaction that is rolled back.

## CI/CD

The workflow runs on every push and pull request, in two parallel jobs on `ubuntu-latest`:

| Job | Steps |
|---|---|
| **PHPStan & PHP-CS-Fixer** | Checks the PHP extensions (`composer check-platform-reqs`), then runs PHPStan at level max and PHP-CS-Fixer with Shopware's ruleset (`--dry-run --diff`). No database is needed. |
| **Plugin lifecycle & PHPUnit** | Uses a MariaDB 11.8 service container. Runs `system:install`, then `plugin:refresh` and `plugin:install --activate Conventions`, and fails unless `plugin:list --format json` reports the plugin as installed and active. Then runs `dal:validate` and PHPUnit. |

Notes on the runner setup:

- `.env` is not committed. `bin/console` and the `TestBootstrapper` then skip dotenv and read the job's environment variables, and a fresh `APP_SECRET` is generated for every run.
- `composer install --no-scripts` skips the `assets:install` auto-script, which would boot Shopware before a database exists.

## Design decisions

### Plugin structure

```
custom/plugins/Conventions/src/
├── Command/                                      console command for expired labels
├── Core/Content/Product/Extension/               adds "labels" to product (and the reverse side to language)
├── Core/Content/ProductLabel/                    entity, translation and mapping definitions, collections
│   ├── Cache/ProductLabelCacheInvalidator.php
│   ├── ScheduledTask/                            hourly task and its handler
│   ├── Service/ExpiredProductLabelDeactivator.php
│   └── Validation/ProductLabelValidator.php
├── Migration/
├── Resources/app/administration/                 module, product tab, snippets
├── Resources/app/storefront/src/scss/            reusable label component
├── Resources/config/services.php
├── Resources/views/storefront/                   sw_extends templates and the labels partial
└── Subscriber/ProductLabelCriteriaSubscriber.php
```

### Data model

- **Translations.** The translation definition extends `EntityTranslationDefinition`. This is what makes `TranslatedField('name')` write to `product_label_translation`.
- **Required name.** The `TranslationsAssociationField` has the `Required` flag, as in core `ProductDefinition`. Without it, a label could be saved with no translation at all, and then the `Required` flag on `name` is never checked.
- **Nullable translation column.** The translated `name` column is nullable, as in core translation tables, so other languages can fall back to the default language. "Required" is still enforced by the DAL.
- **Defaults.** `priority = 0` and `active = true` come from `getDefaults()` and from the migration's column defaults.
- **Product association.** The ManyToMany association is added to `product` with an `EntityExtension`, so core entities aren't changed. The mapping table includes `product_version_id`, because products are versioned.
- **Cascading deletes.** Foreign keys use `ON DELETE CASCADE`, so deleting a label or a product also removes its assignments.
- **API access.** The fields are `ApiAware`, so labels are also available in the Store API for headless frontends.
- **Clean validation.** `bin/console dal:validate` reports no errors. A small `LanguageExtension` adds the required reverse association.

### Validation

- **Where it runs.** `ProductLabelValidator` listens to `PreWriteValidationEvent`. It checks the colour format and that `validFrom` isn't after `validTo`. Because it runs on every write, the admin, the Admin API and imports all get the same rules.
- **How the admin shows errors.** It doesn't repeat these rules in JavaScript. API errors reach the fields through `mapPropertyErrors`, and the error summary appears at the top, just like on the product page.

### Storefront

- **Filtering in the database.** `ProductLabelCriteriaSubscriber` adds the `labels` association to the product criteria, with the filters active, `validFrom <= now` and `validTo >= now`, and sorting by `priority DESC`. The database does the filtering, so the templates only render.
- **Events covered.** Criteria events are matched by their exact class, so each one is subscribed: product page, listing, search, product sliders (static and stream) and cross-selling.
- **Template extension.** Templates use `sw_extends` and blocks only:
  - In Shopware 6.7 the product detail page is built from CMS elements, so the labels extend `element/cms-element-product-name.html.twig`.
  - Listings extend `component/product/card/box-standard.html.twig`, which the `image` and `minimal` box layouts also extend.
- **One partial.** A single partial, `component/product/labels.html.twig`, renders the labels in both places.
- **Styling.** The SCSS component `_product-label.scss` takes the colour from the CSS custom property `--product-label-color`, with the theme's primary colour as a fallback. Light colours automatically get dark text (`ProductLabelEntity::hasLightColor()`).

### Administration

- **Module structure.** It follows core modules such as `sw-property`: a list page, and a detail page for editing. The create page extends the detail page (`Component.extend`), so the form and validation exist only once.
- **List.** It uses `sw-entity-listing` and the `listing` mixin, which provide search, sorting, pagination, inline edit and a language switch.
- **Product tab.** It's added with a `routeMiddleware` route and an override of `sw-product-detail`. The override also adds `labels` to `productCriteria`, so no extra request is needed.
- **Saving assignments.** The tab's `sw-entity-many-to-many-select` works in local mode: changes are saved together with the product by the normal **Save** button.
- **Naming.** All admin components and routes are prefixed with `conventions-`, because the `sw-` prefix is reserved for core.
- **Permissions.** The module uses the existing product permissions (`product.viewer/editor/creator/deleter`).

### Bonus features

- **Expired labels.** `ExpiredProductLabelDeactivator` holds the logic. It's used by an hourly scheduled task (`conventions.product_label.deactivate_expired`) and by the console command `conventions:product-label:deactivate-expired`. It writes through the DAL, so the cache invalidation also runs.
- **Cache invalidation.** `ProductLabelCacheInvalidator` finds the products affected by a label change and dispatches core's `InvalidateProductCache` event, so Shopware itself invalidates the right listing, detail and stream caches.
  - Deleted labels are handled before the delete, because the assignments disappear with `ON DELETE CASCADE`.

### Tooling

- **Coding standard.** `.php-cs-fixer.dist.php` uses the rule set that `shopware-cli` uses for Shopware extensions (`@Symfony` plus Shopware's adjustments), for both `src/` and `tests/`.
- **Portable Docker setup.** `compose.yaml` runs on any machine: no local certificates, external networks or committed secrets are needed. The `APP_SECRET` in the file is for development only.

## What I would improve with more time

- **Own permissions.** Separate ACL privileges for labels, for example `product_label.viewer/editor`, instead of reusing the product permissions.
- **Exact expiry time.** The scheduled task runs hourly, so a label can stay visible on cached pages for up to an hour after its `validTo`. A cache TTL based on the next `validTo` would make this exact.
- **More tests.**
  - A unit test for `ProductLabelValidator`.
  - An integration test that loads a real storefront listing.
  - Jest tests for the admin components.
  - A Playwright end-to-end test covering "create label → assign → visible in storefront".
- **Plugin name.** Give the plugin a vendor-prefixed technical name, for example `YaProductLabel`, instead of `Conventions`.
- **Packaging.** Package the plugin with `shopware-cli extension zip`, so the built administration assets ship with it and no build is needed after installation.
- **More features.**
  - Label position and shape options (corner badge or inline), and an optional text colour field.
  - Per-sales-channel visibility.
  - A storefront filter "products with label X".
  - Bulk assignment of labels from the product list.
