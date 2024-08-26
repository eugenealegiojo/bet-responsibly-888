<?php

namespace Parlay\Api;

use Parlay\Api\DataManager;

/**
 * Library that handles all emails sent via site.
 * Credits: Roberto Ambrósio <roberto@brivalmedia.com>
 */
class Mailer {

	public function __construct() {
	}

	/**
	 * Send the activation e-mail.
	 * @param array $player The Player profile coming from API.
	 * @param string $activationKey The activation key from registration.
	 * @return bool Whether the email was sent or not.
	 */
	public function sendActivationEmail( array $player, string $activationKey ): bool {
		$email_data = [
			'template_id' => 'account_activation',
			'to'          => $player['email'],
			'tags'        => [
				'{alias}'          => $player['alias'],
				'{activation_url}' => site_url( '/activate-account/' . $activationKey ),
				'{activation_key}' => $activationKey,
			],
		];

		return $this->send( $email_data );
	}

	/**
	 * Send the welcome e-mail, after creating and activating an account.
	 * @return bool Whether the email was sent or not.
	 */
	public function sendWelcomeEmail( $player ): bool {
		$email_data = [
			'template_id' => 'welcome',
			'to'          => $player['email'],
			'tags'        => [
				'{alias}' => $player['alias'],
			],
		];

		return $this->send( $email_data );
	}

	/**
	 * Send the forgot password e-mail.
	 *
	 * @param array $player The Player profile coming from API.
	 */
	public function sendForgotPasswordEmail( array $player ): bool {
		$email_data = [
			'template_id' => 'forgot_password',
			'to'          => $player['email'],
			'tags'        => [
				'{alias}'              => $player['alias'],
				'{reset_password_url}' => site_url( '/reset-password/' . $player['reset_pwd_token'] ),
				'{reset_token}'        => $player['reset_pwd_token'],
			],
		];

		return $this->send( $email_data );
	}

	/**
	 * Send email template
	 *
	 * @since 1.2.0
	 */
	public function send( $data ): bool {
		$template  = DataManager::get_template_data( $data['template_id'] );
		$site_name = get_bloginfo( 'name' );
		$to        = $data['to'];
		$from      = $data['from'] ?? $template['from'];
		$subject   = $data['subject'] ?? $template['subject'];
		$tags      = $data['tags'] ?? [];
		$html      = $data['html'] ?? $template['html'];
		$css       = $data['css'] ?? $template['css'];
		$headers   = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $site_name . ' <' . $from . '>',
		);

		// Parse tags
		$tags    = wp_parse_args( $tags, $template['tags'] );
		$html    = $this->parseEmail( $html, $tags );
		$subject = $this->parseEmail( $subject, $tags );

		// Make sure we have <body> tag
		if ( stripos( $html, '<body' ) === false ) {
			$html = '<body>' . $html . '</body>';
		}

		$message = '
		<!DOCTYPE html>
		<html>
		<head>
			<style>' . $css . '</style>
		</head>
		' . $html . '
		</html>
		';

		return wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Send test email
	 *
	 * @since 1.2.0
	 * @param array $data
	 * @param bool $debug
	 */
	public function sendTestEmail( $data, $debug = false ): bool {
		$html       = balanceTags( $data['html'], true );
		$email_data = [
			'template_id' => $data['template_id'],
			'to'          => ! empty( $data['to'] ) ? $data['to'] : get_bloginfo( 'admin_email' ),
			'from'        => ! empty( $data['from'] ) ? $data['from'] : get_bloginfo( 'admin_email' ),
			'subject'     => $data['subject'],
			'html'        => $html,
			'css'         => $data['css'],
		];

		// DEBUG: Write html content for sanity check
		if ( $debug ) {
			$tags          = DataManager::get_email_templates( $data['template_id'] )->tags ?? [];
			$test_message  = $this->parseEmail( $html, $tags );
			$template_base = DataManager::get_email_template_dir() . $data['template_id'];
			PGS()->filesystem->file_put_contents( $template_base . '-test.html', $test_message );
		}

		return $this->send( $email_data );
	}

	private function parseEmail( $content, $tags = [] ): string {
		foreach ( $tags as $tag => $value ) {
			$content = str_replace( $tag, $value, $content );
		}

		return $content;
	}
}
