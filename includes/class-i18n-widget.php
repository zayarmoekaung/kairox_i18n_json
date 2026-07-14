<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Native_JSON_i18n_Language_Switcher_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'native_json_i18n_language_switcher_widget',
			__( 'Language Switcher', 'native-json-i18n' ),
			array( 'description' => __( 'Display the multilingual language switcher.', 'native-json-i18n' ) )
		);
	}

	public function widget( $args, $instance ) {
		$plugin = isset( $GLOBALS['native_i18n_plugin_instance'] ) ? $GLOBALS['native_i18n_plugin_instance'] : null;
		if ( ! $plugin ) {
			return;
		}

		echo $plugin->render_language_switcher( array(
			'layout' => isset( $instance['layout'] ) ? $instance['layout'] : 'horizontal',
			'show_labels' => ! empty( $instance['show_labels'] ),
			'text_color' => isset( $instance['text_color'] ) ? $instance['text_color'] : '',
			'background_color' => isset( $instance['background_color'] ) ? $instance['background_color'] : '',
			'border_radius' => isset( $instance['border_radius'] ) ? $instance['border_radius'] : '4px',
			'padding' => isset( $instance['padding'] ) ? $instance['padding'] : '8px 12px',
			'gap' => isset( $instance['gap'] ) ? $instance['gap'] : '8px',
			'font_size' => isset( $instance['font_size'] ) ? $instance['font_size'] : '14px',
		) );
	}

	public function form( $instance ) {
		$layout = isset( $instance['layout'] ) ? $instance['layout'] : 'horizontal';
		$show_labels = isset( $instance['show_labels'] ) ? (bool) $instance['show_labels'] : true;
		$text_color = isset( $instance['text_color'] ) ? $instance['text_color'] : '';
		$background_color = isset( $instance['background_color'] ) ? $instance['background_color'] : '';
		$border_radius = isset( $instance['border_radius'] ) ? $instance['border_radius'] : '4px';
		$padding = isset( $instance['padding'] ) ? $instance['padding'] : '8px 12px';
		$gap = isset( $instance['gap'] ) ? $instance['gap'] : '8px';
		$font_size = isset( $instance['font_size'] ) ? $instance['font_size'] : '14px';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>"><?php esc_html_e( 'Layout', 'native-json-i18n' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>">
				<option value="horizontal" <?php selected( $layout, 'horizontal' ); ?>><?php esc_html_e( 'Horizontal', 'native-json-i18n' ); ?></option>
				<option value="vertical" <?php selected( $layout, 'vertical' ); ?>><?php esc_html_e( 'Vertical', 'native-json-i18n' ); ?></option>
			</select>
		</p>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_labels' ) ); ?>" value="1" <?php checked( $show_labels, true ); ?> /> <?php esc_html_e( 'Show Labels', 'native-json-i18n' ); ?></label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text_color' ) ); ?>"><?php esc_html_e( 'Text Color', 'native-json-i18n' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text_color' ) ); ?>" type="text" value="<?php echo esc_attr( $text_color ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'background_color' ) ); ?>"><?php esc_html_e( 'Background Color', 'native-json-i18n' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'background_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_color' ) ); ?>" type="text" value="<?php echo esc_attr( $background_color ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>"><?php esc_html_e( 'Border Radius', 'native-json-i18n' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_radius' ) ); ?>" type="text" value="<?php echo esc_attr( $border_radius ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'padding' ) ); ?>"><?php esc_html_e( 'Padding', 'native-json-i18n' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'padding' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'padding' ) ); ?>" type="text" value="<?php echo esc_attr( $padding ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'gap' ) ); ?>"><?php esc_html_e( 'Gap', 'native-json-i18n' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'gap' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'gap' ) ); ?>" type="text" value="<?php echo esc_attr( $gap ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>"><?php esc_html_e( 'Font Size', 'native-json-i18n' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'font_size' ) ); ?>" type="text" value="<?php echo esc_attr( $font_size ); ?>" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['layout'] = isset( $new_instance['layout'] ) ? sanitize_key( $new_instance['layout'] ) : 'horizontal';
		$instance['show_labels'] = ! empty( $new_instance['show_labels'] );
		$instance['text_color'] = isset( $new_instance['text_color'] ) ? sanitize_text_field( $new_instance['text_color'] ) : '';
		$instance['background_color'] = isset( $new_instance['background_color'] ) ? sanitize_text_field( $new_instance['background_color'] ) : '';
		$instance['border_radius'] = isset( $new_instance['border_radius'] ) ? sanitize_text_field( $new_instance['border_radius'] ) : '4px';
		$instance['padding'] = isset( $new_instance['padding'] ) ? sanitize_text_field( $new_instance['padding'] ) : '8px 12px';
		$instance['gap'] = isset( $new_instance['gap'] ) ? sanitize_text_field( $new_instance['gap'] ) : '8px';
		$instance['font_size'] = isset( $new_instance['font_size'] ) ? sanitize_text_field( $new_instance['font_size'] ) : '14px';
		return $instance;
	}
}
