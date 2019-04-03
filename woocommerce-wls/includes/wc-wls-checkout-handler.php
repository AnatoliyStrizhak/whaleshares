<?php
/**
 * WC_wls_Checkout_Handler
 *
 * @package WooCommerce wls Payment Method
 * @category Class Handler
 * @author ReCrypto
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WC_WLS_Checkout_Handler {

	public static function init() {
		$instance = __CLASS__;

		add_action('wp_enqueue_scripts', array($instance, 'enqueue_scripts'));
	}

	public static function enqueue_scripts() {
		// Plugin
		//wp_enqueue_script('wc-wls', WC_WLS_DIR_URL . '/assets/js/plugin.js', array('jquery'), WC_WLS_VERSION);

		// Localize plugin script data
		wp_localize_script('wc-wls', 'wc_wls', array(
			'cart' => array(
				'base_currency' => wc_wls_get_base_fiat_currency(),
				'amounts' => WC_WLS::get_amounts(),
			),
		));


        function add_checkout_script() {
        ?>
            <script type="text/javascript">
            jQuery(document).on( "updated_checkout", function(){

                jQuery("#place_order").click(function() {
                    window.open('https://wallet.whaleshares.io', '_blank');
                });
            });

        </script>

        <?php
        }

        add_action( 'woocommerce_after_checkout_form', 'add_checkout_script' );
    }
}

WC_WLS_Checkout_Handler::init();

