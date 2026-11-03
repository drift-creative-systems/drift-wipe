<?php
/**
 * Plugin Name:       Drift: Wipe
 * Plugin URI:        https://github.com/drift-creative-systems/drift-wipe
 * Description:       Plays a full-screen wipe (fade, slide or curtain) when a visitor clicks a link to another page on your site. The real page loads underneath, so it works with any theme, cache or analytics setup.
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
| The functions live in includes/plugin.php, not here: PHP declares a file's
| top-level functions before running it, so a return here can't skip them.
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
define( 'DRIFT_WIPE_DIR', plugin_dir_path( __FILE__ ) );
define( 'DRIFT_WIPE_URL', plugin_dir_url( __FILE__ ) );
define( 'DRIFT_WIPE_OPTION_GROUP', 'drift_wipe_settings' );
define( 'DRIFT_WIPE_PAGE_SLUG', 'drift-wipe' );
define( 'DRIFT_WIPE_DEFAULT_COLOUR', '#111111' );
define( 'DRIFT_WIPE_DISABLED_META', '_drift_wipe_disabled' );
define( 'DRIFT_WIPE_EXCLUDED_TRANSIENT', 'drift_wipe_excluded_paths' );

require DRIFT_WIPE_DIR . 'includes/plugin.php';
