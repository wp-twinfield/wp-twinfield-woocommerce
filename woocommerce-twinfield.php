<?php

/**
 * Plugin Name: WooCommerce Twinfield
 * Plugin URI: http://pronamic.nl
 * Author: Pronamic
 * Author URI: http://pronamic.nl
 * Version: 1.0.0
 * Domain: woocommerce_twinfield
 */
if ( ! class_exists( 'WooCommerceTwinfield' ) ) :

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
	class WooCommerceTwinfield {

		/**
		 * Sets the product post type to support the twinfield_article
		 * metabox.
		 *
		 * @hooks ACTION wp_twinfield_load_forms
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		}

		public function plugins_loaded() {
			if ( class_exists( 'Woocommerce' ) || class_exists( 'WooCommerce' )) {
				include 'lib/class-woocommerce-invoice.php';
				include 'lib/class-woocommerce-invoice-sync.php';
				include 'lib/class-woocommercetwinfield-integration.php';

				$this->register_hooks();
			}
		}

		/**
		 * Called if the WooCommerce class exists to ensure that the plugin dependancy
		 * is met.
		 *
		 * As long as there are not hooks before 'plugins_loaded' action, no problems will
		 * occur.
		 *
		 * The actions before 'plugins_loaded' are as follows: mu_plugins_loaded, registered_taxonomy and
		 * registered_post_type.
		 *
		 * @see http://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_an_Admin_Page_Request
		 *
		 * @access public
		 * @return void
		 */
		public function register_hooks() {
			// Add the Twinfield Article Metabox to the Product Post Type
			add_post_type_support( 'product', 'twinfield_article' );

			add_action( 'wp_twinfield_formbuilder_load_forms', array( $this, 'load_forms' ) );

			add_action( 'admin_init', array( $this, 'admin_init' ) );

			add_action( 'wp_ajax_woocommerce_twinfield_formbuilder_load_order', array( $this, 'ajax_load_order' ) );

			load_plugin_textdomain( 'woocommerce_twinfield', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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

		public function ajax_load_order() {

			if ( ! filter_has_var( INPUT_POST, 'order_id' ) )
				exit;

			$wc_order = new WC_Order( filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT ) );

			$woocommerce_invoice = new Woocommerce_Invoice( $wc_order, Woocommerce_Invoice::check_for_twinfield_customer_id( $wc_order->id ) );

			echo json_encode( $woocommerce_invoice->prepare_invoice() );
			exit;
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
$woocommerce_twinfield = new WooCommerceTwinfield();
