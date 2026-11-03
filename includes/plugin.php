<?php
/**
 * Plugin code, loaded by drift-wipe.php once the duplicate install guard has passed.
 *
 * @package Drift_Wipe
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Styles, speeds and capability
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
	);

	/**
	 * Filters the transition styles offered on the settings screen.
	 *
	 * Add a style by adding a key => label pair, then enqueue CSS that
	 * animates .drift-wipe-overlay[data-wipe-style="your-key"] on the front
	 * end and on the settings screen (for its preview). If a style is
	 * removed while it's the saved choice, the transition stops running.
	 *
	 * @param array<string, string> $styles Style key => label.
	 */
	$styles = (array) apply_filters( 'drift_wipe_styles', $styles );
	unset( $styles['none'] );

	return array( 'none' => __( 'None (disabled)', 'drift-wipe' ) ) + $styles;
}

/**
 * Speed choices. The multiplier scales every animation duration in
 * assets/wipe.css and the safety timeouts in assets/wipe.js. Multipliers
 * are strings so they print the same in every PHP locale.
 *
 * @return array<string, array{label: string, multiplier: string}>
 */
function drift_wipe_get_speeds() {
	return array(
		'fast'   => array(
			'label'      => __( 'Fast', 'drift-wipe' ),
			'multiplier' => '0.6',
		),
		'normal' => array(
			'label'      => __( 'Normal', 'drift-wipe' ),
			'multiplier' => '1',
		),
		'slow'   => array(
			'label'      => __( 'Slow', 'drift-wipe' ),
			'multiplier' => '1.5',
		),
	);
}

/**
 * Multiplier for the saved speed.
 *
 * @return string
 */
