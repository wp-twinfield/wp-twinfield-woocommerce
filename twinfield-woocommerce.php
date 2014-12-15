<?php
/*
Plugin Name: Twinfield WooCommerce
Plugin URI: http://www.happywp.com/plugins/twinfield-woocommerce/
Description: WordPress Twinfield plugin for WooCommerce.

Version: 1.1.0
Requires at least: 3.6

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: twinfield_woocommerce
Domain Path: /languages/

License: GPL
GitHub URI: https://github.com/wp-twinfield/wp-twinfield-woocommerce
*/

function twinfield_woocommerce_bootstrap() {
	$path = plugin_dir_path( __FILE__ );

	// Requirements
	require_once $path . 'includes/class-twinfield-woocommerce-invoice.php';
	require_once $path . 'includes/class-twinfield-woocommerce-invoice-meta-box.php';
	require_once $path . 'includes/class-twinfield-woocommerce-integration.php';
	require_once $path . 'includes/class-twinfield-woocommerce-plugin.php';

	// Loads the plugin class into global state.
	global $twinfield_woocommerce;

	$twinfield_woocommerce = new Pronamic_Twinfield_WooCommerce_Plugin( __FILE__ );
}

add_action( 'twinfield_bootstrap', 'twinfield_woocommerce_bootstrap' );
