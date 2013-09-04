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

	add_action("woocommerce_product_options_stock", function() {

		global $thepostid; // Set by WooCommerce

		// Availability

		echo '<div class="lowtone woocommerce stock extended_status show_if_outofstock">';

		woocommerce_wp_select(array( 
			"id" => optionKey("availability"), 
			"label" => __("Availability", "lowtone_woocommerce_stock_extended_status"), 
			"options" => array(
				"discontinued" => __("Discontinued", "lowtone_woocommerce_stock_extended_status"),
				"expected" => __("Expected", "lowtone_woocommerce_stock_extended_status"),
			),
			"desc_tip" => true, 
			"description" => __("Custom status. If a custom status is provided it will be displayed with the product. The custom status text is only intended to provide more detailed information and does not affect the availability of the product", "lowtone_woocommerce_stock_extended_status"), 
		));

		// Available from

		echo '<div class="show_if_expected">';

		woocommerce_wp_text_input(array( 
			"id" => ($availableFromKey = optionKey("available_from")), 
			"label" => __("Available from", "lowtone_woocommerce_stock_extended_status"), 
			"value" => ($availableFrom = get_post_meta($thepostid, $availableFromKey, true)) ? date_i18n("Y-m-d", $availableFrom) : "",
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
				
	// Register textdomain

	add_action("plugins_loaded", function() {
		load_plugin_textdomain("lowtone_woocommerce_stock_extended_status", false, basename(__DIR__) . "/assets/languages");
	});

	// Functions

	function optionKey($key) {
		return "_lowtone_woocommerce_stock_extended_status_" . $key;
	}

}