function drift_wipe_get_speed_multiplier() {
	$speeds = drift_wipe_get_speeds();
	$speed  = drift_wipe_sanitize_speed( get_option( 'drift_wipe_speed', 'normal' ) );
	return $speeds[ $speed ]['multiplier'];
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
 * Registers the options and their fields.
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
		'drift_wipe_speed',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'drift_wipe_sanitize_speed',
			'default'           => 'normal',
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

	// No label_for: the field is a fieldset of radio cards with its own legend.
	add_settings_field(
		'drift_wipe_style',
		__( 'Transition style', 'drift-wipe' ),
		'drift_wipe_render_style_field',
		DRIFT_WIPE_PAGE_SLUG,
		'drift_wipe_main'
	);

	add_settings_field(
		'drift_wipe_speed',
		__( 'Speed', 'drift-wipe' ),
		'drift_wipe_render_speed_field',
		DRIFT_WIPE_PAGE_SLUG,
		'drift_wipe_main',
		array( 'label_for' => 'drift_wipe_speed' )
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
 * Keeps the speed to a known key.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function drift_wipe_sanitize_speed( $value ) {
	return ( is_string( $value ) && array_key_exists( $value, drift_wipe_get_speeds() ) ) ? $value : 'normal';
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

add_filter( 'plugin_action_links_' . plugin_basename( DRIFT_WIPE_FILE ), 'drift_wipe_add_settings_link' );
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
 * Styles, previews and the core colour picker, on the settings screen only.
 * wipe.css is loaded too, so the style previews use the real keyframes.
 *
 * @param string $hook_suffix Current admin page.
 * @return void
 */
function drift_wipe_admin_assets( $hook_suffix ) {
	if ( 'settings_page_' . DRIFT_WIPE_PAGE_SLUG !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style( 'drift-wipe', DRIFT_WIPE_URL . 'assets/wipe.css', array(), DRIFT_WIPE_VERSION );
	wp_enqueue_style( 'drift-wipe-admin', DRIFT_WIPE_URL . 'assets/admin.css', array( 'wp-color-picker', 'drift-wipe' ), DRIFT_WIPE_VERSION );

	// Previews start with the saved colour and speed; admin.js updates them live.
	$colour = drift_wipe_sanitize_colour( get_option( 'drift_wipe_colour', DRIFT_WIPE_DEFAULT_COLOUR ) );
	wp_add_inline_style( 'drift-wipe-admin', '.drift-wipe-styles{--drift-wipe-colour:' . $colour . ';--drift-wipe-speed:' . drift_wipe_get_speed_multiplier() . ';}' );

	wp_enqueue_script( 'drift-wipe-admin', DRIFT_WIPE_URL . 'assets/admin.js', array( 'jquery', 'wp-color-picker' ), DRIFT_WIPE_VERSION, true );
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
 * Overlay markup, shared by the front end and the settings previews so the
 * two can't drift apart.
 *
 * @param string               $style      Style key.
 * @param array<string, string> $attributes Extra attributes, name => value. Names are fixed by the caller.
 * @param string               $tag        Wrapper element: div on the front end, span inside the preview labels.
 * @return string
 */
function drift_wipe_overlay_html( $style, $attributes = array(), $tag = 'div' ) {
	$tag  = 'span' === $tag ? 'span' : 'div';
	$html = '<' . $tag . ' class="drift-wipe-overlay" data-wipe-style="' . esc_attr( $style ) . '"';
	foreach ( $attributes as $name => $value ) {
		$html .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
	}
	$html .= '><span class="drift-wipe-overlay__panel drift-wipe-overlay__panel--left"></span>';
	$html .= '<span class="drift-wipe-overlay__panel drift-wipe-overlay__panel--right"></span>';

	return $html . '</' . $tag . '>';
}

/**
 * Transition style cards, each with a small preview that plays on hover or
 * focus (assets/admin.js).
 *
 * @return void
 */
function drift_wipe_render_style_field() {
	$value = get_option( 'drift_wipe_style', 'none' );
	?>
	<fieldset aria-describedby="drift_wipe_style-description">
		<legend class="screen-reader-text"><?php esc_html_e( 'Transition style', 'drift-wipe' ); ?></legend>
		<div class="drift-wipe-styles">
			<?php foreach ( drift_wipe_get_styles() as $key => $label ) : ?>
				<label class="drift-wipe-style">
					<span class="drift-wipe-style__preview" aria-hidden="true">
						<?php
						if ( 'none' !== $key ) {
							echo drift_wipe_overlay_html( $key, array(), 'span' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in drift_wipe_overlay_html().
						}
						?>
					</span>
					<span class="drift-wipe-style__name">
						<input type="radio" name="drift_wipe_style" value="<?php echo esc_attr( $key ); ?>" <?php checked( $value, $key ); ?> />
						<?php echo esc_html( $label ); ?>
					</span>
				</label>
			<?php endforeach; ?>
		</div>
		<p class="description" id="drift_wipe_style-description">
			<?php esc_html_e( 'Plays when a visitor clicks a link to another page on this site. Hover over or tab to a style to preview it. Choose "None" to turn it off.', 'drift-wipe' ); ?>
		</p>
		<?php drift_wipe_render_addon_note(); ?>
	</fieldset>
	<?php
}

/**
 * One line pointing to the add-on's extra styles. Shown on this plugin's
 * settings screen only, and nothing in this plugin depends on the add-on.
 *
 * @return void
 */
function drift_wipe_render_addon_note() {
	/**
	 * Filters whether the settings screen mentions the add-on's extra styles.
	 * The add-on returns false once it's active.
	 *
	 * @param bool $show Default true.
	 */
	if ( ! apply_filters( 'drift_wipe_show_addon_note', true ) ) {
		return;
	}
	?>
	<p class="drift-wipe__addon-note">
		<?php
		printf(
			/* translators: %s: link to the add-on */
			esc_html__( 'More styles, including Diagonal, Circle Reveal and Shutter, come with the %s.', 'drift-wipe' ),
			'<a href="' . esc_url( 'https://driftcreativesystems.co.uk/' ) . '">' . esc_html__( 'Drift: Wipe add-on', 'drift-wipe' ) . '</a>'
		);
		?>
	</p>
	<?php
}

/**
 * Speed select.
 *
 * @return void
 */
function drift_wipe_render_speed_field() {
	$value = drift_wipe_sanitize_speed( get_option( 'drift_wipe_speed', 'normal' ) );
	?>
	<select name="drift_wipe_speed" id="drift_wipe_speed" aria-describedby="drift_wipe_speed-description">
		<?php foreach ( drift_wipe_get_speeds() as $key => $speed ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" data-multiplier="<?php echo esc_attr( $speed['multiplier'] ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $speed['label'] ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description" id="drift_wipe_speed-description">
		<?php esc_html_e( 'How quickly the overlay moves, for every style.', 'drift-wipe' ); ?>
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
						/* translators: 1: the opt-out CSS class, 2: name of the checkbox on the edit screen */
						esc_html__( 'To stop a link playing the transition, add the %1$s class to it. To turn it off for a whole page, tick %2$s on that page\'s edit screen.', 'drift-wipe' ),
						'<code>no-transition</code>',
						'<strong>' . esc_html__( 'Turn off the page transition', 'drift-wipe' ) . '</strong>'
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
// Per-page opt-out
// ---------------------------------------------------------------------------

/**
 * Post types that get the "Turn off the page transition" checkbox: every
 * public type except attachments.
 *
 * @return string[]
 */
function drift_wipe_excludable_post_types() {
	$types = get_post_types( array( 'public' => true ) );
	unset( $types['attachment'] );
	return array_values( $types );
}

add_action( 'add_meta_boxes', 'drift_wipe_add_meta_box' );
/**
 * Adds the checkbox to the edit screen sidebar. In the block editor it
 * shows under the document settings.
 *
 * @return void
 */
function drift_wipe_add_meta_box() {
	add_meta_box(
		'drift-wipe',
		__( 'Page transition', 'drift-wipe' ),
		'drift_wipe_render_meta_box',
		drift_wipe_excludable_post_types(),
		'side',
		'low'
	);
}

/**
 * Meta box contents.
 *
 * @param WP_Post $post Post being edited.
 * @return void
 */
function drift_wipe_render_meta_box( $post ) {
	wp_nonce_field( 'drift_wipe_meta_box', 'drift_wipe_meta_nonce' );
	$disabled = (bool) get_post_meta( $post->ID, DRIFT_WIPE_DISABLED_META, true );
	?>
	<label for="drift_wipe_disabled">
		<input type="checkbox" name="drift_wipe_disabled" id="drift_wipe_disabled" value="1" aria-describedby="drift_wipe_disabled-description" <?php checked( $disabled ); ?> />
		<?php esc_html_e( 'Turn off the page transition', 'drift-wipe' ); ?>
	</label>
	<p class="description" id="drift_wipe_disabled-description">
		<?php esc_html_e( 'No transition plays when this page opens, when visitors leave it, or when they click a link to it. Useful for landing pages.', 'drift-wipe' ); ?>
	</p>
	<?php
}

add_action( 'save_post', 'drift_wipe_save_meta_box' );
/**
 * Saves the checkbox. The block editor posts meta boxes separately after
 * its own REST save, and that request runs save_post too.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function drift_wipe_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['drift_wipe_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['drift_wipe_meta_nonce'] ) ), 'drift_wipe_meta_box' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! empty( $_POST['drift_wipe_disabled'] ) ) {
		update_post_meta( $post_id, DRIFT_WIPE_DISABLED_META, '1' );
	} else {
		delete_post_meta( $post_id, DRIFT_WIPE_DISABLED_META );
	}
}

/*
 * The excluded paths are cached, so rebuild them whenever a post or the
 * permalink structure changes (a slug edit moves the path). Priority 20
 * runs after the meta box has saved.
 */
add_action( 'save_post', 'drift_wipe_flush_excluded_paths', 20 );
add_action( 'trashed_post', 'drift_wipe_flush_excluded_paths' );
add_action( 'untrashed_post', 'drift_wipe_flush_excluded_paths' );
add_action( 'deleted_post', 'drift_wipe_flush_excluded_paths' );
add_action( 'update_option_permalink_structure', 'drift_wipe_flush_excluded_paths' );
add_action( 'update_option_page_on_front', 'drift_wipe_flush_excluded_paths' );
/**
 * Clears the cached excluded paths.
 *
 * @return void
 */
function drift_wipe_flush_excluded_paths() {
	delete_transient( DRIFT_WIPE_EXCLUDED_TRANSIENT );
}

/**
 * Normalises a URL path for matching: decoded, one leading slash and no
 * trailing slash. Must match normalisePath() in assets/wipe.js.
 *
 * @param string $path URL path.
 * @return string
 */
function drift_wipe_normalise_path( $path ) {
	return '/' . trim( rawurldecode( (string) $path ), '/' );
}

/**
 * Paths of published pages with the transition turned off, so wipe.js can
 * skip the exit on links to them. With plain permalinks the query string is
 * kept (e.g. "/?page_id=12"), since the path alone is the same for every
 * page. Cached until a post or the permalinks change.
 *
 * @return string[]
 */
function drift_wipe_get_excluded_paths() {
	$paths = get_transient( DRIFT_WIPE_EXCLUDED_TRANSIENT );
	if ( is_array( $paths ) ) {
		return $paths;
	}

	$ids = get_posts(
		array(
			'post_type'              => drift_wipe_excludable_post_types(),
			'post_status'            => 'publish',
			'fields'                 => 'ids',
			'posts_per_page'         => 500,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Runs only when the transient is empty.
			'meta_query'             => array(
				array(
					'key'   => DRIFT_WIPE_DISABLED_META,
					'value' => '1',
				),
			),
		)
	);

	$paths = array();
	foreach ( $ids as $id ) {
		$parts = wp_parse_url( (string) get_permalink( $id ) );
		if ( ! is_array( $parts ) ) {
			continue;
		}
		$path = drift_wipe_normalise_path( isset( $parts['path'] ) ? $parts['path'] : '/' );
		if ( ! empty( $parts['query'] ) ) {
			$path .= '?' . $parts['query'];
		}
		$paths[] = $path;
	}

	set_transient( DRIFT_WIPE_EXCLUDED_TRANSIENT, $paths, DAY_IN_SECONDS );

	return $paths;
}

// ---------------------------------------------------------------------------
// Front-end output
// ---------------------------------------------------------------------------

/**
 * Whether the transition runs on this request: a style is chosen, this isn't
 * the front page (with the front page setting on), and the page hasn't had
 * the transition turned off. Cached per request.
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

	// Singular pages and a static posts page both have a WP_Post as the queried object.
	$object = get_queried_object();
	if ( $active && $object instanceof WP_Post && get_post_meta( $object->ID, DRIFT_WIPE_DISABLED_META, true ) ) {
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

	/**
	 * Filters the overlay colour on the front end. The result is checked
	 * as a hex colour; anything else falls back to the default.
	 *
	 * @param string $colour Saved colour.
	 */
	$colour = drift_wipe_sanitize_colour( apply_filters( 'drift_wipe_colour', get_option( 'drift_wipe_colour', DRIFT_WIPE_DEFAULT_COLOUR ) ) );
	wp_add_inline_style( 'drift-wipe', ':root{--drift-wipe-colour:' . $colour . ';--drift-wipe-speed:' . drift_wipe_get_speed_multiplier() . ';}' );

	wp_enqueue_script( 'drift-wipe', DRIFT_WIPE_URL . 'assets/wipe.js', array( 'jquery' ), DRIFT_WIPE_VERSION, true );
	wp_add_inline_script( 'drift-wipe', 'var driftWipe = ' . wp_json_encode( array( 'excluded' => drift_wipe_get_excluded_paths() ) ) . ';', 'before' );
}

add_action( 'wp_body_open', 'drift_wipe_output_overlay' );
add_action( 'wp_footer', 'drift_wipe_output_overlay', 1 );
/**
 * Overlay markup. Printed straight after <body> on wp_body_open; themes
 * that don't call wp_body_open() get it early in wp_footer instead. It's
 * position: fixed, so it still covers the page, but the entrance can't
 * start until the footer has loaded.
 *
 * @return void
 */
function drift_wipe_output_overlay() {
	static $done = false;

	if ( $done || ! drift_wipe_is_active() ) {
		return;
	}
	$done = true;

	echo drift_wipe_overlay_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in drift_wipe_overlay_html().
		get_option( 'drift_wipe_style', 'none' ),
		array(
			'id'              => 'drift-wipe-overlay',
			'data-wipe-speed' => drift_wipe_get_speed_multiplier(),
			'aria-hidden'     => 'true',
		)
	);
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
