/**
 * Drift: Wipe.
 *
 * The animation itself runs in CSS (wipe.css). This script does two
 * things: cleans up after the entrance animation, and plays the exit
 * animation on internal link clicks before letting the browser do its
 * normal, real navigation. It also passes the click position to the CSS
 * as --drift-wipe-x / -y for styles that start from it. There's no AJAX
 * content swap, so it works with every plugin, shortcode and analytics
 * setup as-is.
 *
 * Does nothing when the overlay markup isn't on the page (style set to
 * "None", the front page is skipped, or the page has the transition
 * turned off; see drift_wipe_is_active()).
 *
 * Fires "drift_wipe_leave" on document, with the overlay element and the
 * clicked link, just before the exit animation starts. Add-ons can use it
 * to change the overlay (e.g. its colour) for that exit.
 */
(function ($) {
	$(function () {
		var $overlay = $('#drift-wipe-overlay');
		if (!$overlay.length) {
			// Overlay missing (e.g. removed by another script): make sure
			// the scroll lock doesn't outlive it.
			$('body').removeClass('has-drift-wipe');
			return;
		}

		var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduceMotion) {
			$overlay.remove();
			$('body').removeClass('has-drift-wipe');
			return;
		}

		var overlayEl = $overlay.get(0);
		var navigating = false;
		var resetTimer = null;

		// The Speed setting's multiplier. Every animation duration in
		// wipe.css is scaled by it, so the safety timeouts below are too.
		var speed = parseFloat($overlay.attr('data-wipe-speed')) || 1;

		// Paths of pages with the transition turned off (see
		// drift_wipe_get_excluded_paths()). Links to them skip the exit,
		// because the page they open has no entrance to reveal it.
		var excluded = window.driftWipe && Array.isArray(window.driftWipe.excluded) ? window.driftWipe.excluded : [];

		// Links that usually download a file rather than load a page. The
		// browser stays on this page afterwards, so there's nothing to
		// reveal it again.
		var fileLink = /\.(pdf|zip|rar|7z|gz|tar|dmg|exe|msi|apk|docx?|xlsx?|pptx?|odt|ods|csv|rtf|epub|mp3|wav|m4a|mp4|mov|avi|webm)$/i;

		// Same format as drift_wipe_normalise_path(): decoded, one leading
		// slash, no trailing slash.
		function normalisePath(path) {
			try {
				path = decodeURIComponent(path);
			} catch (err) {
				// Malformed escape: compare it as it is.
			}
			return '/' + path.replace(/^\/+|\/+$/g, '');
		}

		function isExcluded(link) {
			if (!excluded.length) {
				return false;
			}
			var path = normalisePath(link.pathname);
			return excluded.indexOf(path) !== -1 || excluded.indexOf(path + link.search) !== -1;
		}

		// Takes the overlay out of the layout once the entrance is done
		// (.is-idle is display: none), so it can't block clicks or tint
		// Safari's toolbar.
		function clearEntrance() {
			$overlay.css('pointer-events', 'none').addClass('is-idle');
			$('body').removeClass('has-drift-wipe');
		}

		$overlay.on('animationend webkitAnimationEnd', function (e) {
			var evt = e.originalEvent || e;
			if (evt.target !== overlayEl && !$.contains(overlayEl, evt.target)) {
				return;
			}
			if (!$overlay.hasClass('is-leaving')) {
				clearEntrance();
			}
		});

		// Safety net in case the entrance animationend event never fires.
		setTimeout(function () {
			if (!$overlay.hasClass('is-leaving')) {
				clearEntrance();
			}
		}, 1500 * speed);

		$(document).on('click', 'a[href]', function (e) {
			if (navigating) {
				return;
			}

			// Another handler already cancelled this click (e.g. a mega
			// menu, accordion or modal trigger that's an <a> but doesn't
			// navigate anywhere). Respect that and do nothing.
			if (e.isDefaultPrevented()) {
				return;
			}

			var link = this;
			var href = link.getAttribute('href');

			if (
				!href ||
				href.charAt(0) === '#' ||
				href.indexOf('mailto:') === 0 ||
				href.indexOf('tel:') === 0 ||
				href.indexOf('javascript:') === 0 ||
				href.indexOf('/wp-admin/') !== -1 ||
				href.indexOf('wp-login.php') !== -1 ||
				link.target ||
				link.hasAttribute('download') ||
				fileLink.test(link.pathname) ||
				link.search.indexOf('download_file=') !== -1 ||
				link.classList.contains('no-transition') ||
				link.hostname !== window.location.hostname ||
				e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0
			) {
				return;
			}

			// Same-page anchor link: leave it to any smooth-scroll handler.
			if (link.pathname === window.location.pathname && link.hash) {
				return;
			}

			if (isExcluded(link)) {
				return;
			}

			e.preventDefault();
			navigating = true;

			var finished = false;
			var go = function () {
				if (finished) {
					return;
				}
				finished = true;
				window.location.href = href;

				// If this page is still here after a few seconds, the click
				// didn't navigate (a file download the filter above missed,
				// or a "leave this page?" prompt that was cancelled). Play
				// the entrance again so the overlay doesn't stay stuck.
				// pagehide cancels this when the page really unloads.
				resetTimer = setTimeout(reset, 4000);
			};

			// Where the exit starts, for styles that use it. Keyboard
			// activation reports no pointer position (detail 0), so use
			// the centre of the link instead.
			var evt = e.originalEvent || e;
			var x = evt.clientX;
			var y = evt.clientY;
			if (!evt.detail) {
				var rect = link.getBoundingClientRect();
				x = rect.left + rect.width / 2;
				y = rect.top + rect.height / 2;
			}
			overlayEl.style.setProperty('--drift-wipe-x', Math.round(x) + 'px');
			overlayEl.style.setProperty('--drift-wipe-y', Math.round(y) + 'px');

			$(document).trigger('drift_wipe_leave', [overlayEl, link]);

			$overlay.css('pointer-events', 'auto').one('animationend webkitAnimationEnd', go);
			$overlay.removeClass('is-idle').addClass('is-leaving');

			// Safety net in case animationend never fires.
			setTimeout(go, 900 * speed);
		});

		// Clears the "leaving" state. Removing .is-leaving swaps the
		// entrance keyframes back in, which reveals the page again; its
		// animationend sets .is-idle once more.
		function reset() {
			clearTimeout(resetTimer);
			navigating = false;
			if (!$overlay.hasClass('is-leaving')) {
				// Left through a link that skipped the exit: the page is
				// already uncovered, so don't replay the entrance.
				return;
			}
			$overlay.removeClass('is-leaving is-idle').css('pointer-events', 'none');
		}

		$(window).on('pagehide', function () {
			clearTimeout(resetTimer);
		});

		// When the page comes back from the back/forward cache, clear any
		// "leaving" state so the overlay doesn't stay over the page.
		$(window).on('pageshow', function (e) {
			var evt = e.originalEvent || e;
			if (evt.persisted) {
				reset();
			}
		});
	});
})(jQuery);
