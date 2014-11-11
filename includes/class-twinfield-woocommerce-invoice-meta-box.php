<?php

class Pronamic_Twinfield_WooCommerce_InvoiceMetaBox {
	public function prepare_sync( $post_id ) {
		$wc_order = new WC_Order( $post_id );

		return new Pronamic_Twinfield_WooCommerce_Invoice( $wc_order );
	}
}
