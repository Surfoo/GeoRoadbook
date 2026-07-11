# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This App Does

GeoRoadbook converts GPX files (from Geocaching Pocket Queries, GSAK, GCTour, etc.) into customizable, ready-to-print geocaching roadbooks. Users upload GPX files, configure options, edit content in-browser (Jodit editor), and export as HTML, ZIP, or PDF (WeasyPrint). It integrates with the Geocaching.com OAuth2 API to fetch a user's saved Pocket Queries.

## Commands

```bash
# Start the stack (nginx + php-fpm + weasyprint)
docker compose up -d --build

# Install dependencies (inside the container)
docker compose exec php-fpm composer install

# Clear cache
docker compose exec php-fpm php bin/console cache:clear

# Run tests
docker compose exec php-fpm php bin/phpunit

# Purge roadbooks older than 30 days
docker compose exec php-fpm php bin/console app:roadbook:purge [--dry-run] [--days N]
```

App: http://localhost:8000/ — WeasyPrint service: http://weasyprint:5001 (internal).

## Architecture

**Symfony 8 + MicroKernelTrait** — stateless, no database. Roadbooks are plain files in `public/roadbook/` ({id}.gpx/.html/.json + pdf/{id}.pdf), deleted after 30 days.

### Key Source Files

| File | Purpose |
|---|---|
| `src/Controller/GeoroadBookController.php` | All roadbook routes: `GET /`, `POST /upload`, `GET /roadbook/{id}` (editor), `/raw`, `POST .../save|delete|export`, `GET .../pdf|zip` |
| `src/Roadbook/GpxParser.php` | Groundspeak GPX → `Geocache` DTOs (coords, waypoints, spoilers, sorting) |
| `src/Roadbook/RoadbookRenderer.php` | Renders DTOs via `templates/roadbook/*.html.twig` (+ `IconMap`, `LocaleCatalog`) |
| `src/Roadbook/Roadbook.php` | File/state engine: tidy cleanup, TOC, hints, markdown/BBCode logs, PDF/ZIP export |
| `src/Roadbook/RoadbookFactory.php` | DI factory (paths from `app.*` parameters in services.yaml) |
| `src/Command/PurgeRoadbooksCommand.php` | 30-day retention cleanup |
| `src/Controller/OAuthController.php` | OAuth routes: `/login`, `/callback`, `/logout` |
| `src/Security/GeocachingAuthenticator.php` | OAuth2 PKCE authenticator (roles from membership level) |
| `docker/weasyprint/server.py` | HTTP sidecar: POST /convert {url} → PDF |

### Generation pipeline

`POST /upload` (JSON: raw GPX text + options) → validate schema version (1/0/1 only) → `GpxParser` → `RoadbookRenderer` (Twig) → tidy + optional addToc/removeImages/encryptHints/parseMarkdown+parseBBcode → files saved → redirect to editor.

PDF export: `POST /roadbook/{id}/export` saves `@page` CSS options to {id}.json, then WeasyPrint fetches `http://webserver/roadbook/{id}/raw` and returns the PDF. Margin boxes, `counter(page)` and `target-counter()` (TOC page numbers) work — do not switch back to Chrome, it ignores them.

### Frontend

Symfony Asset Mapper (no build step), vanilla JS only — **no jQuery**. Two entrypoints in `importmap.php`: `app` (landing: dropzone, validation, fetch submit) and `editor` (Jodit, save/delete/export). Design system: CSS variables in `assets/styles/app.css`, light/dark via `data-theme` on `<html>`.

Generated HTML uses **absolute** asset paths (`/img/...`, `/images/...`, `/design/...`); `Roadbook::buildZip()` rewrites them to relative for the self-contained archive.

### Templates

Twig in `/templates/`. Note the legacy `.twig.html` extension for partials (`_faq`, `_about`, `raw`, `toc`) vs standard `.html.twig` for pages. Roadbook rendering templates in `/templates/roadbook/`, locale files in `/config/locales/`.

## Required Environment Variables

Copy `.env` to `.env.local` and set:

```
GEOCACHING_OAUTH_KEY=        # Geocaching API OAuth client ID
GEOCACHING_OAUTH_SECRET=     # Geocaching API OAuth client secret
GEOCACHING_CALLBACK=         # OAuth redirect URL (e.g. http://localhost:8000/callback)
GEOCACHING_ENV=production    # Geocaching API environment
# Optional overrides: INTERNAL_BASE_URL (default http://webserver), WEASYPRINT_URL (default http://weasyprint:5001)
```

## Important Notes

- `/public/roadbook/` must be writable (gitignored)
- PHP 8.5 with XSL + Tidy extensions (already in the php-fpm image)
- Roadbook URLs are unauthenticated share-links (random id) — anyone with the URL can edit/delete
- E2E tests live in the session scratchpad as Playwright scripts (test.mjs, test-e2e.mjs, test-modals.mjs, test-editor.mjs) using `channel: 'chrome'`

# RTK (Rust Token Killer) - Token-Optimized Commands

## Golden Rule

**Always prefix commands with `rtk`**. If RTK has a dedicated filter, it uses it. If not, it passes through unchanged. This means RTK is always safe to use.

**Important**: Even in command chains with `&&`, use `rtk`:
```bash
# ❌ Wrong
git add . && git commit -m "msg" && git push

# ✅ Correct
rtk git add . && rtk git commit -m "msg" && rtk git push
```

Refer to ~/.claude/RTK.md for the full command reference.
