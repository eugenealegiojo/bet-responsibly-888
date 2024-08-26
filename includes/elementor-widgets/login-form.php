<?php

namespace Parlay\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Login_Form_Widget extends \Elementor\Widget_Base {

	// Widget constructor
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
	}

	public function get_style_depends() {
		return [ 'parlay-api-style' ];
	}

	public function get_script_depends() {
		return [ 'parlay-login-widget' ];
	}

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'parlay-login-form';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Parlay Login Form', 'parlay-api' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-lock-user';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the oEmbed widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'general', 'parlay-games-api' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the oEmbed widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'login', 'form', 'user' ];
	}

	/**
	 * Register oEmbed widget controls.
	 *
	 * Add input fields to allow the user to customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'parlay-api' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label'   => __( 'Title', 'parlay-api' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Login', 'parlay-api' ),
			]
		);

		$this->add_control(
			'forgot_password_text',
			[
				'label'   => __( 'Forgot Password Text', 'parlay-api' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Forgot Password?', 'parlay-api' ),
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'forgot_password_link',
			[
				'label'       => esc_html__( 'Forgot Password Link', 'parlay-api' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'options'     => [ 'url', 'is_external', 'nofollow' ],
				'default'     => [
					'url'         => home_url() . '/forgot-password',
					'is_external' => false,
					'nofollow'    => true,
				],
				'label_block' => true,
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render oEmbed widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */	
	protected function render() {
		$settings = $this->get_settings_for_display();

		$attrs['title'] = $settings['title'];
		$attrs['forgot_password_text'] = $settings['forgot_password_text'];
		$attrs['forgot_password_link'] = $settings['forgot_password_link']['url'];

		if ( ! empty( $settings['forgot_password_link']['url'] ) ) {
			$this->add_link_attributes( 'forgot_password_link', $settings['forgot_password_link'] );
		}
		?>
		<div class="parlay-elementor-widget">
			<?php
				parlay_template( 'user-login', $attrs );
			?>
		</div>
		<?php
	}
}
