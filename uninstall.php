<?php
/**
 * Removes the settings when the plugin is deleted.
 *
 * @package Drift_Wipe
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Deletes this plugin's options, cache and per-page settings on the current site.
 * Names are written out because drift-wipe.php (and its constants) isn't loaded here.
 *
 * @return void
 */
function drift_wipe_delete_options() {
	delete_option( 'drift_wipe_style' );
	delete_option( 'drift_wipe_speed' );
	delete_option( 'drift_wipe_colour' );
	delete_option( 'drift_wipe_skip_front_page' );
	delete_transient( 'drift_wipe_excluded_paths' );
	delete_post_meta_by_key( '_drift_wipe_disabled' );
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
