(function ($) {
	// Function to update the cart totals
	function updateCartTotals() {
		// Check the state of the custom checkbox
		var isPostageInsurance = $("#postage_insurance").is(":checked");

		// Make an AJAX request to update the cart totals
		$.ajax({
			type: "POST",
			url: wc_cart_fragments_params.ajax_url,
			data: {
				action: "update_cart_totals",
				postage_insurance: isPostageInsurance,
			},
			success: function (response) {
				// Refresh the cart fragment to update totals on the page
				$(document.body).trigger("wc_fragment_refresh");
			},
		});
	}

	// Attach the updateCartTotals function to the change event of the custom checkbox
	$("#postage_insurance").on("change", updateCartTotals);
})(jQuery);
