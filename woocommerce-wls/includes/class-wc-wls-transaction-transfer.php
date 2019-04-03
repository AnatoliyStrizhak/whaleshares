<?php
/**
 * WC_WLS_Transaction_Transfer
 *
 * @package WooCommerce WLS Payment Method
 * @category Class
 * @author ReCrypto
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WC_WLS_Transaction_Transfer {

        /**
         * Retrieve "WLS Transaction Transfer" via whaleshares.io API
         *
         * @since 1.0.0
         * @param WC_Order $order
         * @return $transfer
         */
	public static function get($order) {
		$transfer = null;

		if (is_int($order)) {
			$order = wc_get_order($order);
		}
		elseif (isset($order->post_type) && $order->post_type == 'shop_order') {
			$order = wc_get_order($order);
		}

		if (empty($order) || is_wp_error($order) || $order->get_payment_method() != 'wc_wls') {
			return $transfer;
		}

		$data = array(
			'to' => wc_order_get_wls_payee($order->get_id()),
			'memo' => wc_order_get_wls_memo($order->get_id()),
			'amount' => wc_order_get_wls_amount($order->get_id()),
			'amount_currency' => wc_order_get_wls_amount_currency($order->get_id()),
		);

		if (empty($data['to']) || empty($data['memo']) || empty($data['amount'] || empty($data['amount_currency']))) {
			// Initial transaction data not found in this order. Mark the order as searched so that it is not queried again.
			update_post_meta($order->get_id(), '_wc_wls_last_searched_for_transaction', date('m/d/Y h:i:s a', time()));
			
			return $transfer;
		}
		
		$file_contents = file_get_contents("https://api.whaleshares.io/rest2jsonrpc/database_api/get_account_history?params=[%22".$data['to']."%22,-1,300]");
		
		// If failure in retrieving url
		if ($file_contents === false)
			return $transfer;
		
		$tx = json_decode($file_contents, true);
		
		// If error decoding JSON
		if (JSON_ERROR_NONE !== json_last_error()) {
			return $transfer;
		}
				
		foreach ($tx['result'] as $r) {
		    // Format the amount as a string to ensure 3 decimal places, no thousand seperator in order to find a match.
		    $amount = number_format( $data['amount'] , 3, "." , "" );

		    if (isset($r[1]['op'][1]['memo']) && $data['memo'] === $r[1]['op'][1]['memo'] && $r[1]['op'][0]==='transfer' && $amount === preg_replace("/ WLS/","",$r[1]['op'][1]['amount']) && $r[1]['op'][1]['to'] === $data['to'])
                    {
                        $transfer['memo']=$r[1]['op'][1]['memo'];
                        $transfer['time']=$r[1]['timestamp'];

                        $transfer['transaction']="Recieved " . $amount . " " . $data['amount_currency'] . " from " . $r[1]['op'][1]['from'];

                        $transfer['time_desc']=$r[1]['timestamp'];
                        break;
		    }
		}
		
		// Successfully (no errors in retrieving JSON) searched transaction history for the record.
		update_post_meta($order->get_id(), '_wc_wls_last_searched_for_transaction', date('m/d/Y h:i:s a', time()));
		
		return $transfer;
	}
}
