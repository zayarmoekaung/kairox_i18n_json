<?php
/**
 * Plugin Name:       Native JSON i18n for Elementor Pro-Pack
 * Plugin URI:        https://github.com/zayarmoekaung/kairox_i18n_json
 * Description:       Advanced zero-duplication framework language manager with structured JSON storage, admin editing, and runtime translation support.
 * Version:           2.0.0
 * Author:            Zayar Moe Kaung
 * Author URI:        https://github.com/zayarmoekaung
 * License:           GPL2
 * Text Domain:       native-json-i18n
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'NATIVE_I18N_OPTION_NAME' ) ) {
	define( 'NATIVE_I18N_OPTION_NAME', 'native_i18n_config' );
}

if ( ! defined( 'NATIVE_I18N_COOKIE_NAME' ) ) {
	define( 'NATIVE_I18N_COOKIE_NAME', 'wp_user_lang' );
}

$plugin_dir = plugin_dir_path( __FILE__ );

require_once $plugin_dir . 'includes/config/class-i18n-config.php';
require_once $plugin_dir . 'includes/storage/class-i18n-storage.php';
require_once $plugin_dir . 'includes/admin/class-i18n-admin.php';
require_once $plugin_dir . 'includes/frontend/class-i18n-runtime.php';
require_once $plugin_dir . 'includes/class-i18n-plugin.php';

new Native_JSON_i18n_Plugin();
