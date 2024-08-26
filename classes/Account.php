<?php

namespace Parlay\Api;

use Parlay\Api\UserAuth;
use Parlay\Api\Reports;
use Parlay\Api\HelpDesk;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class Account {

	/**
	 * Current page
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private static $current_page;

	/**
	 * Account pages array
	 *
	 * @var array
	 */
	private static $account_pages;

	/**
	 * Account reports requested
	 *
	 */
	private static $account_reports;

	/**
	 * Account reports requested
	 *
	 */
	private static $account_tickets;

	/**
	 * Account main slug
	 */
	const SLUG = 'my-account';

	/**
	 * Specific report/ticket/activity ID from the URL
	 */
	private static $data_id;

	/**
	 * Initializes the account page
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::setup_account_pages();
		add_action( 'wp', __CLASS__ . '::init_account' );
		add_action( 'template_redirect', __CLASS__ . '::template_redirect' );
	}

	/**
	 * Initializes account processes and requests.
	 *
	 * @since 1.0.0
	 */
	public static function init_account() {
		global $wp_the_query;

		// Compat check - check if we are on elementor edit or preview mode.
		if ( parlay_is_elementor_mode() ) {
			// No need to authenticate
			return;
		}

		// Get the current page
		self::$current_page = $wp_the_query->get( 'pgs_account' );

		// Get specific report or ticket ID.
		if ( $wp_the_query->get( 'pgs_ticket_id' ) ) {
			self::$data_id = $wp_the_query->get( 'pgs_ticket_id' );
		} elseif ( $wp_the_query->get( 'pgs_activity_id' ) ) {
			self::$data_id = $wp_the_query->get( 'pgs_activity_id' );
		}

		// Logout request
		if ( 'logout' === self::$current_page && UserAuth::is_authenticated() ) {
			UserAuth::logout();
		}

		// Account page request check
		if ( self::is_account_page() && ! is_admin() ) {

			// Check if authenticated and redirect if not
			UserAuth::is_authenticated( true );

			// Redirect if no ticket id is set
			if ( 'ticket' === self::$current_page && ! self::get_data_id() ) {
				wp_redirect( home_url( '/' . self::SLUG . '/tickets' ) );
				exit;
			}

			// Account update
			if ( isset( $_POST['pgs-account-nonce'] )
				&& wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-edit-profile' )
			) {
				self::update_account();
			}

			// Reports request
			if ( isset( $_POST['pgs-account-nonce'] )
				&& ( wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-reconcile' )
					|| wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-activity' )
					|| wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-transactions' )
					|| wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-wager' )
					|| wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-bonus' )
					|| wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-pre-purchased' )
					)
			) {
				self::process_reports();
			}

			// Helpdesk object
			if ( isset( $_POST['pgs-account-nonce'] )
				&& ( wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-tickets' )
					|| wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-ticket' )
					|| wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-create-ticket' )
					|| wp_verify_nonce( $_POST['pgs-account-nonce'], 'pgs-account-edit-ticket' )
					)
			) {
				self::process_tickets();
			}

			// Check if user token is still valid
			if ( PGS()->get_alert( 'token' ) ) {
				error_log( 'Token expired, redirecting to login: ' . print_r( PGS()->get_alert(), true ) );
				UserAuth::redirect( '/login?referrer=' . esc_url_raw( self::get_current_page_url() ) );
			}
		}
	}

	/**
	 * Setup account pages and generate custom rewrite rules
	 *
	 * @since 1.0.0
	 */
	public static function setup_account_pages() {
		self::$account_pages = [
			// Account pages
			'account' => [
				'dashboard'    => (object) [
					'slug'     => 'dashboard',
					'title'    => __( 'Dashboard', 'parlay-api' ),
					'template' => 'account/dashboard',
				],
				'edit-profile' => (object) [
					'slug'     => 'edit-profile',
					'title'    => __( 'Edit Profile', 'parlay-api' ),
					'template' => 'account/edit-profile',
					'is_form'  => true,
				],
				'withdraw'     => (object) [
					'slug'     => 'withdraw',
					'title'    => __( 'Withdraw', 'parlay-api' ),
					'template' => 'account/withdraw',
				],
				'deposit'      => (object) [
					'slug'     => 'deposit',
					'title'    => __( 'Deposit', 'parlay-api' ),
					'template' => 'account/deposit',
				],
			],

			// Reports
			'reports' => [
				// 'wager'         => (object) [
				//  'slug'     => 'wager',
				//  'title'    => __( 'Wager Activity', 'parlay-api' ),
				//  'template' => 'account/reports/wager',
				//  'is_form'  => true,
				// ],
				'transactions'  => (object) [
					'slug'     => 'transactions',
					'title'    => __( 'Transactions Report', 'parlay-api' ),
					'template' => 'account/reports/transactions',
					'is_form'  => true,
				],
				'reconcile'     => (object) [
					'slug'     => 'reconcile',
					'title'    => __( 'Reconcile Report', 'parlay-api' ),
					'template' => 'account/reports/reconcile',
					'is_form'  => true,
				],
				'activity'      => (object) [
					'slug'     => 'activity',
					'title'    => __( 'Activity Report', 'parlay-api' ),
					'template' => 'account/reports/activity',
					'is_form'  => true,
				],
				'bonus'         => (object) [
					'slug'     => 'bonus',
					'title'    => __( 'Bonus', 'parlay-api' ),
					'template' => 'account/reports/bonus',
					'is_form'  => true,
				],
				'pre-purchased' => (object) [
					'slug'     => 'pre-purchased',
					'title'    => __( 'Pre-Purchased Cards', 'parlay-api' ),
					'template' => 'account/reports/pre-purchased',
					'is_form'  => true,
				],
			],

			// Tickets
			'tickets' => [
				'tickets'       => (object) [
					'slug'     => 'tickets',
					'title'    => __( 'Tickets', 'parlay-api' ),
					'template' => 'account/tickets/lists',
					'is_form'  => true,
				],
				'ticket'        => (object) [
					'slug'     => 'ticket',
					'title'    => __( 'Ticket', 'parlay-api' ),
					'template' => 'account/tickets/view',
					'is_form'  => true,
				],
				'create-ticket' => (object) [
					'slug'     => 'create-ticket',
					'title'    => __( 'Request Help', 'parlay-api' ),
					'template' => 'account/tickets/create',
					'is_form'  => true,
				],
				'edit-ticket'   => (object) [
					'slug'     => 'edit-ticket',
					'title'    => __( 'Edit Ticket', 'parlay-api' ),
					'template' => 'account/tickets/update',
					'is_form'  => true,
				],
			],
		];

		// Add rules for viewing and editing ticket. Get the ticket ID
		add_rewrite_rule(
			self::SLUG . '/ticket/([0-9]+)/?',
			'index.php?pagename=' . self::SLUG . '&pgs_account=ticket&pgs_ticket_id=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			self::SLUG . '/edit-ticket/([0-9]+)/?',
			'index.php?pagename=' . self::SLUG . '&pgs_account=edit-ticket&pgs_ticket_id=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			self::SLUG . '/activity/([0-9]+)/?',
			'index.php?pagename=' . self::SLUG . '&pgs_account=activity&pgs_activity_id=$matches[1]',
			'top'
		);

		// Generate rewrite rules for account pages
		foreach ( self::$account_pages as $group => $pages ) {
			foreach ( $pages as $key => $page ) {
				add_rewrite_rule(
					self::SLUG . '/' . $page->slug . '/?',
					'index.php?pagename=' . self::SLUG . '&pgs_account=' . $key,
					'top'
				);
			}
		}

		// Account activation
		add_rewrite_rule(
			'activate-account/([^/]+)/?',
			'index.php?pagename=login&activation_key=$matches[1]',
			'top'
		);

		// Logout
		add_rewrite_rule(
			self::SLUG . '/logout/?',
			'index.php?pagename=' . self::SLUG . '&pgs_account=logout',
			'top'
		);

		// Reset password request
		add_rewrite_rule(
			'reset-password/([^/]+)/?',
			'index.php?pagename=forgot-password&reset_pwd_token=$matches[1]',
			'top'
		);

		// Rewrite tags
		add_rewrite_tag( '%pgs_account%', '([^/]+)' );
		add_rewrite_tag( '%activation_key%', '([^/]+)' );
		add_rewrite_tag( '%pgs_ticket_id%', '([0-9]+)' );
		add_rewrite_tag( '%pgs_activity_id%', '([0-9]+)' );
		add_rewrite_tag( '%reset_pwd_token%', '([^/]+)' );
	}

	public static function template_redirect() {
		global $wp_query;

		if ( isset( $wp_query->query_vars['activation_key'] ) ) {
			self::account_activation();
		}

		if ( ! UserAuth::is_authenticated() && isset( $wp_query->query_vars['reset_pwd_token'] ) ) {
			$reset_token = sanitize_text_field( $wp_query->query_vars['reset_pwd_token'] );

			// Validate if token exists in transient
			$token_data = get_transient( 'pgs_reset_pwd_token_' . $reset_token );
			if ( empty( $token_data ) ) {
				error_log( 'Reset token does not exist in transient: ' . print_r( $reset_token, true ) );
				PGS()->set_alert('account', [
					'type'    => 'error',
					'message' => __( 'Invalid reset token.', 'parlay-api' ),
				]);
				UserAuth::redirect( '/login' );
			}
		}
	}

	public static function account_activation() {
		global $wp_query;

		if ( ! isset( $wp_query->query_vars['activation_key'] ) ) {
			return;
		}

		if ( UserAuth::is_authenticated() ) {
			PGS()->set_alert('account', [
				'type'    => 'error',
				'message' => __( 'You are already logged in.', 'parlay-api' ),
			] );
			return;
		}

		// Process account activation
		$activation_key = $wp_query->query_vars['activation_key'];

		if ( UserAuth::activate_account( $activation_key ) ) {
			error_log( 'account activated: ' . $activation_key );

			$type    = 'success';
			$message = __( 'Account activated successfully. You can now log in.', 'parlay-api' );
			$timer   = 5000;
			$confirm = 'login';

		} else {
			error_log( 'error activation: ' . $activation_key );
			$type    = 'error';
			$message = __( 'Invalid activation key', 'parlay-api' );
			$timer   = 4000;
			$confirm = '';
		}

		PGS()->set_alert( 'activate', [
			'type'    => $type,
			'message' => $message,
			'timer'   => $timer,
			// 'confirm' => $confirm,
		] );
	}

	public static function update_account() {
		$fields      = self::get_account_fields();
		$update_info = [];
		$type        = '';
		$message     = '';

		foreach ( $fields['personal'] as $field_key => $field ) {
			if ( isset( $_POST[ $field_key ] ) ) {
				$update_info[ $field_key ] = $_POST[ $field_key ];
			}
		}

		foreach ( $fields['account'] as $field_key => $field ) {
			if ( isset( $_POST[ $field_key ] ) ) {
				$update_info[ $field_key ] = $_POST[ $field_key ];
			}
		}

		// Password check
		if ( ! isset( $_POST['password'] ) || ! isset( $_POST['confirm_password'] ) ) {
			$type    = 'error';
			$message = __( 'Please enter password and confirm password.', 'parlay-api' );
		}

		if ( ! empty( trim( $_POST['password'] ) ) && $_POST['password'] !== $_POST['confirm_password'] ) {
			$type    = 'error';
			$message = __( 'Passwords do not match.', 'parlay-api' );
		} elseif ( ! empty( trim( $_POST['password'] ) ) ) {
			$update_info['password'] = $_POST['password'];
		}

		if ( ! empty( $message ) ) {
			PGS()->set_alert( 'account', [
				'type'    => $type,
				'message' => $message ,
				'timer'   => 4000,
			] );
			return;
		}

		$results = UserAuth::update_account( $update_info );
		if ( ! $results ) {
			error_log( 'failed: ' . print_r( UserAuth::get_errors(), true ) );
			$type    = 'error';
			$message = __( 'Account update failed.', 'parlay-api' );
		} else {
			$type    = 'success';
			$message = __( 'Account updated successfully.', 'parlay-api' );
		}

		PGS()->set_alert( 'account', [
			'type'    => $type,
			'timer'   => 3500,
			'message' => $message,
		] );
	}

	public static function process_reports() {
		if ( $_POST['from-date'] === '' || $_POST['to-date'] === '' ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 4000,
				'message' => __( 'Please enter from and to date.', 'parlay-api' ),
			]);
		}

		$report_page   = self::get_current_page();
		$method_name   = '';
		$report_params = [];
		switch ( $report_page ) {
			case 'reconcile':
				$method_name   = 'reconcile';
				$report_params = [
					'from_date' => $_POST['from-date'],
					'to_date'   => $_POST['to-date'],
				];
				break;
			case 'activity':
				// Activity details
				if ( self::get_data_id() ) {
					$method_name   = 'activity_details';
					$report_params = [
						'activity_id' => self::get_data_id(),
					];
				} else {
					$method_name   = 'list_activity';
					$report_params = [
						'from_date' => $_POST['from-date'],
						'to_date'   => $_POST['to-date'],
						'game_type' => ! empty( $_POST['game-type'] ) ? implode( ',', (array) $_POST['game-type'] ) : '',
					];
				}
				break;
			case 'transactions':
				$method_name   = 'transactions';
				$report_params = [
					'from_date'         => $_POST['from-date'],
					'to_date'           => $_POST['to-date'],
					'transaction_types' => ! empty( $_POST['transaction_types'] ) ? implode( ',', (array) $_POST['transaction_types'] ) : '',
					'currencies'        => ! empty( $_POST['currencies'] ) ? implode( ',', (array) $_POST['currencies'] ) : '',
				];
				break;
			case 'bonus':
				$method_name   = 'bonus_report';
				$report_params = [
					'from_date' => $_POST['from-date'],
					'to_date'   => $_POST['to-date'],
					'status'    => '',
				];
				break;
			case 'pre-purchased':
				$method_name   = 'prepurchases';
				$report_params = [
					'from_date'   => $_POST['from-date'],
					'to_date'     => $_POST['to-date'],
					'select_date' => $_POST['select-date'],
				];
				break;
		}
		$response = Reports::{$method_name}( $report_params );

		if ( isset( $response['body']['data'] ) ) {
			do_action( 'qm/debug', 'found reports: ' . print_r( $response['body']['data'], true ) );
			self::$account_reports = $response['body']['data'];
		}
	}

	public static function process_tickets() {
		$current_page  = self::get_current_page();
		$method_name   = '';
		$report_params = [];

		if ( empty( $current_page ) ) {
			return;
		}

		switch ( $current_page ) {
			case 'tickets':
				$method_name   = 'tickets_list';
				$report_params = [
					'from_date' => $_POST['from-date'],
					'to_date'   => $_POST['to-date'],
					'status'    => ! empty( $_POST['status'] ) ? $_POST['status'] : '',
				];
				break;
			case 'create-ticket':
				$method_name   = 'open_ticket';
				$report_params = [
					'code'    => $_POST['subject_code'],
					'comment' => $_POST['comment'],
				];
				break;
			case 'ticket':
				$method_name   = 'create_ticket_comment';
				$report_params = [
					'ticket_id' => self::get_data_id(),
					'comment'   => $_POST['comment'],
				];
				break;
		}

		$response = HelpDesk::{$method_name}( $report_params );

		if ( isset( $response['data'] ) ) {
			self::$account_tickets = $response['data'];
		}
	}

	public static function get_account_fields( $type = '' ) {
		$fields['personal'] = [
			'email'            => (object) [
				'type'           => 'text',
				'label'          => __( 'Email', 'parlay-api' ),
				'value'          => '',
				'update_allowed' => false,
			],
			'fullname'         => (object) [
				'type'     => 'text',
				'label'    => __( 'Name', 'parlay-api' ),
				'value'    => '',
				'required' => true,
			],
			'phoneNumber'      => (object) [
				'type'  => 'text',
				'label' => __( 'Telephone', 'parlay-api' ),
				'value' => '',
			],
			'mobilePhone'      => (object) [
				'type'  => 'text',
				'label' => __( 'Cellphone', 'parlay-api' ),
				'value' => '',
			],
			'address1'         => (object) [
				'type'  => 'text',
				'label' => __( 'Address', 'parlay-api' ),
				'value' => '',
			],
			'city'             => (object) [
				'type'  => 'text',
				'label' => __( 'City', 'parlay-api' ),
				'value' => '',
			],
			'state'            => (object) [
				'type'    => 'select',
				'label'   => __( 'State', 'parlay-api' ),
				'value'   => '',
				'options' => [
					'Acre'                => 'Acre',
					'Alagoas'             => 'Alagoas',
					'Amazonas'            => 'Amazonas',
					'Amapa'               => 'Amapa',
					'Bahia'               => 'Bahia',
					'Ceara'               => 'Ceara',
					'Distrito Federal'    => 'Distrito Federal',
					'Espirito Santo'      => 'Espirito Santo',
					'Goias'               => 'Goias',
					'Maranhao'            => 'Maranhao',
					'Mato Grosso'         => 'Mato Grosso',
					'Mato Grosso do Sul'  => 'Mato Grosso do Sul',
					'Minas Gerais'        => 'Minas Gerais',
					'Para'                => 'Para',
					'Paraiba'             => 'Paraiba',
					'Parana'              => 'Parana',
					'Pernambuco'          => 'Pernambuco',
					'Piaui'               => 'Piaui',
					'Rio de Janeiro'      => 'Rio de Janeiro',
					'Rio Grande do Norte' => 'Rio Grande do Norte',
					'Rio Grande do Sul'   => 'Rio Grande do Sul',
					'Rondonia'            => 'Rondonia',
					'Roraima'             => 'Roraima',
					'Santa Catarina'      => 'Santa Catarina',
					'Sao Paulo'           => 'Sao Paulo',
					'Sergipe'             => 'Sergipe',
					'Tocantins'           => 'Tocantins',
				],
			],
			'postalCode'       => (object) [
				'type'  => 'text',
				'label' => __( 'Postal Code', 'parlay-api' ),
				'value' => '',
			],
			'country'          => (object) [
				'type'    => 'select',
				'label'   => __( 'Country', 'parlay-api' ),
				'value'   => '',
				'options' => [
					'BR' => 'Brasil',
				],
			],
			'gender'           => (object) [
				'type'    => 'select',
				'label'   => __( 'Gender', 'parlay-api' ),
				'value'   => '',
				'options' => [
					'M' => 'Male',
					'F' => 'Female',
				],
			],
			'birthday'         => (object) [
				'type'           => 'text',
				'label'          => __( 'Date Of Birth', 'parlay-api' ),
				'value'          => '',
				'update_allowed' => false,
			],
			'receiveBroadcast' => (object) [
				'type'  => 'checkbox',
				'label' => __( 'Do you want to receive news and promotions via email?', 'parlay-api' ),
				'value' => false,
			],
		];

		$fields['account'] = [
			'userId'  => (object) [
				'type'           => 'text',
				'label'          => __( 'Player ID', 'parlay-api' ),
				'value'          => '',
				'update_allowed' => false,
			],
			'alias'   => (object) [
				'type'           => 'text',
				'label'          => __( 'Nick Name', 'parlay-api' ),
				'value'          => '',
				'update_allowed' => false,
			],
			'balance' => (object) [
				'type'           => 'text',
				'label'          => __( 'Balance', 'parlay-api' ),
				'value'          => isset( DataManager::get_player_balance()['total'] ) ? DataManager::get_player_balance()['total'] : 0,
				'update_allowed' => false,
			],
			// 'cpf'     => (object) [
			//  'type'           => 'text',
			//  'label'          => __( 'CPF', 'parlay-api' ),
			//  'value'          => '',
			//  'update_allowed' => false,
			// ],
		];

		if ( isset( $fields[ $type ] ) ) {
			return $fields[ $type ];
		}

		return $fields;
	}

	public static function get_page_data( $page_slug = '' ) {
		$page_slug   = empty( $page_slug ) ? self::$current_page : $page_slug;
		$page_object = null;

		foreach ( self::get_pages() as $group => $pages ) {
			foreach ( $pages as $key => $page ) {
				if ( $page->slug === $page_slug ) {
					$page_object = $page;
					break;
				}
			}
			if ( ! empty( $page_object ) ) {
				break;
			}
		}

		return $page_object;
	}

	public static function render_page( $atts = [] ) {
		$page     = self::get_page_data();
		$template = ! empty( $page ) ? $page->template : 'account/dashboard';

		if ( 'activity' === self::$current_page && self::get_data_id() ) {
			$template = 'account/reports/activity-details';
		}

		do_action( 'qm/debug', 'current page: ' . self::$current_page . ', template: ' . $template );

		parlay_template( $template, $atts );
	}

	public static function get_pages( $group = '' ) {
		if ( empty( $group ) ) {
			return self::$account_pages;
		}

		return self::$account_pages[ $group ];
	}

	public static function render_nav() {
		parlay_template( 'account/left-nav', [ 'nav_items' => self::get_pages( 'reports' ) ] );
	}

	public static function is_account_page() {
		return is_page( self::SLUG );
	}

	public static function account_url( $page ) {
		return home_url( '/' . self::SLUG . '/' . $page );
	}

	public static function get_current_page() {
		return self::$current_page;
	}

	public static function get_data_id() {
		return self::$data_id;
	}

	public static function get_current_page_url() {

		$page_id = self::get_data_id() ? '/' . self::get_data_id() : '';
		return home_url( '/' . self::SLUG . '/' . self::$current_page . $page_id );
	}

	public static function get_account_reports() {
		return self::$account_reports;
	}

	public static function get_account_tickets() {
		return self::$account_tickets;
	}

	public static function get_ticket_details() {
		$ticket_id = self::get_data_id();

		$ticket_details = [
			'ticket_id'  => $ticket_id,
			'status'     => '',
			'date'       => '',
			'issue_name' => '',
			'comment'    => [],
			'issue_code' => '',
		];

		if ( $ticket_id ) {
			do_action( 'qm/debug', 'ticket id: ' . self::get_data_id() );

			$ticket = HelpDesk::view_ticket_details( $ticket_id ) ?? [];
			if ( empty( $ticket ) ) {
				return false;
			}

			$issue_list  = HelpDesk::get_ticket_issue_list();
			$status_list = HelpDesk::get_status_list();
			$status      = isset( $ticket['closed'] ) ? $status_list['C'] : $status_list['N'];
			$date        = isset( $ticket['created'] ) ? date( 'd-m-Y H:i', strtotime( $ticket['created'] ) ) : '';

			$ticket_details = [
				'ticket_id'  => $ticket_id,
				'status'     => isset( $ticket['closed'] ) ? $status_list['C'] : $status_list['N'],
				'date'       => isset( $ticket['created'] ) ? date( 'd-m-Y H:i', strtotime( $ticket['created'] ) ) : '',
				'issue_name' => $ticket['issueName'],
				'issue_code' => $ticket['issueCode'],
				'comments'   => $ticket['comments'],
			];
		}

		return $ticket_details;
	}

	public static function get_activity_details() {
		$acivity_id = self::get_data_id();
		$details    = [];

		if ( $acivity_id ) {
			do_action( 'qm/debug', 'activity id: ' . self::get_data_id() );

			$details = Reports::activity_details( $acivity_id ) ?? [];
		}

		return $details;
	}
}
