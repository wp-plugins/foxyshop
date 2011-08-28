<?php
add_action('admin_menu', 'foxyshop_customer_management_menu');

function foxyshop_customer_management_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Customer Management'), __('Customers'), 'manage_options', 'foxyshop_customer_management', 'foxyshop_customer_management');
}

function foxyshop_customer_management() {
	global $foxyshop_settings, $wp_version;
	
	foxyshop_list_table_setup('customers');

	//Setup Fields and Defaults
	$foxy_data_defaults = array(
		"customer_id_filter" => "",
		"customer_email_filter" => "",
		"customer_first_name_filter" => "",
		"customer_last_name_filter" => "",
		"customer_state_filter" => ""
	);
	$foxy_data = wp_parse_args(array("api_action" => "customer_list"), $foxy_data_defaults);
	$querystring = "?post_type=foxyshop_product&amp;page=foxyshop_customer_management&amp;foxyshop_search=1";

	if (isset($_GET['foxyshop_search'])) {
		$fields = array("customer_id_filter", "customer_email_filter", "customer_first_name_filter", "customer_last_name_filter", "customer_state_filter");
		foreach ($fields as $field) {
			if (isset($_GET[$field])) {
				$foxy_data[$field] = $_GET[$field];
				$querystring .= "&amp;$field=" . urlencode($_GET[$field]);
			}
		}
		$foxy_data['pagination_start'] = (isset($_GET['pagination_start']) ? $_GET['pagination_start'] : 0);
		if (version_compare($foxyshop_settings['version'], '0.7.0', ">")) $foxy_data['entries_per_page'] = 50;
	}	


	?>	
	
	<div class="wrap">
		<h2>Manage Customers</h2>


		<form action="edit.php" method="get" id="foxyshop_searchform" name="foxyshop_searchform" style="display: block; margin-bottom: 20px;">
		<input type="hidden" name="foxyshop_search" value="1" />
		<input type="hidden" name="post_type" value="foxyshop_product" />
		<input type="hidden" name="page" value="foxyshop_customer_management" />

		<table class="widefat">
		<thead><tr><th colspan="2"><?php _e('Search Options'); ?></th></tr></thead>
		<tbody><tr><td>
		
			<div class="foxyshop_field_control">
				<label for="customer_id_filter">Customer ID</label><input type="text" name="customer_id_filter" id="customer_id_filter" value="<?php echo $foxy_data['customer_id_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="customer_first_name_filter">Customer First Name</label><input type="text" name="customer_first_name_filter" id="customer_first_name_filter" value="<?php echo $foxy_data['customer_first_name_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="customer_last_name_filter">Customer Last Name</label><input type="text" name="customer_last_name_filter" id="customer_last_name_filter" value="<?php echo $foxy_data['customer_last_name_filter']; ?>" />
			</div>
		</td><td>
			<div class="foxyshop_field_control">
				<label for="customer_email_filter">Customer Email</label><input type="text" name="customer_email_filter" id="customer_email_filter" value="<?php echo $foxy_data['customer_email_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="customer_state_filter">Customer State</label><input type="text" name="customer_state_filter" id="customer_state_filter" value="<?php echo $foxy_data['customer_state_filter']; ?>" />
			</div>
			
			<div style="clear: both;"></div>
			<button type="submit" id="foxyshop_search_submit" name="foxyshop_search_submit" class="button-primary" style="clear: left; margin-top: 10px;">Search Records Now</button>
			<button type="button" class="button" style="margin-left: 15px;" onclick="document.location.href = 'edit.php?post_type=foxyshop_product&page=foxyshop_customer_management';">Reset Form</button>
			
		</td></tr></tbody></table>
		</form>

	<?php
	if (!isset($_GET['foxyshop_search'])) return;
	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	//print_r($foxy_data);
	//echo "<pre>" . substr($foxy_response,1,2000) . "</pre>";

	if ($xml->result == "ERROR") {
		echo '<h3>' . $xml->messages->message . '</h3>';
		return;
	}
	?>
	<table cellpadding="0" cellspacing="0" border="0" class="display foxyshop_table_list" id="customers">
		<thead>
			<tr>
				<th>Customer ID</th>
				<th>Last Name</th>
				<th>First Name</th>
				<th>Email</th>
				<th>Orders</th>
				<?php if ($foxyshop_settings['enable_subscriptions']) { ?><th>Subscriptions</th><?php } ?>
			</tr>
		</thead>
		<tbody>
	<?php
	foreach($xml->customers->customer as $customer) {
		$customer_id = $customer->customer_id;
		$customer_first_name = $customer->customer_first_name;
		$customer_last_name = $customer->customer_last_name;
		$customer_email = $customer->customer_email;

		echo '<tr rel="' . $customer_id . '" class="gradeU">';
		echo '<td>' . $customer_id . '</td>';
		echo '<td>' . $customer_last_name . '</td>';
		echo '<td>' . $customer_first_name . '</td>';
		echo '<td>' . $customer_email . '</td>';
		echo '<td><a href="edit.php?post_type=foxyshop_product&page=foxyshop_order_management&customer_id_filter=' . $customer->customer_id . '&transaction_date_filter_begin=&transaction_date_filter_end=&hide_transaction_filter=&foxyshop_search=1">Orders</a></td>';
		if ($foxyshop_settings['enable_subscriptions']) echo '<td><a href="edit.php?post_type=foxyshop_product&page=foxyshop_subscription_management&customer_id_filter=' . $customer->customer_id . '&start_date_filter_begin=&start_date_filter_end=&&foxyshop_search=1">Subscriptions</a></td>';
		echo '</tr>'."\n";
	}

	echo '</tbody></table>';
	
	//Pagination
	$p = (int)(version_compare($foxyshop_settings['version'], '0.7.0', "==") ? 50 : 50);
	$total_records = (int)$xml->statistics->total_customers;
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