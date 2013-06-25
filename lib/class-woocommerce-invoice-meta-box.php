<?php

class Woocommerce_Invoice_Meta_Box {
	
	public $woocommerce_invoice_sync;
	
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		
		$this->woocommerce_invoice_sync = new Woocommerce_Invoice_Sync();
	}
	
	public function add_meta_boxes() {
		add_meta_box(
			'woocommerce_invoice_meta_box',
			__( 'WooCommerce Invoice Sync', 'woocommerce_twinfield' ),
			array( $this, 'view' ),
			'shop_order',
			'side',
			'high'
		);
	}
	
	public function view( $post ) {
		global $woocommerce_twinfield;
		
		// Get the admin script
		wp_enqueue_script( 'woocommerce_twinfield_sync_js', plugins_url( '/admin/woocommerce_twinfield_sync.js', $woocommerce_twinfield->plugin_file() ), array( 'jquery' ) );
		wp_localize_script( 'woocommerce_twinfield_sync_js', 'Woocommerce_Twinfield_Vars', array(
			'spinner' => admin_url( 'images/wpspin_light.gif' )
		) );
		
		$wc_order = new WC_Order();
		$wc_order->populate( $post );
		
		$invoice = new \Woocommerce_Invoice( $wc_order );
		$invoice_number = $invoice->check_for_twinfield_invoice_number();
		$customer_id = $invoice->check_for_twinfield_customer_id();
		
		$view = new \ZFramework\Base\View( $woocommerce_twinfield->plugin_folder() . '/views' );
		$view
			->setView( 'woocommerce_invoice_meta_box_view' )
			->setVariable( 'customer_id', $customer_id )
			->setVariable( 'invoice_number', $invoice_number )
			->render();
	}
	
	public function save( $post_id, $post ) {
		if ( ! filter_has_var( INPUT_POST, 'woocommerce_twinfield_sync' ) )
			return;
		
		$customer_id = filter_input( INPUT_POST, 'woocommerce_invoice_customer_id', FILTER_SANITIZE_STRING );
		
		$this->woocommerce_invoice_sync->sync( $post_id, $customer_id );
	}
}