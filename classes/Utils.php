<?php
namespace Parlay\Api;
use DateTime;
use DateTimeZone;

/**
 * Class Utils
 *
 * Contains various utility functions.
 */
class Utils {

	/**
	 * Check if a key is valid
	 *
	 * @since 1.0.0
	 * @param $key
	 * @return bool
	 */
	public static function isValidKey( $key ) {
		return preg_match( '/[^a-zA-Z\d\s@\.\-_]/', $key ) === 0;
	}

	/**
	 * Update an option
	 *
	 * @since 1.0.0
	 * @param $option
	 * @param $value
	 * @param bool $autoload
	 * @return bool
	 */
	public static function update_option( $option, $value, $autoload = false ) {
		return update_option( $option, $value, $autoload );
	}

	/**
	 * Convert an input date from a date picker to ISO 8601 format with microseconds.
	 *
	 * @param string $input_date The input date in '2024/05/25 20:00' format from the date picker.
	 * @return string The formatted date in ISO 8601 with microseconds.
	 */
	public static function date_to_iso8601( $input_date ) {
		try {
			$date = new DateTime( $input_date, new DateTimeZone( 'UTC' ) );

			$date_parts = explode( 'T', $input_date, 2 );
			$time_part  = substr( $date_parts[1] ?? '', 0, strpos( $date_parts[1] ?? '', '+' ) ?? 0 );
			$date       = new DateTime( $date_parts[0] . ' ' . $time_part, new DateTimeZone( 'UTC' ) );
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			$input_date = $date->format( 'Y/m/d H:i' );

			return $date->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d\TH:i:s\Z' );
		} catch ( \Exception $e ) {
			// Handle exception if the input date is not valid
			return 'Invalid date format';
		}
	}

	public static function format_currency( $number, $currency = null, $removeCentsIfZero = false ) {
		$userdata = PGS()->session->get_userdata();
		
		if ( $currency != null ) {
			$new_currency = strtolower( $currency );
		} elseif ( isset($userdata['currency']) ) {
			//Store the currency set in the session if it exists
			$new_currency = strtolower( $userdata['currency'] );
		} else {
			//Otherwise, use the default
			$new_currency = strtolower( \Parlay\Api\DataManager::get_api_settings('currency') );
		}

		$currency_format = PGS()->config[ $new_currency ];

		if ( $removeCentsIfZero ) {
			$currency_format['decimals'] = 0;
		}

		//Sanity checking...
		if ( empty( $currency_format ) ) {
			do_action('qm/debug', 'The currency ' . $new_currency . ' does not exist in the currency config file.');
			
			$number = $config['gbp']['symbol'] . number_format( floatval( $number ), 2, '.', ',' );
		} else {
			//Format the given number according to the currency configuration settings in pgs_config
			if ( $currency_format['symbol_pos'] == 0 ) {
				$number = $currency_format['symbol'] . number_format( floatval( $number ), $currency_format['decimals'], $currency_format['dec_point'], $currency_format['thousands_sep'] );
			} else {
				$number = number_format( floatval( $number ), $currency_format['decimals'], $currency_format['dec_point'], $currency_format['thousands_sep'] ) . ' ' . $currency_format['symbol'];
			}
		}

		return $number;
	}
}
