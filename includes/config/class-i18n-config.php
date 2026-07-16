<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Native_JSON_i18n_Config {

	/**
	 * Return the default configuration structure.
	 *
	 * @return array
	 */
	public function get_default_config() {
		return array(
			'allowed' => array( 'en' ),
			'labels'  => array( 'en' => 'English' ),
			'default' => 'en',
		);
	}

	/**
	 * Retrieve the plugin configuration.
	 *
	 * @return array
	 */
	public function get_i18n_config() {
		$config = get_option( NATIVE_I18N_OPTION_NAME, array() );

		if ( ! is_array( $config ) ) {
			$config = array();
		}

		$defaults = $this->get_default_config();
		$config = wp_parse_args( $config, $defaults );

		if ( ! isset( $config['allowed'] ) || ! is_array( $config['allowed'] ) ) {
			$config['allowed'] = $defaults['allowed'];
		}

		if ( ! isset( $config['labels'] ) || ! is_array( $config['labels'] ) ) {
			$config['labels'] = $defaults['labels'];
		}

		if ( empty( $config['default'] ) ) {
			$config['default'] = $defaults['default'];
		}

		return $config;
	}

	/**
	 * Save the plugin configuration.
	 *
	 * @param array $config
	 */
	public function save_i18n_config( $config ) {
		update_option( NATIVE_I18N_OPTION_NAME, $config );
	}

	/**
	 * Determine whether a language code is allowed.
	 *
	 * @param string $lang
	 * @param array  $config
	 * @return bool
	 */
	public function is_allowed_language( $lang, $config = null ) {
		if ( null === $config ) {
			$config = $this->get_i18n_config();
		}

		$allowed = isset( $config['allowed'] ) && is_array( $config['allowed'] ) ? $config['allowed'] : array();
		return in_array( $lang, $allowed, true );
	}

	/**
	 * Return the default language code.
	 *
	 * @return string
	 */
	public function get_default_language() {
		$config = $this->get_i18n_config();
		return isset( $config['default'] ) ? $config['default'] : 'en';
	}
}
