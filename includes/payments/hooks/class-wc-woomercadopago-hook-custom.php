<?php
/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 * @package MercadoPago
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_WooMercadoPago_Hook_Custom
 */
class WC_WooMercadoPago_Hook_Custom extends WC_WooMercadoPago_Hook_Abstract {

	/**
	 * Load Hooks
	 */
	public function load_hooks() {
		parent::load_hooks();
		if ( ! empty( $this->payment->settings['enabled'] ) && 'yes' === $this->payment->settings['enabled'] ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_checkout_scripts_custom' ) );
			add_action( 'woocommerce_after_checkout_form', array( $this, 'add_mp_settings_script_custom' ) );
			add_action( 'woocommerce_thankyou', array( $this, 'update_mp_settings_script_custom' ) );
		}
	}

	/**
	 *  Add Discount
	 */
	public function add_discount() {
		// @todo needs processing form data without nonce verification.
		// @codingStandardsIgnoreLine
		if ( ! isset( $_POST['mercadopago_custom'] ) ) {
			return;
		}
		if ( is_admin() && ! defined( 'DOING_AJAX' ) || is_cart() ) {
			return;
		}
		// @todo needs processing form data without nonce verification.
		// @codingStandardsIgnoreLine
		$custom_checkout = $_POST['mercadopago_custom'];
		parent::add_discount_abst( $custom_checkout );
	}

	/**
	 * Add Checkout Scripts
	 */
	public function add_checkout_scripts_custom() {
		if ( is_checkout() && $this->payment->is_available() && ! get_query_var( 'order-received' ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script(
				'woocommerce-mercadopago-checkout',
				plugins_url( '../../assets/js/credit-card' . $suffix . '.js', plugin_dir_path( __FILE__ ) ),
				array( 'jquery' ),
				WC_WooMercadoPago_Constants::VERSION,
				true
			);

			wp_localize_script(
				'woocommerce-mercadopago-checkout',
				'wc_mercadopago_params',
				array(
					'site_id'             => $this->payment->get_option_mp( '_site_id_v1' ),
					'public_key'          => $this->payment->get_public_key(),
					'coupon_mode'         => isset( $this->payment->logged_user_email ) ? $this->payment->coupon_mode : 'no',
					'discount_action_url' => $this->payment->discount_action_url,
					'payer_email'         => esc_js( $this->payment->logged_user_email ),
					'apply'               => __( 'Apply', 'woocommerce-mercadopago' ),
					'remove'              => __( 'Remove', 'woocommerce-mercadopago' ),
					'coupon_empty'        => __( 'Please, inform your coupon code', 'woocommerce-mercadopago' ),
					'choose'              => __( 'To choose', 'woocommerce-mercadopago' ),
					'other_bank'          => __( 'Other bank', 'woocommerce-mercadopago' ),
					'discount_info1'      => __( 'You will save', 'woocommerce-mercadopago' ),
					'discount_info2'      => __( 'with discount of', 'woocommerce-mercadopago' ),
					'discount_info3'      => __( 'Total of your purchase:', 'woocommerce-mercadopago' ),
					'discount_info4'      => __( 'Total of your purchase with discount:', 'woocommerce-mercadopago' ),
					'discount_info5'      => __( '*After payment approval', 'woocommerce-mercadopago' ),
					'discount_info6'      => __( 'Terms and conditions of use', 'woocommerce-mercadopago' ),
					'loading'             => plugins_url( '../../assets/images/', plugin_dir_path( __FILE__ ) ) . 'loading.gif',
					'check'               => plugins_url( '../../assets/images/', plugin_dir_path( __FILE__ ) ) . 'check.png',
					'error'               => plugins_url( '../../assets/images/', plugin_dir_path( __FILE__ ) ) . 'error.png',
					'plugin_version'      => WC_WooMercadoPago_Constants::VERSION,
				)
			);
		}
	}

	/**
	 * Add custom script
	 */
	public function add_mp_settings_script_custom() {
		parent::add_mp_settings_script();
	}

	/**
	 * Add script custom
	 *
	 * @param string $order_id Order Id.
	 */
	public function update_mp_settings_script_custom( $order_id ) {
		// @todo transform js return
		// @codingStandardsIgnoreLine
		echo parent::update_mp_settings_script( $order_id );
	}
}
