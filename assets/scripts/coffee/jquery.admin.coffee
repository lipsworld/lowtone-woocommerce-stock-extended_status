$ = jQuery

$ -> 
	$('#woocommerce-product-data').each ->
		$product_data = $ this

		# Move extended status
		
		$extended_status = $product_data.find '.lowtone.woocommerce.stock.extended_status'

		$extended_status.insertAfter $product_data.find('._backorders_field').parent()

		# Init show_if_outofstock

		$stock_status_input = $product_data.find 'select[name="_stock_status"]'

		$show_if_outofstock = $product_data.find '.show_if_outofstock'

		toggle_show_if_outofstock = ->
			$show_if_outofstock.toggle 'outofstock' == $stock_status_input.val()

		toggle_show_if_outofstock()

		$stock_status_input.change toggle_show_if_outofstock

		# Init show_if_expected

		$availability_input = $product_data.find 'select[name="_lowtone_woocommerce_stock_extended_status_availability"]'

		$show_if_expected = $product_data.find '.show_if_expected'

		toggle_show_if_expected = ->
			$show_if_expected.toggle 'expected' == $availability_input.val()

		toggle_show_if_expected()

		$availability_input.change toggle_show_if_expected