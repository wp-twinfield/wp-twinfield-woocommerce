<?php

/**
 * Title: Twinfield WooCommerce plugin
 * Description:
 * Copyright: Copyright (c) 2005 - 2014
 * Company: Pronamic
 *
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
		// Required plugins
		if ( ! defined( 'WC_VERSION' ) || ! class_exists( 'Pronamic\WP\Twinfield\Plugin\Plugin' ) ) {
			return;
		}

		// Actions
		add_filter( 'woocommerce_integrations', array( $this, 'woocommerce_integrations' ) );

		// Text domain
		load_plugin_textdomain( 'twinfield_woocommerce', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );

		// Post types
		add_post_type_support( 'product', 'twinfield_article' );
		add_post_type_support( 'shop_coupon', 'twinfield_article' );
		add_post_type_support( 'shop_order', 'twinfield_customer' );
		add_post_type_support( 'shop_order', 'twinfield_invoiceable' );

		// Twinfield
		add_action( 'twinfield_post_sales_invoice', array( $this, 'twinfield_post_sales_invoice' ), 20, 2 );
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

	//////////////////////////////////////////////////

	/**
	 * Twinfield post customer
	 *
	 * @param SalesInvoice $invoice
	 * @param int          $post_id
	 */
	public function twinfield_post_sales_invoice( $invoice, $post_id ) {
		if ( 'shop_order' === get_post_type( $post_id ) ) {
			// Integration
			$twinfield_integration = WC()->integrations->integrations['twinfield'];

			$twinfield_default_article_code    = get_option( 'twinfield_default_article_code' );
			$twinfield_default_subarticle_code = get_option( 'twinfield_default_subarticle_code' );

			// Order
			$order = wc_get_order( $post_id );

			// Items
			// @see https://github.com/woothemes/woocommerce/blob/2.5.3/includes/abstracts/abstract-wc-order.php#L1118-L1150
			foreach ( $order->get_items() as $item ) {
				$line = $invoice->new_line();

				// Find and article and subarticle id if set
				$article_code    = get_post_meta( $item['product_id'], '_twinfield_article_code', true );
				if ( empty( $article_code ) ) {
					$article_code = $twinfield_default_article_code;
				}

				$subarticle_code = get_post_meta( $item['product_id'], '_twinfield_subarticle_code', true );
				if ( empty( $subarticle_code ) ) {
					$subarticle_code = $twinfield_default_subarticle_code;
				}

				$line->set_article( $article_code );
				$line->set_subarticle( $subarticle_code );
				$line->set_quantity( $item['qty'] );
				$line->set_units_price_excl( $order->get_item_total( $item, false, false ) );
				$line->set_vat_code( $twinfield_integration->get_tax_class_vat_code( $item['tax_class'] ) );
				$line->set_free_text_1( $item['name'] );
			}

			// Fees
			// @see https://github.com/woothemes/woocommerce/blob/2.5.3/includes/abstracts/abstract-wc-order.php#L1221-L1228
			foreach ( $order->get_fees() as $item ) {
				$line = $invoice->new_line();

				$line->set_article( $twinfield_default_article_code );
				$line->set_subarticle( $twinfield_default_subarticle_code );
				$line->set_quantity( 1 );
				$line->set_units_price_excl( $order->get_item_total( $item, false, false ) );
				$line->set_free_text_1( __( 'Fee', 'twinfield_woocommerce' ) );
			}

			// Shipping
			// @see https://github.com/woothemes/woocommerce/blob/2.5.3/includes/abstracts/abstract-wc-order.php#L1239-L1246
			foreach ( $order->get_shipping_methods() as $item ) {
				$line = $invoice->new_line();

				$line->set_article( $twinfield_integration->get_shipping_method_article_code( $item['method_id'] ) );
				$line->set_subarticle( $twinfield_integration->get_shipping_method_subarticle_code( $item['method_id'] ) );
				$line->set_quantity( 1 );
				$line->set_units_price_excl( $item['cost'] );
				$line->set_vat_code( $twinfield_integration->get_tax_class_vat_code( get_option( 'woocommerce_shipping_tax_class' ) ) );
				$line->set_free_text_1( $item['name'] );
			}
		}

		return $invoice;
	}
}
