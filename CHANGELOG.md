# Changelog

All notable changes to this project are documented here. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/).

## [1.1.0] - 2026-11-03

### Added
- Six transition styles: Slide Down, Slide Left, Slide Right, Shutter (top and bottom panels), Diagonal (clip-path wipe) and Circle Reveal (grows from the click point, or the link's centre for keyboard clicks).
- `drift_wipe_styles` filter to add or remove styles. `none` is always kept as the first option.
- `wipe.js` sets `--drift-wipe-x` / `--drift-wipe-y` on the overlay before the exit animation.

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
