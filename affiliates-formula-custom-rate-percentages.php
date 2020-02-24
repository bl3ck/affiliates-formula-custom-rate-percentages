<?php
/**
 * Plugin Name: Affiliates Custom Formula Rates
 * Plugin URI: http://www.itthinx.com/shop/affiliates-pro/
 * Description: Affiliates Custom Formula Rates
 * Version: 1.0.0
 * Author: bl3ck
 * Author URI: www.itthinx.com
 * License: GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Affiliates_Custom_Formula_Rates {

	/**
	 * Init
	 */
	public static function init() {
		add_filter( 'affiliates_formula_computer_variables', array( __CLASS__, 'affiliates_formula_computer_variables' ), 10, 3 );
	}

	/**
	 * Sets new variable depending on previous months referrals
	 *
	 * @param array $variables
	 * @param object $rate
	 * @param array $context
	 * @return array
	 */
	public static function affiliates_formula_computer_variables( $variables, $rate, $context ) {

		// Get the monthly sales referred by each affiliate
		$affiliate_id = $context['affiliate_id'];
		$totals       = self::get_affiliate_referrals( $affiliate_id );
		// Get the currency
		if ( function_exists( 'get_woocommerce_currency' ) ) {
			$currency = get_woocommerce_currency();
		} else {
			$currency = get_option( 'woocommerce_currency' );
		}
		// Depending on the amount referred for the month
		$first_limit   = 28;
		$second_limit  = 50;
		$third_limit   = 120;
		$forth_limit   = 250;
		$fifth_limit   = 500;
		$sixth_limit   = 1000;
		$seventh_limit = 2500;

		// The default value for the variable
		$variables['c'] = 0.035;

		// Check the previous month's performance and adjust the variable value
		foreach ( $totals as $total ) {
			if ( isset( $totals[$currency] ) ) {
				if ( $total[$currency] >= 0 && $total[$currency] <= $first_limit ) {
					$variables['c'] = 0.035;
				} else if ( $total[$currency] >= $first_limit && $total[$currency] <= $second_limit ) {
					$variables['c'] = 0.07;
				} else if ( $total[$currency] >= $second_limit && $total[$currency] <= $third_limit ) {
					$variables['c'] = 0.105;
				} else if ( $total[$currency] >= $third_limit && $total[$currency] <= $forth_limit ) {
					$variables['c'] = 0.14;
				} else if ( $total[$currency] >= $forth_limit && $total[$currency] <= $fifth_limit ) {
					$variables['c'] = 0.175;
				} else if ( $total[$currency] >= $fifth_limit && $total[$currency] <= $sixth_limit ) {
					$variables['c'] = 0.21;
				} else {
					$variables['c'] = 0.25;
				}
			}
		}

		return $variables;
	}

	/**
	 * Get the totals for affiliate referrals per currency
	 *
	 * @param int $affiliate_id
	 * @return array 
	 */
	private static function get_affiliate_referrals( $affiliate_id ) {
		global $wpdb;
		$referrals_table = _affiliates_get_tablename( 'referrals' );
		$totals          = array();
		$results         = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT SUM(amount) as total, currency_id
			FROM $referrals_table
			WHERE YEAR(datetime) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
			AND MONTH(datetime) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
			AND affiliate_id = %d
			AND status = 'accepted'
			GROUP BY currency_id
			",
			$affiliate_id
		) );
		if ( count( $results ) > 0 ) {
			foreach ( $results as $result ) {
				$totals[] = array(
					$result->currency_id => esc_html( affiliates_format_referral_amount( $result->total, 'display' ) )
				);
			}
		}
		return $totals;
	}

} Affiliates_Custom_Formula_Rates::init();
