# Changelog

All notable changes to this project are documented here. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/).

## [1.1.0] - 2026-11-03

### Added
- Three transition styles: Slide Down, Slide Left and Slide Right.
- Settings screen shows each style as a card with a live preview. It plays on hover or keyboard focus and follows the colour and speed fields as they change. Nothing plays with reduced motion on.
- Speed setting (`drift_wipe_speed`: `fast` / `normal` / `slow`), applied as the `--drift-wipe-speed` multiplier on every animation and the script's safety timeouts.
- "Turn off the page transition" checkbox on the edit screen of every public post type (`_drift_wipe_disabled` post meta). The page plays no entrance or exit, and links to it from other pages skip the exit.
- A line on the settings screen pointing to the add-on's extra styles, hidden with the `drift_wipe_show_addon_note` filter.
- Hooks for add-ons: `drift_wipe_styles` filter (`none` is always kept first), `drift_wipe_colour` filter, and a `drift_wipe_leave` jQuery event on `document` before each exit.
- `wipe.js` sets `--drift-wipe-x` / `--drift-wipe-y` (the click point, or the link's centre for keyboard clicks) on the overlay before the exit, for styles that start from it.
- Themes that don't call `wp_body_open()` get the overlay in `wp_footer` instead.
- Readme FAQs on reduced motion, turning it off per page, and Safari's toolbar tint.

### Changed
- Plugin code moved from `drift-wipe.php` to `includes/plugin.php`.
- Scroll lock moved from `body` to `html:has(> body.has-drift-wipe)`, and `scrollbar-gutter: stable` now applies only while it's locked instead of on every page.
- The right overlay panel is hidden by default and shown only for Curtain, so styles added by filter don't need to hide it.
- Plugin URI now points to the GitHub repository instead of the Author URI.

### Fixed
- Safari could keep the overlay colour in its toolbar after the page had loaded. The overlay is now `display: none` (`.is-idle`) between transitions.
- The page could stay unscrollable when the theme doesn't call `wp_body_open()` or JavaScript doesn't run. The lock now lifts after 1.5 s (scaled by the speed) without JS.
- The overlay could stay over the page after clicking a file link (PDF, zip, WooCommerce download) or cancelling a "leave this page?" prompt. File links are now skipped, and the page is revealed again if it's still there 4 s after the exit.
- The duplicate install guard didn't prevent the "Cannot redeclare" fatal error.

## [1.0.0] - 2026-11-02

First release as **Drift: Wipe**, a free WordPress.org plugin by Drift Creative Systems. Based on Bonsai Page Transitions 1.2.1, which carries on separately for Bonsai client sites. There is no data migration between the two.

### Added
- `drift_wipe_active` filter to turn the transition on or off per request.
- `readme.txt`, `LICENSE` (GPLv2), `uninstall.php` (multisite aware), WordPress.org icon and banners, release workflow with optional SVN deploy.
- Copyright and company details in the admin footer on Settings → Drift: Wipe, the README, readme.txt and the main file header.

### Changed
- Renamed to Drift: Wipe: slug and text domain `drift-wipe`, prefix `drift_wipe_` / `DRIFT_WIPE_`.
- Options renamed to `drift_wipe_style`, `drift_wipe_colour` and `drift_wipe_skip_front_page`.
- Settings moved to Settings → Drift: Wipe, with the Drift monogram in sky blue (`#0ea5e9`).
- Front-end markup renamed: overlay `#drift-wipe-overlay`, `data-wipe-style`, body class `has-drift-wipe`, CSS property `--drift-wipe-colour`, keyframes `driftWipe*`. The `no-transition` opt-out class is unchanged.
- Assets moved to `assets/wipe.css`, `assets/wipe.js` and `assets/admin.css`.
- Minimum PHP lowered to 7.4.

### Fixed
- With reduced motion turned on, the page could stay unscrollable because the scroll lock on `body` was never removed.

### Removed
- Plugin Update Checker and Composer. Updates come from WordPress.org.
- Bundled Bonsai Hub menu and suite installer.
