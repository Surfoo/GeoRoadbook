# GeoRoadbook — Design Brief (Product + UI Refresh)

## 1. The Product

**GeoRoadbook** is a free, open-source web app for **geocachers**. It converts a GPX file (from Geocaching.com Pocket Queries, GSAK, GCTour, etc.) into a **customizable, ready-to-print roadbook**: a trail booklet listing geocaches with descriptions, hints, waypoints, and recent logs. The user uploads a GPX, configures options, can edit the generated content in-browser (TinyMCE rich editor), then exports as **HTML, PDF, or ZIP**.

- **Audience:** geocachers (outdoor hobby), from beginners to power-cachers preparing a trail or an event.
- **Tone:** adventure, cartography, exploration — but a serious, precise tool (GPS data).
- **No database, no native accounts:** optional sign-in via Geocaching.com OAuth, session-only. Generated roadbooks are auto-deleted after 30 days.

## 2. User Journey

1. **Land on the single main page** (`/`) — hero + generation form.
2. **Choose a source:** drag & drop a GPX file (8 MB max), or — if signed in with Geocaching.com — pick a **Pocket Query** from a dropdown fetched from their account.
3. **Pick the roadbook language** (headings/labels generated in the final document).
4. **Customize** via checkboxes:
   - *General:* sort caches (by name / owner / difficulty — conditionally revealed radio options), table of contents.
   - *Geocache content:* note area, short description, long description, hints, waypoints, recent logs.
   - *Advanced (collapsed by default):* remove images (save ink), include spoilers (Spoilers4gpx), page break per cache.
5. **Primary CTA "Generate Roadbook"** in a sticky action footer, next to a reassurance badge: "Private & Secure — roadbooks deleted after 30 days."
6. After generation: view/edit the roadbook, export HTML/PDF/ZIP.

## 3. High-Level Vibe & Theme

**Vibe:** A sophisticated, adventurous "Modern Map" aesthetic. It should feel like a high-end GPS dashboard or a premium topographic mapping application.
**Style:** Minimalist Industrial with "Adventure" accents. Deep charcoal and slate palette with **"Signal Orange" (#e85d04)** for primary actions and **Forest Green** for secondary elements; an occasional "sky" blue for informational touches.
**Typography:**
- **Headings:** 'Playfair Display' — classic, elegant serif.
- **Body:** 'DM Sans' — high readability.
- **Labels/Data:** 'JetBrains Mono' — technical, precise feel.

## 4. Layout Structure

**Header (all pages):**
- A slim, frosted-glass navigation bar.
- Left: brand "◎ GeoRoadbook" (compass icon) in bold serif.
- Right: "Sign in" button (Geocaching.com OAuth) **or**, when signed in, a user pill with a circular 24px avatar, username, and a minimal sign-out link.

**Hero Section:**
- Eyebrow label ("The ultimate geocaching tool"), large title ("Ready for your next adventure?" / "Create your roadbook."), descriptive subtext: "Generate a customizable, ready-to-print roadbook from your GPX files."
- Background: subtle, animated topographic line pattern in a slightly lighter charcoal than the base background.

**The "Source" Card (primary action):**
- A large, central glassmorphism card, 12px corner radius, subtle border-glow.
- **File upload:** dedicated drag-and-drop zone with dashed border and a "Map Pin" icon; on file selection the icon becomes a checkmark and the filename is shown.
- **Pocket Queries:** clean dropdown, only shown when the user is signed in.
- **Language selector:** simple, searchable select box, with an info note explaining it controls the generated document's labels.

**The "Options" Grid:**
- Two-column responsive grid of "Feature Cards":
  - **Group 1 — General Settings:** sort (with conditionally revealed radio options), table of contents.
  - **Group 2 — Geocache Content:** notes, descriptions (short/long), hints, waypoints, recent logs.
- **Interactive elements:** custom checkboxes turning Signal Orange when checked; small "info" icon per label with hover tooltip.
- **Advanced toggle:** "Page break", "Remove images", and "Spoilers" hidden behind a "Show Advanced Options" text link (progressive disclosure — the form is dense).

**Action Footer (sticky):**
- Persistent bottom bar (appears once a file is selected).
- Primary "Generate Roadbook" button — large, orange, subtle pulse animation.
- Left side (desktop): shield icon + "Private & Secure — roadbooks deleted after 30 days."

**Secondary "tunnel" page (signed-out users reaching member features):**
- Short explanatory text + large "Sign in with geocaching.com" button.

**Footer:**
- Discreet brand mark, links to FAQ / About (opened as **modals**) / GitHub, baseline "Made with ❤️ for the world's geocachers. Happy hunting!"

## 5. Specific Component Details

- **Buttons:** 8px rounded corners, slight elevation shadow, smooth hover transition.
- **Input fields:** deep charcoal background, 1px slate border glowing orange on focus.
- **Alerts & flash messages:** semi-transparent backgrounds with high-contrast left borders — Success (Green), Warning (Amber), Error (Red). Needed for: validation errors, deletion success flash, generation errors.
- **Modals (FAQ/About):** clean, center-aligned, background blur. FAQ covers accepted formats, limits, privacy; About presents the open-source project.
- **States to design:** visitor vs. signed-in, empty vs. file-dropped drop zone, form validation errors, success flash, loading state during generation.

## 6. Visual Assets & Iconography

- Subtle geocaching-themed icons: compass, map, map pin, cache container, hiking boot, shield (privacy), gear (advanced options).
- All icons/illustrations use Signal Orange and Forest Green accents for consistency.

## 7. Constraints

- Single main page: visual hierarchy must funnel toward the "Generate" CTA.
- Dense form (many checkboxes) → clear grouping and progressive disclosure are essential.
- Fully responsive (may be used on mobile before heading out), but the final output is designed for **print**.
- Current stack (context, not a hard requirement): Bootstrap 5 + Bootstrap Icons, light jQuery interactions, custom CSS with variables (`--bg-base`, `--border`, …).
