<?php

class Pronamic_Twinfield_WooCommerce_Integration extends WC_Integration {
	/**
	 * Constructs and initialize an WooCommerce Twinfield integration class
	 */
	public function __construct() {
		$this->id           = 'twinfield';
		$this->method_title = __( 'Twinfield', 'twinfield_woocommerce' );

		$this->init_form_fields();
		$this->init_settings();

		$this->discount_article_id    = $this->get_option( 'discount_article_id' );
		$this->discount_subarticle_id = $this->get_option( 'discount_subarticle_id' );
		$this->shipping_article_id    = $this->get_option( 'shipping_article_id' );
		$this->shipping_subarticle_id = $this->get_option( 'shipping_subarticle_id' );

		add_action( 'woocommerce_update_options_integration_twinfield', array( $this, 'process_admin_options' ) );
	}

	private function get_field_name( $key ) {
		return $this->plugin_id . $this->id . '_' . $key;
	}

	public function admin_options() {
		parent::admin_options();

		// @see https://github.com/woothemes/woocommerce/blob/v2.2.3/includes/admin/settings/class-wc-settings-tax.php#L45-L52
		$this->output_shipping_methods();
		$this->output_tax_rates_vat_codes();
	}

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
		<h4>Shipping Methods</h4>

		<p>
			<?php _ex( 'You can find your articles in Twinfield under "Credit management » Items".', 'twinfield.com', 'twinfield' ); ?>
		</p>

		<table class="widefat">
			<thead>
				<tr>
					<th width="25%">Method</th>
					<th>Article Code</th>
					<th>Subarticle Code</th>
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
							<?php echo $shipping_method->method_title; ?>
						</td>
						<td>
							<input type="text" value="<?php echo esc_attr( $article_code ); ?>" name="<?php echo $this->get_field_name( 'shipping_method_article_codes' ); ?>[<?php echo esc_attr( $shipping_method->id ); ?>]" />
						</td>
						<td>
							<input type="text" value="<?php echo esc_attr( $subarticle_code ); ?>" name="<?php echo $this->get_field_name( 'shipping_method_subarticle_codes' ); ?>[<?php echo esc_attr( $shipping_method->id ); ?>]" />
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
			'standard' => __( 'Standard Rates', 'woocommerce' )
		);

		$tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option('woocommerce_tax_classes' ) ) ) );

		if ( $tax_classes ) {
			foreach ( $tax_classes as $class ) {
				$sections[ sanitize_title( $class ) ] = $class;
			}
		}

		// Values
		$tax_classes_vat_codes = $this->get_option( 'tax_classes_vat_codes' );

		?>
		<h4>Tax Rates</h4>

		<p>
			<?php _ex( 'You can find your VAT codes in Twinfield under "General » Company » VAT".', 'twinfield.com', 'twinfield_woocommerce' ); ?>
		</p>

		<table class="widefat">
			<thead>
				<tr>
					<th width="25%">Rates</th>
					<th>VAT Code</th>
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
							<?php echo $label; ?>
						</td>
						<td>
							<input type="text" value="<?php echo esc_attr( $vat_code ); ?>" name="<?php echo $this->get_field_name( 'tax_classes_vat_codes' ); ?>[<?php echo esc_attr( $key ); ?>]" />
						</td>
					</tr>

				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Initialize form fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'twinfield_discount' => array(
				'title'       => __( 'Discount', 'twinfield_woocommerce' ),
				'type'        => 'title',
			),
			'discount_article_id' => array(
				'title'       => __( 'Article ID', 'twinfield_woocommerce' ),
				'type'        => 'text',
			),
			'discount_subarticle_id' => array(
				'title'       => __( 'Subarticle ID', 'twinfield_woocommerce' ),
				'type'        => 'text',
			),
			'shipping_method_article_codes' => array(
				'type'        => 'twinfield_codes',
			),
			'shipping_method_subarticle_codes' => array(
				'type'        => 'twinfield_codes',
			),
			'tax_classes_vat_codes' => array(
				'type'        => 'twinfield_codes',
			),
		);
	}

	public static function get_twinfield_settings() {
		return get_option( 'woocommerce_twinfield_settings', array() );
	}

	public static function get_discount_article_id() {
		$settings = self::get_twinfield_settings();

		return $settings['discount_article_id'];
	}

	public static function get_discount_subarticle_id() {
		$settings = self::get_twinfield_settings();

		return $settings['discount_subarticle_id'];
	}

	public static function get_shipping_article_id( $shipping_method ) {
		$settings = self::get_twinfield_settings();

		$key = 'shipping_article_id_method_' . $shipping_method;

		if ( array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		} else {
			return false;
		}
	}

	public static function get_shipping_subarticle_id( $shipping_method ) {
		$settings = self::get_twinfield_settings();

		$key = 'shipping_subarticle_id_method_' . $shipping_method;

		if ( array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		} else {
			return false;
		}
	}

	public static function add_shipping_method_to_freetext() {
		$settings = self::get_twinfield_settings();

		if ( array_key_exists( 'add_shipping_method_to_freetext', $settings ) ) {
			return $settings['add_shipping_method_to_freetext'];
		} else {
			return false;
		}
	}
}

function add_woocommerce_twinfield_integration( $integrations ) {
	$integrations[] = 'Pronamic_Twinfield_WooCommerce_Integration';
	return $integrations;
}

add_filter( 'woocommerce_integrations', 'add_woocommerce_twinfield_integration' );
