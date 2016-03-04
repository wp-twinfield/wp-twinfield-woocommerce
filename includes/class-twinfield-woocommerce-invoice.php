<?php

/**
 * FormBuilder Form Invoice Extended Class to support WooCommerce Orders
 *
 * Call an instance of this Invoice and pass into it, the WC_Order you wish
 * to create an entry for.
 *
 * @package FormBuilder\Form\Invoice
 * @subpackage WooCommerce Invoice
 *
 * @author Leon Rowland <leon@rowland.nl>
 * @version 1.0.0
 */

class Pronamic_Twinfield_WooCommerce_Invoice {
	/**
	 * Holds the passed in WC_Order instance
	 * from instantiation
	 *
	 * @var WC_Order
	 */
	private $order;

	/**
	 * Holds the WC Order in the class for use when
	 * fill_class is called.
	 *
	 * The $order must be optional to support the base
	 * FormBuilder UI
	 *
	 * @access public
	 * @param WC_Order $order OPTIONAL
	 * @return void
	 */
	public function __construct( WC_Order $order = null ) {
		$this->order = $order;
	}

	/**
	 * Checks to see if a WC_Order is set, then get the
	 * data from prepare_invoice method and use that
	 * instead of the first passed data.
	 *
	 * Calls parent::fill_class()
	 *
	 * @overide
	 *
	 * @access public
	 * @param array $data
	 * @return object
	 */
	public function fill_class( array $data ) {
		if ( null !== $this->order ) {
			$data = $this->prepare_invoice( $this->order );
		}

		return parent::fill_class( $data );
	}

