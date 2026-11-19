/**
 * Drift: Wipe settings screen.
 *
 * Starts the core colour picker and plays the style previews. Each preview
 * is the real overlay markup with the real keyframes from wipe.css, so it
 * matches the front end. A preview loops while its card is hovered or
 * focused, and picks up the colour and speed fields as they change. With
 * reduced motion on, wipe.css hides the previews and nothing plays.
 */
(function ($) {
	$(function () {
		var $grid = $('.drift-wipe-styles');
		var gridEl = $grid.get(0);
		var $speed = $('#drift_wipe_speed');
		var speed = parseFloat($speed.find(':selected').data('multiplier')) || 1;

		function setProperty(name, value) {
			if (gridEl) {
				gridEl.style.setProperty(name, value);
			}
		}

		$('.drift-wipe-colour-picker').wpColorPicker({
			change: function (e, ui) {
				setProperty('--drift-wipe-colour', ui.color.toString());
			},
			clear: function () {
				setProperty('--drift-wipe-colour', $('#drift_wipe_colour').data('default-color'));
			}
		});

		$speed.on('change', function () {
			speed = parseFloat($(this).find(':selected').data('multiplier')) || 1;
			setProperty('--drift-wipe-speed', speed);
		});

		if (!gridEl || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
			return;
		}

		// Cover (exit), hold, reveal (entrance), hold, repeat. The holds
		// leave room for the slowest style at the chosen speed.
		function play($card) {
			var $overlay = $card.find('.drift-wipe-overlay');
			if (!$overlay.length) {
				return;
			}
			stop($card);

			var cover = function () {
				$overlay.addClass('is-leaving');
				$card.data('driftWipeTimer', setTimeout(reveal, 1100 * speed));
			};
			var reveal = function () {
				$overlay.removeClass('is-leaving');
				$card.data('driftWipeTimer', setTimeout(cover, 1300 * speed));
			};
			cover();
		}

		// Removing .is-leaving plays the entrance, so the preview always
		// ends uncovered.
		function stop($card) {
			clearTimeout($card.data('driftWipeTimer'));
			$card.find('.drift-wipe-overlay').removeClass('is-leaving');
		}

		$grid
			.on('mouseenter focusin', '.drift-wipe-style', function () {
				play($(this));
			})
			.on('mouseleave focusout', '.drift-wipe-style', function () {
				stop($(this));
			});
	});
})(jQuery);
