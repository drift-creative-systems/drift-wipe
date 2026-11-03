=== Drift: Wipe ===
Contributors: driftcreativesystems
Tags: page transitions, transition, animation, page loader, fade
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Full-screen page transitions for any theme. Fade, slide or curtain wipes at your own speed, with real page loads underneath.

== Description ==

Drift: Wipe plays a full-screen wipe when a visitor clicks a link to another page on your site, then reveals the new page as it loads.

It doesn't swap content with AJAX. The browser still does a normal page load and the overlay just covers it, so it works with any theme, page builder, caching plugin or analytics setup without changes.

Six styles, each with a live preview on the settings screen:

* **Fade**: the overlay fades in, then fades out on the new page.
* **Slide Up**: the overlay slides up from the bottom, then off the top.
* **Slide Down**: the overlay slides down from the top, then off the bottom.
* **Slide Left**: the overlay slides in from the right, then off to the left.
* **Slide Right**: the overlay slides in from the left, then off to the right.
* **Curtain**: two panels close from the sides, then open again.

Features:

* Pick the overlay colour to match your brand.
* Choose Fast, Normal or Slow.
* Turn it off on any page, such as a landing page, with a checkbox on the edit screen.
* Skip the front page, if your theme already has its own intro or loader there.
* Turn it off for any link by adding the `no-transition` class.
* Leaves alone links that open in a new tab, downloads, same-page anchors, email and phone links, links to other sites, wp-admin, and clicks with Ctrl, Cmd, Shift or the middle button.
* Respects the visitor's reduced motion setting: no overlay at all.
* Back and forward buttons never leave the overlay stuck over the page.
* Nothing loads on the front end while the style is set to None.
* Everything is removed when you delete the plugin.

More styles (Diagonal, Circle Reveal and Shutter) are available in the separate Drift: Wipe add-on. Everything in this plugin works fully without it.

= Privacy =

Drift: Wipe doesn't connect to any external service, set cookies, track visitors or collect data.

= For developers =

* `drift_wipe_capability` (filter): the capability needed to manage the settings. Default `manage_options`.
* `drift_wipe_active` (filter): return `false` to turn the transition off on the current page, e.g. a checkout or landing page. Receives `( $active, $style )`.
* `drift_wipe_styles` (filter): add or remove transition styles. Receives an array of `key => label`. For a new style, also enqueue CSS that animates `.drift-wipe-overlay[data-wipe-style="your-key"]`, on the front end and on the settings screen for its preview. None is always kept.
* `drift_wipe_colour` (filter): change the overlay colour on the front end. Must return a hex colour.
* `drift_wipe_show_addon_note` (filter): return `false` to hide the line about the add-on on the settings screen.
* `drift_wipe_leave` (jQuery event on `document`): fires just before the exit animation, with the overlay element and the clicked link.

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

Check that a style other than None is chosen, and clear your page cache if you use one.

The overlay is added straight after the opening `<body>` tag through `wp_body_open()`. All default themes and block themes call it. If your theme doesn't, the overlay is added in the footer instead, so the transition still works, but the entrance can start a little late on long pages. Asking the theme author to add `wp_body_open()` fixes that.

= How do I stop one link playing the transition? =

Add `no-transition` to the link's CSS classes. In the block editor, select the link's block and use Advanced → Additional CSS class(es).

= How do I turn it off on one page, like a landing page? =

Edit the page and tick "Turn off the page transition" in the Page transition box in the sidebar. No transition plays when that page opens, when visitors leave it, or when they click a link to it from elsewhere on the site. It works for posts and any public custom post type too.

= Does it respect the reduced motion setting? =

Yes. If a visitor has reduced motion turned on (System Settings → Accessibility → Display on a Mac, Settings → Accessibility → Motion on iPhone, or Settings → Accessibility → Visual effects on Windows), there's no overlay at all and links work as normal.

= Why does Safari's toolbar change colour during the transition? =

Safari tints its toolbar to match the top of the page, so while the overlay covers the screen the toolbar takes its colour. It goes back to normal once the new page is revealed.

= Does it slow my site down? =

The exit animation adds well under a second before the next page starts loading. The files are small and only load when a style is chosen.

= My theme already has a loading screen on the home page =

Tick "Don't play the entrance animation on the front page". Links from the front page to other pages still play the transition.

= Does it work with caching plugins? =

Yes. After changing the settings, clear your page cache so visitors get the new version.

== Changelog ==

= 1.1.0 =
* New styles: Slide Down, Slide Left and Slide Right.
* Live previews of each style on the settings screen.
* New Speed setting: Fast, Normal or Slow.
* Turn the transition off on any page from its edit screen.
* New `drift_wipe_styles` and `drift_wipe_colour` filters, and a `drift_wipe_leave` script event.
* Fix: Safari could keep the overlay colour in its toolbar after the page had loaded.
* Works with themes that don't call `wp_body_open()`.
* Fix: the page could stay unscrollable if the theme doesn't call `wp_body_open()` or JavaScript doesn't run.
* Fix: the overlay could stay over the page after clicking a link to a file download.

= 1.0.0 =
* First public release: Fade, Slide Up and Curtain transitions, overlay colour, front page option and per-link opt-out.

== Upgrade Notice ==

= 1.1.0 =
Three new slide styles, live previews, a speed setting and a per-page off switch.

= 1.0.0 =
First public release.
