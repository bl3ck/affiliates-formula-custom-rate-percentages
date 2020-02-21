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
	 * Function Desc
	 * @param array $variables
	 * @param object $rate
	 * @param array $context
	 * @return array
	 */
	public static function affiliates_formula_computer_variables( $variables, $rate, $context ) {

		// 1. Get the monthly sales referred by each affiliate
		$affiliate_id = $context['affiliate_id'];
		$totals       = self::get_affiliate_referrals( $affiliate_id );
		// 2. Depending on the amount referred for the month
		// @todo you also need to set the limits aka $first_limit, $second_limit etc.
		$first_limit   = 28;
		$second_limit  = 50;
		$third_limit   = 120;
		$forth_limit   = 250;
		$fifth_limit   = 500;
		$sixth_limit   = 1000;
		$seventh_limit = 2500;
		// @todo before the ending else statement you need to add the rest of cases applying for each amount.		
		if ( isset( $totals['amount'] ) ) {
			if ( $totals['amount'] >= 0 && $totals['amount'] <= $first_limit ) {
				$variables['c'] = 0.035;
			} elseif ( $totals['amount'] >= $first_limit && $totals['amount'] <= $second_limit ) {
				$variables['c'] = 0.07;
			} elseif ( $totals['amount'] >= $second_limit && $totals['amount'] <= $third_limit ) {
				$variables['c'] = 0.105;
			} elseif ( $totals['amount'] >= $third_limit && $totals['amount'] <= $forth_limit ) {
				$variables['c'] = 0.14;
			} elseif ( $totals['amount'] >= $forth_limit && $totals['amount'] <= $fifth_limit ) {
				$variables['c'] = 0.175;
			} elseif ( $totals['amount'] >= $fifth_limit && $totals['amount'] <= $sixth_limit ) {
				$variables['c'] = 0.21;
			} else {
				$variables['c'] = 0.25;
			}
		}
		// 2.1 Apply on the the following percentages 3.50% 7% 10.50% 14% 17.50% 21% 25%
		// 3. Credit the affiliate with the right new computed amount as his monthly referral

		//return $rates;
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
			AND affiliate_id = $affiliate_id
			AND status = 'accepted'
			GROUP BY currency_id
			"
		) );
		if ( count( $results ) > 0 ) {
			foreach ( $results as $result ) {
				$totals[] = array(
					'amount'   => esc_html( affiliates_format_referral_amount( $result->total, 'display' ) ),
					'currency' => esc_html( $result->currency_id )
				);
			}
		}
		return $totals;
	}

} Affiliates_Custom_Formula_Rates::init();
