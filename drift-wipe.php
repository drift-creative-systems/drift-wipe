<?php
/**
 * Plugin Name:       Drift: Wipe
 * Plugin URI:        https://driftcreativesystems.co.uk/
 * Description:       Plays a full-screen wipe (fade, slide, curtain, shutter, diagonal or circle) when a visitor clicks a link to another page on your site. The real page loads underneath, so it works with any theme, cache or analytics setup.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Drift Creative Systems
 * Author URI:        https://driftcreativesystems.co.uk/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       drift-wipe
 *
 * Copyright © 2026 Drift Creative Systems Ltd
 *
 * @package Drift_Wipe
 */

defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| Duplicate install guard
|--------------------------------------------------------------------------
| A second copy (e.g. a GitHub "Source code" zip unpacked alongside the real
| folder) would fatal on redeclared functions. Bail and tell the admin instead.
*/
if ( defined( 'DRIFT_WIPE_VERSION' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Drift: Wipe is installed more than once. Please delete the duplicate plugin folder.', 'drift-wipe' ) . '</p></div>';
		}
	);
	return;
}

define( 'DRIFT_WIPE_VERSION', '1.1.0' );
define( 'DRIFT_WIPE_FILE', __FILE__ );
define( 'DRIFT_WIPE_URL', plugin_dir_url( __FILE__ ) );
define( 'DRIFT_WIPE_OPTION_GROUP', 'drift_wipe_settings' );
define( 'DRIFT_WIPE_PAGE_SLUG', 'drift-wipe' );
define( 'DRIFT_WIPE_DEFAULT_COLOUR', '#111111' );

// ---------------------------------------------------------------------------
// Styles and capability
// ---------------------------------------------------------------------------

/**
 * Transition styles. Keys match the data-wipe-style values in assets/wipe.css.
 * "none" is always first and can't be filtered out.
 *
 * @return array<string, string>
 */
function drift_wipe_get_styles() {
	$styles = array(
		'fade'        => __( 'Fade', 'drift-wipe' ),
		'slide-up'    => __( 'Slide Up', 'drift-wipe' ),
		'slide-down'  => __( 'Slide Down', 'drift-wipe' ),
		'slide-left'  => __( 'Slide Left', 'drift-wipe' ),
		'slide-right' => __( 'Slide Right', 'drift-wipe' ),
		'curtain'     => __( 'Curtain', 'drift-wipe' ),
		'shutter'     => __( 'Shutter', 'drift-wipe' ),
		'diagonal'    => __( 'Diagonal', 'drift-wipe' ),
		'circle'      => __( 'Circle Reveal', 'drift-wipe' ),
	);

	/**
	 * Filters the transition styles offered on the settings screen.
	 *
	 * Add a style by adding a key => label pair, then enqueue CSS that
	 * animates .drift-wipe-overlay[data-wipe-style="your-key"]. If a style
	 * is removed while it's the saved choice, the transition stops running.
	 *
	 * @param array<string, string> $styles Style key => label.
	 */
	$styles = (array) apply_filters( 'drift_wipe_styles', $styles );
	unset( $styles['none'] );

	return array( 'none' => __( 'None (disabled)', 'drift-wipe' ) ) + $styles;
}

/**
 * Capability needed to see and save the settings screen.
 *
 * @return string
 */
function drift_wipe_capability() {
	/**
	 * Filters the capability needed to manage the Drift: Wipe settings.
	 *
	 * @param string $capability Default manage_options.
	 */
	return (string) apply_filters( 'drift_wipe_capability', 'manage_options' );
}

// ---------------------------------------------------------------------------
// Settings registration
// ---------------------------------------------------------------------------

add_action( 'admin_init', 'drift_wipe_register_settings' );
/**
 * Registers the three options and their fields.
 *
 * @return void
 */
