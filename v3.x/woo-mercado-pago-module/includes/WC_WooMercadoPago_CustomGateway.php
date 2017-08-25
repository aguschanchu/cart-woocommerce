<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer - Marcelo Tomio Hama / marcelo.hama@mercadolivre.com
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// This include Mercado Pago library SDK
require_once dirname( __FILE__ ) . '/sdk/lib/mercadopago.php';

/**
 * Summary: Extending from WooCommerce Payment Gateway class.
 * Description: This class implements Mercado Pago custom checkout.
 * @since 3.0.0
 */
class WC_WooMercadoPago_CustomGateway extends WC_Payment_Gateway {

	public function __construct() {

		// Mercao Pago instance.
		$this->site_data = WC_Woo_Mercado_Pago_Module::get_site_data( true );
		$this->mp = new MP(
			WC_Woo_Mercado_Pago_Module::get_module_version(),
			get_option( '_mp_access_token' )
		);
		
		// WooCommerce fields.
		$this->id = 'woo-mercado-pago-custom';
		$this->supports = array( 'products', 'refunds' );
		/*$this->icon = apply_filters(
			'woocommerce_mercadopago_icon',
			plugins_url( 'assets/images/credit_card.png', plugin_dir_path( __FILE__ ) )
		);*/

		$this->method_title = __( 'Mercado Pago - Custom Checkout', 'woo-mercado-pago-module' );
		$this->method_description = '<img width="200" height="52" src="' .
			plugins_url( 'assets/images/mplogo.png', plugin_dir_path( __FILE__ ) ) .
		'"><br><br><strong>' .
			__( 'We give you the possibility to adapt the payment experience you want to offer 100% in your website, mobile app or anywhere you want. You can build the design that best fits your business model, aiming to maximize conversion.', 'woo-mercado-pago-module' ) .
		'</strong>';

		// TODO: Verify sandbox availability.
		$this->mp->sandbox_mode( false );

		// How checkout is shown.
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		// How checkout payment behaves.
		$this->coupon_mode        = $this->get_option( 'coupon_mode', 'no' );
		$this->binary_mode        = $this->get_option( 'binary_mode', 'no' );
		$this->gateway_discount   = $this->get_option( 'gateway_discount', 0 );
		
		// Logging and debug.
		$_mp_debug_mode = get_option( '_mp_debug_mode', '' );
		if ( ! empty ( $_mp_debug_mode ) ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->log = new WC_Logger();
			} else {
				$this->log = WC_Woo_Mercado_Pago_Module::woocommerce_instance()->logger();
			}
		}

		// Render our configuration page and init/load fields.
		$this->init_form_fields();
		$this->init_settings();

