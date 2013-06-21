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

class Woocommerce_Invoice extends \Pronamic\WP\Twinfield\FormBuilder\Form\Invoice {
	
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
	 * Currently sets a key of 'products' that hold all
	 * the products in WooCommerce.  Allows the article/subarticle
	 * inputs to be dropdowns.
	 * 
	 * @overide
	 * 
	 * @access public
	 * @return void
	 */
	public function prepare_extra_variables() {
		parent::prepare_extra_variables();

		$all_products = $this->get_all_products();
		$this->set_extra_variables( 'products', $all_products );
	}

	/**
	 * Called from inside prepare_extra_variables that gives back the 
	 * array of all products
	 * 
	 * @access public
	 * @return array
	 */
	public function get_all_products() {
		$products_query = new WP_Query( array(
			'post_type'		 => 'product',
			'posts_per_page' => -1
			) );

		return $products_query->posts;
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
	public function prepare_invoice( WC_Order $order ) {
		// Array for holding data for fill_class() method
		$fill_class_data = array( );

		$order_items = $order->get_items();

		$fill_class_data['lines'] = array( );
		foreach ( $order_items as $item_id => $item ) {
			
			$_product = get_product( $item_id );
			
			$article_information = get_post_meta( $item_id, '_twinfield_article', true );

			// Find and article and subarticle id if set
			$article_id		 = ( isset( $article_information['article_id'] ) ) ? $article_information['article_id'] : '';
			$subarticle_id	 = ( isset( $article_information['subarticle_id'] ) ) ? $article_information['subarticle_id'] : '';

			// Data for the lines
			$fill_class_data['lines'][] = array(
				'article'	 => $article_id,
				'subarticle' => $subarticle_id,
				'quantity'	 => $_product->get_stock_quantity(),
				'units' => $_product->get_price()
			);
		}

		return $fill_class_data;
	}

}