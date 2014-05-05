<?php
/*
Plugin Name: WooCommerce Invoice Payment Gateway by Enollo
Description: Add invoice payment gateway
Version: 0.01
License: GPL
Author: Enollo
Author URI: enollo.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 **/
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}


/**
 * Invoice Payment Gateway
 *
 * @access public
 * @param mixed $hook
 * @return void
 */

function wipg_init_invoice_gateway_class() {
	include_once( plugin_dir_path( __FILE__ ) . '/inc/class-wc-gateway-invoice.php' );
}
add_action( 'plugins_loaded', 'wipg_init_invoice_gateway_class' );

function wipg_add_invoice_gateway_class( $methods ) {
	$methods[] = 'WC_Gateway_Invoice';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'wipg_add_invoice_gateway_class' );