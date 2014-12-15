<?php

/**
 * Title: Twinfield WooCommerce plugin
 * Description:
 * Copyright: Copyright (c) 2005 - 2014
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_Twinfield_WooCommerce_Plugin {
	/**
	 * Constructs and initialize plugin
	 *
	 * @param unknown $file
	 */
	public function __construct( $file ) {
		$this->file = $file;

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Plugins loaded
	 */
	public function plugins_loaded() {
		/*
		 * WooCommerce version >= 2.1.0 = WC_VERSION          » https://github.com/woothemes/woocommerce/blob/v2.1.0/woocommerce.php#L255-L256
		 * WooCommerce version <  2.1.0 = WOOCOMMERCE_VERSION » https://github.com/woothemes/woocommerce/blob/v2.0.20/woocommerce.php#L132-L133
		 */
		if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
			// Actions
			add_filter( 'woocommerce_integrations', array( $this, 'woocommerce_integrations' ) );

			// Text domain
			load_plugin_textdomain( 'twinfield_woocommerce', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );

			// Post types
			add_post_type_support( 'product', 'twinfield_article' );
			add_post_type_support( 'shop_coupon', 'twinfield_article' );
			add_post_type_support( 'shop_order', 'twinfield_invoiceable' );

			\Pronamic\WP\Twinfield\Invoice\InvoiceMetaBoxFactory::register( 'shop_order', 'Pronamic_Twinfield_WooCommerce_InvoiceMetaBox' );
		}
	}

	//////////////////////////////////////////////////

	/**
	 * WooCommerce integrations
	 *
	 * @param array $integrations
	 * @return array
	 */
	public function woocommerce_integrations( $integrations ) {
		$integrations[] = 'Pronamic_Twinfield_WooCommerce_Integration';

		return $integrations;
	}
}
