<?php
add_action('admin_menu', 'foxyshop_customer_management_menu');

function foxyshop_customer_management_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Customer Management'), __('Customers'), 'manage_options', 'foxyshop_customer_management', 'foxyshop_customer_management');
}

function foxyshop_customer_management() {
	global $foxyshop_settings, $wp_version;
	
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
		<div class="icon32" id="icon-users"><br></div>
		<h2>Manage Customers</h2>


		<form action="edit.php" method="get" id="foxyshop_searchform" name="foxyshop_searchform" style="display: block; margin: 14px 0 20px 0;">
		<input type="hidden" name="foxyshop_search" value="1" />
		<input type="hidden" name="post_type" value="foxyshop_product" />
		<input type="hidden" name="page" value="foxyshop_customer_management" />

		<table class="widefat">
		<thead><tr><th colspan="2"><img src="<?php echo FOXYSHOP_DIR; ?>/images/search-icon.png" alt="" /><?php _e('Search Options'); ?></th></tr></thead>
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
			<button type="submit" id="foxyshop_search_submit" name="foxyshop_search_submit" class="button-primary" style="clear: left; margin: 10px 0 6px 0;">Search Records Now</button>
			<button type="button" class="button" style="margin-left: 15px;" onclick="document.location.href = 'edit.php?post_type=foxyshop_product&page=foxyshop_customer_management';">Reset Form</button>
			
		</td></tr></tbody></table>
		</form>

	<?php
	if (!isset($_GET['foxyshop_search'])) return;
	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	//print_r($foxy_data);
	//echo "<pre>" . substr($foxy_response,1,2000) . "</pre>";

	if ((string)$xml->result == "ERROR") {
		echo '<h3>' . (string)$xml->messages->message . '</h3>';
		return;
	}
	?>
	<table cellpadding="0" cellspacing="0" border="0" class="wp-list-table widefat foxyshop-list-table" id="customer_table" style="margin-top: 14px;">
		<thead>
			<tr>
				<th><span><?php _e('Customer ID'); ?></span><span class="sorting-indicator"></span></th>
				<th><span><?php _e('Last Name'); ?></span><span class="sorting-indicator"></span></th>
				<th><span><?php _e('First Name'); ?></span><span class="sorting-indicator"></span></th>
				<th><span><?php _e('Email'); ?></span><span class="sorting-indicator"></span></th>
				<th><span><?php _e('Orders'); ?></span><span class="sorting-indicator"></span></th>
				<?php if ($foxyshop_settings['enable_subscriptions']) echo "<th><span>" . __('Subscriptions') . "</span><span class=\"sorting-indicator\"></span></th>\n"; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e('Customer ID'); ?></th>
				<th><?php _e('Last Name'); ?></th>
				<th><?php _e('First Name'); ?></th>
				<th><?php _e('Email'); ?></th>
				<th><?php _e('Orders'); ?></th>
				<?php if ($foxyshop_settings['enable_subscriptions']) echo "<th>" . __('Subscriptions') . "</th>\n"; ?>
			</tr>
		</tfoot>
		<tbody>

	<?php
	$holder = "";
	foreach($xml->customers->customer as $customer) {
		$customer_id = (string)$customer->customer_id;
		$customer_first_name = (string)$customer->customer_first_name;
		$customer_last_name = (string)$customer->customer_last_name;
		$customer_email = (string)$customer->customer_email;

		echo '<tr rel="' . $customer_id . '">';
		echo '<td><strong><a href="#" class="view_detail">' . (string)$customer_id . '</a></strong></td>';
		echo '<td>' . (string)$customer_last_name . '</td>';
		echo '<td>' . (string)$customer_first_name . '</td>';
		echo '<td>' .(string) $customer_email . '</td>';
		echo '<td><a href="edit.php?post_type=foxyshop_product&page=foxyshop_order_management&customer_id_filter=' . (string)$customer->customer_id . '&transaction_date_filter_begin=&transaction_date_filter_end=&hide_transaction_filter=&foxyshop_search=1">Orders</a></td>';
		if ($foxyshop_settings['enable_subscriptions']) echo '<td><a href="edit.php?post_type=foxyshop_product&page=foxyshop_subscription_management&customer_id_filter=' . (string)$customer->customer_id . '&start_date_filter_begin=&start_date_filter_end=&&foxyshop_search=1">Subscriptions</a></td>';
		echo '</tr>'."\n";


		$holder .= '<div class="detail_holder" id="holder_' . $customer_id. '">'."\n";

		//Customer Details
		$holder .= '<div class="foxyshop_list_col">';
		$holder .= '<h4>Customer Details</h4>';
		$holder .= '<ul>';
		if ((string)$customer->customer_phone != "") $holder .= '<li>' . (string)$customer->customer_phone . '</li>';
		$holder .= '<li><a href="mailto:' . $customer->customer_email . '">' . (string)$customer->customer_email . '</a></li>';
		if ((string)$customer->cc_number != "") $holder .= '<li>' . __('Card') . ': ' . (string)$customer->cc_number . '</li>';
		if ((string)$customer->cc_exp_month != "") $holder .= '<li>' . __('Exp') . ': ' . (string)$customer->cc_exp_month . '-' . (string)$customer->cc_exp_year . '</li>';
		$holder .= '<li>' . __('Last Modified') . ': ' . (string)$customer->last_modified_date . '</li>';
		$holder .= '<li>&nbsp;</li>';

		//Attributes
		if (version_compare($foxyshop_settings['version'], '0.7.2', ">=")) {
			foreach($customer->attributes->attribute as $attribute) {
				$holder .= '<li><strong>' . str_replace("_"," ",$attribute->attribute_name) . ':</strong> ' . $attribute->attribute_value . '</li>';
			}
		}

		$holder .= '</ul>';
		$holder .= '</div>';

		//Customer Address
		$holder .= '<div class="foxyshop_list_col">';
		$holder .= '<h4>Customer Address</h4>';
		$holder .= '<ul>';
		$holder .= '<li>' . (string)$customer->customer_first_name . ' ' . (string)$customer->customer_last_name . '</li>';
		if ((string)$customer->customer_company != "") $holder .= '<li>' . (string)$customer->customer_company . '</li>';
		if ((string)$customer->customer_address1 != "") $holder .= '<li>' . (string)$customer->customer_address1 . '</li>';
		if ((string)$customer->customer_address2 != "") $holder .= '<li>' . (string)$customer->customer_address2 . '</li>';
		if ((string)$customer->customer_city != "") $holder .= '<li>' . (string)$customer->customer_city . ', ' . (string)$customer->customer_state . ' ' . (string)$customer->customer_postal_code . '</li>';
		if ((string)$customer->customer_country != "") $holder .= '<li>' . (string)$customer->customer_country . '</li>';
		$holder .= '</ul>';
		$holder .= '</div>';

		//Shipping Addresses (if entered)
		if ((string)$customer->shipping_first_name != "") {
			$holder .= '<div class="foxyshop_list_col">';
			$holder .= '<h4>Shipping Details</h4>';
			$holder .= '<ul>';
			$holder .= '<li>' . (string)$customer->shipping_first_name . ' ' . (string)$customer->shipping_last_name . '</li>';
			if ((string)$customer->shipping_company != "") $holder .= '<li>' . (string)$customer->shipping_company . '</li>';
			if ((string)$customer->shipping_address1 != "")$holder .= '<li>' . $customer->shipping_address1 . '</li>';
			if ((string)$customer->shipping_address2 != "") $holder .= '<li>' . (string)$customer->shipping_address2 . '</li>';
			if ((string)$customer->shipping_city != "")$holder .= '<li>' . (string)$customer->shipping_city . ', ' . (string)$customer->shipping_state . ' ' . (string)$customer->shipping_postal_code . '</li>';
			if ((string)$customer->shipping_country != "")$holder .= '<li>' . (string)$customer->shipping_country . '</li>';
			if ((string)$customer->shipping_phone != "") $holder .= '<li>' . (string)$customer->shipping_phone . '</li>';
			$holder .= '</ul>';
			$holder .= '</div>';
		}

		//Multi-ship Addresses
		foreach($customer->shipto_addresses->shipto_address as $shipto_address) {
			$holder .= '<div class="foxyshop_list_col">';
			$holder .= '<h4>Shipping Details: ' . $shipto_address->address_name . '</h4>';
			$holder .= '<ul>';
			$holder .= '<li>' . (string)$shipto_address->shipto_first_name . ' ' . (string)$shipto_address->shipto_last_name . '</li>';
			if ((string)$shipto_address->shipto_company != "") $holder .= '<li>' . (string)$shipto_address->shipto_company . '</li>';
			$holder .= '<li>' . (string)$shipto_address->shipto_address1 . '</li>';
			if ((string)$shipto_address->shipto_address2 != "") $holder .= '<li>' . (string)$shipto_address->shipto_address2 . '</li>';
			$holder .= '<li>' . (string)$shipto_address->shipto_city . ', ' . (string)$shipto_address->shipto_state . ' ' . (string)$shipto_address->shipto_postal_code . '</li>';
			$holder .= '<li>' . (string)$shipto_address->shipto_country . '</li>';
			if ((string)$shipto_address->shipto_phone != "") $holder .= '<li>' . (string)$shipto_address->shipto_phone . '</li>';
			$holder .= '</ul>';
			$holder .= '</div>';
		}
		$holder .= '<div style="clear: both; height: 20px;"></div>';
		$holder .= "</div>\n";


	}

	echo '</tbody></table>';

	?>
	<div id="details_holder"><?php echo $holder; ?></div>
	
	<script type="text/javascript" src="<?php echo FOXYSHOP_DIR; ?>/js/jquery.tablesorter.js"></script>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$(".foxyshop-list-table thead th").click(function() {
			$("#foxyshop-list-inline .detail_holder").appendTo("#details_holder");
			$("#foxyshop-list-inline").remove();
		});
		$(".foxyshop-list-table").tablesorter({
			'cssDesc': 'asc sorted',
			'cssAsc': 'desc sorted'
		});
		$(".view_detail").click(function() {
			var id = $(this).parents("tr").attr("rel");

			if ($("#foxyshop-list-inline #holder_" + id).length > 0) {
				$("#foxyshop-list-inline .detail_holder").appendTo("#details_holder");
				$("#foxyshop-list-inline").remove();
			} else {
				$("#foxyshop-list-inline .detail_holder").appendTo("#details_holder");
				$("#foxyshop-list-inline").remove();

				$(this).parents("tr").after('<tr id="foxyshop-list-inline"><td colspan="7"></td></tr>');
				$("#holder_"+id).appendTo("#foxyshop-list-inline td");
			}

			return false;
		});
	});
	</script>

	
	<?php
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