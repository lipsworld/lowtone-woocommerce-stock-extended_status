<?php
/*
 * Plugin Name: Extended Stock Status
 * Plugin URI: http://wordpress.lowtone.nl/plugins/woocommerce-stock-extended_status/
 * Description: Add additional information to the stock status.
 * Version: 1.0
 * Author: Lowtone <info@lowtone.nl>
 * Author URI: http://lowtone.nl
 * License: http://wordpress.lowtone.nl/license
 */
/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2013, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\plugins\lowtone\woocommerce\stock\extended_status
 */

namespace lowtone\woocommerce\stock\extended_status {

	add_action("admin_enqueue_scripts", function($hook) {
		if (!("post.php" == $hook || "post-new.php" == $hook))
			return;

		wp_enqueue_script("lowtone_woocommerce_stock_extended_status", plugins_url("/assets/scripts/jquery.admin.js", __FILE__), array("jquery"));
	});

	// Add options to inventory tab

	add_action("woocommerce_product_options_stock", function() {

		global $thepostid; // Set by WooCommerce

		// Availability

		echo '<div class="lowtone woocommerce stock extended_status show_if_outofstock">';

		woocommerce_wp_select(array( 
			"id" => optionKey("availability"), 
			"label" => __("Availability", "lowtone_woocommerce_stock_extended_status"), 
			"options" => array(
				"unknown" => __("Unknown", "lowtone_woocommerce_stock_extended_status"),
				"discontinued" => __("Discontinued", "lowtone_woocommerce_stock_extended_status"),
				"expected" => __("Expected", "lowtone_woocommerce_stock_extended_status"),
			),
			"desc_tip" => true, 
			"description" => __("Custom status. If a custom status is provided it will be displayed with the product. The custom status text is only intended to provide more detailed information and does not affect the availability of the product", "lowtone_woocommerce_stock_extended_status"), 
		));

		// Available from

		echo '<div class="show_if_expected">';

		woocommerce_wp_text_input(array( 
			"id" => optionKey("available_from"), 
			"label" => __("Available from", "lowtone_woocommerce_stock_extended_status"), 
			"value" => ($availableFrom = availableFrom($thepostid)) ? date_i18n("Y-m-d", $availableFrom) : "",
			"desc_tip" => true, 
			"description" => __("Custom status. If a custom status is provided it will be displayed with the product. The custom status text is only intended to provide more detailed information and does not affect the availability of the product", "lowtone_woocommerce_stock_extended_status"), 
			"type" => "date",
		));

		do_action("woocommerce_product_options_custom_stock_status_fields");

		echo '</div>' . 
			'</div>';
	});

	// Save post
	
	add_action("save_post", function($postId, $post) {

		if (isset($_POST[optionKey("availability")]))
			update_post_meta($postId, optionKey("availability"), $_POST[optionKey("availability")]);

		if (isset($_POST[optionKey("available_from")]))
			update_post_meta($postId, optionKey("available_from"), strtotime($_POST[optionKey("available_from")]));

	}, 10, 2);

	// Output
	
	add_filter("woocommerce_stock_html", function() {
		global $product;

		$availability = $product->get_availability();

		if (!$availability["availability"])
			return;

		$html = '<div class="stock ' . esc_attr($availability["class"]) . '">';

		switch ($availability["class"]) {
			case "out-of-stock":
				switch (availability($product->id)) {
					case "discontinued":
						$html .= '<p>' . esc_html(__("Discontinued", "lowtone_woocommerce_stock_extended_status")) . '</p>';
						break;

					case "expected":
						if (NULL !== ($availableFrom = availableFrom($product->id)) && ($dateDiff = dateDiff($availableFrom)) < 22) {

							if ($dateDiff > 7) 
								$html .= '<p>' . esc_html(__("Expected within 2-3 weeks", "lowtone_woocommerce_stock_extended_status")) . '</p>';
							else 
								$html .= '<p>' . esc_html(__("Expected within days", "lowtone_woocommerce_stock_extended_status")) . '</p>';

						} else 
							$html .= '<p>' . esc_html(__("Expected (see description)", "lowtone_woocommerce_stock_extended_status")) . '</p>';

						break;

					default:
						$html .= '<p>' . esc_html($availability["availability"]) . '</p>';
				}

				break;

			default:
				$html .= '<p>' . esc_html($availability["availability"]) . '</p>';

		}

		$html .= '</div>';

		return apply_filters("lowtone_woocommerce_stock_extended_status_output", $html, $availability);
	}, apply_filters("lowtone_woocommerce_stock_extended_status_output_filter_priority", 10), 2);
				
	// Register textdomain

	add_action("plugins_loaded", function() {
		load_plugin_textdomain("lowtone_woocommerce_stock_extended_status", false, basename(__DIR__) . "/assets/languages");
	});

	// Functions

	function optionKey($key) {
		return "_lowtone_woocommerce_stock_extended_status_" . $key;
	}

	function availability($id) {
		return get_post_meta($id, optionKey("availability"), true) ?: NULL;
	}

	function availableFrom($id) {
		return get_post_meta($id, optionKey("available_from"), true) ?: NULL;
	}

	function dateDiff($date) {
		return ($date - mktime(0, 0, 0)) / (60*60*24);
	}

}