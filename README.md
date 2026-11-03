![Drift Creative Systems](.github/banner.jpg)

# Drift: Wipe

Free WordPress plugin by **Drift Creative Systems**. Plays a full-screen wipe (Fade, Slide Up/Down/Left/Right or Curtain) when a visitor clicks a link to another page on the site. There's no AJAX content swap: the browser does a real navigation and the overlay covers and reveals it, so it works with any theme, plugin, cache or analytics setup as-is.

- WordPress.org slug: `drift-wipe` (submission pending)
- Licence: GPL-2.0-or-later
- Requires WordPress 6.0+, PHP 7.4+

The user-facing documentation is [readme.txt](readme.txt), which WordPress.org displays. This file is for developers.

## How it works

- **Entrance** (page load) is a CSS animation on the overlay, so there's no flash of uncovered content while the script loads.
- When the entrance ends, the script adds `.is-idle` (`display: none`) to the overlay. Safari tints its toolbar from fixed elements at the top of the page, even invisible ones, so the overlay must be out of the layout between transitions.
- **Exit** (internal link click) is triggered by `assets/wipe.js`: it cancels the click, sets `--drift-wipe-x` / `--drift-wipe-y` to the click point (the link's centre for keyboard clicks) for styles that start from it, fires `drift_wipe_leave` on `document`, swaps `.is-idle` for `.is-leaving` (which swaps in the exit keyframes), waits for `animationend` or 900 ms × speed, whichever comes first, then sets `window.location.href`. At Normal speed, keep every exit animation under 900 ms.
- **Speed**: every duration in `wipe.css` is `calc(<time> * var(--drift-wipe-speed, 1))`. The multiplier (0.6 / 1 / 1.5) is printed on `:root` and as `data-wipe-speed` on the overlay, which the script uses to scale its safety timeouts.
- **Per-page off switch**: the `_drift_wipe_disabled` post meta (meta box on every public post type) makes `drift_wipe_is_active()` false, so nothing loads on that page. The paths of all such published pages are cached in the `drift_wipe_excluded_paths` transient (flushed on any post save, trash, delete, and permalink or front page change) and passed to the script as `driftWipe.excluded`, so links to them skip the exit. Paths are compared decoded, with one leading and no trailing slash; plain permalinks keep their query string.
- Skipped: links to pages with the transition turned off, `#` anchors and same-page hash links, `mailto:` / `tel:` / `javascript:`, `target` set, `download`, links to common file types (`.pdf`, `.zip`, `.docx`, media…) and WooCommerce `download_file=` URLs, `wp-admin` / `wp-login.php`, other hosts, modified or non-left clicks, clicks another handler already cancelled, and links with the `no-transition` class.
- If the page is still there 4 s after the exit (a download the filter missed, or a cancelled "leave this page?" prompt), `.is-leaving` is removed and the entrance plays again. `pagehide` cancels that timer on a real navigation.
- Scroll is locked on `html:has(> body.has-drift-wipe)` during the entrance, with the scrollbar gutter reserved so the width doesn't jump. The script removes the class when the entrance ends; without JS, a `driftWipeUnlock` animation lifts the lock after 1.5 s.
- `prefers-reduced-motion: reduce` removes the overlay and the scroll lock.
- On `pageshow` with `event.persisted` (back/forward cache) the leaving state is cleared, so the overlay never stays stuck over the page.
- Overlay markup is printed on `wp_body_open`. Themes that don't call `wp_body_open()` get it at the start of `wp_footer` instead (it only prints once).
- `drift-wipe.php` holds the header, the duplicate-install guard and the constants; everything else is in `includes/plugin.php`. Keep functions out of the main file: PHP declares a file's top-level functions before running it, so the guard's `return` can't stop them redeclaring.
- Front-end assets, overlay and the `has-drift-wipe` body class are all gated by `drift_wipe_is_active()`, cached per request.

| Front end | Name |
|---|---|
| Overlay | `#drift-wipe-overlay.drift-wipe-overlay[data-wipe-style]` |
| Panels | `.drift-wipe-overlay__panel--left` / `--right` |
| Body class (scroll lock during entrance) | `has-drift-wipe` |
| Colour | CSS custom property `--drift-wipe-colour` |
| Speed multiplier | CSS custom property `--drift-wipe-speed`, and `data-wipe-speed` on the overlay |
| Idle (between transitions) | `.is-idle` on the overlay |
| Click point (exit) | `--drift-wipe-x` / `--drift-wipe-y`, set inline on the overlay |
| Excluded paths | `window.driftWipe.excluded` |
| Opt-out class | `no-transition` |

## Settings

Settings → Drift: Wipe (`options-general.php?page=drift-wipe`). Styles are radio cards with a preview each: the real overlay markup (`drift_wipe_overlay_html()`) and `wipe.css` keyframes inside a mock page, played by `assets/admin.js` on hover or focus.

Four options, the `_drift_wipe_disabled` post meta and the `drift_wipe_excluded_paths` transient, all deleted in `uninstall.php` on every site in a multisite network:

| Option | Type | Default |
|---|---|---|
| `drift_wipe_style` | `none` / `fade` / `slide-up` / `slide-down` / `slide-left` / `slide-right` / `curtain` (plus any added by `drift_wipe_styles`) | `none` |
| `drift_wipe_speed` | `fast` / `normal` / `slow` | `normal` |
| `drift_wipe_colour` | hex colour | `#111111` |
| `drift_wipe_skip_front_page` | boolean | `true` |

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `drift_wipe_capability` | filter | Capability to view and save the settings. Default `manage_options` |
| `drift_wipe_active` | filter | Turn the transition on or off for the current request: `( $active, $style )` |
| `drift_wipe_styles` | filter | Add or remove styles: `key => label`. A new key needs its own CSS for `.drift-wipe-overlay[data-wipe-style="key"]`, enqueued on the front end and on the settings screen (`settings_page_drift-wipe`) for its preview. Only the left panel shows by default; show `.drift-wipe-overlay__panel--right` for two-panel styles. `none` is always kept |
| `drift_wipe_colour` | filter | Overlay colour on the front end. Sanitised as hex afterwards |
| `drift_wipe_show_addon_note` | filter | Return `false` to hide the add-on line under the style cards |
| `drift_wipe_leave` | jQuery event on `document` | Fires before each exit: `( event, overlayEl, link )`. E.g. set `--drift-wipe-colour` on the overlay for colour cycling |

These are the extension points for the paid add-on, sold outside WordPress.org. Nothing in this plugin is gated or shown locked. The add-on provides Diagonal, Circle Reveal and Shutter (moved out of the free plugin before 1.1.0 was released) and colour cycling.

## Development

- Coding standard: `phpcs.xml.dist` (WordPress, prefixes `drift_wipe` / `DRIFT_WIPE`).
- Run **Plugin Check** before every release. The PHPCS-based checks don't work in WordPress Playground (its filesystem can't `flock()`), so use a real install, e.g. WP-CLI with the SQLite drop-in:
  ```sh
  wp plugin install plugin-check --activate
  wp plugin check drift-wipe --include-low-severity-errors --include-low-severity-warnings \
    --require=./wp-content/plugins/plugin-check/cli.php
  ```
  Check a copy built from `.distignore`, not the repo, so dev files don't trip the `file_type` check.
- Directory guidelines review: the `wp-plugin-directory-guidelines` Claude Code skill.

## Releasing

1. Bump the version in **three** places: the `Version:` header, `DRIFT_WIPE_VERSION` and `Stable tag:` in `readme.txt`. Add a changelog entry to both `readme.txt` and `CHANGELOG.md`.
2. Merge `develop` → `main`, then publish a GitHub release tagged `vX.Y.Z`.
3. `.github/workflows/release.yml` checks the three versions match, builds `drift-wipe.zip` from `.distignore` and attaches it to the release.
4. Once approved on WordPress.org, set the repo variable `WPORG_DEPLOY=true` and the secrets `SVN_USERNAME` / `SVN_PASSWORD`. Releases then deploy to SVN automatically, and readme/asset changes on `main` sync on their own (`wporg-assets.yml`).

WordPress.org assets (icon, banners, screenshots) live in `.wordpress-org/`.

## Licence

GPL-2.0-or-later. See [LICENSE](LICENSE).

Copyright © 2026 Drift Creative Systems Ltd

Drift Creative Systems Ltd. Registered in England and Wales, company number 17548220. Registered office: 167-169 Great Portland Street, 5th Floor, London W1W 5PF
