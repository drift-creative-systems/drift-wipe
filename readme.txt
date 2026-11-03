=== Drift: Wipe ===
Contributors: driftcreativesystems
Tags: page transitions, transition, animation, page loader, fade
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Full-screen page transitions for any theme. Fade, slide, curtain, shutter, diagonal or circle wipes, with real page loads underneath.

== Description ==

Drift: Wipe plays a full-screen wipe when a visitor clicks a link to another page on your site, then reveals the new page as it loads.

It doesn't swap content with AJAX. The browser still does a normal page load and the overlay just covers it, so it works with any theme, page builder, caching plugin or analytics setup without changes.

Nine styles:

* **Fade**: the overlay fades in, then fades out on the new page.
* **Slide Up**: the overlay slides up from the bottom, then off the top.
* **Slide Down**: the overlay slides down from the top, then off the bottom.
* **Slide Left**: the overlay slides in from the right, then off to the left.
* **Slide Right**: the overlay slides in from the left, then off to the right.
* **Curtain**: two panels close from the sides, then open again.
* **Shutter**: two panels close from the top and bottom, then open again.
* **Diagonal**: an angled wipe sweeps across the screen from left to right.
* **Circle Reveal**: a circle grows from where the visitor clicked, then shrinks away to the centre on the new page.

Features:

* Pick the overlay colour to match your brand.
* Skip the front page, if your theme already has its own intro or loader there.
* Turn it off for any link by adding the `no-transition` class.
* Leaves alone links that open in a new tab, downloads, same-page anchors, email and phone links, links to other sites, wp-admin, and clicks with Ctrl, Cmd, Shift or the middle button.
* Respects the visitor's reduced motion setting: no overlay at all.
* Back and forward buttons never leave the overlay stuck over the page.
* Nothing loads on the front end while the style is set to None.
* Everything is removed when you delete the plugin.

= Privacy =

Drift: Wipe doesn't connect to any external service, set cookies, track visitors or collect data.

= For developers =

* `drift_wipe_capability` (filter): the capability needed to manage the settings. Default `manage_options`.
* `drift_wipe_active` (filter): return `false` to turn the transition off on the current page, e.g. a checkout or landing page. Receives `( $active, $style )`.
* `drift_wipe_styles` (filter): add or remove transition styles. Receives an array of `key => label`. For a new style, also enqueue CSS that animates `.drift-wipe-overlay[data-wipe-style="your-key"]`. None is always kept.

Source code, issues and pull requests: [github.com/drift-creative-systems/drift-wipe](https://github.com/drift-creative-systems/drift-wipe).

= Copyright =

Copyright © 2026 Drift Creative Systems Ltd

Drift Creative Systems Ltd. Registered in England and Wales, company number 17548220. Registered office: 167-169 Great Portland Street, 5th Floor, London W1W 5PF

== Installation ==

1. In your WordPress admin, go to Plugins → Add New, search for "Drift: Wipe", then install and activate it.
2. Go to Settings → Drift: Wipe.
3. Choose a transition style and overlay colour, then click Save Changes.

== Frequently Asked Questions ==

= Nothing happens when I click a link =

Check that a style other than None is chosen. The overlay is added straight after the opening `<body>` tag, so your theme needs to call `wp_body_open()`. All default themes and block themes do, but some older themes don't. Ask the theme author to add it.

= How do I stop one link playing the transition? =

Add `no-transition` to the link's CSS classes. In the block editor, select the link's block and use Advanced → Additional CSS class(es).

= Does it slow my site down? =

The exit animation adds well under a second before the next page starts loading. The files are small and only load when a style is chosen.

= My theme already has a loading screen on the home page =

Tick "Don't play the entrance animation on the front page". Links from the front page to other pages still play the transition.

= Does it work with caching plugins? =

Yes. After changing the settings, clear your page cache so visitors get the new version.

== Changelog ==

= 1.1.0 =
* New styles: Slide Down, Slide Left, Slide Right, Shutter, Diagonal and Circle Reveal.
* New `drift_wipe_styles` filter for adding or removing styles.

= 1.0.0 =
* First public release: Fade, Slide Up and Curtain transitions, overlay colour, front page option and per-link opt-out.

== Upgrade Notice ==

= 1.1.0 =
Six new transition styles, including Circle Reveal from the click point.

= 1.0.0 =
First public release.
