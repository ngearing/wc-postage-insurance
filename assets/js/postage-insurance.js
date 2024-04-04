(function ($) {
	// Function to update the cart totals
	function updateCartTotals() {
		// Check the state of the custom checkbox
		var isPostageInsurance =
			document.querySelector("#postage_insurance").checked;

		// Make an AJAX request to update the cart totals
		$.ajax({
			type: "POST",
			url: wc_cart_fragments_params.ajax_url,
			data: {
				action: "update_cart_totals",
				postage_nonce: wc_cart_fragments_params.nonce,
				postage_insurance: isPostageInsurance,
			},
			complete: function (response) {
				// Refresh page.
				window.location.reload();
			},
		});
	}

	// Attach the updateCartTotals function to the change event of the custom checkbox
	$("#postage_insurance").on("change", updateCartTotals);
})(jQuery);
