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

	var isCheckout = function () {
		var form = document.querySelector("form.checkout");
		if (form) {
			return true;
		}

		return false;
	};

	var update_cart_totals_div = function (html_str, target) {
		var $html = $.parseHTML(html_str);

		$(target).replaceWith($html);
		$(document.body).trigger("updated_totals");
	};

	// Function to update the cart totals
	function updateCartTotals() {
		// Check the state of the custom checkbox
		var isPostageInsurance =
			document.querySelector("#postage_insurance").checked;
		var updateTarget = isCheckout() ? "table.shop_table" : "div.cart_totals";

		block($(updateTarget));

		// Make an AJAX request to update totals.
		$.ajax({
			type: "POST",
			url: wc_cart_fragments_params.ajax_url,
			data: {
				action: "update_postage_insurance",
				checkout: isCheckout() ? true : null,
				postage_nonce: wc_cart_fragments_params.nonce,
				postage_insurance: isPostageInsurance,
			},
			success: function (response) {
				update_cart_totals_div(response, updateTarget);
				setupEventListeners();
			},
			complete: function () {
				unblock($(updateTarget));
			},
			error: function (resp) {
				console.log("Error: " + resp);
				unblock($(updateTarget));
			},
		});
	}

	function setupEventListeners() {
		// Cart page event listener.
		$("#postage_insurance").on("change", updateCartTotals);
		// Checkout page event listener.
		$("form.checkout").on("change", "#postage_insurance", updateCartTotals);
	}
	setupEventListeners();
})(jQuery);
