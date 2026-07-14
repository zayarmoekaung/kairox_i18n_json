<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin = isset( $GLOBALS['native_i18n_plugin_instance'] ) ? $GLOBALS['native_i18n_plugin_instance'] : null;
if ( ! $plugin ) {
	return '';
}

$attributes = isset( $attributes ) ? $attributes : array();
return $plugin->render_language_switcher( $attributes );
