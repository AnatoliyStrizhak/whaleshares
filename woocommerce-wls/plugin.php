<?php
/**
 * Plugin Name: WooCommerce WLS Payment Method
 * Plugin URI: https://github.com/sagescrub/woocommerce-wls-payment-method
 * Description: Accept wls payments directly to your shop (Currencies: wls).
 * Version: 1.0.19
 * Author: <a href="https://wlsit.com/@sagescrub">sagescrub</a>, <a href="https://wlsit.com/@recrypto">ReCrypto</a>
 * Requires at least: 4.1
 * Tested up to: 5.1
 *
 * WC requires at least: 3.1
 * WC tested up to: 3.5.6
 *
 * Text Domain: wc-wls-payment-method
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define('WC_WLS_VERSION', '1.0.19');
define('WC_WLS_DIR_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('WC_WLS_DIR_URL', trailingslashit(plugin_dir_url(__FILE__)));

register_activation_hook(__FILE__, 'wc_wls_activate');
register_deactivation_hook(__FILE__, 'wc_wls_deactivate');

/** 
 * Plugin activation
 *
 * @since 1.0.0
 */
function wc_wls_activate() {
	do_action('wc_wls_activated');

	$settings = get_option('woocommerce_wc_wls_settings', array());

	if ( ! isset($settings['accepted_currencies'])) {
		$settings['accepted_currencies'] = array(
			'WLS',
		);
	}

	update_option('woocommerce_wc_wls_settings', $settings);

	// Make sure to have fresh currency rates
	update_option('wc_wls_rates', array());
}

/**
 * Plugin deactivation
 *
 * @since 1.0.0
 */
function wc_wls_deactivate() {
	do_action('wc_wls_deactivated');

	// Make sure to have fresh currency rates
	update_option('wc_wls_rates', array());
}

/**
 * Plugin init
 * 
 * @since 1.0.0
 */
function wc_wls_init() {

	/**
	 * Fires before including the files
	 *
	 * @since 1.0.0
	 */
	do_action('wc_wls_pre_init');

	require_once(WC_WLS_DIR_PATH . 'libraries/wordpress.php');
	require_once(WC_WLS_DIR_PATH . 'libraries/woocommerce.php');

	require_once(WC_WLS_DIR_PATH . 'includes/wc-wls-functions.php');
	require_once(WC_WLS_DIR_PATH . 'includes/class-wc-wls.php');
	require_once(WC_WLS_DIR_PATH . 'includes/class-wc-wls-transaction-transfer.php');

	require_once(WC_WLS_DIR_PATH . 'includes/class-wc-gateway-wls.php');

	require_once(WC_WLS_DIR_PATH . 'includes/wc-wls-handler.php');
	require_once(WC_WLS_DIR_PATH . 'includes/wc-wls-cart-handler.php');
	require_once(WC_WLS_DIR_PATH . 'includes/wc-wls-checkout-handler.php');
	require_once(WC_WLS_DIR_PATH . 'includes/wc-wls-order-handler.php');
	require_once(WC_WLS_DIR_PATH . 'includes/wc-wls-product-handler.php');

	/**
	 * Fires after including the files
	 *
	 * @since 1.0.0
	 */
	do_action('wc_wls_init');
}
add_action('plugins_loaded', 'wc_wls_init');



/**
 * Register "WooCommerce wls" as payment gateway in WooCommerce
 *
 * @since 1.0.0
 *
 * @param array $gateways
 * @return array $gateways
 */
function wc_wls_register_gateway($gateways) {
	$gateways[] = 'WC_Gateway_WLS';

	return $gateways;
}
add_filter('woocommerce_payment_gateways', 'wc_wls_register_gateway');
