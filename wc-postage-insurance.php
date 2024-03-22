<?php
/**
 * Plugin Name: WooCommerce Postage Insurance
 * Author: Nathan
 * Version: 0.0.1
 *
 * @package wpi
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


// Display postage insurance checkbox.
function wpi_display_postage_insurance_input() {
	// Load script
	wp_enqueue_script( 'wpi-postage_insurance' );
	?>
<tr class="cart-postage-insurance">
	<th>
		<label for="postage_insurance">Add Postage Insurance?</label>
	</th>
	<td data-title="Postage Insurance">
		<input type="checkbox" name="postage_insurance" id="postage_insurance" value="1" <?php checked( WC()->session->get( 'postage_insurance' ), 'yes' ); ?>>
		<small>(+1% of Cart Total)</small>
	</td>
</tr>
	<?php
}
add_action( 'woocommerce_cart_totals_after_shipping', 'wpi_display_postage_insurance_input' );
add_action( 'woocommerce_review_order_after_shipping', 'wpi_display_postage_insurance_input' );


// Load script to update totals for checkbox.
function wpi_load_script() {
	wp_enqueue_script(
		'wpi-postage-insurance',
		plugins_url( 'assets/js/postage-insurance.js', __FILE__ ),
		array( 'jquery', 'wc-checkout', 'wc-cart' ),
		false,
		true
	);

	wp_localize_script(
		'wpi-postage-insurance',
		'wc_cart_fragments_params',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wpi_load_script' );


// Function to handle AJAX request to update cart totals
function update_cart_totals_callback() {
	// Get checkbox state
	$postage_insurance = isset( $_POST['postage_insurance'] ) ? sanitize_text_field( $_POST['postage_insurance'] ) : '';

	// Update session if postage_insurance  is checked or unchecked
	if ( $postage_insurance ) {
		WC()->session->set( 'postage_insurance', $postage_insurance );
	}

	// Return success message
	echo 'success';

	// Always use die() at the end of ajax functions to avoid issues
	die();
}
add_action( 'wp_ajax_update_cart_totals', 'update_cart_totals_callback' );
add_action( 'wp_ajax_nopriv_update_cart_totals', 'update_cart_totals_callback' );


function woocommerce_custom_surcharge( $cart ) {
	global $woocommerce;

	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	$insurance = WC()->session->get( 'postage_insurance' );

	if ( $insurance ) {
		$surcharge = 10;
		$cart->add_fee( 'Postage Insurance', $surcharge, true, '' );
	}
}
add_action( 'woocommerce_cart_calculate_fees', 'woocommerce_custom_surcharge' );

function save_postage_insurance_checkbox( $order_id ) {
	if ( isset( $_POST['postage_insurance'] ) ) {
		update_post_meta( $order_id, 'postage_insurance', 'yes' );
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'save_postage_insurance_checkbox' );

function display_postage_insurance_order_meta( $order ) {
	$postage_insurance = get_post_meta( $order->get_id(), 'postage_insurance', true );
	if ( $postage_insurance === 'yes' ) {
		echo '<p><strong>Postage Insurance:</strong> Yes</p>';
		$insurance_cost = wc_price( $order->get_fee_total( 'postage-insurance' ) );
		echo '<p><strong>Postage Insurance Cost:</strong> ' . $insurance_cost . '</p>';
	} else {
		echo '<p><strong>Postage Insurance:</strong> No</p>';
	}
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'display_postage_insurance_order_meta', 10, 1 );
