# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This App Does

GeoRoadbook converts GPX files (from Geocaching Pocket Queries, GSAK, GCTour, etc.) into customizable, ready-to-print geocaching roadbooks. Users upload GPX files, configure options, edit content in-browser, and export as HTML, PDF, or ZIP. It integrates with the Geocaching.com OAuth2 API to fetch a user's saved Pocket Queries.

## Commands

```bash
# Install dependencies
composer install

# Clear cache
php bin/console cache:clear

# Install web assets
php bin/console assets:install

# Start dev server (requires Symfony CLI)
symfony server:start

# Run tests
php bin/phpunit

# Run a single test file
php bin/phpunit tests/path/to/SomeTest.php

# List all console commands
php bin/console list
```

## Architecture

**Symfony 7 + MicroKernelTrait** — minimal, stateless application with no database (no Doctrine).

### Key Source Files

| File | Purpose |
|---|---|
| `src/Controller/GeoroadBookController.php` | Main page (`GET /`) — renders GPX upload form |
| `src/Controller/OAuthController.php` | OAuth routes: `/login`, `/callback`, `/logout` |
| `src/Security/GeocachingAuthenticator.php` | Custom OAuth2 authenticator (extends OAuth2Authenticator) |
| `src/Security/User.php` | In-memory user entity (session-only, not persisted) |
| `src/Security/UserProvider.php` | User loader — **note:** `loadUserByIdentifier()` is a stub |

### Authentication Flow

OAuth2 PKCE flow against the Geocaching.com API via `surfoo/oauth2-geocaching`. User roles are assigned based on membership level: `ROLE_PREMIUM`, `ROLE_BASIC`, `ROLE_USER`. No database — users live in the session only.

### Templates

Twig templates in `/templates/`. Notable: `_header.twig.html` and `_footer.twig.html` use `.twig.html` extension (not the standard `.html.twig`).

### Frontend Assets

Uses Symfony Asset Mapper (not Webpack Encore). JS/CSS entry points are in `/assets/`. Third-party libs (jQuery, Bootstrap, TinyMCE, bootstrap-fileinput) are served from `/public/assets/`.

## Required Environment Variables

Copy `.env` to `.env.local` and set:

```
GEOCACHING_OAUTH_KEY=        # Geocaching API OAuth client ID
GEOCACHING_OAUTH_SECRET=     # Geocaching API OAuth client secret
GEOCACHING_CALLBACK=         # OAuth redirect URL (e.g. http://localhost:8000/callback)
GEOCACHING_ENV=production    # Geocaching API environment
```

## Important Notes

- `/public/roadbook/` is where generated roadbooks are written — ensure it's writable
- PHP 8.3+ required; also needs XSL, Tidy extensions for roadbook generation
- The `importmap.php` file manages JS import maps (Symfony Asset Mapper)

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