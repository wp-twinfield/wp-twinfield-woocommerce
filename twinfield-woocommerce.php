<?php
/*
Plugin Name: Twinfield WooCommerce
Plugin URI: http://www.pronamic.eu/plugins/woocommerce-twinfield/
Description: WordPress Twinfield plugin for WooCommerce.

Version: 1.1.1
Requires at least: 3.6

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: twinfield_woocommerce
Domain Path: /languages/

License: GPL
GitHub URI: https://github.com/wp-twinfield/wp-twinfield-woocommerce
*/

/**
 * Composer autoload.
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Plugin bootstrap.
 */
global $twinfield_woocommerce_plugin;

$twinfield_woocommerce_plugin = new Pronamic_Twinfield_WooCommerce_Plugin( __FILE__ );
