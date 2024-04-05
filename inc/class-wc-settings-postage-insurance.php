<?php
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WC_Settings_Postage_Insurance', false ) ) {
	return new WC_Settings_Postage_Insurance();
}

class WC_Settings_Postage_Insurance extends WC_Settings_Page {

	public function __construct() {
		$this->id    = 'wcpi';
		$this->label = __( 'Postage Insurance', 'wcpi' );

		parent::__construct();
	}

	protected function get_settings_for_default_section() {
		$settings = array(
			array(
				'type'  => 'title',
				'id'    => 'wcpi_fields',
				'title' => __( 'Postage Insurance', 'wcpi' ),
				'desc'  => __( 'Edit the value of your postage insurance.', 'wcpi' ),
			),
			array(
				'type'    => 'checkbox',
				'id'      => 'wcpi_enabled',
				'default' => '',
				'title'   => __( 'Enabled?', 'wcpi' ),
				'desc'    => __( 'Add postage insurance option to cart/checkout pages.', 'wcpi' ),
			),
			array(
				'type'    => 'text',
				'id'      => 'wcpi_fee',
				'default' => '10',
				'title'   => __( 'Fee Amount', 'wcpi' ),
				'desc'    => __( 'The cost of postage insurance.', 'wcpi' ),
			),
			array(
				'type'    => 'checkbox',
				'id'      => 'wcpi_taxable',
				'default' => '',
				'title'   => __( 'Taxable?', 'wcpi' ),
				'desc'    => __( 'Is the postage insurance taxable.', 'wcpi' ),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wcpi_fields',
			),
		);

		return apply_filters( 'woocommerce_postage_insurance_settings', $settings );
	}
}

return new WC_Settings_Postage_Insurance();
