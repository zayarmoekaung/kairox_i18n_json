<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Native_JSON_i18n_Plugin {

	/**
	 * @var Native_JSON_i18n_Config
	 */
	private $config;

	/**
	 * @var Native_JSON_i18n_Storage
	 */
	private $storage;

	/**
	 * @var Native_JSON_i18n_Admin
	 */
	private $admin;

	/**
	 * @var Native_JSON_i18n_Runtime
	 */
	private $runtime;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->storage = new Native_JSON_i18n_Storage( dirname( dirname( __FILE__ ) ) . '/languages' );
		$this->config = new Native_JSON_i18n_Config();
		$this->admin = new Native_JSON_i18n_Admin( $this->config, $this->storage );
		$this->runtime = new Native_JSON_i18n_Runtime( $this->config, $this->storage );

		global $native_i18n_plugin_instance;
		$native_i18n_plugin_instance = $this;

		$this->register_hooks();
	}

	/**
	 * Register the top-level hooks.
	 */
	private function register_hooks() {
		add_action( 'init', array( $this, 'bootstrap' ) );
		add_action( 'init', array( $this, 'handle_i18n_cookie_routing' ) );
		add_action( 'elementor/elements/categories_manager', array( $this, 'register_my_plugin_category' ) );
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'init', array( $this, 'register_elementor_widgets' ) );
		add_action( 'widgets_init', array( $this, 'register_wp_widget' ) );

		$this->admin->register_hooks();
		$this->runtime->register_hooks();
	}

	/**
	 * Bootstrap the plugin infrastructure.
	 */
	public function bootstrap() {
		$this->storage->ensure_storage_directory();
		$this->ensure_default_config();
		$this->storage->ensure_default_language_file( $this->config->get_default_language() );
	}

	/**
	 * Register the WordPress block for the language switcher.
	 */
	public function register_blocks() {
		$block_dir = dirname( __DIR__ ) . '/includes/blocks/language-switcher';
		if ( file_exists( $block_dir . '/block.json' ) ) {
			$script_handle = 'native-json-i18n-language-switcher-editor';
			$script_path = $block_dir . '/editor.js';
			if ( file_exists( $script_path ) ) {
				wp_register_script(
					$script_handle,
					plugins_url( 'includes/blocks/language-switcher/editor.js', dirname( __DIR__ ) . '/kairox_i18n_json.php' ),
					array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n' ),
					filemtime( $script_path ),
					true
				);
			}

			register_block_type_from_metadata(
				$block_dir,
				array(
					'render_callback' => array( $this->runtime, 'render_language_switcher' ),
					'editor_script' => $script_handle,
				)
			);
		}
	}

	/**
	 * Register the Elementor widget for the language switcher.
	 */
	public function register_elementor_widgets() {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		$widget_file = dirname( __DIR__ ) . '/includes/elementor/class-i18n-language-switcher-widget.php';
		if ( ! file_exists( $widget_file ) ) {
			return;
		}

		add_action( 'elementor/widgets/register', function( $widgets_manager ) use ( $widget_file ) {
			require_once $widget_file;
			$widgets_manager->register( new Native_JSON_i18n_Elementor_Language_Switcher_Widget() );
		} );
	}

	/**
	 * Register the classic WordPress widget as a fallback.
	 */
	public function register_wp_widget() {
		require_once dirname( __DIR__ ) . '/includes/class-i18n-widget.php';
		register_widget( 'Native_JSON_i18n_Language_Switcher_Widget' );
	}
	/**
	 * Register Custom Category
	 */
	function register_my_plugin_category( $elements_manager ) {
    $elements_manager->add_category(
        'kairox-json-i18n', 
        [
            'title' => esc_html__( 'Kairox JSON i18n', 'native-json-i18n' ),
			'icon' => 'fa fa-language',
        ]
    );
	function register_my_block_category( $categories, $post ) {
    return array_merge(
        $categories,
        [
            [
                'slug'  => 'kairox-json-i18n',
                'title' => __( 'Kairox JSON i18n', 'native-json-i18n' ),
                'icon'  => null, 
            ],
        ]
    );
}
}
	/**
	 * Ensure the config option exists with safe defaults.
	 */
	private function ensure_default_config() {
		$config = $this->config->get_i18n_config();
		if ( empty( $config ) ) {
			$config = $this->config->get_default_config();
			$this->config->save_i18n_config( $config );
		}
	}

	/**
	 * Delegate the language routing handler.
	 */
	public function handle_i18n_cookie_routing() {
		$this->runtime->handle_i18n_cookie_routing();
	}

	/**
	 * Delegate the admin dashboard menu registration.
	 */
	public function register_admin_dashboard_menu() {
		$this->admin->register_admin_dashboard_menu();
	}

	/**
	 * Delegate the admin asset enqueue.
	 *
	 * @param string $hook
	 */
	public function enqueue_dashboard_code_assets( $hook ) {
		$this->admin->enqueue_dashboard_code_assets( $hook );
	}

	/**
	 * Delegate the admin action processor.
	 */
	public function process_admin_form_actions() {
		$this->admin->process_admin_form_actions();
	}

	/**
	 * Delegate the dashboard renderer.
	 */
	public function render_admin_view() {
		$this->admin->render_admin_view();
	}

	/**
	 * Delegate the shortcode renderer.
	 *
	 * @param array $atts
	 * @return string
	 */
	public function render_i18n_shortcode( $atts ) {
		return $this->runtime->render_i18n_shortcode( $atts );
	}

	/**
	 * Delegate the language switcher renderer.
	 *
	 * @return string
	 */
	public function render_language_switcher() {
		return $this->runtime->render_language_switcher();
	}

	/**
	 * Delegate title translation behavior.
	 *
	 * @param string $title
	 * @param int    $id
	 * @return string
	 */
	public function dynamic_post_title( $title, $id = null ) {
		return $this->runtime->dynamic_post_title( $title, $id );
	}

	/**
	 * Delegate content translation behavior.
	 *
	 * @param string $content
	 * @return string
	 */
	public function dynamic_post_content( $content ) {
		return $this->runtime->dynamic_post_content( $content );
	}
}
