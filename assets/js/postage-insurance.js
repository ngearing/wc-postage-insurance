(function ($) {
	/**
	 * Check if a node is blocked for processing.
	 *
	 * @param {JQuery Object} $node
	 * @return {bool} True if the DOM Element is UI Blocked, false if not.
	 */
	var is_blocked = function ($node) {
		return $node.is(".processing") || $node.parents(".processing").length;
	};

	/**
	 * Block a node visually for processing.
	 *
	 * @param {JQuery Object} $node
	 */
	var block = function ($node) {
		if (!is_blocked($node)) {
			$node.addClass("processing").block({
				message: null,
				overlayCSS: {
					background: "#fff",
					opacity: 0.6,
				},
			});
		}
	};

	/**
	 * Unblock a node after processing is complete.
	 *
	 * @param {JQuery Object} $node
	 */
	var unblock = function ($node) {
		$node.removeClass("processing").unblock();
	};

	var update_cart_totals_div = function (html_str) {
		var $html = $.parseHTML(html_str);

		// TODO: fix this not working...
		var $new_totals = $(".cart_totals", $html);

		$(".cart_totals").replaceWith($new_totals);
		$(document.body).trigger("updated_cart_totals");
	};

	// Function to update the cart totals
	function updateCartTotals() {
		// Check the state of the custom checkbox
		var isPostageInsurance =
			document.querySelector("#postage_insurance").checked;

		block($("div.cart_totals "));

		// Make an AJAX request to update the cart totals
		$.ajax({
			type: "POST",
			url: wc_cart_fragments_params.ajax_url,
			data: {
				action: "update_cart_totals",
				postage_nonce: wc_cart_fragments_params.nonce,
				postage_insurance: isPostageInsurance,
			},
			success: function (response) {
				response = JSON.parse(response);
				update_cart_totals_div(response);
			},
			complete: function () {
				unblock($("div.cart_totals"));
			},
			error: function (resp) {
				console.log("Error: " + resp);
				unblock($("div.cart_totals"));
			},
		});
	}

	// Attach the updateCartTotals function to the change event of the custom checkbox
	$("#postage_insurance").on("change", updateCartTotals);
})(jQuery);
