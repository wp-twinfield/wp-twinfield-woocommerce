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

	<p>
		<label for="WoocommerceTwinfieldSyncInvoiceIDInput"><?php _e( 'Twinfield Invoice ID', 'twinfield_woocommerce' ); ?></label>
		<input id="WoocommerceTwinfieldSyncInvoiceIDInput" class="small-text" type="text" name="woocommerce_invoice_id" value="<?php echo esc_attr( $invoice_number ); ?>" />
	</p>

<?php endif; ?>

<p>
	<label for="WoocommerceTwinfieldSyncCustomerIDInput"><?php _e( 'Customer ID', 'twinfield_woocommerce' ); ?></label>
	<input id="WoocommerceTwinfieldSyncCustomerIDInput" class="WoocommerceTwinfieldSyncCustomerIDInput" type="text" name="woocommerce_invoice_customer_id" value="<?php echo esc_attr( $customer_id ); ?>" />
</p>

<?php if ( $invoice_number ) : ?>

	<a class="button" target="_blank" href="<?php echo esc_attr( twinfield_admin_view_invoice_link( $invoice_number ) ); ?>"><?php _e( 'View', 'twinfield_woocommerce' ); ?></a>

<?php endif; ?>

<input class="WoocommerceTwinfieldSyncButton button button-primary" type="submit" name="woocommerce_twinfield_sync" value="<?php esc_attr_e( 'Sync', 'twinfield_woocommerce' ); ?>" />

<span class="WoocommerceTwinfieldSyncSpinnerHolder"></span>
