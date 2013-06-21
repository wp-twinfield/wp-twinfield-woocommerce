<?php

class Woocommerce_Invoice_Meta_Box {
	
	private $woocommerce_invoice_sync;
	
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
		
		$view = new \ZFramework\Base\View( $woocommerce_twinfield->plugin_folder() . '/views' );
		$view
			->setView( 'woocommerce_invoice_meta_box_view' )
			->render();
	}
	
	public function save( $post_id, $post ) {
		if ( ! filter_has_var( INPUT_POST, 'woocommerce_twinfield_sync' ) )
			return;
		
		$this->woocommerce_twinfield_sync->sync( $post_id );
	}
}