		// Used by IPN to receive IPN incomings.
		add_action(
			'woocommerce_api_wc_woomercadopago_customgateway',
			array( $this, 'check_ipn_response' )
		);
		// Used by IPN to process valid incomings.
		add_action(
			'valid_mercadopago_custom_ipn_request',
			array( $this, 'successful_request' )
		);
		// Process the cancel order meta box order action.
		add_action(
			'woocommerce_order_action_cancel_order',
			array( $this, 'process_cancel_order_meta_box_actions' )
		);
		// Used in settings page to hook "save settings" action.
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array( $this, 'custom_process_admin_options' )
		);
		// Scripts for custom checkout.
		add_action(
			'wp_enqueue_scripts',
			array( $this, 'custom_checkout_scripts' )
		);
		// Apply the discounts.
		/*add_action(
			'woocommerce_cart_calculate_fees',
			array( $this, 'add_discount_custom' ), 10
		);*/
		// Display discount in payment method title.
		/*add_filter(
			'woocommerce_gateway_title',
			array( $this, 'get_payment_method_title_custom' ), 10, 2
		);*/

	}

	/**
	 * Summary: Initialise Gateway Settings Form Fields.
	 * Description: Initialise Gateway settings form fields with a customized page.
	 */
	public function init_form_fields() {

		// Show message if credentials are not properly configured.
		$_site_id_v1 = get_option( '_site_id_v1', '' );
		if ( empty( $_site_id_v1 ) ) {
			$this->form_fields = array(
				'no_credentials_title' => array(
					'title' => sprintf(
						__( 'It appears that your credentials are not properly configured.<br/>Please, go to %s and configure it.', 'woo-mercado-pago-module' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=mercado-pago-settings' ) ) . '">' .
						__( 'Mercado Pago Settings', 'woo-mercado-pago-module' ) .
						'</a>'
					),
					'type' => 'title'
				),
			);
			return;
		}

		// This array draws each UI (text, selector, checkbox, label, etc).
		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'woo-mercado-pago-module' ),
				'type' => 'checkbox',
				'label' => __( 'Enable Custom Checkout', 'woo-mercado-pago-module' ),
				'default' => 'no'
			),
			'checkout_options_title' => array(
				'title' => __( 'Checkout Interface: How checkout is shown', 'woo-mercado-pago-module' ),
				'type' => 'title'
			),
			'title' => array(
				'title' => __( 'Title', 'woo-mercado-pago-module' ),
				'type' => 'text',
				'description' => __( 'Title shown to the client in the checkout.', 'woo-mercado-pago-module' ),
				'default' => __( 'Mercado Pago - Credit Card', 'woo-mercado-pago-module' )
			),
			'description' => array(
				'title' => __( 'Description', 'woo-mercado-pago-module' ),
				'type' => 'textarea',
				'description' => __( 'Description shown to the client in the checkout.', 'woo-mercado-pago-module' ),
				'default' => __( 'Pay with Mercado Pago', 'woo-mercado-pago-module' )
			),
			'payment_title' => array(
				'title' => __( 'Payment Options: How payment options behaves', 'woo-mercado-pago-module' ),
				'type' => 'title'
			),
			'coupon_mode' => array(
				'title' => __( 'Coupons', 'woo-mercado-pago-module' ),
				'type' => 'checkbox',
				'label' => __( 'Enable coupons of discounts', 'woo-mercado-pago-module' ),
				'default' => 'no',
				'description' => __( 'If there is a Mercado Pago campaign, allow your store to give discounts to customers.', 'woo-mercado-pago-module' )
			),
			'binary_mode' => array(
				'title' => __( 'Binary Mode', 'woo-mercado-pago-module' ),
				'type' => 'checkbox',
				'label' => __( 'Enable binary mode for checkout status', 'woo-mercado-pago-module' ),
				'default' => 'no',
				'description' =>
					__( 'When charging a credit card, only [approved] or [reject] status will be taken.', 'woo-mercado-pago-module' )
			),
			'gateway_discount' => array(
				'title' => __( 'Discount by Gateway', 'woo-mercado-pago-module' ),
				'type' => 'number',
				'description' => __( 'Give a percentual (0 to 100) discount for your customers if they use this payment gateway.', 'woo-mercado-pago-module' ),
				'default' => '0'
			)
		);

	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the
	 * erroring field out.
	 * @return bool was anything saved?
	 */
	public function custom_process_admin_options() {
		$this->init_settings();
		$post_data = $this->get_post_data();
		foreach ( $this->get_form_fields() as $key => $field ) {
			if ( 'title' !== $this->get_field_type( $field ) ) {
				$value = $this->get_field_value( $key, $field, $post_data );
				if ( $key == 'gateway_discount') {
					if ( ! is_numeric( $value ) || empty ( $value ) ) {
						$this->settings[$key] = 0;
					} else {
						if ( $value < 0 || $value >= 100 || empty ( $value ) ) {
							$this->settings[$key] = 0;
						} else {
							$this->settings[$key] = $value;
						}
					}
				} else {
					$this->settings[$key] = $this->get_field_value( $key, $field, $post_data );
				}
			}
		}
		$_site_id_v1 = get_option( '_site_id_v1', '' );
		$is_test_user = get_option( '_test_user_v1', false );
		if ( ! empty( $_site_id_v1 ) && ! $is_test_user ) {
			// Create MP instance.
			$mp = new MP(
				WC_Woo_Mercado_Pago_Module::get_module_version(),
				get_option( '_mp_access_token' )
			);
			// Analytics.
			$infra_data = WC_Woo_Mercado_Pago_Module::get_common_settings();
			$infra_data['checkout_custom_credit_card'] = ( $this->settings['enabled'] == 'yes' ? 'true' : 'false' );
			$infra_data['checkout_custom_credit_card_coupon'] = ( $this->settings['coupon_mode'] == 'yes' ? 'true' : 'false' );
			$response = $mp->analytics_save_settings( $infra_data );
		}
		// Apply updates.
		return update_option(
			$this->get_option_key(),
			apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings )
		);
	}

	/**
	 * Handles the manual order refunding in server-side.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$payments = get_post_meta( $order_id, '_Mercado_Pago_Payment_IDs', true );

		// Validate.
		if ( $this->mp == null || empty( $payments ) ) {
			$this->write_log( __FUNCTION__, 'no payments or credentials invalid' );
			return false;
		}

		// Processing data about this refund.
		$total_available = 0;
		$payment_structs = array();
		$payment_ids = explode( ', ', $payments );
		foreach ( $payment_ids as $p_id ) {
			$p = get_post_meta( $order_id, 'Mercado Pago - Payment ' . $p_id, true );
			$p = explode( '/', $p );
			$paid_arr = explode( ' ', substr( $p[2], 1, -1 ) );
			$paid = ( (float) $paid_arr[1] );
			$refund_arr = explode( ' ', substr( $p[3], 1, -1 ) );
			$refund = ( (float) $refund_arr[1] );
			$p_struct = array( 'id' => $p_id, 'available_to_refund' => $paid - $refund );
			$total_available += $paid - $refund;
			$payment_structs[] = $p_struct;
		}
		$this->write_log( __FUNCTION__,
			'refunding ' . $amount . ' because of ' . $reason . ' and payments ' .
			json_encode( $payment_structs, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
		);

		// Do not allow refund more than available or invalid amounts.
		if ( $amount > $total_available || $amount <= 0 ) {
			return false;
		}

		// Iteratively refunfind amount, taking in consideration multiple payments.
		$remaining_to_refund = $amount;
		foreach ( $payment_structs as $to_refund ) {
			if ( $remaining_to_refund <= $to_refund['available_to_refund'] ) {
				// We want to refund an amount that is less than the available for this payment, so we
				// can just refund and return.
				$response = $this->mp->partial_refund_payment(
					$to_refund['id'], $remaining_to_refund,
					$reason, $this->invoice_prefix . $order_id
				);
				$message = $response['response']['message'];
				$status = $response['status'];
				$this->write_log( __FUNCTION__,
					'refund payment of id ' . $p_id . ' => ' .
					( $status >= 200 && $status < 300 ? 'SUCCESS' : 'FAIL - ' . $message )
				);
				if ( $status >= 200 && $status < 300 ) {
					return true;
				} else {
					return false;
				}
			} elseif ( $to_refund['available_to_refund'] > 0 ) {
				// We want to refund an amount that exceeds the available for this payment, so we
				// totally refund this payment, and try to complete refund in other/next payments.
				$response = $this->mp->partial_refund_payment(
					$to_refund['id'], $to_refund['available_to_refund'],
					$reason, $this->invoice_prefix . $order_id
				);
				$message = $response['response']['message'];
				$status = $response['status'];
				$this->write_log( __FUNCTION__,
					'refund payment of id ' . $p_id . ' => ' .
					( $status >= 200 && $status < 300 ? 'SUCCESS' : 'FAIL - ' . $message )
				);
				if ( $status < 200 || $status >= 300 ) {
					return false;
				}
				$remaining_to_refund -= $to_refund['available_to_refund'];
			}
			if ( $remaining_to_refund == 0 )
				return true;
		}

		// Reaching here means that there we run out of payments, and there is an amount
		// remaining to be refund, which is impossible as it implies refunding more than
		// available on paid amounts.
		return false;
	}

	/**
	 * Handles the manual order cancellation in server-side.
	 */
	public function process_cancel_order_meta_box_actions( $order ) {

		$used_gateway = ( method_exists( $order, 'get_meta' ) ) ?
			$order->get_meta( '_used_gateway' ) :
			get_post_meta( $order->id, '_used_gateway', true );
		$payments = ( method_exists( $order, 'get_meta' ) ) ?
			$order->get_meta( '_Mercado_Pago_Payment_IDs' ) :
			get_post_meta( $order->id, '_Mercado_Pago_Payment_IDs',	true );

		// A watchdog to prevent operations from other gateways.
		if ( $used_gateway != 'WC_WooMercadoPago_CustomGateway' ) {
			return;
		}

		$this->write_log( __FUNCTION__, 'cancelling payments for ' . $payments );

		// Canceling the order and all of its payments.
		if ( $this->mp != null && ! empty( $payments ) ) {
			$payment_ids = explode( ', ', $payments );
			foreach ( $payment_ids as $p_id ) {
				$response = $this->mp->cancel_payment( $p_id );
				$message = $response['response']['message'];
				$status = $response['status'];
				$this->write_log( __FUNCTION__,
					'cancel payment of id ' . $p_id . ' => ' .
					( $status >= 200 && $status < 300 ? 'SUCCESS' : 'FAIL - ' . $message )
				);
			}
		} else {
			$this->write_log( __FUNCTION__, 'no payments or credentials invalid' );
		}
	}

	// Write log.
	private function write_log( $function, $message ) {
		$_mp_debug_mode = get_option( '_mp_debug_mode', '' );
		if ( ! empty ( $_mp_debug_mode ) ) {
			$this->log->add(
				$this->id,
				'[' . $function . ']: ' . $message
			);
		}
	}

	/*
	 * ========================================================================
	 * CHECKOUT BUSINESS RULES (CLIENT SIDE)
	 * ========================================================================
	 */

	public function add_checkout_script() {

		$public_key = get_option( '_mp_public_key' );
		$is_test_user = get_option( '_test_user_v1', false );

		if ( ! empty( $public_key ) && ! $is_test_user ) {

			$w = WC_Woo_Mercado_Pago_Module::woocommerce_instance();
			$available_payments = array();
			$gateways = WC()->payment_gateways->get_available_payment_gateways();
			foreach ( $gateways as $g ) {
				$available_payments[] = $g->id;
			}
			$available_payments = str_replace( '-', '_', implode( ', ', $available_payments ) );
			if ( wp_get_current_user()->ID != 0 ) {
				$logged_user_email = wp_get_current_user()->user_email;
			} else {
				$logged_user_email = null;
			}
			?>
			<script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
			<script type="text/javascript">
				var MA = ModuleAnalytics;
				MA.setPublicKey( '<?php echo $public_key; ?>' );
				MA.setPlatform( 'WooCommerce' );
				MA.setPlatformVersion( '<?php echo $w->version; ?>' );
				MA.setModuleVersion( '<?php echo WC_Woo_Mercado_Pago_Module::VERSION; ?>' );
				MA.setPayerEmail( '<?php echo ( $logged_user_email != null ? $logged_user_email : "" ); ?>' );
				MA.setUserLogged( <?php echo ( empty( $logged_user_email ) ? 0 : 1 ); ?> );
				MA.setInstalledModules( '<?php echo $available_payments; ?>' );
				MA.post();
			</script>
			<?php

		}

	}

	public function update_checkout_status( $order_id ) {
		$public_key = get_option( '_mp_public_key' );
		$is_test_user = get_option( '_test_user_v1', false );
		if ( ! empty( $public_key ) && ! $is_test_user ) {
			if ( get_post_meta( $order_id, '_used_gateway', true ) != 'WC_WooMercadoPago_CustomGateway' ) {
				return;
			}
			$this->write_log( __FUNCTION__, 'updating order of ID ' . $order_id );
			echo '<script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
			<script type="text/javascript">
				var MA = ModuleAnalytics;
				MA.setPublicKey( "' . $public_key . '" );
				MA.setPaymentType("credit_card");
				MA.setCheckoutType("custom");
				MA.put();
			</script>';
		}
	}

	public function custom_checkout_scripts() {
		if ( is_checkout() && $this->is_available() ) {
			if ( ! get_query_var( 'order-received' ) ) {
				/*$session_id = $this->api->get_session_id(); */
				wp_enqueue_style(
					'woocommerce-mercadopago-style', plugins_url(
						'assets/css/custom_checkout_mercadopago.css',
						plugin_dir_path( __FILE__ ) ) );
				/*wp_enqueue_script(
					'woocommerce-mercadopago-v1',
					'https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js' );*/
				wp_enqueue_script(
					'woo-mercado-pago-module-custom-js',
					plugins_url( 'assets/js/credit-card.js', plugin_dir_path( __FILE__ ) ),
					array(),
					WC_Woo_Mercado_Pago_Module::VERSION,
					true
				);

				/*wp_localize_script(
					'pagseguro-checkout',
					'wc_pagseguro_params',
					array(
						'session_id'         => $session_id,
						'interest_free'      => __( 'interest free', 'woocommerce-pagseguro' ),
						'invalid_card'       => __( 'Invalid credit card number.', 'woocommerce-pagseguro' ),
						'invalid_expiry'     => __( 'Invalid expiry date, please use the MM / YYYY date format.', 'woocommerce-pagseguro' ),
						'expired_date'       => __( 'Please check the expiry date and use a valid format as MM / YYYY.', 'woocommerce-pagseguro' ),
						'general_error'      => __( 'Unable to process the data from your credit card on the PagSeguro, please try again or contact us for assistance.', 'woocommerce-pagseguro' ),
						'empty_installments' => __( 'Select a number of installments.', 'woocommerce-pagseguro' ),
					)
				);*/
			}
		}
	}

	public function payment_fields() {
		
		wp_enqueue_script( 'wc-credit-card-form' );
		
		/*$amount = $this->get_order_total();*/
		$logged_user_email = ( wp_get_current_user()->ID != 0 ) ? wp_get_current_user()->user_email : null;
		$customer = isset( $logged_user_email ) ? $this->mp->get_or_create_customer( $logged_user_email ) : null;

		$parameters = array(
			/*'public_key' => $this->public_key,
			'site_id' => $this->site_id,*/
			'images_path'    => plugins_url( 'assets/images/', plugin_dir_path( __FILE__ ) ),
			'banner_path'    => $this->site_data['checkout_banner_custom'],
			'customer_cards' => isset( $customer ) ? ( isset( $customer['cards'] ) ? $customer['cards'] : array() ) : array(),
			'customerId'     => isset( $customer ) ? ( isset( $customer['id'] ) ? $customer['id'] : null ) : null,
			'payer_email'    => $logged_user_email,
			'coupon_mode'    => isset( $logged_user_email ) ? $this->coupon_mode : 'no'

			/*'amount' => $amount * ( (float) $this->currency_ratio > 0 ? (float) $this->currency_ratio : 1 ),
			'coupon_mode' => $this->coupon_mode,
			'is_currency_conversion' => $this->currency_ratio,
			'woocommerce_currency' => get_woocommerce_currency(),
			'account_currency' => $this->country_configs['currency'],
			'discount_action_url' => $this->domain .
				'/woocommerce-mercadopago-module/?wc-api=WC_WooMercadoPagoCustom_Gateway',
			'form_labels' => array(
				'form' => array(
					'payment_converted' =>
						__( 'Payment converted from', 'woocommerce-mercadopago-module' ),
					'to' => __( 'to', 'woocommerce-mercadopago-module' ),
					'coupon_empty' =>
						__( 'Please, inform your coupon code', 'woocommerce-mercadopago-module' ),
					'apply' => __( 'Apply', 'woocommerce-mercadopago-module' ),
					'remove' => __( 'Remove', 'woocommerce-mercadopago-module' ),
					'discount_info1' => __( 'You will save', 'woocommerce-mercadopago-module' ),
					'discount_info2' => __( 'with discount from', 'woocommerce-mercadopago-module' ),
					'discount_info3' => __( 'Total of your purchase:', 'woocommerce-mercadopago-module' ),
					'discount_info4' =>
						__( 'Total of your purchase with discount:', 'woocommerce-mercadopago-module' ),
					'discount_info5' => __( '*Uppon payment approval', 'woocommerce-mercadopago-module' ),
					'discount_info6' =>
						__( 'Terms and Conditions of Use', 'woocommerce-mercadopago-module' ),
					'coupon_of_discounts' => __( 'Discount Coupon', 'woocommerce-mercadopago-module' ),
					'label_other_bank' => __( 'Other Bank', 'woocommerce-mercadopago-module' ),
					'label_choose' => __( 'Choose', 'woocommerce-mercadopago-module' ),
					'your_card' => __( 'Your Card', 'woocommerce-mercadopago-module' ),
					'other_cards' => __( 'Other Cards', 'woocommerce-mercadopago-module' ),
					'other_card' => __( 'Other Card', 'woocommerce-mercadopago-module' ),
					'ended_in' => __( 'ended in', 'woocommerce-mercadopago-module' ),
					'card_holder_placeholder' =>
						__( ' as it appears in your card ...', 'woocommerce-mercadopago-module' ),
					'payment_method' => __( 'Payment Method', 'woocommerce-mercadopago-module' ),
					'credit_card_number' => __( 'Credit card number', 'woocommerce-mercadopago-module' ),
					'expiration_month' => __( 'Expiration month', 'woocommerce-mercadopago-module' ),
					'expiration_year' => __( 'Expiration year', 'woocommerce-mercadopago-module' ),
					'year' => __( 'Year', 'woocommerce-mercadopago-module' ),
					'month' => __( 'Month', 'woocommerce-mercadopago-module' ),
					'card_holder_name' => __( 'Card holder name', 'woocommerce-mercadopago-module' ),
					'security_code' => __( 'Security code', 'woocommerce-mercadopago-module' ),
					'document_type' => __( 'Document Type', 'woocommerce-mercadopago-module' ),
					'document_number' => __( 'Document number', 'woocommerce-mercadopago-module' ),
					'issuer' => __( 'Issuer', 'woocommerce-mercadopago-module' ),
					'installments' => __( 'Installments', 'woocommerce-mercadopago-module' )
				),
				'error' => array(
					// Card number.
					'205' => __( 'Parameter cardNumber can not be null/empty', 'woocommerce-mercadopago-module' ),
					'E301' => __( 'Invalid Card Number', 'woocommerce-mercadopago-module' ),
					// Expiration date.
					'208' => __( 'Invalid Expiration Date', 'woocommerce-mercadopago-module' ),
					'209' => __( 'Invalid Expiration Date', 'woocommerce-mercadopago-module' ),
					'325' => __( 'Invalid Expiration Date', 'woocommerce-mercadopago-module' ),
					'326' => __( 'Invalid Expiration Date', 'woocommerce-mercadopago-module' ),
					// Card holder name.
					'221' => __( 'Parameter cardholderName can not be null/empty', 'woocommerce-mercadopago-module' ),
					'316' => __( 'Invalid Card Holder Name', 'woocommerce-mercadopago-module' ),
					// Security code.
					'224' => __( 'Parameter securityCode can not be null/empty', 'woocommerce-mercadopago-module' ),
					'E302' => __( 'Invalid Security Code', 'woocommerce-mercadopago-module' ),
					// Doc type.
					'212' => __( 'Parameter docType can not be null/empty', 'woocommerce-mercadopago-module' ),
					'322' => __( 'Invalid Document Type', 'woocommerce-mercadopago-module' ),
					// Doc number.
					'214' => __( 'Parameter docNumber can not be null/empty', 'woocommerce-mercadopago-module' ),
					'324' => __( 'Invalid Document Number', 'woocommerce-mercadopago-module' ),
					// Doc sub type.
					'213' => __( 'The parameter cardholder.document.subtype can not be null or empty', 'woocommerce-mercadopago-module' ),
					'323' => __( 'Invalid Document Sub Type', 'woocommerce-mercadopago-module' ),
					// Issuer.
					'220' => __( 'Parameter cardIssuerId can not be null/empty', 'woocommerce-mercadopago-module' )
				)
			)*/
		);

		wc_get_template(
			'credit-card/payment-form.php',
			$parameters,
			'woo/mercado/pago/module/',
			WC_Woo_Mercado_Pago_Module::get_templates_path()
		);
	}

	/*
	 * ========================================================================
	 * AUXILIARY AND FEEDBACK METHODS (SERVER SIDE)
	 * ========================================================================
	 */

	// Called automatically by WooCommerce, verify if Module is available to use.
	public function is_available() {
		if ( ! did_action( 'wp_loaded' ) ) {
			return false;
		}
		global $woocommerce;
		$w_cart = $woocommerce->cart;
		// Check if we have SSL.
		if ( empty( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] == 'off' ) {
			return false;
		}
		// Check for recurrent product checkout.
		if ( isset( $w_cart ) ) {
			if ( WC_Woo_Mercado_Pago_Module::is_subscription( $w_cart->get_cart() ) ) {
				return false;
			}
		}
		// Check if this gateway is enabled and well configured.
		$_mp_public_key = get_option( '_mp_public_key' );
		$_mp_access_token = get_option( '_mp_access_token' );
		$_site_id_v1 = get_option( '_site_id_v1' );
		$available = ( 'yes' == $this->settings['enabled'] ) &&
			! empty( $_mp_public_key ) &&
			! empty( $_mp_access_token ) &&
			! empty( $_site_id_v1 );
		return $available;
	}

}