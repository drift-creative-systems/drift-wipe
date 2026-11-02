<?php
/**
 * Removes the settings when the plugin is deleted.
 *
 * @package Drift_Wipe
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Deletes this plugin's options on the current site.
 *
 * @return void
 */
function drift_wipe_delete_options() {
	delete_option( 'drift_wipe_style' );
	delete_option( 'drift_wipe_colour' );
	delete_option( 'drift_wipe_skip_front_page' );
}

if ( is_multisite() ) {
	$drift_wipe_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $drift_wipe_site_ids as $drift_wipe_site_id ) {
		switch_to_blog( $drift_wipe_site_id );
		drift_wipe_delete_options();
		restore_current_blog();
	}
} else {
	drift_wipe_delete_options();
}
