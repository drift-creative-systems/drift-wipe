![Drift Creative Systems](.github/banner.jpg)

# Drift: Wipe

Free WordPress plugin by **Drift Creative Systems**. Plays a full-screen wipe (Fade, Slide Up or Curtain) when a visitor clicks a link to another page on the site. There's no AJAX content swap: the browser does a real navigation and the overlay covers and reveals it, so it works with any theme, plugin, cache or analytics setup as-is.

- WordPress.org slug: `drift-wipe` (submission pending)
- Licence: GPL-2.0-or-later
- Requires WordPress 6.0+, PHP 7.4+

The user-facing documentation is [readme.txt](readme.txt), which WordPress.org displays. This file is for developers.

## How it works

- **Entrance** (page load) is a CSS animation on the overlay, so there's no flash of uncovered content while the script loads.
- **Exit** (internal link click) is triggered by `assets/wipe.js`: it cancels the click, adds `.is-leaving` to the overlay (which swaps in the exit keyframes), waits for `animationend` or 900 ms, whichever comes first, then sets `window.location.href`.
- Skipped: `#` anchors and same-page hash links, `mailto:` / `tel:` / `javascript:`, `target` set, `download`, `wp-admin` / `wp-login.php`, other hosts, modified or non-left clicks, clicks another handler already cancelled, and links with the `no-transition` class.
- `prefers-reduced-motion: reduce` removes the overlay and the scroll lock.
- On `pageshow` with `event.persisted` (back/forward cache) the leaving state is cleared, so the overlay never stays stuck over the page.
- Overlay markup is printed on `wp_body_open`, so the theme must call `wp_body_open()`.
- Front-end assets, overlay and the `has-drift-wipe` body class are all gated by `drift_wipe_is_active()`, cached per request.

| Front end | Name |
|---|---|
| Overlay | `#drift-wipe-overlay.drift-wipe-overlay[data-wipe-style]` |
| Panels | `.drift-wipe-overlay__panel--left` / `--right` |
| Body class (scroll lock during entrance) | `has-drift-wipe` |
| Colour | CSS custom property `--drift-wipe-colour` |
| Opt-out class | `no-transition` |

## Settings

Settings → Drift: Wipe (`options-general.php?page=drift-wipe`). Three options, all deleted in `uninstall.php` on every site in a multisite network:

| Option | Type | Default |
|---|---|---|
| `drift_wipe_style` | `none` / `fade` / `slide-up` / `curtain` | `none` |
| `drift_wipe_colour` | hex colour | `#111111` |
| `drift_wipe_skip_front_page` | boolean | `true` |

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `drift_wipe_capability` | filter | Capability to view and save the settings. Default `manage_options` |
| `drift_wipe_active` | filter | Turn the transition on or off for the current request: `( $active, $style )` |

These are the extension points for a paid add-on, sold outside WordPress.org. Nothing in this plugin is gated.

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
