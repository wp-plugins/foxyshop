<?php
if (isset($_GET['foxyshop_print_invoice'])) add_action('admin_init', 'foxyshop_print_invoice');
function foxyshop_print_invoice() {
	global $foxyshop_settings;
	
	//Setup Fields and Defaults
	$foxy_data_defaults = array(
		"is_test_filter" => "0",
		"hide_transaction_filter" => "0",
		"data_is_fed_filter" => "",
		"id_filter" => "",
		"order_total_filter" => "",
		"coupon_code_filter" => "",
		"transaction_date_filter_begin" => date("Y-m-d", strtotime("-10 days")),
		"transaction_date_filter_end" => date("Y-m-d"),
		"customer_id_filter" => "",
		"customer_email_filter" => "",
		"customer_first_name_filter" => "",
		"customer_last_name_filter" => "",
		"customer_state_filter" => "",
		"shipping_state_filter" => "",
		"customer_ip_filter" => "",
		"product_code_filter" => "",
		"product_name_filter" => "",
		"product_option_name_filter" => "",
		"product_option_value_filter" => ""
	);
	$foxy_data = wp_parse_args(array("api_action" => "transaction_list"), $foxy_data_defaults);

	if (isset($_GET['foxyshop_search'])) {
		$fields = array("is_test_filter", "hide_transaction_filter", "data_is_fed_filter", "id_filter", "order_total_filter", "coupon_code_filter", "transaction_date_filter_begin", "transaction_date_filter_end", "customer_id_filter", "customer_email_filter", "customer_first_name_filter", "customer_last_name_filter","customer_state_filter", "shipping_state_filter", "customer_ip_filter", "product_code_filter", "product_name_filter", "product_option_name_filter", "product_option_value_filter");
		foreach ($fields as $field) {
			if (isset($_GET[$field])) {
				$foxy_data[$field] = $_GET[$field];
			}
		}
		$foxy_data['pagination_start'] = (isset($_GET['pagination_start']) ? $_GET['pagination_start'] : 0);
		if ($foxyshop_settings['version'] != "0.7.0") $foxy_data['entries_per_page'] = 50;
	}	

	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);

	if ($xml->result == "ERROR") {
		echo '<h3>' . $xml->messages->message . '</h3>';
		return;
	}

	include(foxyshop_get_template_file('/foxyshop-receipt.php'));
	die;
}

add_action('admin_menu', 'foxyshop_order_management_menu');
function foxyshop_order_management_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Order Management'), __('Orders'), 'manage_options', 'foxyshop_order_management', 'foxyshop_order_management');
}

