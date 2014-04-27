<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
/**
 * Invoice Payment Gateway
 *
 * Provides a Invoice Payment Gateway, mainly for testing purposes.
 *
 * @class 		WC_Gateway_Invoice
 * @extends		WC_Payment_Gateway
 * @version		2.1.0
 * @package		WooCommerce/Classes/Payment
 * @author 		WooThemes
 */
class WC_Gateway_Invoice extends WC_Payment_Gateway {
 
    /**
     * Constructor for the gateway.
     */
	public function __construct() {
		$this->id                 = 'invoice';
		$this->icon               = apply_filters('woocommerce_invoice_icon', '');
		$this->has_fields         = false;
		$this->method_title       = __( 'Invoice', 'woocommerce' );
		$this->method_description = __( 'Allows invoice payments. Why would you take invoices in this day and age? Well you probably wouldn\'t but it does allow you to make test purchases for testing order emails and the \'success\' pages etc.', 'woocommerce' );
 
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
 
		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );
 
		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    	add_action( 'woocommerce_thankyou_invoice', array( $this, 'thankyou_page' ) );
 
    	// Customer Emails
    	add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }
 
    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields() {
 
    	$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Invoice Payment', 'woocommerce' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'Invoice', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
				'default'     => __( 'You will receive an invoice with instructions how to pay for your order.', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'instructions' => array(
				'title'       => __( 'Instructions', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
				'default'     => __( 'Please allow 48 hours for us to verify stock and send you instructions on how to pay for your order.', 'woocommerce' ),
				'desc_tip'    => true,
			),
		);
    }
 
    /**
     * Output for the order received page.
     */
	public function thankyou_page() {
		if ( $this->instructions )
        	echo wpautop( wptexturize( $this->instructions ) );
	}
 
    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
    	if ( $sent_to_admin || $order->status !== 'on-hold' || $order->payment_method !== 'invoice' )
    		return;
 
		if ( $this->instructions )
        	echo wpautop( wptexturize( $this->instructions ) );
	}
 
    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
	public function process_payment( $order_id ) {
 
		$order = new WC_Order( $order_id );
 
		// Mark as on-hold (we're awaiting the invoice)
		$order->update_status( 'on-hold', __( 'Awaiting invoice payment', 'woocommerce' ) );
 
		// Reduce stock levels
		//$order->reduce_order_stock();
 
		// Remove cart
		WC()->cart->empty_cart();
 
		// Return thankyou redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url( $order )
		);
	}
}