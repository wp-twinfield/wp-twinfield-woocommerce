<?php

/**
 * Title: Twinfield WooCommerce integration
 * Description:
 * Copyright: Copyright (c) 2005 - 2014
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_Twinfield_WooCommerce_Integration extends WC_Integration {
	/**
	 * Constructs and initialize an WooCommerce Twinfield integration class
	 */
	public function __construct() {
		$this->id           = 'twinfield';
		$this->method_title = __( 'Twinfield', 'twinfield_woocommerce' );

		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_integration_twinfield', array( $this, 'process_admin_options' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Initialize form fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'shipping_method_article_codes' => array(
				'type' => 'twinfield_codes',
			),
			'shipping_method_subarticle_codes' => array(
				'type' => 'twinfield_codes',
			),
			'tax_classes_vat_codes' => array(
				'type' => 'twinfield_codes',
			),
		);
	}

	//////////////////////////////////////////////////

	/**
	 * Admin options
	 */
	public function admin_options() {
		parent::admin_options();

		// @see https://github.com/woothemes/woocommerce/blob/v2.2.3/includes/admin/settings/class-wc-settings-tax.php#L45-L52
		$this->output_shipping_methods();
		$this->output_tax_rates_vat_codes();
	}

	//////////////////////////////////////////////////

	/**
	 * Helper function for the custom fields
	 *
	 * @param string $key
	 * @return string
	 */
	private function get_field_name( $key ) {
		return $this->plugin_id . $this->id . '_' . $key;
	}

	//////////////////////////////////////////////////
	// Helper functions
	//////////////////////////////////////////////////

	public function get_tax_class_vat_code( $tax_class ) {
		$vat_code = null;

		$tax_class = empty( $tax_class ) ? 'standard' : $tax_class;

		$tax_classes_vat_codes = $this->get_option( 'tax_classes_vat_codes' );

		if ( isset( $tax_classes_vat_codes[ $tax_class ] ) ) {
			$vat_code = $tax_classes_vat_codes[ $tax_class ];
		}

		if ( empty( $vat_code ) ) {
			$vat_code = get_option( 'twinfield_default_vat_code' );
		}

		return $vat_code;
	}

	public function get_shipping_method_article_code( $method_id ) {
		$article_code = null;

		$shipping_method_article_codes = $this->get_option( 'shipping_method_article_codes' );

		if ( isset( $shipping_method_article_codes[ $method_id ] ) ) {
			$article_code = $shipping_method_article_codes[ $method_id ];
		}

		if ( empty( $article_code ) ) {
			$article_code = get_option( 'twinfield_default_article_code' );
		}

		return $article_code;
	}

	public function get_shipping_method_subarticle_code( $method_id ) {
		$article_code = null;

		$shipping_method_article_codes = $this->get_option( 'shipping_method_subarticle_codes' );

		if ( isset( $shipping_method_article_codes[ $method_id ] ) ) {
			$article_code = $shipping_method_article_codes[ $method_id ];
		}

		if ( empty( $article_code ) ) {
			$article_code = get_option( 'twinfield_default_subarticle_code' );
		}

		return $article_code;
	}

	//////////////////////////////////////////////////
	// Custom form fields function
	//////////////////////////////////////////////////

	public function generate_twinfield_codes_html() {

	}

	public function validate_twinfield_codes_field( $key ) {
		$name = $this->get_field_name( $key );

		$data = filter_input( INPUT_POST, $name, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );

		return $data;
	}

	public function output_shipping_methods() {
		// Get the WC Shopping class
		$wc_shipping = new WC_Shipping();

		// Get all shipping methods
		$shipping_methods = $wc_shipping->load_shipping_methods();

		// Values
		$shipping_method_article_codes    = $this->get_option( 'shipping_method_article_codes' );
		$shipping_method_subarticle_codes = $this->get_option( 'shipping_method_subarticle_codes' );

		?>
		<h4><?php esc_html_e( 'Shipping Methods', 'twinfield_woocommerce' ); ?></h4>

		<p>
			<?php esc_html_e( 'You can find your articles in Twinfield under "Credit management » Items".', 'twinfield' ); ?>
		</p>

		<table class="widefat">
			<thead>
				<tr>
					<th width="25%"><?php esc_html_e( 'Method', 'twinfield_woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Article Code', 'twinfield_woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Subarticle Code', 'twinfield_woocommerce' ); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php foreach ( $shipping_methods as $shipping_method ) : ?>

					<tr>
						<?php

						$article_code = '';
						if ( isset( $shipping_method_article_codes[ $shipping_method->id ] ) ) {
							$article_code = $shipping_method_article_codes[ $shipping_method->id ];
						}

						$subarticle_code = '';
						if ( isset( $shipping_method_subarticle_codes[ $shipping_method->id ] ) ) {
							$subarticle_code = $shipping_method_subarticle_codes[ $shipping_method->id ];
						}

						?>
						<td width="25%">
							<?php echo esc_html( $shipping_method->method_title ); ?>
						</td>
						<td>
							<input type="text" value="<?php echo esc_attr( $article_code ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'shipping_method_article_codes' ) ); ?>[<?php echo esc_attr( $shipping_method->id ); ?>]" />
						</td>
						<td>
							<input type="text" value="<?php echo esc_attr( $subarticle_code ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'shipping_method_subarticle_codes' ) ); ?>[<?php echo esc_attr( $shipping_method->id ); ?>]" />
						</td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>
		<?php
	}

	public function output_tax_rates_vat_codes() {
		// Tax classes
		// @see https://github.com/woothemes/woocommerce/blob/v2.2.3/includes/admin/settings/class-wc-settings-tax.php#L45-L52
		$sections = array(
			'standard' => __( 'Standard Rates', 'woocommerce' ),
		);

		$tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );

		if ( $tax_classes ) {
			foreach ( $tax_classes as $class ) {
				$sections[ sanitize_title( $class ) ] = $class;
			}
		}

		// Values
		$tax_classes_vat_codes = $this->get_option( 'tax_classes_vat_codes' );

		?>
		<h4><?php esc_html_e( 'Tax Rates', 'twinfield_woocommerce' ); ?></h4>

		<p>
			<?php esc_html_e( 'You can find your VAT codes in Twinfield under "General » Company » VAT".', 'twinfield_woocommerce' ); ?>
		</p>

		<table class="widefat">
			<thead>
				<tr>
					<th width="25%"><?php esc_html_e( 'Rates', 'twinfield_woocommerce' ); ?></th>
					<th><?php esc_html_e( 'VAT Code', 'twinfield_woocommerce' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ( $sections as $key => $label ) : ?>

					<tr>
						<?php

						$vat_code = '';
						if ( isset( $tax_classes_vat_codes[ $key ] ) ) {
							$vat_code = $tax_classes_vat_codes[ $key ];
						}

						?>
						<td width="25%">
							<?php echo esc_html( $label ); ?>
						</td>
						<td>
							<input type="text" value="<?php echo esc_attr( $vat_code ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tax_classes_vat_codes' ) ); ?>[<?php echo esc_attr( $key ); ?>]" />
						</td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>
		<?php
	}
}