	/**
	 * Maps the WC_Order to the required array for the fill_class method
	 *
	 * Is called from this child classes fill_class method, where if the
	 * WC_Order exists from instantiation then the data returned here overides
	 * the default data passed into fill_class()
	 *
	 * You can just also call this public method and manually pass the data
	 * into fill_class or use for any other purpose.
	 *
	 * @access public
	 * @param WC_Order $order
	 * @return array
	 */
	public function prepare_invoice( WC_Order $order = null ) {
		if ( $order ) {
			$this->order = $order;
		}

		// Twinfield WooCommerce integration
		$twinfield_integration = WC()->integrations->integrations['twinfield'];

		// Array for holding data for fill_class() method
		$fill_class_data = array();
		$fill_class_data['customerID']    = get_post_meta( $order->id, '_twinfield_customer_id', true );
		$fill_class_data['invoiceType']   = get_option( 'twinfield_default_invoice_type' );
		$fill_class_data['invoiceNumber'] = get_post_meta( $order->id, '_twinfield_invoice_number', true );

		/////////
		// Header
		/////////

		$explanation_text  = __( 'Invoice created by WooCommerce', 'twinfield_woocommerce' );
		$explanation_text .= "\r\n";

		////////
		// Products
		////////

		// Get all ordered products
		$order_items = $this->order->get_items();

		// Add line to explanation
		$explanation_text .= sprintf( __( '%d items ordered: ', 'twinfield_woocommerce' ), count( $order_items ) );
		$explanation_text .= "\r\n";

		// Prepare the lines for the form
		$fill_class_data['lines'] = array();

		// Go through all the products and add the items order information
		foreach ( $order_items as $item ) {
			// Find and article and subarticle id if set
			$article_code    = get_post_meta( $item['product_id'], '_twinfield_article_code', true );
			if ( empty( $article_code ) ) {
				$article_code = get_option( 'twinfield_default_article_code' );
			}

			$subarticle_code = get_post_meta( $item['product_id'], '_twinfield_subarticle_code', true );
			if ( empty( $subarticle_code ) ) {
				$subarticle_code = get_option( 'twinfield_default_subarticle_code' );
			}

			// Data for the lines
			$fill_class_data['lines'][] = array(
				'active'         => true,
				'article'	     => $article_code,
				'subarticle'     => $subarticle_code,
				'quantity'       => $item['qty'],
				'unitspriceexcl' => $order->get_item_total( $item, false, false ),
				'vatcode'        => $twinfield_integration->get_tax_class_vat_code( $item['tax_class'] ),
				'freetext1'      => $item['name'],
				'freetext2'      => $order->get_line_tax( $item ),
			);

			$explanation_text .= sprintf(
				_x( '----- %d %s at %s', '[Quantity] [Product Name] at [Price]', 'twinfield_woocommerce' ),
				$item['qty'],
				$item['name'],
				$order->get_item_total( $item, false )
			);

			$explanation_text .= "\r\n";
		}

		////////
		// Shipping
		////////

		// @see https://github.com/woothemes/woocommerce/blob/2.2.8/includes/class-wc-tax.php#L364-L504
		$line_items_shipping = $order->get_items( 'shipping' );

		$shipping_tax_class = get_option( 'woocommerce_shipping_tax_class' );

		if ( '' == $shipping_tax_class ) {
			$tax_classes = array();

			foreach ( $this->order->get_items() as $item ) {
				$tax_classes[] = $item['tax_class'];
			}

			$tax_classes = array_unique( $tax_classes );

			global $wpdb;

			$query = "
				SELECT tax_rate_class
				FROM {$wpdb->prefix}woocommerce_tax_rates
				WHERE tax_rate_class IN ('" . implode( "','", $tax_classes ) . "')
				ORDER BY tax_rate
				LIMIT 1
				;";

			$tax_class = $wpdb->get_var( $query );

			$shipping_tax_class = $tax_class;
		}

		$vat_code = $twinfield_integration->get_tax_class_vat_code( $shipping_tax_class );

		foreach ( $line_items_shipping as $item ) {
			$shipping_taxes = isset( $item['taxes'] ) ? $item['taxes'] : '';
			$tax_data       = maybe_unserialize( $shipping_taxes );

			$fill_class_data['lines'][] = array(
				'active'         => true,
				'article'        => $twinfield_integration->get_shipping_method_article_code( $item['method_id'] ),
				'subarticle'     => $twinfield_integration->get_shipping_method_subarticle_code( $item['method_id'] ),
				'quantity'       => 1,
				'unitspriceexcl' => $item['cost'],
				'vatcode'        => $vat_code,
				'freetext1'      => $item['name'],
			);
		}

		///////
		// Fee
		///////

		/*
		// @see https://github.com/woothemes/woocommerce/blob/2.2.8/includes/admin/meta-boxes/views/html-order-items.php#L121
		$coupons = $order->get_items( array( 'coupon' ) );

		foreach ( $coupons as $item_id => $item ) {
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $item['name'] ) );

			var_dump( $post_id );
			var_dump( $item_id );
			var_dump( $item );
		}

		var_dump( $coupons );exit;

		if ( '0.00' != $order->get_order_discount() || '0.00' != $order->get_cart_discount() ) {
			// Add line to explanation
			$explanation_text .= sprintf( __( 'Cart Discount: %s', 'twinfield_woocommerce' ), $order->get_cart_discount() );
			$explanation_text .= "\r\n";
			$explanation_text .= sprintf( __( 'Order Discount: %s', 'twinfield_woocommerce' ), $order->get_order_discount() );
			$explanation_text .= "\r\n";

			// Get discount article/subarticle
			$discount_article_id = Pronamic_Twinfield_WooCommerce_Integration::get_discount_article_id();
			$discount_subarticle_id = Pronamic_Twinfield_WooCommerce_Integration::get_discount_subarticle_id();

			$discount_line = array(
				'active'         => true,
				'article'        => $discount_article_id,
				'subarticle'     => ( isset( $discount_subarticle_id ) ? $discount_subarticle_id : '' ),
				'quantity'       => 1,
				'unitspriceexcl' => - abs( $order->get_order_discount() ),
				'vatcode'        => 'VN',
			);

			// Discounts
			$fill_class_data['lines'][] = $discount_line;
		}
		*/

		/////////
		// Finalization
		/////////

		// Set the explanation to the header text.
		$fill_class_data['headertext'] = $explanation_text;

		return $fill_class_data;
	}
}