function drift_wipe_register_settings() {
	register_setting(
		DRIFT_WIPE_OPTION_GROUP,
		'drift_wipe_style',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'drift_wipe_sanitize_style',
			'default'           => 'none',
			'show_in_rest'      => false,
		)
	);

	register_setting(
		DRIFT_WIPE_OPTION_GROUP,
		'drift_wipe_colour',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'drift_wipe_sanitize_colour',
			'default'           => DRIFT_WIPE_DEFAULT_COLOUR,
			'show_in_rest'      => false,
		)
	);

	register_setting(
		DRIFT_WIPE_OPTION_GROUP,
		'drift_wipe_skip_front_page',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
			'show_in_rest'      => false,
		)
	);

	add_settings_section( 'drift_wipe_main', '', '__return_false', DRIFT_WIPE_PAGE_SLUG );

	add_settings_field(
		'drift_wipe_style',
		__( 'Transition style', 'drift-wipe' ),
		'drift_wipe_render_style_field',
		DRIFT_WIPE_PAGE_SLUG,
		'drift_wipe_main',
		array( 'label_for' => 'drift_wipe_style' )
	);

	add_settings_field(
		'drift_wipe_colour',
		__( 'Overlay colour', 'drift-wipe' ),
		'drift_wipe_render_colour_field',
		DRIFT_WIPE_PAGE_SLUG,
		'drift_wipe_main',
		array( 'label_for' => 'drift_wipe_colour' )
	);

	add_settings_field(
		'drift_wipe_skip_front_page',
		__( 'Front page', 'drift-wipe' ),
		'drift_wipe_render_skip_front_page_field',
		DRIFT_WIPE_PAGE_SLUG,
		'drift_wipe_main'
	);
}

/*
 * options.php checks manage_options for every option group unless told
 * otherwise, so a filtered capability could see the page but not save.
 */
add_filter( 'option_page_capability_' . DRIFT_WIPE_OPTION_GROUP, 'drift_wipe_capability' );

/**
 * Keeps the style to a known key.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function drift_wipe_sanitize_style( $value ) {
	return ( is_string( $value ) && array_key_exists( $value, drift_wipe_get_styles() ) ) ? $value : 'none';
}

/**
 * Keeps the colour to a valid hex value.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function drift_wipe_sanitize_colour( $value ) {
	$colour = is_string( $value ) ? sanitize_hex_color( trim( $value ) ) : '';
	return $colour ? $colour : DRIFT_WIPE_DEFAULT_COLOUR;
}

// ---------------------------------------------------------------------------
// Admin screen
// ---------------------------------------------------------------------------

add_action( 'admin_menu', 'drift_wipe_add_menu' );
/**
 * Adds Settings → Drift: Wipe.
 *
 * @return void
 */
function drift_wipe_add_menu() {
	add_options_page(
		__( 'Drift: Wipe', 'drift-wipe' ),
		__( 'Drift: Wipe', 'drift-wipe' ),
		drift_wipe_capability(),
		DRIFT_WIPE_PAGE_SLUG,
		'drift_wipe_render_settings_page'
	);
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'drift_wipe_add_settings_link' );
/**
 * Adds a Settings link on the Plugins screen.
 *
 * @param array $links Action links.
 * @return array
 */
function drift_wipe_add_settings_link( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . DRIFT_WIPE_PAGE_SLUG ) ) . '">' . esc_html__( 'Settings', 'drift-wipe' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

add_filter( 'admin_footer_text', 'drift_wipe_admin_footer_text' );
/**
 * Shows the copyright and company details in the admin footer, on this plugin's settings screen only.
 *
 * @param string $text Footer text.
 * @return string
 */
function drift_wipe_admin_footer_text( $text ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'settings_page_' . DRIFT_WIPE_PAGE_SLUG !== $screen->id ) {
		return $text;
	}
	return 'Copyright &copy; ' . esc_html( wp_date( 'Y' ) ) . ' Drift Creative Systems Ltd &middot; Registered in England and Wales, company number 17548220. Registered office: 167-169 Great Portland Street, 5th Floor, London W1W 5PF';
}

