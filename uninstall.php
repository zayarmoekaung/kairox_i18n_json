<?php
/**
 * Uninstall handler for the Native JSON i18n plugin.
 *
 * This file is called when the plugin is uninstalled via the WordPress admin.
 * It removes all plugin data including configuration, language files, and directories.
 *
 * @package Native_JSON_i18n
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Define the option name if not already defined
if ( ! defined( 'NATIVE_I18N_OPTION_NAME' ) ) {
	define( 'NATIVE_I18N_OPTION_NAME', 'native_i18n_config' );
}

/**
 * Recursively delete a directory and all its contents.
 *
 * @param string $dir Directory path to delete.
 * @return bool True if deletion was successful, false otherwise.
 */
function native_i18n_rmdir_recursive( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return false;
	}

	$items = scandir( $dir );

	if ( false === $items ) {
		return false;
	}

	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item ) {
			continue;
		}

		$path = $dir . DIRECTORY_SEPARATOR . $item;

		if ( is_dir( $path ) ) {
			native_i18n_rmdir_recursive( $path );
		} else {
			@unlink( $path );
		}
	}

	return @rmdir( $dir );
}

/**
 * Clean up all plugin data on uninstall.
 */
function native_i18n_uninstall() {
	// Remove plugin configuration from options table
	delete_option( NATIVE_I18N_OPTION_NAME );

	// Remove plugin language storage directory and all its contents
	$plugin_dir = plugin_dir_path( __FILE__ );
	$languages_dir = $plugin_dir . 'includes/languages';

	if ( is_dir( $languages_dir ) ) {
		native_i18n_rmdir_recursive( $languages_dir );
	}

	// Clean up any transients (if used in the future)
	// delete_transient( 'native_i18n_cache_key' );

	// Optional: Log the uninstall action
	// error_log( 'Native JSON i18n plugin uninstalled and cleaned up.' );
}

// Execute the uninstall process
native_i18n_uninstall();
