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
	// $("#postage_insurance").on("change", updateCartTotals);
})(jQuery);

jQuery(document).ready(function ($) {
	// Function to handle checkbox change event
	$("#postage_insurance").change(function () {
		// Trigger AJAX call to update cart totals
		refreshCartTotals();
	});

	// Function to refresh cart totals via AJAX
	function refreshCartTotals() {
		$.ajax({
			type: "POST",
			url: wc_cart_params.ajax_url,
			data: {
				action: "update_cart_totals",
				postage_insurance: $("#postage_insurance").is(":checked")
					? "yes"
					: "no", // Pass checkbox state
			},
			success: function (response) {
				// Reload the page or update specific elements on the page containing cart totals
				// For example, you can update the cart totals displayed on the page
				$(".cart_totals").load(location.href + " .cart_totals");
			},
		});
	}
});
