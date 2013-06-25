<?php

/**
 * Metabox view for the WooCommerce Order Metabox.
 * 
 * Will submit the order via ajax to the FormBuilder methods.
 * 
 * @author Leon Rowland <leon@rowland.nl>
 */

?>
<div class="WoocommerceTwinfieldSyncMessageHolder"></div>
<?php if ( $invoice_number ) : ?>
<p><?php printf( __( 'Twinfield Invoice Number: %d', 'woocommerce-twinfield' ), $invoice_number ); ?></p>
<?php endif; ?>
<p>
	<label for="WoocommerceTwinfieldSyncCustomerIDInput"><?php _e( 'Customer ID', 'woocommerce-twinfield' ); ?></label>
	<input id="WoocommerceTwinfieldSyncCustomerIDInput" class="WoocommerceTwinfieldSyncCustomerIDInput" type="text" name="woocommerce_invoice_customer_id" value="<?php echo $customer_id; ?>"/>
</p>
<input class="WoocommerceTwinfieldSyncButton button button-primary" type="submit" name="woocommerce_twinfield_sync" value="<?php _e( 'Sync', 'woocommerce-twinfield' ); ?>"/>
<span class="WoocommerceTwinfieldSyncSpinnerHolder"></span>