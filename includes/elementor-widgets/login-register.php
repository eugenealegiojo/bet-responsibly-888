<?php

namespace Parlay\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Login_Register_Widget extends \Elementor\Widget_Base {

	// Widget constructor
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
	}

	public function get_style_depends() {
		return [ 'parlay-login-widget', 'parlay-api-style' ];
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
		return 'parlay-login-register';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Parlay Popup Login/Register', 'parlay-api' );
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
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'parlay-games-api', 'general' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'login', 'form', 'user' ];
	}

	/**
	 * Get custom help URL.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget help URL.
	 */
	public function get_custom_help_url() {
		// return 'https://developers.elementor.com/docs/widgets/';
	}

	/**
	 * Register widget controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		// Login Section
		$this->start_controls_section(
			'login_section',
			[
				'label' => esc_html__( 'Login', 'parlay-api' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'login_btn_text',
			[
				'label'   => __( 'Button Text', 'parlay-api' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Login', 'parlay-api' ),
			]
		);

		$this->add_control(
			'login_btn_action',
			[
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => esc_html__( 'Select Action', 'parlay-api' ),
				'options' => [
					'default'  => esc_html__( 'Default', 'parlay-api' ),
					'lightbox' => esc_html__( 'Lightbox', 'parlay-api' ),
					'link'     => esc_html__( 'Link', 'parlay-api' ),
				],
				'default' => 'lightbox',
			]
		);

		$this->add_control(
			'login_link',
			[
				'label'       => esc_html__( 'Login Link', 'parlay-api' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'options'     => [ 'url', 'is_external', 'nofollow' ],
				'default'     => [
					'url'         => '',
					'is_external' => true,
					'nofollow'    => true,
				],
				'label_block' => true,
				'condition'   => [
					'login_btn_action' => 'link',
				],
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

		// Register Section
		$this->start_controls_section(
			'register_section',
			[
				'label' => esc_html__( 'Register', 'parlay-api' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'register_btn_text',
			[
				'label'   => __( 'Button Text', 'parlay-api' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Register', 'parlay-api' ),
			]
		);

		$this->add_control(
			'register_link',
			[
				'label'       => esc_html__( 'Registration Link', 'parlay-api' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'options'     => [ 'url', 'is_external', 'nofollow' ],
				'default'     => [
					'url'         => '',
					'is_external' => true,
					'nofollow'    => true,
				],
				'label_block' => true,
			]
		);
		$this->end_controls_section();

		// Style Tab
		$this->start_controls_section(
			'login_section_style',
			[
				'label' => esc_html__( 'Login Button', 'parlay-api' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'login_btn_bg_color',
			[
				'label'     => __( 'Background Color', 'parlay-api' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7f817e',
				'selectors' => [
					'{{WRAPPER}} .parlay-login-button' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'login_btn_bg_color_hover',
			[
				'label'     => __( 'Hover Background Color', 'parlay-api' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#5a5b5a',
				'selectors' => [
					'{{WRAPPER}} .parlay-login-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'login_btn_text_color',
			[
				'label'     => __( 'Text Color', 'parlay-api' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .parlay-login-button > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'login_btn_font_family',
			[
				'label'     => esc_html__( 'Font Family', 'parlay-api' ),
				'type'      => \Elementor\Controls_Manager::FONT,
				'default'   => '"Kumbh Sans"',
				'selectors' => [
					'{{WRAPPER}} .parlay-login-button' => 'font-family: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'login_btn_padding',
			[
				'label'      => __( 'Padding', 'parlay-api' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'top'    => '13',
					'right'  => '28',
					'bottom' => '13',
					'left'   => '28',
					'unit'   => 'px', // Default unit
				],
				'selectors'  => [
					'{{WRAPPER}} .parlay-login-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'login_btn_border',
				'selector' => '{{WRAPPER}} .parlay-login-button',
			]
		);

		$this->add_control(
			'login_btn_border_radius',
			[
				'label'      => __( 'Border Radius', 'text-domain' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'top'    => '4',
					'right'  => '4',
					'bottom' => '4',
					'left'   => '4',
					'unit'   => 'px', // Default unit
				],
				'selectors'  => [
					'{{WRAPPER}} .parlay-login-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_inline_editing_attributes( 'login_btn_text', 'none' );
		$this->add_inline_editing_attributes( 'register_btn_text', 'none' );

		if ( ! empty( $settings['login_link']['url'] ) ) {
			$this->add_link_attributes( 'login_link', $settings['login_link'] );
		}

		if ( ! empty( $settings['forgot_password_link']['url'] ) ) {
			$this->add_link_attributes( 'forgot_password_link', $settings['forgot_password_link'] );
		}

		if ( ! empty( $settings['register_link']['url'] ) ) {
			$this->add_link_attributes( 'register_link', $settings['register_link'] );
		}

		?>
		<div class="parlay-elementor-widget">
			<?php if ( ! parlay_is_authenticated() ) : ?>
				<?php if ( ! empty( $settings['register_btn_text'] ) ) : ?>
					<div class="sc_item_button sc_button_wrap">
						<a <?php $this->print_render_attribute_string( 'register_link' ); ?> <?php echo $this->get_render_attribute_string( 'login_btn_text' ); ?> class="sc_button sc_button_default btn-sign-up sc_button_size_small sc_button_icon_left">
							<span class="sc_button_text"><span class="sc_button_title"><?php echo esc_html( $settings['register_btn_text'] ); ?></span></span>
						</a>
					</div>
				<?php endif; ?>
				<div class="parlay-api-login-form">
					
					<div class="parlay-login-button">
						<?php if ( 'lightbox' === $settings['login_btn_action'] ) : ?>
							<a href="#" class="open-lightbox" data-elementor-open-lightbox="yes" data-lightbox-id="parlay-login-form" <?php echo $this->get_render_attribute_string( 'login_btn_text' ); ?>>
								<?php echo esc_html( $settings['login_btn_text'] ); ?>
							</a>
						<?php else : ?>
							<a <?php $this->print_render_attribute_string( 'login_link' ); ?> <?php echo $this->get_render_attribute_string( 'login_btn_text' ); ?>>
								<?php echo esc_html( $settings['login_btn_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
					
					<?php if ( 'lightbox' === $settings['login_btn_action'] ) : ?>
						<div id="parlay-login-form">
							<h2><?php _e( 'Login', 'parlay-api' ); ?></h2>
							<form class="parlay-login-block parlay-form" method="post" data-endpoint="/account/login">
								
								<input type="text" id="username" name="username" required placeholder="<?php echo esc_attr__( 'Username', 'parlay-api' ); ?>">
								<input type="password" id="password" name="password" required placeholder="<?php echo esc_attr__( 'Password', 'parlay-api' ); ?>">
								
								<?php if ( ! empty( $settings['forgot_password_text'] ) ) : ?>
									<div class="forgot-pwd-link">
										<a href="<?php echo esc_url( $settings['forgot_password_link']['url'] ); ?>">
											<?php echo esc_html( $settings['forgot_password_text'] ); ?>
										</a>
									</div>
								<?php endif; ?>	

								<button class="submit" type="submit"><?php _e( 'Login', 'parlay-api' ); ?></button>
								
								<?php wp_nonce_field( 'wp_rest' ); ?>
							</form>
							<div class="form-error-message"></div>
						</div>
					<?php endif; ?>
				</div>	
			<?php else : ?>
				<div class="sc_item_button sc_button_wrap">
					<a href="<?php echo parlay_account_url( '/deposit' ); ?>" class="sc_button sc_button_default btn-sign-up sc_button_size_small sc_button_icon_left">
						<span class="sc_button_text"><span class="sc_button_title"><?php _e( 'Deposit', 'parlay-api' ); ?></span></span>
					</a>
				</div>
				<?php parlay_template( 'account/dashboard-menu' ); ?>
			<?php endif; ?>
		</div>
		<?php
	}
}
