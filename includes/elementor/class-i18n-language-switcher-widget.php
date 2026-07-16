<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Native_JSON_i18n_Elementor_Language_Switcher_Widget extends Widget_Base {

	public function get_name() {
		return 'native-json-i18n-language-switcher';
	}

	public function get_title() {
		return __( 'Language Switcher', 'native-json-i18n' );
	}

	public function get_icon() {
		return 'eicon-globe';
	}

	public function get_categories() {
		return array( 'kairox-json-i18n' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Settings', 'native-json-i18n' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label' => __( 'Layout', 'native-json-i18n' ),
				'type' => Controls_Manager::SELECT,
				'options' => array(
					'horizontal' => __( 'Horizontal', 'native-json-i18n' ),
					'vertical' => __( 'Vertical', 'native-json-i18n' ),
				),
				'default' => 'horizontal',
			)
		);

		$this->add_control(
			'show_labels',
			array(
				'label' => __( 'Show Labels', 'native-json-i18n' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'native-json-i18n' ),
				'label_off' => __( 'No', 'native-json-i18n' ),
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'Style', 'native-json-i18n' ),
				'tab' => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label' => __( 'Text Color', 'native-json-i18n' ),
				'type' => Controls_Manager::COLOR,
			)
		);

		$this->add_control(
			'background_color',
			array(
				'label' => __( 'Background Color', 'native-json-i18n' ),
				'type' => Controls_Manager::COLOR,
			)
		);

		$this->add_control(
			'border_radius',
			array(
				'label' => __( 'Border Radius', 'native-json-i18n' ),
				'type' => Controls_Manager::TEXT,
				'default' => '4px',
			)
		);

		$this->add_control(
			'padding',
			array(
				'label' => __( 'Padding', 'native-json-i18n' ),
				'type' => Controls_Manager::TEXT,
				'default' => '8px 12px',
			)
		);

		$this->add_control(
			'gap',
			array(
				'label' => __( 'Gap', 'native-json-i18n' ),
				'type' => Controls_Manager::TEXT,
				'default' => '8px',
			)
		);

		$this->add_control(
			'font_size',
			array(
				'label' => __( 'Font Size', 'native-json-i18n' ),
				'type' => Controls_Manager::TEXT,
				'default' => '14px',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$plugin = isset( $GLOBALS['native_i18n_plugin_instance'] ) ? $GLOBALS['native_i18n_plugin_instance'] : null;
		if ( ! $plugin ) {
			return;
		}

		echo $plugin->render_language_switcher( array(
			'layout' => $settings['layout'],
			'show_labels' => ! empty( $settings['show_labels'] ),
			'text_color' => $settings['text_color'],
			'background_color' => $settings['background_color'],
			'border_radius' => $settings['border_radius'],
			'padding' => $settings['padding'],
			'gap' => $settings['gap'],
			'font_size' => $settings['font_size'],
		) );
	}
}
