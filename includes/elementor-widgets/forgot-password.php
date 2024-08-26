<?php

namespace Parlay\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ForgotPassword_Form_Widget extends \Elementor\Widget_Base {

	// Widget constructor
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
	}

	public function get_style_depends() {
		return [ 'parlay-api-style', 'parlay-alert' ];
	}

	public function get_script_depends() {
		return [ 'parlay-api-script', 'parlay-alert' ];
	}

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'parlay-forgot-password';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Parlay Forgot Password Form', 'parlay-api' );
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
		return [ 'password', 'form', 'user' ];
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
				'default' => __( 'Forgot Password?', 'parlay-api' ),
			]
		);

		$this->add_control(
			'heading_text',
			[
				'label'   => __( 'Heading Text', 'parlay-api' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'Please enter your email address. You will receive an email message with information on how to reset your password.', 'parlay-api' ),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'   => __( 'Button Text', 'parlay-api' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Reset Password', 'parlay-api' ),
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
		global $wp_query;

		$settings              = $this->get_settings_for_display();
        $attrs['title']        = $settings['title'];
		$attrs['heading_text'] = $settings['heading_text'];
		$attrs['button_text']  = $settings['button_text'];
		?>
		<div class="parlay-elementor-widget">
			<?php

			if ( ! isset( $wp_query->query_vars['reset_pwd_token'] ) ) {
				parlay_template( 'account/forgot-password', $attrs );
			} else {
                $attrs['token'] = $wp_query->query_vars['reset_pwd_token'];
				parlay_template( 'account/reset-password', $attrs );
			}
			?>
		</div>
		<?php
	}
}
