<?php

/**
 * Plugin Name: WP WooCommerce Twinfield
 * Plugin URI: http://pronamic.nl
 * Author: Pronamic
 * Author URI: http://pronamic.nl
 * Version: 1.0.0
 */
if ( ! class_exists( 'Woocommerce_Twinfield' ) ) :

	/**
	 * Plugin class that adds support for twinfield_article to the product
	 * post type and sets the hook for wp_twinfield_formbuilder_load_forms
	 * to load the Woocommerce Invoice Form for the FormBuilder UI
	 * 
	 * @package Woocommerce Twinfield
	 * 
	 * @author Leon Rowland <leon@rowland.nl>
	 * @version 1.0.0
	 */
	class Woocommerce_Twinfield {

		/**
		 * Sets the product post type to support the twinfield_article
		 * metabox.
		 * 
		 * @hooks ACTION wp_twinfield_load_forms
		 */
		public function __construct() {

			// Add the Twinfield Article Metabox to the Product Post Type
			add_post_type_support( 'product', 'twinfield_article' );

			add_action( 'wp_twinfield_formbuilder_load_forms', array( $this, 'load_forms' ) );
			
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			
			include 'lib/class-woocommerce-invoice.php';
			include 'lib/class-woocommerce-invoice-sync.php';
		}

		/**
		 * Registers the Woocommerce_Invoice class as a form for the 
		 * formbuilder factory.
		 * 
		 * Sets the view for that form so it works in the formbuilder ui
		 */
		public function load_forms() {
			// Makes an instance of WooCommerce Invoice
			$woocommerce_invoice = new Woocommerce_Invoice();
			$woocommerce_invoice->set_view( dirname( __FILE__ ) . '/views/woocommerce_invoice_form.php' );

			// Registers the woocommerce invoice form
			\Pronamic\WP\Twinfield\FormBuilder\FormBuilderFactory::register_form( 'Woocommerce Invoice', $woocommerce_invoice );
		}
		
		public function admin_init() {
			include 'lib/class-woocommerce-invoice-meta-box.php';
			$invoice_meta_box = new Woocommerce_Invoice_Meta_Box();
		}

		public function plugin_folder() {
			return dirname( __FILE__ );
		}
		
		public function plugin_file() {
			return __FILE__;
		}
	}

endif;

// Loads the plugin class into global state.
global $woocommerce_twinfield;
$woocommerce_twinfield = new Woocommerce_Twinfield();