add_action( 'admin_enqueue_scripts', 'drift_wipe_admin_assets' );
/**
 * Styles and the core colour picker, on the settings screen only.
 *
 * @param string $hook_suffix Current admin page.
 * @return void
 */
function drift_wipe_admin_assets( $hook_suffix ) {
	if ( 'settings_page_' . DRIFT_WIPE_PAGE_SLUG !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style( 'drift-wipe-admin', DRIFT_WIPE_URL . 'assets/admin.css', array( 'wp-color-picker' ), DRIFT_WIPE_VERSION );
	wp_enqueue_script( 'wp-color-picker' );
	wp_add_inline_script( 'wp-color-picker', "jQuery(function($){ $('.drift-wipe-colour-picker').wpColorPicker(); });" );
}

/**
 * Drift monogram. Fixed markup; only the fill is variable.
 *
 * @param string $fill Fill colour.
 * @param int    $size Width and height in px.
 * @return string
 */
function drift_wipe_mark_svg( $fill = 'currentColor', $size = 32 ) {
	$paths = array(
		'M17 12H54C77 12 91 29 91 50C91 72 76 88 54 88H45L55 76H54C70 76 79 64 79 50C79 35 69 24 54 24H29V44H17Z',
		'M17 55H29L17 67Z',
		'M41 55H54L25 88H12Z',
		'M55 76H70L59 88H43Z',
	);

	$svg = sprintf( '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%1$d" viewBox="0 0 100 100" aria-hidden="true" focusable="false">', (int) $size );
	foreach ( $paths as $d ) {
		$svg .= '<path fill="' . esc_attr( $fill ) . '" d="' . esc_attr( $d ) . '"/>';
	}

	return $svg . '</svg>';
}

/**
 * Transition style select.
 *
 * @return void
 */
function drift_wipe_render_style_field() {
	$value = get_option( 'drift_wipe_style', 'none' );
	?>
	<select name="drift_wipe_style" id="drift_wipe_style" aria-describedby="drift_wipe_style-description">
		<?php foreach ( drift_wipe_get_styles() as $key => $label ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description" id="drift_wipe_style-description">
		<?php esc_html_e( 'Plays when a visitor clicks a link to another page on this site. Choose "None" to turn it off.', 'drift-wipe' ); ?>
	</p>
	<?php
}

/**
 * Overlay colour picker.
 *
 * @return void
 */
function drift_wipe_render_colour_field() {
	$value = get_option( 'drift_wipe_colour', DRIFT_WIPE_DEFAULT_COLOUR );
	?>
	<input type="text" class="drift-wipe-colour-picker" name="drift_wipe_colour" id="drift_wipe_colour" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( DRIFT_WIPE_DEFAULT_COLOUR ); ?>" aria-describedby="drift_wipe_colour-description" />
	<p class="description" id="drift_wipe_colour-description">
		<?php esc_html_e( 'Background colour of the overlay.', 'drift-wipe' ); ?>
	</p>
	<?php
}

/**
 * Skip front page checkbox.
 *
 * @return void
 */
function drift_wipe_render_skip_front_page_field() {
	$value = get_option( 'drift_wipe_skip_front_page', true );
	?>
	<label for="drift_wipe_skip_front_page">
		<input type="checkbox" name="drift_wipe_skip_front_page" id="drift_wipe_skip_front_page" value="1" aria-describedby="drift_wipe_skip_front_page-description" <?php checked( (bool) $value ); ?> />
		<?php esc_html_e( 'Don\'t play the entrance animation on the front page', 'drift-wipe' ); ?>
	</label>
	<p class="description" id="drift_wipe_skip_front_page-description">
		<?php esc_html_e( 'Turn this on if your theme already has its own front page loader or intro, so the two don\'t overlap.', 'drift-wipe' ); ?>
	</p>
	<?php
}

/**
 * Settings screen.
 *
 * @return void
 */
function drift_wipe_render_settings_page() {
	if ( ! current_user_can( drift_wipe_capability() ) ) {
		return;
	}
	?>
	<div class="wrap drift-wipe">
		<h1 class="drift-wipe__title">
			<span class="drift-wipe__mark"><?php echo drift_wipe_mark_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed markup, attributes escaped in drift_wipe_mark_svg(). ?></span>
			<?php echo esc_html( get_admin_page_title() ); ?>
		</h1>

		<form method="post" action="options.php">
			<?php settings_fields( DRIFT_WIPE_OPTION_GROUP ); ?>

			<div class="drift-wipe__card">
				<p class="drift-wipe__intro">
					<?php
					printf(
						/* translators: %s: the opt-out CSS class */
						esc_html__( 'To stop a link playing the transition, add the %s class to it.', 'drift-wipe' ),
						'<code>no-transition</code>'
					);
					?>
				</p>
				<?php do_settings_sections( DRIFT_WIPE_PAGE_SLUG ); ?>
			</div>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Front-end output
// ---------------------------------------------------------------------------

/**
 * Whether the transition runs on this request: a style is chosen and, with
 * the front page setting on, this isn't the front page. Cached per request.
 *
 * @return bool
 */
function drift_wipe_is_active() {
	static $active = null;

	if ( null !== $active ) {
		return $active;
	}

	$style  = get_option( 'drift_wipe_style', 'none' );
	$active = 'none' !== $style && array_key_exists( $style, drift_wipe_get_styles() );

	if ( $active && get_option( 'drift_wipe_skip_front_page', true ) && is_front_page() ) {
		$active = false;
	}

	/**
	 * Filters whether the transition runs on the current page.
	 *
	 * @param bool   $active Whether it runs.
	 * @param string $style  Chosen style key.
	 */
	$active = (bool) apply_filters( 'drift_wipe_active', $active, $style );

	return $active;
}

add_action( 'wp_enqueue_scripts', 'drift_wipe_enqueue_assets' );
/**
 * Front-end styles and script, only when the transition runs.
 *
 * @return void
 */
function drift_wipe_enqueue_assets() {
	if ( ! drift_wipe_is_active() ) {
		return;
	}

	wp_enqueue_style( 'drift-wipe', DRIFT_WIPE_URL . 'assets/wipe.css', array(), DRIFT_WIPE_VERSION );

	$colour = drift_wipe_sanitize_colour( get_option( 'drift_wipe_colour', DRIFT_WIPE_DEFAULT_COLOUR ) );
	wp_add_inline_style( 'drift-wipe', ':root{--drift-wipe-colour:' . $colour . ';}' );

	wp_enqueue_script( 'drift-wipe', DRIFT_WIPE_URL . 'assets/wipe.js', array( 'jquery' ), DRIFT_WIPE_VERSION, true );
}

add_action( 'wp_body_open', 'drift_wipe_output_overlay' );
/**
 * Overlay markup. Needs the theme to call wp_body_open(), as all default
 * themes do.
 *
 * @return void
 */
function drift_wipe_output_overlay() {
	if ( ! drift_wipe_is_active() ) {
		return;
	}

	$style = get_option( 'drift_wipe_style', 'none' );
	?>
	<div class="drift-wipe-overlay" id="drift-wipe-overlay" data-wipe-style="<?php echo esc_attr( $style ); ?>" aria-hidden="true">
		<span class="drift-wipe-overlay__panel drift-wipe-overlay__panel--left"></span>
		<span class="drift-wipe-overlay__panel drift-wipe-overlay__panel--right"></span>
	</div>
	<?php
}

add_filter( 'body_class', 'drift_wipe_body_class' );
/**
 * Locks scrolling while the entrance animation plays; the script removes
 * the class once it finishes.
 *
 * @param array $classes Body classes.
 * @return array
 */
function drift_wipe_body_class( $classes ) {
	if ( drift_wipe_is_active() ) {
		$classes[] = 'has-drift-wipe';
	}
	return $classes;
}
