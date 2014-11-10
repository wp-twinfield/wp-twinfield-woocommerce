<?php

class WoocommerceTwinfield_Integration extends WC_Integration {
	/**
	 * Constructs and initialize an WooCommerce Twinfield integration class
	 */
	public function __construct() {
		$this->id                 = 'twinfield';
		$this->method_title       = __( 'Twinfield', 'twinfield_woocommerce' );
		$this->method_description = __( 'Settings for the WooCommerce Twinfield integration.', 'twinfield_woocommerce' );
		
		$this->init_form_fields();
		$this->init_settings();
		
		$this->discount_article_id    = $this->get_option( 'discount_article_id' );
		$this->discount_subarticle_id = $this->get_option( 'discount_subarticle_id' );
		$this->shipping_article_id    = $this->get_option( 'shipping_article_id' );
		$this->shipping_subarticle_id = $this->get_option( 'shipping_subarticle_id' );
		
		add_action( 'woocommerce_update_options_integration_twinfield', array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize form fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'twinfield_discount' => array(
				'title'       => __( 'Discount', 'twinfield_woocommerce' ),
				'type'        => 'title',
				'description' => __( 'You can supply just the Article ID or even a specific Subarticle ID.  It is only required that you fill in the ArticleID field', 'twinfield_woocommerce' ),
			),			
			'discount_article_id' => array(
				'title'       => __( 'Discount Article ID', 'twinfield_woocommerce' ),
				'description' => __( 'Article ID for the Discount Article from Twinfield', 'twinfield_woocommerce' ),
				'type'        => 'text',
				'default'     => '',
			),
			'discount_subarticle_id' => array(
				'title'       => __( 'Discount Subarticle ID', 'twinfield_woocommerce' ),
				'description' => __( 'Subarticle ID for the Discount Article from Twinfield. <i>(optional)</i>', 'twinfield_woocommerce' ),
				'type'        => 'text',
				'default'     => '',
			),			
			'twinfield_shipping' => array(
				'title'       => __( 'Shipping', 'twinfield_woocommerce' ),
				'type'        => 'title',
				'description' => __( 'You can supply just the Article ID or even a specific Subarticle ID.  It is only required that you fill in the ArticleID field<br/>If you have all your shipping methods under 1 Article, then fill the same Article ID for all shipping methods below', 'twinfield_woocommerce' ),
			),		
			'add_shipping_method_to_freetext' => array(
				'title'       => __( 'Add Shipping Method to FreeText field in Invoice?', 'twinfield_woocommerce' ),
				'type'        => 'select',
				'options'     => array(
					'0' => __( 'No', 'twinfield_woocommerce' ),
					'1' => __( 'Yes', 'twinfield_woocommerce' ),
				),
			),
		);
		
		// Get the WC Shopping class
		$wc_shipping = new WC_Shipping();
		
		// Get all shipping methods
		$shipping_methods = $wc_shipping->load_shipping_methods();
		
		foreach ( $shipping_methods as $shipping_method ) {
			$this->form_fields['shipping_title_method_' . $shipping_method->id ] = array(
				'title' => $shipping_method->method_title,
				'type'  => 'title',
			);
			
			$this->form_fields['shipping_article_id_method_' . $shipping_method->id ] = array(
				'title' => __( 'Article ID', 'twinfield_woocommerce' ),
				'type'  => 'text',
			);
			
			$this->form_fields['shipping_subarticle_id_method_' . $shipping_method->id ] = array(
				'title' => __( 'Subarticle ID', 'twinfield_woocommerce' ),
				'type'  => 'text',
			);
		}
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
			return $settings[$key];
		} else {
			return false;
		}
	}
	
	public static function get_shipping_subarticle_id( $shipping_method ) {
		$settings = self::get_twinfield_settings();
		
		$key = 'shipping_subarticle_id_method_' . $shipping_method;
		
		if ( array_key_exists( $key, $settings) ) {
			return $settings[$key];
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
	$integrations[] = 'WoocommerceTwinfield_Integration';
	return $integrations;
}

add_filter( 'woocommerce_integrations', 'add_woocommerce_twinfield_integration' );
