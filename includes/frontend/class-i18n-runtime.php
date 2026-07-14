<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Native_JSON_i18n_Runtime {

	/**
	 * @var Native_JSON_i18n_Config
	 */
	private $config;

	/**
	 * @var Native_JSON_i18n_Storage
	 */
	private $storage;

	/**
	 * Constructor.
	 *
	 * @param Native_JSON_i18n_Config  $config
	 * @param Native_JSON_i18n_Storage $storage
	 */
	public function __construct( $config, $storage ) {
		$this->config = $config;
		$this->storage = $storage;
	}

	/**
	 * Register frontend hooks.
	 */
	public function register_hooks() {
		add_shortcode( 'i18n', array( $this, 'render_i18n_shortcode' ) );
		add_shortcode( 'lang_switcher', array( $this, 'render_language_switcher' ) );
		add_filter( 'the_title', array( $this, 'dynamic_post_title' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'dynamic_post_content' ), 1 );
	}

	/**
	 * Handle language switching through a query parameter.
	 */
	public function handle_i18n_cookie_routing() {
		if ( ! isset( $_GET['lang'] ) ) {
			return;
		}

		$lang = sanitize_key( wp_unslash( $_GET['lang'] ) );
		$config = $this->config->get_i18n_config();

		if ( $this->config->is_allowed_language( $lang, $config ) ) {
			setcookie( NATIVE_I18N_COOKIE_NAME, $lang, time() + ( 86400 * 30 ), COOKIEPATH, COOKIE_DOMAIN );
			$_COOKIE[ NATIVE_I18N_COOKIE_NAME ] = $lang;
		}
	}

	/**
	 * Return the current runtime language.
	 *
	 * @return string
	 */
	public function get_current_runtime_lang() {
		$config = $this->config->get_i18n_config();
		$cookie_lang = isset( $_COOKIE[ NATIVE_I18N_COOKIE_NAME ] ) ? sanitize_key( wp_unslash( $_COOKIE[ NATIVE_I18N_COOKIE_NAME ] ) ) : '';

		if ( $cookie_lang && $this->config->is_allowed_language( $cookie_lang, $config ) ) {
			return $cookie_lang;
		}

		return isset( $config['default'] ) ? $config['default'] : 'en';
	}

	/**
	 * Render the translation shortcode.
	 *
	 * @param array $atts
	 * @return string
	 */
	public function render_i18n_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'key' => '' ), $atts );
		$key = isset( $atts['key'] ) ? $atts['key'] : '';
		$value = $this->resolve_translation_value( $key );

		return esc_html( null === $value ? $key : $value );
	}

	/**
	 * Resolve a translation value for a given dot-separated key.
	 *
	 * @param string $key
	 * @param string $lang
	 * @return mixed|null
	 */
	private function resolve_translation_value( $key, $lang = null ) {
		if ( empty( $key ) ) {
			return null;
		}

		if ( null === $lang ) {
			$lang = $this->get_current_runtime_lang();
		}

		$segments = explode( '.', $key );
		$value = $this->storage->load_language_file( $lang );

		foreach ( $segments as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return null;
			}
			$value = $value[ $segment ];
		}

		return $value;
	}

	/**
	 * Render the language switcher shortcode.
	 *
	 * @return string
	 */
	public function render_language_switcher() {
		$config = $this->config->get_i18n_config();
		$current_lang = $this->get_current_runtime_lang();
		$current_url = remove_query_arg( 'lang' );
		$output = '<div class="custom-lang-switcher">';

		foreach ( $config['allowed'] as $code ) {
			$active_class = ( $current_lang === $code ) ? 'is-active' : '';
			$switch_url = add_query_arg( 'lang', $code, $current_url );
			$name = isset( $config['labels'][ $code ] ) ? $config['labels'][ $code ] : strtoupper( $code );

			$output .= sprintf(
				'<a href="%s" class="lang-link %s" data-lang="%s">%s</a>',
				esc_url( $switch_url ),
				esc_attr( $active_class ),
				esc_attr( $code ),
				esc_html( $name )
			);
		}

		$output .= '</div>';
		return $output;
	}

	/**
	 * Replace a post title with a translated version when available.
	 *
	 * @param string $title
	 * @param int    $id
	 * @return string
	 */
	public function dynamic_post_title( $title, $id = null ) {
		if ( is_admin() || ! $id || ! is_main_query() ) {
			return $title;
		}

		$config = $this->config->get_i18n_config();
		$current_lang = $this->get_current_runtime_lang();

		if ( $current_lang === $config['default'] ) {
			return $title;
		}

		$meta_key = 'title_' . $current_lang;
		$translated_title = get_post_meta( $id, $meta_key, true );
		return ! empty( $translated_title ) ? $translated_title : $title;
	}

	/**
	 * Replace a post content block with a translated version when available.
	 *
	 * @param string $content
	 * @return string
	 */
	public function dynamic_post_content( $content ) {
		if ( is_admin() || ! is_main_query() || ! in_the_loop() ) {
			return $content;
		}

		global $post;
		if ( ! $post instanceof WP_Post ) {
			return $content;
		}

		$config = $this->config->get_i18n_config();
		$current_lang = $this->get_current_runtime_lang();

		if ( $current_lang === $config['default'] ) {
			return $content;
		}

		$meta_key = 'content_' . $current_lang;
		$translated_content = get_post_meta( $post->ID, $meta_key, true );
		if ( empty( $translated_content ) ) {
			return $content;
		}

		remove_filter( 'the_content', array( $this, 'dynamic_post_content' ), 1 );
		$processed_content = apply_filters( 'the_content', $translated_content );
		add_filter( 'the_content', array( $this, 'dynamic_post_content' ), 1 );
		return $processed_content;
	}
}
