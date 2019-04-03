<?php
/**
 * WC_wls_Cart_Handler
 *
 * @package WooCommerce wls Payment Method
 * @category Class Handler
 * @author ReCrypto
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WC_WLS_Cart_Handler {

	public static function init() {
		$instance = __CLASS__;

		add_action('woocommerce_after_calculate_totals', array($instance, 'calculate_totals'), 30);
	}

	public static function calculate_totals($cart) {
		if (empty($cart) || is_wp_error($cart)) {
			return;
		}
		
		$amounts = array();
		$from_currency_symbol = wc_wls_get_base_fiat_currency();

		if ($currencies = wc_wls_get_currencies()) {
			foreach ($currencies as $to_currency_symbol => $currency) {
				$amount = wc_wls_rate_convert($cart->get_total('edit'), $from_currency_symbol, $to_currency_symbol);
				
				if ($amount <= 0) {
					continue;
				}

				if (WC_WLS::get_amount_currency() == $to_currency_symbol) {

					
					WC_WLS::set_amount($amount);
	

			}

				$amounts["{$to_currency_symbol}_{$from_currency_symbol}"] = $amount;
			}

			foreach ($currencies as $to_currency_symbol => $currency) {
				if ( ! isset($amounts["{$to_currency_symbol}_{$from_currency_symbol}"])) {
					$amounts["{$to_currency_symbol}_{$from_currency_symbol}"] = $cart->get_total('edit');
					WC_WLS::set_amount($cart->get_total('edit'));
				}
			}
			
			WC_WLS::set_from_amount($cart->get_total('edit'));
			WC_WLS::set_amounts($amounts);
		}
	}
}

WC_WLS_Cart_Handler::init();