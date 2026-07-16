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
		
		// Append language parameter to internal links for persistence
		add_filter( 'post_link', array( $this, 'append_language_to_url' ), 10, 1 );
		add_filter( 'post_type_link', array( $this, 'append_language_to_url' ), 10, 1 );
		add_filter( 'term_link', array( $this, 'append_language_to_url' ), 10, 1 );
		add_filter( 'nav_menu_item_url', array( $this, 'append_language_to_url' ), 10, 1 );
		add_filter( 'home_url', array( $this, 'append_language_to_home_url' ), 10, 4 );
		add_filter( 'page_link', array( $this, 'append_language_to_url' ), 10, 1 );
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
	 * Render the language switcher shortcode or block/widget output.
	 *
	 * @param array  $attributes Optional rendering attributes.
	 * @param string $content    Optional content block.
	 * @return string
	 */
	public function render_language_switcher( $attributes = array(), $content = '' ) {
		$config = $this->config->get_i18n_config();
		$current_lang = $this->get_current_runtime_lang();
		$current_url = remove_query_arg( 'lang' );

		$layout = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'horizontal';
		$show_labels = isset( $attributes['show_labels'] ) ? filter_var( $attributes['show_labels'], FILTER_VALIDATE_BOOLEAN ) : true;
		$text_color = isset( $attributes['text_color'] ) ? sanitize_text_field( $attributes['text_color'] ) : '';
		$background_color = isset( $attributes['background_color'] ) ? sanitize_text_field( $attributes['background_color'] ) : '';
		$border_radius = isset( $attributes['border_radius'] ) ? sanitize_text_field( $attributes['border_radius'] ) : '4px';
		$padding = isset( $attributes['padding'] ) ? sanitize_text_field( $attributes['padding'] ) : '8px 12px';
		$gap = isset( $attributes['gap'] ) ? sanitize_text_field( $attributes['gap'] ) : '8px';
		$font_size = isset( $attributes['font_size'] ) ? sanitize_text_field( $attributes['font_size'] ) : '14px';
		$class_name = isset( $attributes['className'] ) ? sanitize_html_class( $attributes['className'] ) : '';

		$classes = array( 'custom-lang-switcher', 'custom-lang-switcher--' . $layout );
		if ( $class_name ) {
			$classes[] = $class_name;
		}

		$wrapper_style = array( 'display:inline-block', 'position:relative' );
		$link_style = array();
		if ( $text_color ) {
			$link_style[] = 'color:' . $text_color;
		}
		if ( $background_color ) {
			$link_style[] = 'background-color:' . $background_color;
		}
		if ( $border_radius ) {
			$link_style[] = 'border-radius:' . $border_radius;
		}
		if ( $padding ) {
			$link_style[] = 'padding:' . $padding;
		}
		if ( $font_size ) {
			$link_style[] = 'font-size:' . $font_size;
		}

		$flag_data = $this->load_flag_data();
		$menu_items = array();
		foreach ( $config['allowed'] as $code ) {
			$active_class = ( $current_lang === $code ) ? 'is-active' : '';
			$switch_url = add_query_arg( 'lang', $code, $current_url );
			$flag_meta = isset( $flag_data[ $code ] ) ? $flag_data[ $code ] : array( 'name' => strtoupper( $code ), 'flag' => '🌐' );
			$name = isset( $config['labels'][ $code ] ) ? $config['labels'][ $code ] : $flag_meta['name'];
			$label = $show_labels ? $name : strtoupper( $code );
			$flag = $this->get_flag_markup( $code, $flag_data );
			$menu_items[] = sprintf(
				'<li class="lang-menu-item %s"><a href="%s" class="lang-link %s" data-lang="%s" style="%s">%s %s</a></li>',
				esc_attr( $active_class ),
				esc_url( $switch_url ),
				esc_attr( $active_class ),
				esc_attr( $code ),
				esc_attr( implode( '; ', $link_style ) ),
				esc_html( $flag ),
				esc_html( $label )
			);
		}

		$styles = '<style>
			.custom-lang-switcher { display:inline-block; position:relative; font-family:inherit; }
			.custom-lang-switcher .lang-switcher-trigger {
				border:1px solid rgba(0,0,0,0.15);
				background:#fff;
				cursor:pointer;
				padding:8px 12px;
				font-weight:600;
				line-height:1;
				display:inline-flex;
				align-items:center;
				gap:6px;
			}
			.custom-lang-switcher .lang-switcher-menu {
				list-style:none;
				margin:4px 0 0;
				padding:6px;
				position:absolute;
				top:100%;
				left:0;
				background:#fff;
				border:1px solid rgba(0,0,0,0.12);
				border-radius:8px;
				box-shadow:0 8px 24px rgba(0,0,0,0.12);
				min-width:160px;
				opacity:0;
				visibility:hidden;
				transform:translateY(-6px);
				transition:all 0.2s ease;
				z-index:999;
			}
			.custom-lang-switcher:hover .lang-switcher-menu,
			.custom-lang-switcher:focus-within .lang-switcher-menu {
				opacity:1;
				visibility:visible;
				transform:translateY(0);
			}
			.custom-lang-switcher .lang-menu-item a {
				display:flex;
				align-items:center;
				gap:8px;
				text-decoration:none;
				padding:6px 8px;
				border-radius:6px;
			}
			.custom-lang-switcher .lang-menu-item a:hover {
				background:rgba(0,0,0,0.05);
			}
		</style>';

		$current_flag_meta = isset( $flag_data[ $current_lang ] ) ? $flag_data[ $current_lang ] : array( 'name' => strtoupper( $current_lang ), 'flag' => '🌐' );
		$current_label = $show_labels ? ( isset( $config['labels'][ $current_lang ] ) ? $config['labels'][ $current_lang ] : $current_flag_meta['name'] ) : strtoupper( $current_lang );

		$output = sprintf(
			'<div class="%s" style="%s">%s<button type="button" class="lang-switcher-trigger" style="%s">%s</button><ul class="lang-switcher-menu">%s</ul></div>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( implode( '; ', $wrapper_style ) ),
			$styles,
			esc_attr( implode( '; ', $link_style ) ),
			esc_html( $this->get_flag_markup( $current_lang, $flag_data ) . ' ' . $current_label ),
			implode( '', $menu_items )
		);
		return $output;
	}

	/**
	 * Load flag metadata from a JSON file.
	 *
	 * @return array
	 */
	private function load_flag_data() {
		$path = dirname( __FILE__ ) . '/flags.json';
		if ( ! file_exists( $path ) ) {
			return array();
		}

		$contents = file_get_contents( $path );
		if ( false === $contents ) {
			return array();
		}

		$data = json_decode( $contents, true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Return a flag markup for a language code.
	 *
	 * @param string $code
	 * @param array  $flag_data
	 * @return string
	 */
	private function get_flag_markup( $code, $flag_data = array() ) {
		if ( isset( $flag_data[ $code ]['flag'] ) && ! empty( $flag_data[ $code ]['flag'] ) ) {
			return $flag_data[ $code ]['flag'];
		}

		return '🌐';
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

	/**
	 * Append the current language parameter to a URL to retain language selection.
	 *
	 * @param string $url The URL to modify.
	 * @return string
	 */
	public function append_language_to_url( $url ) {
		// Don't modify admin URLs
		if ( is_admin() ) {
			return $url;
		}

		$current_lang = $this->get_current_runtime_lang();
		$config = $this->config->get_i18n_config();

		// Only append if current language is not the default
		if ( $current_lang === $config['default'] ) {
			return $url;
		}

		// Avoid adding lang parameter multiple times
		if ( strpos( $url, 'lang=' ) !== false ) {
			return $url;
		}

		// Don't modify external links
		$site_url = home_url();
		if ( strpos( $url, $site_url ) === false ) {
			return $url;
		}

		return add_query_arg( 'lang', $current_lang, $url );
	}

	/**
	 * Append the current language parameter to home URL.
	 *
	 * @param string $url    The complete home URL including scheme and path.
	 * @param string $path   Path relative to home URL.
	 * @param string $scheme The scheme to use.
	 * @param int    $blog_id Blog ID.
	 * @return string
	 */
	public function append_language_to_home_url( $url, $path, $scheme, $blog_id ) {
		// Don't modify admin URLs
		if ( is_admin() ) {
			return $url;
		}

		$current_lang = $this->get_current_runtime_lang();
		$config = $this->config->get_i18n_config();

		// Only append if current language is not the default
		if ( $current_lang === $config['default'] ) {
			return $url;
		}

		// Avoid adding lang parameter multiple times
		if ( strpos( $url, 'lang=' ) !== false ) {
			return $url;
		}

		return add_query_arg( 'lang', $current_lang, $url );
	}
}
