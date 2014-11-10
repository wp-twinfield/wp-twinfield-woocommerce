<?php

class Woocommerce_InvoiceMetaBox {
    public function prepare_sync($post_id, $customer_id, $invoice_id = null, $invoice_type = null ) {
        $wc_order = new WC_Order( $post_id );

        return new WooCommerce_Invoice( $wc_order, $customer_id, $invoice_id, $invoice_type );
    }
}
