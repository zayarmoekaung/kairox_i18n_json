<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Native_JSON_i18n_Storage {

	/**
	 * Absolute path to the language storage directory.
	 *
	 * @var string
	 */
	private $languages_dir;

	/**
	 * Constructor.
	 *
	 * @param string $languages_dir
	 */
	public function __construct( $languages_dir ) {
		$this->languages_dir = trailingslashit( $languages_dir );
	}

	/**
	 * Ensure the language storage directory exists.
	 */
	public function ensure_storage_directory() {
		if ( ! file_exists( $this->languages_dir ) ) {
			wp_mkdir_p( $this->languages_dir );
		}
	}

	/**
	 * Return the storage directory path.
	 *
	 * @return string
	 */
	public function get_languages_dir() {
		return $this->languages_dir;
	}

	/**
	 * Return the file path for a language file.
	 *
	 * @param string $lang
	 * @return string
	 */
	public function get_language_file_path( $lang ) {
		return $this->languages_dir . sanitize_key( $lang ) . '.json';
	}

	/**
	 * Load a language file from disk.
	 *
	 * @param string $lang
	 * @return array
	 */
	public function load_language_file( $lang ) {
		$file_path = $this->get_language_file_path( $lang );

		if ( ! file_exists( $file_path ) ) {
			return array();
		}

		$contents = file_get_contents( $file_path );
		if ( false === $contents ) {
			return array();
		}

		$decoded = json_decode( $contents, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Write a language file to disk.
	 *
	 * @param string $lang
	 * @param mixed  $data
	 * @return bool
	 */
	public function write_language_file( $lang, $data ) {
		$this->ensure_storage_directory();
		$file_path = $this->get_language_file_path( $lang );
		$encoded = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		if ( false === $encoded ) {
			return false;
		}

		return false !== file_put_contents( $file_path, $encoded );
	}

	/**
	 * Ensure a default language file exists.
	 *
	 * @param string $default_lang
	 */
	public function ensure_default_language_file( $default_lang = 'en' ) {
		$default_path = $this->get_language_file_path( $default_lang );

		if ( ! file_exists( $default_path ) ) {
			$this->write_language_file( $default_lang, array( 'global' => array( 'welcome' => 'Welcome' ) ) );
		}
	}
}
