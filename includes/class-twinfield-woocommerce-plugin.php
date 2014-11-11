<?php

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
class Pronamic_Twinfield_WooCommerce_Plugin {

	/**
	 * Sets the product post type to support the twinfield_article
	 * metabox.
	 *
	 * @hooks ACTION wp_twinfield_load_forms
	 */
	public function __construct( $file ) {
		$this->file = $file;

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	public function plugins_loaded() {
		/*
		 * WooCommerce version >= 2.1.0 = WC_VERSION          » https://github.com/woothemes/woocommerce/blob/v2.1.0/woocommerce.php#L255-L256
		 * WooCommerce version <  2.1.0 = WOOCOMMERCE_VERSION » https://github.com/woothemes/woocommerce/blob/v2.0.20/woocommerce.php#L132-L133
		 */
		if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
			$this->register_hooks();

			load_plugin_textdomain( 'twinfield_woocommerce', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
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
		add_post_type_support( 'shop_order', 'twinfield_invoiceable' );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function admin_init() {
		\Pronamic\WP\Twinfield\Invoice\InvoiceMetaBoxFactory::register( 'shop_order', 'Pronamic_Twinfield_WooCommerce_InvoiceMetaBox' );
	}
}
