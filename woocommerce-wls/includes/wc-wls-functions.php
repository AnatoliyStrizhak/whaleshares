<?php
/**
 * WooCommerce wls Helpers
 *
 * @package WooCommerce wls Payment Method
 * @category Helper
 * @author ReCrypto
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve wls currencies
 *
 * @since 1.0.0
 * @return array
 */
function wc_wls_get_currencies() {
	return apply_filters('wc_wls_currencies', array(
		'WLS' => 'WLS'
	));
}

/**
 * Retrieve payment method settings
 *
 * @since 1.0.0
 * @return array
 */
function wc_wls_get_settings() {
	return get_option('woocommerce_wc_wls_settings', array());
}

/**
 * Retrieve single payment method settings
 *
 * @since 1.0.0
 * @return mixed
 */
function wc_wls_get_setting($key) {
	$settings = wc_wls_get_settings();

	return isset($settings[$key]) ? $settings[$key] : null;
}

/**
 * Retrieve wls accepted currencies
 *
 * @since 1.0.0
 * @return array
 */
function wc_wls_get_accepted_currencies() {
	$accepted_currencies = wc_wls_get_setting('accepted_currencies');

	return apply_filters('wc_wls_accepted_currencies', $accepted_currencies ? $accepted_currencies : array());
}

/**
 * Check if the wls payment method settings has accepted currencies
 *
 * @since 1.0.0
 * @return array
 */
function wc_wls_has_accepted_currencies() {
	$currencies = wc_wls_get_accepted_currencies();
	return ( ! empty($currencies));
}

/**
 * Check currency is accepted on wls payment method
 *
 * @since 1.0.0
 * @param string $currency_symbol
 * @return boolean
 */
function wc_wls_is_accepted_currency($currency_symbol) {
	$currencies = wc_wls_get_accepted_currencies();
	return in_array($currency_symbol, $currencies);
}


# Fiat

/**
 * Retrieve shop's base fiat currency symbol.
 *
 * @since 1.0.1
 * @return string $store_currency_symbol
 */
function wc_wls_get_base_fiat_currency() {
	$store_currency_symbol = wc_wls_get_currency_symbol();
	
	// Allow accepted wls currencies (e.g. wls or SBD selected in plugin settings) or accepted fiat currencies.
	// If the WooCommerce store currency is neither, then default to USD.
	if ( ! wc_wls_is_accepted_currency( $store_currency_symbol ) && ! in_array($store_currency_symbol, wc_wls_get_accepted_fiat_currencies())) {
		$store_currency_symbol = apply_filters('wc_wls_base_default_fiat_currency', 'USD');
	}

	return apply_filters('wc_wls_base_fiat_currency', $store_currency_symbol);
}

/**
 * Retrieve list of accept fiat currencies
 *
 * @since 1.0.1
 * @return array
 */
function wc_wls_get_accepted_fiat_currencies() {
	return apply_filters('wc_wls_accepted_fiat_currencies', array(
		'AUD', 'BGN', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK', 'GBP', 'HKD', 'HRK', 'HUF', 'IDR', 'ILS', 'INR', 'JPY', 'KRW', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RON', 'RUB', 'SEK', 'SGD', 'THB', 'TRY', 'ZAR', 'EUR'
	));
}

/**
 * Check fiat currency is accepted on WooCommerce shop
 *
 * @since 1.0.1
 * @param string $currency_symbol
 * @return boolean
 */
function wc_wls_is_accepted_fiat_currency($currency_symbol) {
	$currencies = wc_wls_get_accepted_fiat_currencies();
	return in_array($currency_symbol, $currencies);
}


# Rates

/**
 * Retrieve wls rates
 *
 * @since 1.0.0
 * @return array
 */
function wc_wls_get_rates() {
	return get_option('wc_wls_rates', array());
}

/**
 * Retrieve rate
 *
 * @since 1.0.0
 * @param string $from_currency_symbol
 * @param string $to_currency_symbol
 * @return float
 */
function wc_wls_get_rate($from_currency_symbol, $to_currency_symbol) {
	$rates = wc_wls_get_rates();

	$from_currency_symbol = strtoupper($from_currency_symbol);
	$to_currency_symbol = strtoupper($to_currency_symbol);

	$pair_currency_symbol = "{$to_currency_symbol}_{$from_currency_symbol}";

	return apply_filters(
		'wc_wls_rate', 
		(isset($rates[$pair_currency_symbol]) ? $rates[$pair_currency_symbol] : null), 
		$from_currency_symbol, 
		$to_currency_symbol
	);
}

/**
 * Convert the amount from FIAT to crypto amount
 *
 * @since 1.0.0
 * @param float $amount
 * @param string $from_currency_symbol
 * @param string $to_currency_symbol
 * @return float
 */
function wc_wls_rate_convert($amount, $from_currency_symbol, $to_currency_symbol) {
	// If from and to currency symbols are the same, return the same amount.
	if ( strcmp( strtoupper( $from_currency_symbol ), strtoupper( $to_currency_symbol ) ) == 0 )
		return $amount;
	
	$rate = wc_wls_get_rate($from_currency_symbol, $to_currency_symbol);

	return apply_filters(
		'wc_wls_rate_convert', 
		($rate > 0 ? round($amount / $rate, 3, PHP_ROUND_HALF_UP) : 0), 
		$amount, 
		$from_currency_symbol, 
		$to_currency_symbol
	);
}


# Order functions

/**
 * Retrieve order's wls payee username
 *
 * @since 1.0.0
 * @param int $order_id
 * @return string
 */
function wc_order_get_wls_payee($order_id) {
	return apply_filters('wc_order_wls_payee', get_post_meta($order_id, '_wc_wls_payee', true), $order_id);
}

/**
 * Retrieve order's wls memo
 *
 * @since 1.0.0
 * @param int $order_id
 * @return string
 */
function wc_order_get_wls_memo($order_id) {
	return apply_filters('wc_order_wls_memo', get_post_meta($order_id, '_wc_wls_memo', true), $order_id);
}

/**
 * Retrieve order's wls amount
 *
 * @since 1.0.0
 * @param int $order_id
 * @return string
 */
function wc_order_get_wls_amount($order_id) {
	return apply_filters('wc_order_wls_amount', get_post_meta($order_id, '_wc_wls_amount', true), $order_id);
}

/**
 * Retrieve order's wls amount currency
 *
 * @since 1.0.0
 * @param int $order_id
 * @return string
 */
function wc_order_get_wls_amount_currency($order_id) {
	return apply_filters('wc_order_wls_amount_currency', get_post_meta($order_id, '_wc_wls_amount_currency', true), $order_id);
}

/**
 * Retrieve order's wls status
 *
 * @since 1.0.0
 * @param int $order_id
 * @return string
 */
function wc_order_get_wls_status($order_id) {
	return apply_filters('wc_order_wls_status', get_post_meta($order_id, '_wc_wls_status', true), $order_id);
}
