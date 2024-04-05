<?php
/**
 * Plugin Name: WooCommerce Postage Insurance
 * Author: Nathan
 * Version: 0.0.1
 * Requires Plugins: woocommerce
 *
 * @package wcpi
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( is_admin() ) {
	add_filter(
		'woocommerce_get_settings_pages',
		function ( $settings ) {
			$settings[] = include plugin_dir_path( __FILE__ ) . '/inc/class-wc-settings-postage-insurance.php';
			return $settings;
		}
	);
}

// Display postage insurance checkbox.
function wcpi_display_postage_insurance_field() {
	wp_enqueue_script( 'wpi-postage-insurance' );

	$insurance = WC()->session->get( 'postage_insurance' );

	?>
	<tr class="cart-postage-insurance">
		<th>
			<label for="postage_insurance">Add Postage Insurance?</label>
		</th>
		<td data-title="Postage Insurance">
			<input type="checkbox" name="postage_insurance" id="postage_insurance" value="1" <?php checked( $insurance ); ?> />
			<small>(+1% of Cart Total)</small>
		</td>
	</tr>
	<?php
}
add_action( 'woocommerce_cart_totals_after_shipping', 'wcpi_display_postage_insurance_field' );
add_action( 'woocommerce_review_order_after_shipping', 'wcpi_display_postage_insurance_field' );


// Load script to update totals for checkbox.
function wcpi_load_script() {
	wp_register_script(
		'wpi-postage-insurance',
		plugins_url( 'assets/js/postage-insurance.js', __FILE__ ),
		array( 'jquery' ),
		'0.0.1',
		true
	);

	wp_localize_script(
		'wpi-postage-insurance',
		'wc_cart_fragments_params',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'postage_insurance' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wcpi_load_script' );


// Function to handle AJAX request to update cart totals.
function wcpi_update_postage_insurance() {
	// Get checkbox state.
	if ( ! empty(
		$_POST['postage_nonce']
	) && ! wp_verify_nonce( $_POST['postage_nonce'], 'postage_insurance' ) ) {
		echo json_encode(
			array(
				'error'   => true,
				'message' => 'nonce error',
			)
		); // Return error if nonce fails.
		die();
	}

	$postage_insurance = false;
	if ( isset( $_POST['postage_insurance'] ) ) {
		$postage_insurance = json_decode( $_POST['postage_insurance'] );
	}

	// Update session true or false.
	WC()->session->set( 'postage_insurance', $postage_insurance );

	// Check if request is from cart or checkout.
	$is_checkout = isset( $_POST['checkout'] ) && ! empty( $_POST['checkout'] );

	// Output recalculated totals.
	wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
	WC()->cart->calculate_totals();
	if ( $is_checkout ) {
		woocommerce_order_review();
	} else {
		woocommerce_cart_totals();
	}

	wp_die();
}
add_action( 'wp_ajax_update_postage_insurance', 'wcpi_update_postage_insurance' );
add_action( 'wp_ajax_nopriv_update_postage_insurance', 'wcpi_update_postage_insurance' );


/**
 * Add the custom fee to the cart.
 *
 * @param WC_Cart $cart  The WooCommerce Cart object.
 * @return void
 */
function wcpi_add_fees( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	$insurance = WC()->session->get( 'postage_insurance' );

	if ( $insurance ) {
		// Get fee amount from options.
		$fee       = get_option( 'woocommerce_postage_insurance_cost', 10 );
		$taxable   = get_option( 'woocommerce_postage_insurance_taxable', false );
		$tax_class = get_option( 'woocommerce_postage_insurance_tax_class', '' );

		// If no cost set, return -1 so it can be hidden.
		if ( empty( $fee ) || ! is_numeric( $fee ) ) {
			$amount = '-1';
		} else {
			$amount = wc_format_decimal( $fee );
		}

		// Add custom fee.
		$cart->add_fee( __( 'Postage Insurance', 'wcpi' ), $amount, $taxable, $tax_class );
	}
}
add_action( 'woocommerce_cart_calculate_fees', 'wcpi_add_fees' );

function wcpi_save_postage_insurance_checkbox( $order_id ) {
	if ( isset( $_POST['postage_insurance'] ) ) {
		update_post_meta( $order_id, 'postage_insurance', true );
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'wcpi_save_postage_insurance_checkbox' );

/**
 * Display postage insurance details on the order edit page.
 *
 * @param WC_Order $order  Order object.
 * @return void
 */
function wcpi_display_postage_insurance_order_meta( $order ) {
	// Check if order has postage insurance meta data.
	// Data is saved as 1 or 0, covert to bool.
	$postage_insurance = (bool) get_post_meta( $order->get_id(), 'postage_insurance', true );

	if ( $postage_insurance ) {
		echo '<p><strong>Postage Insurance:</strong> Yes</p>';
		$fees = $order->get_fees();
		foreach ( $fees as $fee ) {
			if ( strpos( $fee->get_name(), 'Postage Insurance' ) !== false && $fee->get_total() != 0 ) {
				echo '<p><strong>Postage Insurance Cost:</strong> ' . wc_price( $fee->get_total() ) . '</p>';
			}
		}
	} else {
		echo '<p><strong>Postage Insurance:</strong> No</p>';
	}
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'wcpi_display_postage_insurance_order_meta', 10, 1 );
