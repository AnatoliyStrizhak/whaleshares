<?php
/**
 * WC_Gateway_wls
 *
 * @package WooCommerce wls Payment Method
 * @category Class
 * @author ReCrypto
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WC_Gateway_WLS extends WC_Payment_Gateway {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->id                 = 'wc_wls';
		$this->has_fields         = true;
		$this->order_button_text  = __('Proceed to wls', 'wc-wls');
		$this->method_title       = __('WLS', 'wc-wls' );
		$this->method_description = sprintf(__('Process payments via wls.', 'wc-wls'), '<a href="' . admin_url('admin.php?page=wc-status') . '">', '</a>');
		$this->supports           = array(
			'products',
			'refunds'
		);

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title          = $this->get_option('title');
		$this->description    = $this->get_option('description');
		$this->payee          = $this->get_option('payee');

		// WordPress hooks
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
	}


	# Backend

	/**
	 * Backend form settings
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {

		if ($accepted_currencies = wc_wls_get_currencies()) {
			foreach ($accepted_currencies as $accepted_currency_key => $accepted_currency) {
				$accepted_currencies[$accepted_currency_key] = sprintf('%1$s (%2$s)', $accepted_currency, $accepted_currency_key);
			}
		}

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __('Enable/Disable', 'wc-wls'),
				'type'    => 'checkbox',
				'label'   => __('Enable WooCommerce WLS', 'wc-wls'),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __('Title', 'wc-wls'),
				'type'        => 'text',
				'description' => __('This controls the title which the user sees during checkout.', 'wc-wls'),
				'default'     => __('Whaleshares', 'wc-wls' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __('Description', 'wc-wls'),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => __('This controls the description which the user sees during checkout.', 'wc-wls'),
				'default'     => __('Pay via Whaleshares', 'wc-wls')
			),
			'payee' => array(
				'title'       => __('Payee', 'wc-wls'),
				'type'        => 'text',
				'description' => __('This is your wls username where your customers will pay you.', 'wc-wls'),
				'default'     => '',
				'desc_tip'    => true,
			),
			'accepted_currencies' => array(
				'title'       => __('Accepted Currencies', 'wc-wls'),
				'type'        => 'multiselect',
				'description' => __('Select the wls currencies you will accept.', 'wc-wls'),
				'default'     => '',
				'desc_tip'    => true,
				'options'     => $accepted_currencies,
				'select_buttons' => true,
			),
			'show_insightful' => array(
				'title'   => __('Enable insightful prices on products', 'wc-wls'),
				'type'    => 'checkbox',
				'label'   => __('Shows an insightful prices on products that displays the accepted currencies such as SBD and/or wls rates converted from the product price.', 'wc-wls'),
				'default' => 'no'
			),
			'show_discounted_price' => array(
				'title'   => __('Show Discounted Price', 'wc-wls'),
				'type'    => 'checkbox',
				'label'   => __('If enabled, products that are on sale will display the original price in wls/SBD with strikethrough. Only operational when "Enable insightful prices on products" is enabled.', 'wc-wls'),
				'default' => 'no'
			),			
		);
	}


	# Frontend

	/**
	 * Frontend payment method fields
	 *
	 * @since 1.0.0
	 */
	public function payment_fields() {

		if ( ! $this->payee) {
			if (is_super_admin()) {
				_e('Please set your wls username at the WooCommerce Settings to get paid via wls.', 'wc-wls');
			}
			else {
				_e('Sorry, wls payments is not available right now.', 'wc-wls');
			}
		}
		elseif ( ! wc_wls_get_accepted_currencies()) {
			if (is_super_admin()) {
				_e('Please set one or more accepted currencies at the WooCommerce Settings to get paid via wls.', 'wc-wls');
			}
			else {
				_e('Sorry, wls payments is not available right now.', 'wc-wls');
			}
		} else {
			$description = $this->get_description();

			if ($description) {
				echo wpautop(wptexturize(trim($description)));
			}

			if ( $this->supports( 'tokenization' ) && is_checkout() ) {
				$this->tokenization_script();
				$this->saved_payment_methods();
				$this->form();
				$this->save_payment_method_checkbox();
			} else {
				$this->form();
			}
		}
	}
	
	/**
	 * Frontend payment method form
	 *
	 * @since 1.0.0
	 */
	public function form() {

		$amount_currencies_html = '';

		if ($currencies = wc_wls_get_currencies()) {
			foreach ($currencies as $currency_symbol => $currency) {
				if (wc_wls_is_accepted_currency($currency_symbol)) {
					$amount_currencies_html .= sprintf('<option value="%s">%s</option>', $currency_symbol, $currency);
				}
			}
		}


		$default_fields = array(
			'amount' => '<p class="form-row form-row-wide">
				<label for="' . $this->field_id('amount') . '">' . esc_html__( 'Amount', 'wc-wls' ) . '</label>
				<span id="' . $this->field_id('amount') . '">' . WC_WLS::get_amount() . ' ' .  WC_WLS::get_amount_currency() . '</span>

			</p>',

		);

		$fields = wp_parse_args($default_fields, apply_filters('wc_wls_form_fields', $default_fields, $this->id)); ?>

		<fieldset id="<?php echo esc_attr($this->id); ?>-wls-form" class='wc-wls-form wc-payment-form'>
			<?php do_action('wc_wls_form_start', $this->id); ?>

			<?php foreach ($fields as $field) : 
					 echo $field;
			 endforeach;

			do_action('wc_wls_form_end', $this->id); ?>

			<div class="clear"></div>
		</fieldset><?php
	}


	# Helpers

	/**
	 * Output field name HTML
	 *
	 * Gateways which support tokenization do not require names - we don't want the data to post to the server.
	 *
	 * @since 1.0.0
	 * @param string $name
	 * @return string
	 */
	public function field_name($name) {
		return $this->supports('tokenization') ? '' : ' name="' . $this->field_id($name) . '" ';
	}

	/**
	 * Construct field identifier
	 *
	 * @since 1.0.0
	 * @param string $key
	 * @return string
	 */
	public function field_id($key) {
		return esc_attr(sprintf('%s-%s', $this->id, $key));
	}


	/**
	 * Get gateway icon.
	 * @return string
	 */
	public function get_icon() {
		$icon_html = '';
		$icon      = apply_filters('wc_wls_icon', WC_WLS_DIR_URL . '/assets/img/wls-64.png');

		$icon_html .= '<img src="' . esc_attr($icon) . '" alt="' . esc_attr__('wls acceptance mark', 'wc-wls') . '" />';

		return apply_filters('woocommerce_gateway_icon', $icon_html, $this->id);
	}


	# Handlers

	/**
	 * Process payment
	 *
	 * Validation takes place by querying transactions to whaleshares API
	 *
	 * @since 1.0.0
	 * @param int $order_id
	 * @return array $response
	 */
	public function process_payment($order_id) {

		$response = null;

		$order = new WC_Order($order_id);

		// Reduce stock levels
		wc_reduce_stock_levels($order_id);

                // Remove cart
                WC()->cart->empty_cart();
		
		$payee = get_post_meta($order_id, '_wc_wls_payee', true);
		$amount = get_post_meta($order_id, '_wc_wls_amount', true);
		$amount_currency = get_post_meta($order_id, '_wc_wls_amount_currency', true);
		$memo = get_post_meta($order_id, '_wc_wls_memo', true);		
		$from_amount = get_post_meta($order_id, '_wc_wls_from_amount', true);	
		$from_currency = get_post_meta($order_id, '_wc_wls_from_currency', true);	
		$exchange_rate = get_post_meta($order_id, '_wc_wls_exchange_rate', true);	
		
		if (empty($memo)) {
			$payee = WC_WLS::get_payee();
			$amount = WC_WLS::get_amount();
			$amount_currency = WC_WLS::get_amount_currency();
			$memo = WC_WLS::get_memo();
			$from_amount = WC_WLS::get_from_amount();
			$from_currency = WC_WLS::get_from_currency();
			$exchange_rate = WC_WLS::get_exchange_rate();

			// Allow overriding payee on a per order basis
			$payee = apply_filters('woocommerce_gateway_wls_wlsconnect_payee', $payee, $order );			
			$memo = apply_filters('woocommerce_gateway_wls_wlsconnect_memo', $memo, $order );			
			
			update_post_meta($order_id, '_wc_wls_payee', $payee);
			update_post_meta($order_id, '_wc_wls_amount', $amount);
			update_post_meta($order_id, '_wc_wls_amount_currency', $amount_currency);
			update_post_meta($order_id, '_wc_wls_memo', $memo);
			update_post_meta($order_id, '_wc_wls_from_amount', $from_amount);
			update_post_meta($order_id, '_wc_wls_from_currency', $from_currency);
			update_post_meta($order_id, '_wc_wls_exchange_rateo', $exchange_rate);

			update_post_meta($order->get_id(), '_wc_wls_status', 'pending');
			
			// Add order note indicating details of payment request
			$order->add_order_note(
				sprintf(
					__('wls payment <strong>Initiated</strong>:<br />Payee: %s<br />Amount Due: %s %s<br />Converted From: %s %s<br />Exchange Rate: 1 %s = %s %s<br />Memo: %s', 'wc-wls'), 
					$payee, 
					$amount,
					$amount_currency,
					$from_amount,
					$from_currency,
					$from_currency,
					$exchange_rate,
					$amount_currency,
					$memo
				)				
			);			
                WC_WLS::reset();

		}

            $response = array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );

            return $response;
        }


	/**
	 * Validate frontend fields
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public function validate_fields() {

		$amount_currency = isset($_POST[$this->field_id('amount_currency')]) ? $_POST[$this->field_id('amount_currency')] : 'wls';
		$from_currency_symbol = wc_wls_get_base_fiat_currency();
		
		WC_WLS::set_from_currency($from_currency_symbol);
		
		if (wc_wls_is_accepted_currency($amount_currency)) {
			WC_WLS::set_amount_currency($amount_currency);

			if ($amounts = WC_WLS::get_amounts()) {
				if (isset($amounts[WC_WLS::get_amount_currency() . '_' . $from_currency_symbol])) {
					WC_WLS::set_amount($amounts[WC_WLS::get_amount_currency() . '_' . $from_currency_symbol]);
					
					// Get exchange rate based off 1 unit of the base fiat currency
					WC_WLS::set_exchange_rate(wc_wls_rate_convert(1, $from_currency_symbol, WC_WLS::get_amount_currency()));
				}
			}
		}

		if (empty(WC_WLS::get_memo())) {
			WC_WLS::set_memo();
		}

		WC_WLS::set_payee($this->payee);
		
		return true;
	}

	/**
	 * Cannot be refunded
	 *
	 * @since 1.0.0
	 * @param WC_Order $order
	 * @return boolean
	 */
	public function can_refund_order($order) {
		return $order->get_payment_method() == 'wc_wls' && false;
	}
}
