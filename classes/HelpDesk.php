<?php

namespace Parlay\Api;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class HelpDesk {

	/**
	 *  open_ticket
	 *
	 *  @param  $params arra[code, players_comment]
	 *  @return bool
	 */
	public static function open_ticket( $params ) {
		$params = array(
			'code'    => $params['code'],
			'comment' => $params['comment'],
		);

		$response = DataManager::api_request( 'player/tickets', 'POST', $params );

		do_action( 'qm/debug', 'new ticket response: ' . print_r( $response, true ) );

		if ( $response['code'] > 204 ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 4500,
				'message' => __( 'Unable to open new ticket.', 'parlay-api' ),
			]);

			return false;
		}

		PGS()->set_alert( 'account', [
			'type'    => 'success',
			'timer'   => 4500,
			'message' => __( 'Ticket submitted successfully.', 'parlay-api' ),
		]);

		return $response;
	}

	/**
	 *  tickets_list
	 *
	 *
	 *  @param  $params
	 *  @return bool
	 */
	public static function tickets_list( $params ) {
		if ( ! isset( $params['from_date'] ) || ! isset( $params['to_date'] ) ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 4000,
				'message' => __( 'Please enter from and to date.', 'parlay-api' ),
			]);
			return false;
		}

		$params = array(
			'from'   => Utils::date_to_iso8601( $params['from_date'] ),
			'to'     => Utils::date_to_iso8601( $params['to_date'] ),
			'status' => $params['status'],
		);

		$response = DataManager::api_request( 'player/tickets', 'GET', $params );

		do_action( 'qm/debug', 'tickets response: ' . print_r( $response, true ) );

		if ( $response['code'] > 204 ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Unable to process tickets list.', 'parlay-api' ),
			]);

			return false;
		}

		return $response['body'];
	}

	/**
	 *  view_ticket_details
	 *
	 *  @param  $user_id
	 *  @param  $ticket_number
	 *  @return bool
	 */
	public static function view_ticket_details( $ticket_id ) {
		$params = array(
			'ticketId' => $ticket_id,
		);

		$response = DataManager::api_request( 'player/tickets/' . $ticket_id, 'GET', $params );

		do_action( 'qm/debug', 'tickets response: ' . print_r( $response, true ) );

		if ( $response['code'] > 204 ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Unable to retrieve ticket details.', 'parlay-api' ),
			]);

			return false;
		}

		return $response['body'];
	}

	/**
	 *  create_ticket_comments
	 *
	 *  @param  $ticket_number
	 *  @param  $players_comment
	 *  @return bool
	 */
	public static function create_ticket_comment( $params ) {
		$query = array(
			'comment' => $params['comment'],
		);

		$response = DataManager::api_request( 'player/tickets/' . $params['ticket_id'] . '/comment', 'POST', $query );

		do_action( 'qm/debug', 'tickets response: ' . print_r( $response, true ) );

		if ( $response['code'] > 204 ) {
			PGS()->set_alert( 'account', [
				'type'    => 'error',
				'timer'   => 3500,
				'message' => __( 'Unable to add a comment.', 'parlay-api' ),
			]);

			return false;
		}

		PGS()->set_alert( 'account', [
			'type'    => 'success',
			'timer'   => 4500,
			'message' => __( 'Comment successfully added.', 'parlay-api' ),
		]);

		return $response['body'];
	}

	/**
	 *  get_ticket_issue_list
	 */
	public static function get_ticket_issue_list() {
		$issue_list = array(
			'DB' => __( 'Billing', 'parlay-api' ),
			'LC' => __( 'Lost Credit', 'parlay-api' ),
			'LO' => __( 'Lost Connection', 'parlay-api' ),
			'LU' => __( 'Locked Up', 'parlay-api' ),
			'OT' => __( 'Other', 'parlay-api' ),
			'PW' => __( 'Forgot Password', 'parlay-api' ),
			'RE' => __( 'Comment', 'parlay-api' ),
			'WN' => __( 'Win Not Credited', 'parlay-api' ),
			'CC' => __( 'Credit Card', 'parlay-api' ),
		);

		return $issue_list;
	}

	public static function get_status_list( $code = '' ) {
		$status = [
			''  => __( 'All', 'parlay-api' ),
			'N' => __( 'New', 'parlay-api' ),
			'P' => __( 'Pending', 'parlay-api' ),
			'C' => __( 'Closed', 'parlay-api' ),
		];

		if ( ! empty( $code ) ) {
			if ( isset( $status[ $code ] ) ) {
				return $status[ $code ];
			}

			return $code;
		}

		return $status;
	}
}
