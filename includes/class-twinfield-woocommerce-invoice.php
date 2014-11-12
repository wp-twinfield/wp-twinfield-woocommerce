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

class Pronamic_Twinfield_WooCommerce_Invoice extends \Pronamic\WP\Twinfield\FormBuilder\Form\Invoice {
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
	 * Sets some extra variables for the FormBuilder UI.
	 *
	 * Currently sets a key of 'orders' that hold all
	 * the orders in WooCommerce.
	 *
	 * @overide
	 *
	 * @access public
	 * @return void
	 */
	public function prepare_extra_variables() {
		parent::prepare_extra_variables();

		$all_orders = $this->get_all_orders();
		$this->set_extra_variables( 'orders', $all_orders );
	}

	/**
	 * Called from inside prepare_extra_variables that gives back the
	 * array of all orders
	 *
	 * @access public
	 * @return array
	 */
	public function get_all_orders() {
		$orders_query = new WP_Query( array(
			'post_type'      => 'shop_order',
			'posts_per_page' => -1,
		) );

		return $orders_query->posts;
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
				'vatcode'        => 'VH',
				'freetext1'      => $order->get_line_tax( $item )
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

		if ( '0.00' != $order->get_shipping_tax() ) {
			// Add line to explanation
			$explanation_text .= sprintf( __( 'Shipping cost: %s', 'twinfield_woocommerce' ), $order->get_shipping_tax() );
			$explanation_text .= "\r\n";

			// Get shipping article/subarticle
			$shipping_article_id = Pronamic_Twinfield_WooCommerce_Integration::get_shipping_article_id( $order->shipping_method );
			$shipping_subarticle_id = Pronamic_Twinfield_WooCommerce_Integration::get_shipping_subarticle_id( $order->shipping_method );

			$shipping_line = array(
				'active'         => true,
				'article'        => $shipping_article_id,
				'subarticle'     => ( isset( $shipping_subarticle_id ) ? $shipping_subarticle_id : '' ),
				'quantity'       => 1,
				'unitspriceexcl' => $order->get_shipping_tax(),
				'vatcode'        => 'VN',
			);

			if ( Pronamic_Twinfield_WooCommerce_Integration::add_shipping_method_to_freetext() ) {
				$shipping_line['freetext1'] = $order->get_shipping_method();
			}

			// Shipping Fees
			$fill_class_data['lines'][] = $shipping_line;
		}

		///////
		// Discounts
		///////

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

		/////////
		// Finalization
		/////////

		// Set the explanation to the header text.
		$fill_class_data['headertext'] = $explanation_text;

		return $fill_class_data;
	}
}
