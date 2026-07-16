<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Native_JSON_i18n_Admin {

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
	 * Register the admin hooks.
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'register_admin_dashboard_menu' ) );
		add_action( 'admin_init', array( $this, 'process_admin_form_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_code_assets' ) );
	}

	/**
	 * Register the plugin admin dashboard page.
	 */
	public function register_admin_dashboard_menu() {
		add_menu_page(
			'JSON Localization Engine',
			'JSON i18n',
			'manage_options',
			'json-i18n-dashboard',
			array( $this, 'render_admin_view' ),
			'dashicons-translation',
			85
		);
	}

	/**
	 * Enqueue CodeMirror assets for the admin editor.
	 *
	 * @param string $hook
	 */
	public function enqueue_dashboard_code_assets( $hook ) {
		if ( 'toplevel_page_json-i18n-dashboard' !== $hook ) {
			return;
		}

		$settings = wp_enqueue_code_editor( array( 'type' => 'application/json' ) );
		if ( false !== $settings ) {
			wp_add_inline_script(
				'code-editor',
				sprintf( 'jQuery(function($){ wp.codeEditor.initialize("json_editor_textarea", %s); });', wp_json_encode( $settings ) )
			);
		}
	}

	/**
	 * Process admin form actions.
	 */
	public function process_admin_form_actions() {
		if ( ! isset( $_POST['i18n_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['i18n_nonce'] ), 'i18n_action_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['add_lang_btn'] ) ) {
			$this->handle_add_language_action();
		}

		if ( isset( $_POST['save_json_btn'] ) ) {
			$this->handle_save_language_file_action();
		}

		if ( isset( $_POST['export_json_btn'] ) ) {
			$this->handle_export_language_file_action();
		}

		if ( isset( $_POST['delete_json_btn'] ) ) {
			$this->handle_delete_language_file_action();
		}

		if ( isset( $_POST['import_json_btn'] ) && isset( $_FILES['import_file'] ) ) {
			$this->handle_import_language_file_action();
		}
	}

	/**
	 * Add a new locale and create its language file.
	 */
	private function handle_add_language_action() {
		$config = $this->config->get_i18n_config();
		$new_code = sanitize_key( wp_unslash( $_POST['new_lang_code'] ) );
		$new_label = sanitize_text_field( wp_unslash( $_POST['new_lang_label'] ) );

		if ( empty( $new_code ) || $this->config->is_allowed_language( $new_code, $config ) ) {
			return;
		}

		$config['allowed'][] = $new_code;
		$config['labels'][ $new_code ] = ! empty( $new_label ) ? $new_label : strtoupper( $new_code );
		$this->config->save_i18n_config( $config );

		$target_file = $this->storage->get_language_file_path( $new_code );
		if ( ! file_exists( $target_file ) ) {
			$this->storage->write_language_file( $new_code, new stdClass() );
		}

		wp_redirect( add_query_arg( array( 'page' => 'json-i18n-dashboard', 'edit_lang' => $new_code, 'status' => 'lang_added' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Save the currently edited JSON language file.
	 */
	private function handle_save_language_file_action() {
		$active_lang = sanitize_key( wp_unslash( $_POST['active_editing_lang'] ) );
		$raw_json = wp_unslash( $_POST['json_code_content'] );

		$decoded = json_decode( $raw_json );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_die( 'Configuration write aborted: Invalid JSON syntax supplied.' );
		}

		$this->storage->write_language_file( $active_lang, $decoded );
		wp_redirect( add_query_arg( array( 'page' => 'json-i18n-dashboard', 'edit_lang' => $active_lang, 'status' => 'saved' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Export the active language file as a download.
	 */
	private function handle_export_language_file_action() {
		$active_lang = sanitize_key( wp_unslash( $_POST['active_editing_lang'] ) );
		$file_path = $this->storage->get_language_file_path( $active_lang );

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $active_lang . '-export-' . date( 'Y-m-d' ) . '.json"' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		readfile( $file_path );
		exit;
	}

	/**
	 * Import and overwrite a language file.
	 */
	private function handle_import_language_file_action() {
		$active_lang = sanitize_key( wp_unslash( $_POST['active_editing_lang'] ) );
		$uploaded = $_FILES['import_file'];

		if ( empty( $uploaded['tmp_name'] ) ) {
			return;
		}

		$raw_imported_data = file_get_contents( $uploaded['tmp_name'] );
		$decoded = json_decode( $raw_imported_data );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_die( 'Import rejected: The uploaded file is not valid JSON.' );
		}

		$this->storage->write_language_file( $active_lang, $decoded );
		wp_redirect( add_query_arg( array( 'page' => 'json-i18n-dashboard', 'edit_lang' => $active_lang, 'status' => 'imported' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Delete a language file and remove it from configuration.
	 */
	private function handle_delete_language_file_action() {
		$active_lang = sanitize_key( wp_unslash( $_POST['active_editing_lang'] ) );
		$config = $this->config->get_i18n_config();

		if ( ! $this->config->is_allowed_language( $active_lang, $config ) ) {
			return;
		}

		if ( isset( $config['default'] ) && $config['default'] === $active_lang ) {
			wp_die( 'Cannot delete the default language file. Change the default language first.' );
		}

		if ( $this->storage->delete_language_file( $active_lang ) ) {
			$config['allowed'] = array_values( array_diff( $config['allowed'], array( $active_lang ) ) );
			if ( isset( $config['labels'][ $active_lang ] ) ) {
				unset( $config['labels'][ $active_lang ] );
			}
			$this->config->save_i18n_config( $config );
		}

		wp_redirect( add_query_arg( array( 'page' => 'json-i18n-dashboard', 'status' => 'deleted' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Render the admin dashboard view.
	 */
	public function render_admin_view() {
		$config = $this->config->get_i18n_config();
		$selected = isset( $_GET['edit_lang'] ) ? sanitize_key( wp_unslash( $_GET['edit_lang'] ) ) : $config['default'];
		$selected = $this->config->is_allowed_language( $selected, $config ) ? $selected : $config['default'];
		$file_path = $this->storage->get_language_file_path( $selected );
		$editor_data = file_exists( $file_path ) ? file_get_contents( $file_path ) : '{}';
		?>
		<div class="wrap regular-i18n-dashboard">
			<h1>JSON i18n Native Architecture Framework Dashboard</h1>
			<p class="description">Zero database row duplication for your Elementor projects. Build once, localize globally.</p>

			<?php if ( isset( $_GET['status'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php
						$status = sanitize_key( wp_unslash( $_GET['status'] ) );
						if ( 'saved' === $status ) {
							echo 'JSON schema changes compiled and written safely to configuration file.';
						} elseif ( 'lang_added' === $status ) {
							echo 'New localization target tracked and created successfully.';
						} elseif ( 'imported' === $status ) {
							echo 'JSON matrix successfully imported and parsed into local storage.';
						} elseif ( 'deleted' === $status ) {
							echo 'Language JSON file deleted and locale tracking removed.';
						}
					?></p>
				</div>
			<?php endif; ?>

			<div style="display:flex; gap:20px; margin-top:20px; flex-wrap:wrap;">
				<div style="flex:1; min-width:280px; background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; box-shadow:0 1px 1px rgba(0,0,0,.04);">
					<h3>Add System Language Tracking</h3>
					<form method="POST" action="">
						<?php wp_nonce_field( 'i18n_action_nonce', 'i18n_nonce' ); ?>
						<p>
							<label>ISO Language Code (e.g., 'th', 'fr', 'my')</label><br/>
							<input type="text" name="new_lang_code" style="width:100%;" required placeholder="es" />
						</p>
						<p>
							<label>Display Label Name</label><br/>
							<input type="text" name="new_lang_label" style="width:100%;" required placeholder="Español" />
						</p>
						<input type="submit" name="add_lang_btn" class="button button-secondary" value="Add New Locale Target" />
					</form>

					<hr style="margin:25px 0; border:none; border-top:1px solid #eee;" />

					<h3>Managed Locales</h3>
					<ul style="padding:0; margin:0; list-style:none;">
						<?php foreach ( $config['allowed'] as $code ) : ?>
							<li style="margin-bottom:8px; padding:10px; background:<?php echo $selected === $code ? '#f0f6fa' : '#fafafa'; ?>; border:1px solid <?php echo $selected === $code ? '#007cba' : '#ddd'; ?>; border-radius:3px; display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
								<strong><?php echo esc_html( isset( $config['labels'][ $code ] ) ? $config['labels'][ $code ] : strtoupper( $code ) ); ?> (<code><?php echo esc_html( $code ); ?></code>)</strong>
								<div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
									<a href="<?php echo esc_url( add_query_arg( 'edit_lang', $code ) ); ?>" class="button button-small <?php echo $selected === $code ? 'button-primary' : ''; ?>">Edit JSON</a>
									<?php if ( isset( $config['default'] ) && $config['default'] !== $code ) : ?>
										<form method="POST" action="" style="margin:0; display:inline;">
											<?php wp_nonce_field( 'i18n_action_nonce', 'i18n_nonce' ); ?>
											<input type="hidden" name="active_editing_lang" value="<?php echo esc_attr( $code ); ?>" />
											<input type="submit" name="delete_json_btn" class="button button-small button-danger" value="Delete" onclick="return confirm('Delete this locale and remove its JSON file?');" />
										</form>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div style="flex:3; min-width:320px; background:#fff; padding:25px; border:1px solid #ccd0d4; border-radius:4px; box-shadow:0 1px 1px rgba(0,0,0,.04);">
					<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
						<h2 style="margin:0;">Editing Dictionary Configuration Asset: <span style="color:#007cba;"><?php echo esc_html( strtoupper( $selected ) ); ?></span></h2>
					</div>

					<div style="background:#f6f7f7; padding:12px; margin-bottom:15px; border-left:4px solid #72777c; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
						<form method="POST" action="" style="margin:0;">
							<?php wp_nonce_field( 'i18n_action_nonce', 'i18n_nonce' ); ?>
							<input type="hidden" name="active_editing_lang" value="<?php echo esc_attr( $selected ); ?>" />
							<input type="submit" name="export_json_btn" class="button button-secondary" value="⬇ Export Raw JSON File" />
						</form>

						<form method="POST" action="" enctype="multipart/form-data" style="margin:0; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
							<?php wp_nonce_field( 'i18n_action_nonce', 'i18n_nonce' ); ?>
							<input type="hidden" name="active_editing_lang" value="<?php echo esc_attr( $selected ); ?>" />
							<input type="file" name="import_file" accept=".json" required style="max-width:220px;" />
							<input type="submit" name="import_json_btn" class="button button-secondary" value="⬆ Overwrite via Import" onclick="return confirm('Warning: Proceeding will instantly clear and replace all current keys for this language file. Continue?');" />
						</form>

				<form method="POST" action="" style="margin:0;">
					<?php wp_nonce_field( 'i18n_action_nonce', 'i18n_nonce' ); ?>
					<input type="hidden" name="active_editing_lang" value="<?php echo esc_attr( $selected ); ?>" />
					<input type="submit" name="delete_json_btn" class="button button-danger" value="🗑 Delete JSON File" onclick="return confirm('This will permanently remove the JSON file and unregister the language. Are you sure?');" />
				</form>
						<div style="margin-bottom:15px;">
							<textarea id="json_editor_textarea" name="json_code_content" style="width:100%; min-height:450px; font-family:monospace;" class="widefat"><?php echo esc_textarea( $editor_data ); ?></textarea>
						</div>

						<input type="submit" name="save_json_btn" class="button button-primary button-large" value="Commit Matrix Code Updates" />
					</form>
				</div>
			</div>
		</div>
		<?php
	}
}
