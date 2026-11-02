/**
 * Drift: Wipe.
 *
 * The animation itself runs in CSS (wipe.css). This script does two
 * things: cleans up after the entrance animation, and plays the exit
 * animation on internal link clicks before letting the browser do its
 * normal, real navigation. There's no AJAX content swap, so it works with
 * every plugin, shortcode and analytics setup as-is.
 *
 * Does nothing when the overlay markup isn't on the page (style set to
 * "None", or the front page is skipped; see drift_wipe_is_active()).
 */
(function ($) {
	$(function () {
		var $overlay = $('#drift-wipe-overlay');
		if (!$overlay.length) {
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

		function clearEntrance() {
			$overlay.css('pointer-events', 'none');
			$('body').removeClass('has-drift-wipe');
		}

		// Once the entrance animation finishes, turn pointer-events off so
		// the (now invisible) overlay can never block a click.
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
		}, 1500);

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

			e.preventDefault();
			navigating = true;

			var finished = false;
			var go = function () {
				if (finished) {
					return;
				}
				finished = true;
				window.location.href = href;
			};

			$overlay.css('pointer-events', 'auto').one('animationend webkitAnimationEnd', go);
			$overlay.addClass('is-leaving');

			// Safety net in case animationend never fires.
			setTimeout(go, 900);
		});

		// When the page comes back from the back/forward cache, clear any
		// "leaving" state so the overlay doesn't stay over the page.
		$(window).on('pageshow', function (e) {
			var evt = e.originalEvent || e;
			if (evt.persisted) {
				navigating = false;
				$overlay.removeClass('is-leaving').css('pointer-events', 'none');
			}
		});
	});
})(jQuery);
