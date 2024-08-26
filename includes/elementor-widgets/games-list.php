<?php

namespace Parlay\Api;
use Parlay\Api\DataManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class GamesList extends \Elementor\Widget_Base {

	// Widget constructor
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
	}

	public function get_script_depends() {
		return [ 'parlay-games-widget' ];
	}

	public function get_style_depends() {
		return [ 'parlay-games-widget' ];
	}

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'parlay-games-list';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Parlay Games List', 'parlay-api' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-apps';
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
	 * Retrieve the list of keywords the oEmbed widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'bingo', 'games', 'casino' ];
	}

	public function get_tags() {
		$defaults = [
			'featured'    => esc_html__( 'Featured', 'parlay-api' ),
			'new'         => esc_html__( 'New', 'parlay-api' ),
			'holiday'     => esc_html__( 'Holiday', 'parlay-api' ),
			'featuredVIP' => esc_html__( 'Featured VIP', 'parlay-api' ),
			'rey'         => esc_html__( 'Rey', 'parlay-api' ),
		];

		$get_tags = DataManager::get_tags();
		if ( $get_tags ) {
			foreach ( $get_tags as $key => $tag ) {
				$tags[ $tag['name'] ] = isset( $tag['translation'] ) ? $tag['translation'] : $tag['name'];
			}
		} else {
			$tags = $defaults;
		}

		return $tags;
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
			'content', [
				'label' => esc_html__( 'Game Options', 'parlay-api' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'category', [
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => esc_html__( 'Game Category', 'parlay-api' ),
				'options' => [
					'bingo'  => esc_html__( 'Bingo', 'parlay-api' ),
					'casino' => esc_html__( 'Casino', 'parlay-api' ),
				],
				'default' => 'casino',
			]
		);

		$this->add_control(
			'game_type', [
				'label'       => esc_html__( 'Select Game Type', 'parlay-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => [
					'BA' => __( 'Baccarat', 'parlay-api' ),
					'JE' => __( 'Bejeweled', 'parlay-api' ),
					'BI' => __( 'Bingo', 'parlay-api' ),
					'BB' => __( 'Bingo Bets', 'parlay-api' ),
					'BS' => __( 'Bingo Social', 'parlay-api' ),
					'BJ' => __( 'Blackjack', 'parlay-api' ),
					'CB' => __( 'Caribbean Poker', 'parlay-api' ),
					'CG' => __( 'Community Games', 'parlay-api' ),
					'CR' => __( 'Craps', 'parlay-api' ),
					'HL' => __( 'Hi-Lo', 'parlay-api' ),
					'KE' => __( 'Keno', 'parlay-api' ),
					'LB' => __( 'Live Bingo', 'parlay-api' ),
					'BL' => __( 'Lobby', 'parlay-api' ),
					'LD' => __( 'Lotto', 'parlay-api' ),
					'MA' => __( 'Match', 'parlay-api' ),
					'PS' => __( 'Multiplayer Slots', 'parlay-api' ),
					'PT' => __( 'Pull Tabs', 'parlay-api' ),
					'QZ' => __( 'Quiz', 'parlay-api' ),
					'RL' => __( 'Roulette', 'parlay-api' ),
					'SC' => __( 'Scratch Card', 'parlay-api' ),
					'SG' => __( 'Sit N\' Go', 'parlay-api' ),
					'SB' => __( 'Skill Bingo', 'parlay-api' ),
					'SL' => __( 'Slots', 'parlay-api' ),
					'SO' => __( 'Social', 'parlay-api' ),
					'PK' => __( 'Video/Table Poker', 'parlay-api' ),
					'VB' => __( 'Video Bingo', 'parlay-api' ),
				],
				'default'     => '',
				'condition'   => [
					'category' => 'casino',
				],
			]
		);

		$this->add_control(
			'tags', [
				'label'       => esc_html__( 'Tags', 'parlay-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => $this->get_tags(),
				'default'     => '',
				'condition'   => [
					'category' => 'casino',
				],
			]
		);

		$this->add_control(
			'free_game', [
				'label'        => __( 'Includes free games', 'parlay-api' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'parlay-api' ),
				'label_off'    => __( 'No', 'parlay-api' ),
				'return_value' => 'yes', // Value when switch is on
				'default'      => 'no', // Default value
				'condition'    => [
					'category' => 'casino',
				],
			]
		);

		$this->add_control(
			'fallback_thumbnail', [
				'label'     => __( 'Fallback Thumbnail', 'parlay-api' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'separator' => 'before',
			],
		);

		$this->add_control(
			'limit', [
				'label'        => __( 'Limit', 'parlay-api' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'parlay-api' ),
				'label_off'    => __( 'Off', 'parlay-api' ),
				'return_value' => 'yes', // Value when switch is on
				'default'      => 'yes', // Default value
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'count', [
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'label'       => esc_html__( 'Count', 'parlay-api' ),
				'placeholder' => '0',
				'min'         => 0,
				'max'         => 50,
				'step'        => 1,
				'default'     => 3,
				'condition'   => [
					'limit' => 'yes',
				],
			]
		);

		$this->add_control(
			'sort_by', [
				'label'   => esc_html__( 'Game Order', 'parlay-api' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'name'   => esc_html__( 'By Name', 'parlay-api' ),
					'id'     => esc_html__( 'By ID', 'parlay-api' ),
					'custom' => esc_html__( 'Custom Order', 'parlay-api' ),
				],
				'default' => 'name',
				'condition' => [
					'category' => 'casino',
				]
			]
		);

		$this->add_control(
			'sort_order', [
				'label'     => esc_html__( 'Sort Order', 'parlay-api' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => [
					'asc'  => esc_html__( 'Ascending', 'parlay-api' ),
					'desc' => esc_html__( 'Descending', 'parlay-api' ),
				],
				'default'   => 'asc',
				'condition' => [
					'sort_by' => [ 'name', 'id' ],
				],
			]
		);

		$this->add_control(
			'games_order', [
				'label'       => __( 'Order Games', 'plugin-name' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'ai'          => false,
				'options'     => [],
				'condition'   => [
					'sort_by'  => 'custom',
					'category' => 'casino',
				],
			]
		);

		$this->add_control(
			'columns', [
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'label'       => esc_html__( 'Columns', 'parlay-api' ),
				'placeholder' => '0',
				'min'         => 0,
				'max'         => 50,
				'step'        => 1,
				'default'     => 3,
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'games_filter', [
				'label'        => __( 'Games Filter', 'parlay-api' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'parlay-api' ),
				'label_off'    => __( 'Off', 'parlay-api' ),
				'return_value' => 'yes', // Value when switch is on
				'default'      => 'no', // Default value
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$attributes['category']           = ! empty( $settings['category'] ) ? $settings['category'] : 'casino';
		$attributes['columns']            = (int) $settings['columns'] > 0 ? (int) $settings['columns'] : 3;
		$attributes['games_filter']       = 'yes' === $settings['games_filter'] ? true : false;
		$attributes['fallback_thumbnail'] = ! empty( $settings['fallback_thumbnail']['url'] ) ? $settings['fallback_thumbnail']['url'] : '';

		// // API data params - filtering and sorting results
		$attributes['freePlay']    = 'yes' === $settings['free_game'] ? 'true' : 'false';
		$attributes['limit_count'] = 'yes' === $settings['limit'] && (int) $settings['count'] > 0 ? (int) $settings['count'] : 0;

		$attributes['filters']['type']    = ! empty( $settings['game_type'] ) ? array_filter( $settings['game_type'] ) : '';
		$attributes['filters']['tags']    = ! empty( $settings['tags'] ) ? array_filter( $settings['tags'] ) : '';
		$attributes['sort']['sort_by']    = ! empty( $settings['sort_by'] ) ? $settings['sort_by'] : 'name';
		$attributes['sort']['sort_order'] = ! empty( $settings['sort_order'] ) ? $settings['sort_order'] : 'asc';

		// Custom games sorting order
		$attributes['games_order'] = $settings['games_order'] ?? [];

		// Render filter scripts
		if ( true === $attributes['games_filter'] ) {
			wp_enqueue_script( 'parlay-games-filter' );
		}

		// Render content
		parlay_template( 'games-list', $attributes );
	}
}
