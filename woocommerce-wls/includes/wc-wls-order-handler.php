<?php
/**
 * WC_wls_Order_Handler
 *
 * @package WooCommerce wls Payment Method
 * @category Class Handler
 * @author ReCrypto
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WC_WLS_Order_Handler {

	public static function init() {
		$instance = __CLASS__;

		add_action('wc_order_wls_status', array($instance, 'default_order_wls_status'));

		add_action('woocommerce_view_order', array($instance, 'payment_details'), 5);
		add_action('woocommerce_thankyou', array($instance, 'payment_details'), 5);

	}

	public static function default_order_wls_status($status) {
		return $status ? $status : 'pending';
	}

	public static function payment_details($order_id) {
		$order = wc_get_order($order_id);

		if ($order->get_payment_method() != 'wc_wls') 
			return; ?>

		<section class="woocommerce-wls-order-payment-details">

			<h2 class="woocommerce-wls-order-payment-details__title"><?php _e( 'WLS Payment details', 'wc-wls' ); ?></h2>

                        <?php
                            if(wc_order_get_wls_status($order_id)==='pending')
                            {
                        ?>
			        <p class="woocommerce-wls-payment-memo-prompt">
                                <strong>Now you must make a transfer through your Whaleshares.io wallet.</strong><br/>
                                Please don't forget to include the <strong>"MEMO"</strong> for this transaction in Whaleshares.io wallet.
                                Also double check <strong>"TO"</strong> and <strong>"AMOUNT"</strong> fields when making a transfer.
                                <br/><br/><a href='https://wallet.whaleshares.io' target='_blank' class='button'>PAY through WHALSHARES.IO Wallet</a>
                        <?php                     
                            }
                        ?>
</p>
			
			<table class="woocommerce-table woocommerce-table--wls-order-payment-details shop_table wls_order_payment_details">
				<tbody>
					<tr>
						<th><?php _e('To', 'wc-wls'); ?></th>
						<td><?php echo wc_order_get_wls_payee($order_id); ?></td>
					</tr>
					<tr>
						<th><?php _e('Memo', 'wc-wls'); ?></th>
						<td><?php echo wc_order_get_wls_memo($order_id); ?></td>
					</tr>
					<tr>
						<th><?php _e('Amount', 'wc-wls'); ?></th>
						<td><?php echo wc_order_get_wls_amount($order_id); ?></td>
					</tr>
					<tr>
						<th><?php _e('Currency', 'wc-wls'); ?></th>
						<td><?php echo wc_order_get_wls_amount_currency($order_id); ?></td>
					</tr>
					<tr>
						<th><?php _e('Status', 'wc-wls'); ?></th>
						<td><?php echo wc_order_get_wls_status($order_id); ?></td>
					</tr>
				</tbody>
			</table>

			<?php do_action( 'wc_wls_order_payment_details_after_table', $order ); ?>

		</section>

		<?php if ($transfer = get_post_meta($order->get_id(), '_wc_wls_transaction_transfer', true)) : ?>
		<section class="woocommerce-wls-order-transaction-details">

			<h2 class="woocommerce-wls-order-transaction-details__title"><?php _e( 'wls Transfer details', 'wc-wls' ); ?></h2>

			<table class="woocommerce-table woocommerce-table--wls-order-transaction-details shop_table wls_order_payment_details">
				<tbody>
					<tr>
						<th><?php _e('WLS Transaction', 'wc-wls'); ?></th>
						<td><?php echo $transfer['transaction']; ?></td>
					</tr>
					<tr>
						<th><?php _e('Time', 'wc-wls'); ?></th>
						<td><?php echo $transfer['time']; ?></td>
					</tr>
					<tr>
						<th><?php _e('Memo', 'wc-wls'); ?></th>
						<td><?php echo $transfer['memo']; ?></td>
					</tr>					
				</tbody>
			</table>

			<?php do_action( 'wc_wls_order_transaction_details_after_table', $order ); ?>

		</section>
		<?php endif; ?>

		<?php
	}
}

WC_WLS_Order_Handler::init();
