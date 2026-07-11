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

<!-- rtk-instructions v2 -->
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

## RTK Commands by Workflow

### Build & Compile (80-90% savings)
```bash
rtk cargo build         # Cargo build output
rtk cargo check         # Cargo check output
rtk cargo clippy        # Clippy warnings grouped by file (80%)
rtk tsc                 # TypeScript errors grouped by file/code (83%)
rtk lint                # ESLint/Biome violations grouped (84%)
rtk prettier --check    # Files needing format only (70%)
rtk next build          # Next.js build with route metrics (87%)
```

### Test (60-99% savings)
```bash
rtk cargo test          # Cargo test failures only (90%)
rtk go test             # Go test failures only (90%)
rtk jest                # Jest failures only (99.5%)
rtk vitest              # Vitest failures only (99.5%)
rtk playwright test     # Playwright failures only (94%)
rtk pytest              # Python test failures only (90%)
rtk rake test           # Ruby test failures only (90%)
rtk rspec               # RSpec test failures only (60%)
rtk test <cmd>          # Generic test wrapper - failures only
```

### Git (59-80% savings)
```bash
rtk git status          # Compact status
rtk git log             # Compact log (works with all git flags)
rtk git diff            # Compact diff (80%)
rtk git show            # Compact show (80%)
rtk git add             # Ultra-compact confirmations (59%)
rtk git commit          # Ultra-compact confirmations (59%)
rtk git push            # Ultra-compact confirmations
rtk git pull            # Ultra-compact confirmations
rtk git branch          # Compact branch list
rtk git fetch           # Compact fetch
rtk git stash           # Compact stash
rtk git worktree        # Compact worktree
```

Note: Git passthrough works for ALL subcommands, even those not explicitly listed.

### GitHub (26-87% savings)
```bash
rtk gh pr view <num>    # Compact PR view (87%)
rtk gh pr checks        # Compact PR checks (79%)
rtk gh run list         # Compact workflow runs (82%)
rtk gh issue list       # Compact issue list (80%)
rtk gh api              # Compact API responses (26%)
```

### JavaScript/TypeScript Tooling (70-90% savings)
```bash
rtk pnpm list           # Compact dependency tree (70%)
rtk pnpm outdated       # Compact outdated packages (80%)
rtk pnpm install        # Compact install output (90%)
rtk npm run <script>    # Compact npm script output
rtk npx <cmd>           # Compact npx command output
rtk prisma              # Prisma without ASCII art (88%)
```

### Files & Search (60-75% savings)
```bash
rtk ls <path>           # Tree format, compact (65%)
rtk read <file>         # Code reading with filtering (60%)
rtk grep <pattern>      # Search grouped by file (75%). Format flags (-c, -l, -L, -o, -Z) run raw.
rtk find <pattern>      # Find grouped by directory (70%)
```

### Analysis & Debug (70-90% savings)
```bash
rtk err <cmd>           # Filter errors only from any command
rtk log <file>          # Deduplicated logs with counts
rtk json <file>         # JSON structure without values
rtk deps                # Dependency overview
rtk env                 # Environment variables compact
rtk summary <cmd>       # Smart summary of command output
rtk diff                # Ultra-compact diffs
```

### Infrastructure (85% savings)
```bash
rtk docker ps           # Compact container list
rtk docker images       # Compact image list
rtk docker logs <c>     # Deduplicated logs
rtk kubectl get         # Compact resource list
rtk kubectl logs        # Deduplicated pod logs
```

### Network (65-70% savings)
```bash
rtk curl <url>          # Compact HTTP responses (70%)
rtk wget <url>          # Compact download output (65%)
```

### Meta Commands
```bash
rtk gain                # View token savings statistics
rtk gain --history      # View command history with savings
rtk discover            # Analyze Claude Code sessions for missed RTK usage
rtk proxy <cmd>         # Run command without filtering (for debugging)
rtk init                # Add RTK instructions to CLAUDE.md
rtk init --global       # Add RTK to ~/.claude/CLAUDE.md
```

## Token Savings Overview

| Category | Commands | Typical Savings |
|----------|----------|-----------------|
| Tests | vitest, playwright, cargo test | 90-99% |
| Build | next, tsc, lint, prettier | 70-87% |
| Git | status, log, diff, add, commit | 59-80% |
| GitHub | gh pr, gh run, gh issue | 26-87% |
| Package Managers | pnpm, npm, npx | 70-90% |
| Files | ls, read, grep, find | 60-75% |
| Infrastructure | docker, kubectl | 85% |
| Network | curl, wget | 65-70% |

Overall average: **60-90% token reduction** on common development operations.
<!-- /rtk-instructions -->