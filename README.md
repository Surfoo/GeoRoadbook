# GeoRoadbook

GeoRoadbook is a web app to create your geocaching roadbook ready-to-print from your GPX file (Pocket Query, GSAK, GCTour, …). Upload a GPX (or pick one of your Pocket Queries via the Geocaching.com API), customize the content, edit it in the browser, and export as HTML, ZIP, or a print-grade PDF with a paginated table of contents, page headers/footers, and PDF bookmarks.

Hosted here: [http://georoadbook.vaguelibre.net/](http://georoadbook.vaguelibre.net/)

## Stack

* [Symfony 8](https://symfony.com/) (no database — roadbooks are plain files, users live in the session)
* [Bootstrap 5](https://getbootstrap.com/) + vanilla JavaScript via Symfony Asset Mapper (no build step)
* [Jodit](https://xdsoft.net/jodit/) in-browser editor
* [WeasyPrint](https://weasyprint.org/) sidecar for HTML → PDF conversion (CSS Paged Media)
* Twig rendering of parsed GPX (typed `GpxParser` + `RoadbookRenderer`), [jBBCode](https://github.com/jbowens/jBBCode) and [cebe/markdown](https://github.com/cebe/markdown) for log parsing
* PHP 8.4 with the `tidy` extension
* OAuth2 (PKCE) against the Geocaching.com API for Pocket Queries

## Development setup

```bash
git clone https://github.com/Surfoo/georoadbook.git
cd georoadbook
cp .env .env.local          # set GEOCACHING_OAUTH_KEY/SECRET/CALLBACK
docker compose up -d --build
docker compose exec php-fpm composer install
```

The app runs at [http://localhost:8000/](http://localhost:8000/). Three services: `webserver` (nginx), `php-fpm`, and `weasyprint` (PDF conversion).

### Code quality

Rector, php-cs-fixer, and PHPStan (level 6) run in CI (`.github/workflows/check.yml`) and in the versioned pre-commit hook. Activate the hook once per clone:

```bash
git config core.hooksPath .githooks
```

Composer shortcuts: `composer rector`, `composer php-cs-fixer`, `composer phpstan`.

### Housekeeping

Roadbooks are deleted after 30 days without modification. Run the purge from cron:

```bash
php bin/console app:roadbook:purge          # add --dry-run to preview, --days N to override
```

## License

GeoRoadbook is distributed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).

## Contact

- original author: surfooo at gmail dot com
