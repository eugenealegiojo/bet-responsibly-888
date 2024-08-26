<?php

namespace Parlay\Api;

use Parlay\Api\DataManager;
use Parlay\Api\Utils;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class Reports {

	/**
	 * reconcile
	 *
	 * @param  $params from_date and to_date
	 * @return bool
	 */
	public static function reconcile( $params ) {
		if ( ! isset( $params['from_date'] ) || ! isset( $params['to_date'] ) ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Date from and date to are required.', 'parlay-api' ),
			]);
			return false;
		}

		$params = array(
			'from' => Utils::date_to_iso8601( $params['from_date'] ),
			'to'   => Utils::date_to_iso8601( $params['to_date'] ),
		);

		$response = DataManager::api_request( 'player/report/reconcile', 'GET', $params );

		if ( 200 !== $response['code'] ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Unable to process reconcile reports.', 'parlay-api' ),
			]);

			return false;
		}

		return $response;
	}

	/**
	 * list_activity
	 *
	 * @param  $params [from_date] [to_date] [game_type]
	 * @return bool
	 */
	public static function list_activity( $params ) {
		if ( ! isset( $params['from_date'] ) || ! isset( $params['to_date'] ) ) {
			error_log( 'invalid params' );
			return false;
		}

		$params = array(
			'from'     => Utils::date_to_iso8601( $params['from_date'] ),
			'to'       => Utils::date_to_iso8601( $params['to_date'] ),
			'gameType' => isset( $params['game_type'] ) ? $params['game_type'] : null,
		);

		$response = DataManager::api_request( 'player/report/activity', 'GET', $params );

		do_action( 'qm/debug', 'activity response: ' . print_r( $params, true ) );

		if ( 200 !== $response['code'] ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Unable to process activity reports.', 'parlay-api' ),
			]);

			return false;
		}

		return $response;
	}

	public static function activity_details( $acivity_id ) {
		if ( ! isset( $acivity_id ) ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Missing activity id.', 'parlay-api' ),
			]);

			return false;
		}

		$params = [
			'gameSessionId' => $acivity_id,
		];

		$response = DataManager::api_request( 'player/report/activity/' . $acivity_id, 'GET' );

		do_action( 'qm/debug', 'activity details response: ' . print_r( $response, true ) );

		if ( 200 !== $response['code'] ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Unable to process activity details.', 'parlay-api' ),
			]);

			return false;
		}

		return $response['body'];
	}

	/**
	 * bingo_card_detail
	 *
	 * @param  $user_id
	 * @param  $game_session_id
	 * @param  $card_id
	 * @return bool
	 */
	public static function bingo_card_detail( $user_id, $game_session_id, $card_id ) {
		$params = array(
			'userId'        => $user_id,
			'gameSessionId' => $game_session_id,
			'cardId'        => $card_id,
		);

		$xml_response = $this->pgs_api_model->api_request( BINGO_CARD_DETAIL, $params );

		if ( isset( $xml_response->error ) ) {
			set_feedback( 'error', (string) $xml_response->error['token'] );

			log_message( 'debug', 'Bingo Card Detail Failed : ' . $xml_response->error['token'] );

			return false;
		} else {
			return $xml_response->bingoCardGameLog;
		}
	}

	/**
	 * top_winners
	 *
	 * @return bool
	 */
	public static function top_winners() {
		$xml_response = $this->pgs_api_model->api_request( TOP_WINNERS );

		if ( isset( $xml_response->error ) ) {
			set_feedback( 'error', (string) $xml_response->error['token'] );

			log_message( 'debug', 'Top Winners Failed : ' . $xml_response->error['token'] );

			return false;
		} else {
			return $xml_response->rows;
		}
	}

	/**
	 * date_report
	 *
	 * Runs reporting call for the following API's:
	 * Use Variable report_name =
	 * REFERRAL_EARNINGS
	 * PREPURCHASE_REPORT
	 * TRANSACTION_REPORT (optional Field $transaction_status)
	 * QUALIFY_BONUS (optional field $bonus_status)
	 * VIEW_TICKETS (optional field $ticket_status)
	 *
	 * @param  $report_name
	 * @param  $user_id
	 * @param  $start_month
	 * @param  $start_day
	 * @param  $start_year
	 * @param  $start_hour
	 * @param  $start_minute
	 * @param  $end_month
	 * @param  $end_day
	 * @param  $end_year
	 * @param  $end_hour
	 * @param  $end_minute
	 * @param  $transaction_status
	 * @param $bonus_status
	 * @return bool
	 */
	public static function date_report( $report_name, $params, $return_all = false ) {
		$multi_param = '';
		foreach ( $params as $key => $value ) {
			if ( $key == 'bonusStatus' ) {
				$multi_param = $value;
			}
			if ( empty( $value ) && $value != '0' ) {
				unset( $params[ $key ] );
			}
		}

		$xml_response = $this->pgs_api_model->api_request( $report_name, $params, null, $multi_param );

		if ( isset( $xml_response->error ) ) {
			set_feedback( 'error', (string) $xml_response->error['token'] );

			log_message( 'debug', $report_name . ' Failed : ' . $xml_response->error['token'] );

			return false;
		} else {
			if ( $return_all ) {
				return $xml_response;
			} else {
				return $xml_response->row;
			}
		}
	}

	/**
	 *  bonus_report
	 */
	public static function bonus_report( $params ) {
		if ( ! isset( $params['from_date'] ) || ! isset( $params['to_date'] ) ) {
			error_log( 'invalid params' );
			return false;
		}

		$params = array(
			'from'   => Utils::date_to_iso8601( $params['from_date'] ),
			'to'     => Utils::date_to_iso8601( $params['to_date'] ),
			'status' => isset( $params['params'] ) ? $params['params'] : '',
		);

		$response = DataManager::api_request( 'player/report/bonus', 'GET', $params );

		do_action( 'qm/debug', 'bonus response: ' . print_r( $response, true ) );

		if ( 200 !== $response['code'] ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Unable to process bonus reports.', 'parlay-api' ),
			]);

			return false;
		}

		return $response;
	}

	/**
	 *  bonus_report
	 *
	 *  Runs a bonus report and porcesses the results for table output.
	 */
	public static function casino_bonus_report( $report_name, $params ) {
		$multi_param = '';

		foreach ( $params as $key => $value ) {
			if ( $key == 'bonusStatus' ) {
				$multi_param = $value . ',bonusStatus';
			}

			if ( empty( $value ) && $value != '0' ) {
				unset( $params[ $key ] );
			}
		}

		$xml_response = $this->pgs_api_model->api_request( CASINO_BONUS_REPORT, $params, null, $multi_param );

		if ( isset( $xml_response->error ) ) {
			set_feedback( 'error', (string) $xml_response->error['token'] );

			log_message( 'debug', $report_name . ' Failed : ' . $xml_response->error['token'] );

			return false;
		} else {
			foreach ( $xml_response->row as $row ) {
				//Convert bonussourceid integer to meaningful value
				switch ( $row['bonussourceid'] ) {
					case -1:
						$bonus_source = 'Unknown';
						break;
					case 1:
						$bonus_source = 'Loyalty Level';
						break;
					case 2:
						$bonus_source = 'Campaign';
						break;
					case 3:
						$bonus_source = 'Manual';
						break;
					case 4:
						$bonus_source = 'Chat';
						break;
					case 5:
						$bonus_source = 'Registration';
						break;
					case 6:
						$bonus_source = 'Deposit';
						break;
					case 7:
						$bonus_source = 'Referral';
						break;
					case 8:
						$bonus_source = 'Game';
						break;
					case 9:
						$bonus_source = 'Trial Bonus';
						break;
					case 10:
						$bonus_source = 'External';
						break;
					case 11:
						$bonus_source = 'E-Gift Bonus';
						break;
					case 12:
						$bonus_source = 'Promotional';
						break;
					case 13:
						$bonus_source = 'Refunded';
						break;
					case 14:
						$bonus_source = 'Wager Reversal';
						break;
					case 15:
						$bonus_source = 'Consoloation Prize';
						break;
					case 16:
						$bonus_source = 'Game Push';
						break;
					case 17:
						$bonus_source = 'Trial Player Push';
						break;
					case 18:
						$bonus_source = 'Extra Promotional';
						break;
					default:
						$bonus_source = 'Unknown/Not Specified';
						break;
				}

				$row['bonussourceid'] = $bonus_source;

				//Convert bonusstatusid integer to meaningful value
				switch ( $row['bonusstatusid'] ) {
					case 0:
						$status = 'label.bonus.state.unknown';
						break;
					case 1:
						$status = 'label.bonus.state.unqualified';
						break;
					case 2:
						$status = 'label.bonus.state.qualifying';
						break;
					case 3:
						$status = 'label.bonus.state.qualified';
						break;
					case 4:
						$status = 'label.bonus.state.cancelled';
						break;
					case 5:
						$status = 'label.bonus.state.expired';
						break;
					case 6:
						$status = 'label.bonus.state.cancelledQualPending';
						break;
					case 7:
						$status = 'label.bonus.state.cancelledUnqualPending';
						break;
					case 8:
						$status = 'label.bonus.state.spent';
						break;
				}

				$row['bonusstatusid'] = $status;
			}

			return $xml_response->row;
		}
	}

	public static function prepurchases( $params ) {
		if ( ! isset( $params['from_date'] ) || ! isset( $params['to_date'] ) ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Date from and date to are required.', 'parlay-api' ),
			]);
			return false;
		}

		$response_data = [];

		$params = array(
			'fromTime'     => Utils::date_to_iso8601( $params['from_date'] ),
			'toTime'       => Utils::date_to_iso8601( $params['to_date'] ),
			'selectByDate' => isset( $params['select_date'] ) ? $params['select_date'] : 'order',
		);

		$response = DataManager::api_request( 'player/prepurchases', 'GET', $params );

		do_action( 'qm/debug', 'prepurchases response: ' . print_r( $response, true ) );

		if ( 200 !== $response['code'] ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Unable to process pre-purchased reports.', 'parlay-api' ),
			]);

			return false;
		}

		if ( isset( $response['body'] ) && is_array( $response['body'] ) ) {
			$response_data['body']['data'] = $response['body'];
		} else {
			$response_data['body']['data'] = [];
		}

		return $response_data;
	}
	/**
	 * history_reports
	 *
	 * @return bool
	 */
	public static function transactions( $params ) {
		if ( ! isset( $params['from_date'] ) || ! isset( $params['to_date'] ) ) {
			error_log( 'invalid params' );
			return false;
		}

		$params = array(
			'from'                  => Utils::date_to_iso8601( $params['from_date'] ),
			'to'                    => Utils::date_to_iso8601( $params['to_date'] ),
			'transactionCategories' => isset( $params['transaction_types'] ) ? $params['transaction_types'] : null,
			'currencies'            => isset( $params['currencies'] ) ? $params['currencies'] : null,
		);

		$response = DataManager::api_request( 'player/report/transaction', 'GET', $params );

		do_action( 'qm/debug', 'transaction params: ' . print_r( $params, true ) );

		if ( 200 !== $response['code'] ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Unable to process transaction reports.', 'parlay-api' ),
			]);

			return false;
		}

		return $response;
	}

	/**
	 * detail_history
	 * possible report_names:
	 * DEPOSIT_HISTORY_DETAIL
	 * WITHDRAW_HISTORY_DETAIL
	 *
	 * @param  $user_id
	 * @param  $report_name
	 * @param  $transaction_id
	 * @return bool
	 */
	public static function detail_history( $user_id, $report_name, $transaction_id ) {
		$report_name = strtoupper( $report_name );
		$params      = array(
			'userId'        => $user_id,
			'transactionId' => $transaction_id,
		);

		$xml_response = $this->pgs_api_model->api_request( $report_name, $params );

		if ( isset( $xml_response->error ) ) {
			set_feedback( 'error', (string) $xml_response->error['token'] );

			log_message( 'debug', $report_name . ' Failed : ' . $xml_response->error['token'] );

			return false;
		} else {
			return $xml_response->rows;
		}
	}

	/**
	 *  deposit_details
	 */
	public static function deposit_details( $user_id, $trans_id ) {
		$params = array(
			'userId'        => $user_id,
			'transactionId' => $trans_id,
		);

		$xml_response = $this->pgs_api_model->api_request( DEPOSIT_DETAIL, $params );

		if ( count( $xml_response->row ) != 0 ) {
			return $xml_response->row;
		} else {
			set_feedback( 'error', (string) $xml_response->error['token'] );

			log_message( 'debug', DEPOSIT_DETAIL . ' Failed : ' . $xml_response->error['token'] );

			return false;
		}
	}

	/**
	 *  withdrawal_details
	 */
	public static function withdrawal_details( $user_id, $trans_id ) {
		$params = array(
			'userId'        => $user_id,
			'transactionId' => $trans_id,
		);

		$xml_response = $this->pgs_api_model->api_request( WITHDRAWAL_DETAIL, $params );

		if ( count( $xml_response->row ) != 0 ) {
			return $xml_response->row;
		} else {
			set_feedback( 'error', (string) $xml_response->error['token'] );

			log_message( 'debug', WITHDRAWAL_DETAIL . ' Failed : ' . $xml_response->error['token'] );

			return false;
		}
	}

	/**
	 *  get_search_options
	 *
	 *  Returns the data needed to populate search option checkbox trees depending
	 *  on the report being run.
	 *
	 *  The returned data will contain a language translation for the root node as
	 *  well as check box labels and values for each search option available.
	 *
	 *  @param  - The PGS constant representing the report being run.
	 *  @return - An array of label and checkbox values.
	 */
	public static function get_search_options( $report_type ) {
		switch ( $report_type ) {
			case WITHDRAW_HISTORY:
				$option_label = 'label.trans.all';

				$search_options = array(
					'txnStatus_pending'   => array(
						'value' => 'pending-true',
						'label' => 'label.trans.pending',
					),
					'txnStatus_completed' => array(
						'value' => 'completed-true',
						'label' => 'label.trans.completed',
					),
					'txnStatus_flowback'  => array(
						'value' => 'flowback-true',
						'label' => 'label.trans.flowback',
					),
				);

				break;

			case DEPOSIT_HISTORY:
				$option_label = 'label.trans.all';

				$search_options = array(
					'txnStatus_approved' => array(
						'value' => 'approved-true',
						'label' => 'label.trans.approved',
					),
					'txnStatus_declined' => array(
						'value' => 'declined-true',
						'label' => 'label.trans.declined',
					),
					'txnStatus_pending'  => array(
						'value' => 'pending-true',
						'label' => 'label.trans.pending',
					),
				);

				break;

			case BONUS_REPORT:
				$option_label = 'label.bonus.status.all';

				$search_options = array(
					'bonusStatus_UB' => array(
						'value' => '1',
						'label' => 'label.status.unqualified',
					),
					'bonusStatus_QB' => array(
						'value' => '2',
						'label' => 'label.qualifying',
					),
					'bonusStatus_AB' => array(
						'value' => '3',
						'label' => 'label.awarded',
					),
					'bonusStatus_CB' => array(
						'value' => '4',
						'label' => 'label.cancelled',
					),
					'bonusStatus_EB' => array(
						'value' => '5',
						'label' => 'label.expired',
					),
					'bonusStatus_SP' => array(
						'value' => '6',
						'label' => 'label.spent',
					),
				);

				break;

			case CASINO_BONUS_REPORT:
				$option_label = 'label.bonus.status.all';

				$search_options = array(
					'bonusStatus_QB' => array(
						'value' => '2',
						'label' => 'label.qualifying',
					),
					'bonusStatus_AB' => array(
						'value' => '3',
						'label' => 'label.awarded',
					),
					'bonusStatus_CB' => array(
						'value' => '4',
						'label' => 'label.cancelled',
					),
					'bonusStatus_EB' => array(
						'value' => '5',
						'label' => 'label.expired',
					),
					'bonusStatus_SP' => array(
						'value' => '6',
						'label' => 'label.spent',
					),
				);

				break;

			case LIST_SESSIONS:
				$option_label = 'label.game.types.all';

				$search_options = array(
					'game_type_BI' => array(
						'value' => 'BI',
						'label' => 'label.game.bi',
					),
					'game_type_SL' => array(
						'value' => 'SL',
						'label' => 'label.game.sl',
					),
					'game_type_PT' => array(
						'value' => 'PT',
						'label' => 'label.game.pt',
					),
					'game_type_RL' => array(
						'value' => 'RL',
						'label' => 'label.game.rl',
					),
					'game_type_BJ' => array(
						'value' => 'BJ',
						'label' => 'label.game.bj',
					),
					'game_type_CR' => array(
						'value' => 'CR',
						'label' => 'label.game.cr',
					),
					'game_type_PK' => array(
						'value' => 'PK',
						'label' => 'label.game.pk',
					),
					'game_type_KE' => array(
						'value' => 'KE',
						'label' => 'label.game.ke',
					),
					'game_type_SC' => array(
						'value' => 'SC',
						'label' => 'label.game.sc',
					),
					'game_type_VB' => array(
						'value' => 'VB',
						'label' => 'label.game.vb',
					),
				);

				break;

			case TRANSACTION_REPORT:
				$option_label = 'label.trans.all';

				if ( $this->config->item( 'site_id' ) == 'gor' || $this->config->item( 'site_id' ) == 'kas' ) {
					$search_options = array(
						'txnStatus_1' => array(
							'value' => '1',
							'label' => 'label.trans.debit',
						),
						'txnStatus_3' => array(
							'value' => '3',
							'label' => 'label.trans.payouts',
						),
						'txnStatus_4' => array(
							'value' => '4',
							'label' => 'label.trans.credit',
						),
						'txnStatus_5' => array(
							'value' => '5',
							'label' => 'label.trans.bonus',
						),
						'txnStatus_6' => array(
							'value' => '6',
							'label' => 'label.trans.referrals',
						),
						'txnStatus_7' => array(
							'value' => '7',
							'label' => 'label.trans.transfers',
						),
						'txnStatus_8' => array(
							'value' => '8',
							'label' => 'label.trans.refunds',
						),
					);
				} else {
					$search_options = array(
						'txnStatus_0' => array(
							'value' => '0',
							'label' => 'label.trans.purchases',
						),
						'txnStatus_1' => array(
							'value' => '1',
							'label' => 'label.trans.debit',
						),
						'txnStatus_2' => array(
							'value' => '2',
							'label' => 'label.trans.declined',
						),
						'txnStatus_3' => array(
							'value' => '3',
							'label' => 'label.trans.payouts',
						),
						'txnStatus_4' => array(
							'value' => '4',
							'label' => 'label.trans.credit',
						),
						'txnStatus_5' => array(
							'value' => '5',
							'label' => 'label.trans.bonus',
						),
						'txnStatus_6' => array(
							'value' => '6',
							'label' => 'label.trans.referrals',
						),
						'txnStatus_7' => array(
							'value' => '7',
							'label' => 'label.trans.transfers',
						),
						'txnStatus_8' => array(
							'value' => '8',
							'label' => 'label.trans.refunds',
						),
					);

				}
				break;

			case 'TICKET_STATUS':
				$option_label = lang( 'label.ticket.status.all' );

				$search_options = array(
					'ALL' => lang( 'label.ticket.status.all' ),
					'N'   => lang( 'label.ticket.status.new' ),
					'P'   => lang( 'label.ticket.status.pending' ),
					'C'   => lang( 'label.ticket.status.closed' ),
				);

				break;
		}

		$data = array(
			'label'   => $option_label,
			'options' => $search_options,
		);

		return $data;
	}

	/**
	 *  withdrawal_details
	 */
	public static function email_reconcile( $startDay, $startMonth, $startYear, $endDay, $endMonth, $endYear ) {

		//print_r($this->CI->session->all_userdata()); exit;

		$params = array(
			'userId'     => $this->CI->session->userdata( 'user_id' ),

			'startDay'   => $startDay,
			'startMonth' => $startMonth - 1,
			'startYear'  => $startYear,

			'endDay'     => $endDay,
			'endMonth'   => $endMonth - 1,
			'endYear'    => $endYear,
		);

		$xml_response = $this->pgs_api_model->api_request( RECONCILE_EMAIL, $params );

		if ( isset( $xml_response->error ) ) {
			set_feedback( 'error', (string) $xml_response->error['token'] );
			log_message( 'debug', 'Reconcile Email Report Failed : ' . $xml_response->error['token'] );
			return false;

		} else {
			return true;
		}
	}
}
