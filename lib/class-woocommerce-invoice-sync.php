<?php

class Woocommerce_Invoice_Sync {
	
	private $woocommerce_invoice;
	
	public function __construct() {
		add_action( 'wp_ajax_woocommerce_twinfield_sync', array( $this, 'ajax_sync' ) );
	}
	
	public function sync( $post_id ) {
		// Get the WC Order
		$wc_order = new WC_Order( $post_id );
		
		// Get the Woocommerce Invoice
		$this->woocommerce_invoice = new Woocommerce_Invoice( $wc_order );
		
		
		if ( $this->woocommerce_invoice->submit() ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function ajax_sync() {
		if ( ! filter_has_var( INPUT_POST, 'post_id' ) )
			echo json_encode( array( 'ret' => false ) );
		
		$state = $this->sync( filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT ) );
		
		if ( true === $state ) {
			echo json_encode( array( 'ret' => true, 'msg' => __( 'Successfully synced', 'woocommerce-twinfield' ) ) );
		} else {
			echo json_encode( array( 'ret' => false, 'msgs' => $this->get_response()->getErrorMessages() ) );
		}
		
		exit;
	}
	
	public function get_response() {
		return $this->woocommerce_invoice->get_response();
	}
	
}