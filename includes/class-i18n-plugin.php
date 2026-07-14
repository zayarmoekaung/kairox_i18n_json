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

		$this->register_hooks();
	}

	/**
	 * Register the top-level hooks.
	 */
	private function register_hooks() {
		add_action( 'init', array( $this, 'bootstrap' ) );
		add_action( 'init', array( $this, 'handle_i18n_cookie_routing' ) );

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
