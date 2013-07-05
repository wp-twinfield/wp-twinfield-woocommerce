<?php

class Woocommerce_Invoice_Sync {
	
	private $woocommerce_invoice;
	
	public function __construct() {
		add_action( 'wp_ajax_woocommerce_twinfield_sync', array( $this, 'ajax_sync' ) );
	}
	
	public function sync( $post_id, $customer_id ) {
		// Get the WC Order
		$wc_order = new WC_Order( $post_id );
		
		// Get the Woocommerce Invoice
		$this->woocommerce_invoice = new WooCommerce_Invoice( $wc_order, $customer_id );
		
		if ( $this->woocommerce_invoice->submit() ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function ajax_sync() {
		if ( ! filter_has_var( INPUT_POST, 'post_id' ) )
			echo json_encode( array( 'ret' => false ) );
		
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		$customer_id = filter_input( INPUT_POST, 'customer_id', FILTER_VALIDATE_INT );
		
		$state = $this->sync( $post_id, $customer_id );
		
		if ( true === $state ) {
			$this->woocommerce_invoice->successful();
			
			echo json_encode( array( 'ret' => true, 'msg' => __( 'Successfully synced', 'woocommerce-twinfield' ) ) );
		} else {
			echo json_encode( array( 'ret' => false, 'msgs' => $this->get_response()->getErrorMessages(), 'adv' => $this->woocommerce_invoice ) );
		}
		
		exit;
	}
	
	public function get_response() {
		return $this->woocommerce_invoice->get_response();
	}
	
}