function foxyshop_order_management() {
	global $foxyshop_settings, $wp_version;
	
	foxyshop_list_table_setup('orders');

	//Setup Fields and Defaults
	$foxy_data_defaults = array(
		"is_test_filter" => "0",
		"hide_transaction_filter" => "0",
		"data_is_fed_filter" => "",
		"id_filter" => "",
		"order_total_filter" => "",
		"coupon_code_filter" => "",
		"transaction_date_filter_begin" => date("Y-m-d", strtotime("-10 days")),
		"transaction_date_filter_end" => date("Y-m-d"),
		"customer_id_filter" => "",
		"customer_email_filter" => "",
		"customer_first_name_filter" => "",
		"customer_last_name_filter" => "",
		"customer_state_filter" => "",
		"shipping_state_filter" => "",
		"customer_ip_filter" => "",
		"product_code_filter" => "",
		"product_name_filter" => "",
		"product_option_name_filter" => "",
		"product_option_value_filter" => ""
	);
	$foxy_data = wp_parse_args(array("api_action" => "transaction_list"), $foxy_data_defaults);
	$querystring = "?post_type=foxyshop_product&amp;page=foxyshop_order_management&amp;foxyshop_search=1";
	
	if (isset($_GET['foxyshop_search'])) {
		$fields = array("is_test_filter", "hide_transaction_filter", "data_is_fed_filter", "id_filter", "order_total_filter", "coupon_code_filter", "transaction_date_filter_begin", "transaction_date_filter_end", "customer_id_filter", "customer_email_filter", "customer_first_name_filter", "customer_last_name_filter","customer_state_filter", "shipping_state_filter", "customer_ip_filter", "product_code_filter", "product_name_filter", "product_option_name_filter", "product_option_value_filter");
		foreach ($fields as $field) {
			if (isset($_GET[$field])) {
				$foxy_data[$field] = $_GET[$field];
				$querystring .= "&amp;$field=" . urlencode($_GET[$field]);
			}
		}
		$foxy_data['pagination_start'] = (isset($_GET['pagination_start']) ? $_GET['pagination_start'] : 0);
		if ($foxyshop_settings['version'] != "0.7.0") $foxy_data['entries_per_page'] = 50;
	}	


	?>	
	
	<div class="wrap">
		<h2>Manage Orders</h2>


		<form action="edit.php" method="get" id="foxyshop_searchform" name="foxyshop_searchform" style="display: block; margin-bottom: 20px;">
		<input type="hidden" name="foxyshop_search" value="1" />
		<input type="hidden" name="post_type" value="foxyshop_product" />
		<input type="hidden" name="page" value="foxyshop_order_management" />

		<table class="widefat">
		<thead><tr><th colspan="2"><?php _e('Search Options'); ?></th></tr></thead>
		<tbody><tr><td>

			<div class="foxyshop_field_control">
				<label for="is_test_filter">Test Transactions</label>
				<select name="is_test_filter" id="is_test_filter">
				<?php
				$selectArray = array("0" => "Live", "1" => "Test", "" => "Both");
				foreach ($selectArray as $selectKey=>$selectOption) {
					echo '<option value="' . $selectKey . '"' . ($foxy_data['is_test_filter'] == $selectKey ? ' selected="selected"' : '') . '>' . $selectOption . '</option>'."\n";
				} ?>
				</select>

			</div>

			<div class="foxyshop_field_control">
				<label for="hide_transaction_filter">Transaction Status</label>
				<select name="hide_transaction_filter" id="hide_transaction_filter">
				<?php
				$selectArray = array("0" => "Unfilled Orders", "1" => "Archived Orders", "" => "Both");
				foreach ($selectArray as $selectKey=>$selectOption) {
					echo '<option value="' . $selectKey . '"' . ($foxy_data['hide_transaction_filter'] == $selectKey ? ' selected="selected"' : '') . '>' . $selectOption . '</option>'."\n";
				} ?>
				</select>

			</div>

			<div class="foxyshop_field_control">
				<label for="data_is_fed_filter">Datafeed Status</label>
				<select name="data_is_fed_filter" id="data_is_fed_filter">
				<?php
				$selectArray = array("0" => "Fed", "1" => "Unfed", "" => "Both");
				foreach ($selectArray as $selectKey=>$selectOption) {
					echo '<option value="' . $selectKey . '"' . ($foxy_data['data_is_fed_filter'] == $selectKey ? ' selected="selected"' : '') . '>' . $selectOption . '</option>'."\n";
				} ?>
				</select>

			</div>

			<div class="foxyshop_field_control">
				<label for="order_id_filter">Order ID</label><input type="text" name="id_filter" id="id_filter" value="<?php echo $foxy_data['id_filter']; ?>" />
			</div>

			<div class="foxyshop_field_control">
				<label for="order_total_filter">Order Total</label><input type="text" name="order_total_filter" id="order_total_filter" value="<?php echo $foxy_data['order_total_filter']; ?>" />
			</div>

			<div class="foxyshop_field_control">
				<label for="coupon_code_filter">Coupon Code</label><input type="text" name="coupon_code_filter" id="coupon_code_filter" value="<?php echo $foxy_data['coupon_code_filter']; ?>" />
			</div>

			<div class="foxyshop_field_control">
				<label for="product_code_filter">Product Code</label><input type="text" name="product_code_filter" id="product_code_filter" value="<?php echo $foxy_data['product_code_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="product_name_filter">Product Name</label><input type="text" name="product_name_filter" id="product_name_filter" value="<?php echo $foxy_data['product_name_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="product_option_name_filter">Product Option Name</label><input type="text" name="product_option_name_filter" id="product_option_name_filter" value="<?php echo $foxy_data['product_option_name_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="product_option_value_filter">Product Option Value</label><input type="text" name="product_option_value_filter" id="product_option_value_filter" value="<?php echo $foxy_data['product_option_value_filter']; ?>" />
			</div>

		</td><td>

			<div class="foxyshop_field_control">
				<label for="transaction_date_filter_begin">Date Range</label><input type="text" name="transaction_date_filter_begin" id="transaction_date_filter_begin" value="<?php echo $foxy_data['transaction_date_filter_begin']; ?>" />
				<span>to</span><input type="text" name="transaction_date_filter_end" id="transaction_date_filter_end" value="<?php echo $foxy_data['transaction_date_filter_end'];; ?>" />
				<span>YYYY-MM-DD</span>
			</div>


		
			<div class="foxyshop_field_control">
				<label for="customer_id_filter">Customer ID</label><input type="text" name="customer_id_filter" id="customer_id_filter" value="<?php echo $foxy_data['customer_id_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="customer_email_filter">Customer Email</label><input type="text" name="customer_email_filter" id="customer_email_filter" value="<?php echo $foxy_data['customer_email_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="customer_first_name_filter">Customer First Name</label><input type="text" name="customer_first_name_filter" id="customer_first_name_filter" value="<?php echo $foxy_data['customer_first_name_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="customer_last_name_filter">Customer Last Name</label><input type="text" name="customer_last_name_filter" id="customer_last_name_filter" value="<?php echo $foxy_data['customer_last_name_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="customer_state_filter">Customer State</label><input type="text" name="customer_state_filter" id="customer_state_filter" value="<?php echo $foxy_data['customer_state_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="shipping_state_filter">Shipping State</label><input type="text" name="shipping_state_filter" id="shipping_state_filter" value="<?php echo $foxy_data['shipping_state_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="customer_ip_filter">Customer IP</label><input type="text" name="customer_ip_filter" id="customer_ip_filter" value="<?php echo $foxy_data['customer_ip_filter']; ?>" />
			</div>
			
			<div style="clear: both;"></div>
			<button type="submit" id="foxyshop_search_submit" name="foxyshop_search_submit" class="button-primary" style="clear: left; margin-top: 10px;">Search Records Now</button>
			<button type="button" class="button submitcancel" style="margin-left: 15px;" onclick="document.location.href = 'edit.php?post_type=foxyshop_product&page=foxyshop_order_management';">Reset Form</button>
			<button type="submit" class="button" style="margin-left: 15px;" name="foxyshop_print_invoice" id="foxyshop_print_invoice">Print Invoices</button>
			
		</td></tr></tbody></table>
			
		
		</form>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#foxyshop_searchform button").live("click", function() {
				if ($(this).attr("id") == "foxyshop_print_invoice") {
					$("#foxyshop_searchform").attr("target","_blank");
				} else {
					$("#foxyshop_searchform").attr("target","_self");
				}
			});
		});
		</script>

		<?php if (version_compare($wp_version, '3.1', '>=')) { ?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#transaction_date_filter_begin, #transaction_date_filter_end").datepicker({ dateFormat: 'yy-mm-dd' });
		});
		</script>
		<?php } ?>

	<?php
	if (!isset($_GET['foxyshop_search'])) return;
	
	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	//var_dump($xml);

	if ($xml->result == "ERROR") {
		echo '<h3>' . $xml->messages->message . '</h3>';
		return;
	}
	?>
		<table cellpadding="0" cellspacing="0" border="0" class="display foxyshop_table_list" id="subscriptions">
			<thead>
				<tr>
					<th>Order ID</th>
					<th>Order Date</th>
					<th>Customer</th>
					<th>Order Total</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
	<?php
	foreach($xml->transactions->transaction as $transaction) {
		$transaction_id = $transaction->id;
		$transaction_date = $transaction->transaction_date;
		$customer_first_name = $transaction->customer_first_name;
		$customer_last_name = $transaction->customer_last_name;
		$is_anonymous = $transaction->is_anonymous;
		$customer_id = $transaction->customer_id;
		
		$customer_name = $customer_last_name . ', ' . $customer_first_name;
		if ((int)$is_anonymous != 1 && $customer_id) $customer_name = '<a href="edit.php?post_type=foxyshop_product&page=foxyshop_customer_management&customer_id_filter=' . $customer_id . '&foxyshop_search=1" title="Customer ' . $customer_id . '">' . $customer_name . '</a>';
		
		foreach($transaction->transaction_details->transaction_detail as $transaction_detail) {
			$product_code = $transaction_detail->product_code;
			$product_name = $transaction_detail->product_name;
			$product_price = $transaction_detail->product_price;
		}
		echo '<tr rel="' . $transaction_id . '" class="gradeC">';
		echo '<td><a href="' . $transaction->receipt_url . '" target="_blank">' . $transaction_id . '</a></td>';
		echo '<td>' . $transaction_date . '</td>';
		echo '<td>' . $customer_name . '</td>';
		echo '<td>' . foxyshop_currency((double)$transaction->order_total) . '</td>';
		echo '<td><a href="#" rel="' . $transaction_id . '" class="archive_order">Archive</a></td>';
		echo '</tr>'."\n";
	}
	
	echo '</tbody></table>';
	
	//Pagination
	$p = (int)($foxyshop_settings['version'] == "0.7.0" ? 50 : 50);
	$total_records = (int)$xml->statistics->total_orders;
	$filtered_total = (int)$xml->statistics->filtered_total;
	$pagination_start = (int)$xml->statistics->pagination_start;
	$pagination_end = (int)$xml->statistics->pagination_end;
	if ($pagination_start > 1 || $filtered_total > $pagination_end) {
		echo '<div id="admin_list_pagination">';
		echo $xml->messages->message[1] . '<br />';
		if ($pagination_start > 1) echo '<a href="edit.php' . $querystring . '&amp;pagination_start=' . ($pagination_start - $p - 1) . '">&laquo; Previous</a>';
		if ($pagination_end < $filtered_total) {
			if ($pagination_start > 1) echo ' | ';
			echo '<a href="edit.php' . $querystring . '&amp;pagination_start=' . $pagination_end . '">Next &raquo;</a>';
		}
		echo '</div>';
	}

	echo '</div>';

}



?>