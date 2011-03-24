<?php
add_action('admin_menu', 'foxyshop_subscription_management_menu');
//add_action('admin_init', 'set_foxyshop_settings');

function foxyshop_subscription_management_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Subscription Management'), __('Subscriptions'), 'manage_options', 'foxyshop_subscription_management', 'foxyshop_subscription_management');
}

function foxyshop_subscription_management() {
	global $foxyshop_settings, $wp_version;
	
	foxyshop_list_table_setup('subscriptions');

	//Setup Fields and Defaults
	$foxy_data_defaults = array(
		"is_active_filter" => "",
		"frequency_filter" => "",
		"past_due_amount_filter" => "",
		"start_date_filter_begin" => date("Y-m-d", strtotime("-10 days")),
		"start_date_filter_end" =>  date("Y-m-d"),
		"next_transaction_date_filter_begin" => "",
		"next_transaction_date_filter_end" => "",
		"end_date_filter_begin" => "",
		"end_date_filter_end" => "",
		"third_party_id_filter" => "",
		"last_transaction_id_filter" => "",
		"customer_id_filter" => "",
		"customer_email_filter" => "",
		"customer_first_name_filter" => "",
		"customer_last_name_filter" => "",
		"product_code_filter" => "",
		"product_name_filter" => "",
		"product_option_name_filter" => "",
		"product_option_value_filter" => ""
	);
	$foxy_data = wp_parse_args(array("api_action" => "subscription_list"), $foxy_data_defaults);
	
	if (isset($_GET['foxyshop_search'])) {
		$fields = array("is_active_filter", "frequency_filter", "past_due_amount_filter","start_date_filter_begin", "start_date_filter_end", "next_transaction_date_filter_begin", "next_transaction_date_filter_end", "end_date_filter_begin", "end_date_filter_end", "third_party_id_filter", "last_transaction_id_filter", "customer_id_filter", "customer_email_filter", "customer_first_name_filter", "customer_last_name_filter", "product_code_filter", "product_name_filter", "product_option_name_filter", "product_option_value_filter");
		foreach ($fields as $field) {
			if (isset($_GET[$field])) $foxy_data[$field] = $_GET[$field];
		}
	}	

	?>	
	
	<div class="wrap">
		<h2>Manage Subscriptions</h2>


		<form action="edit.php" method="get" id="foxyshop_searchform" name="foxyshop_searchform" style="display: block; margin-bottom: 20px;">
		<input type="hidden" name="foxyshop_search" value="1" />
		<input type="hidden" name="post_type" value="foxyshop_product" />
		<input type="hidden" name="page" value="foxyshop_subscription_management" />

		<table class="widefat">
		<thead><tr><th colspan="2"><?php _e('Search Options'); ?></th></tr></thead>
		<tbody><tr><td>
			<div class="foxyshop_field_control">
				<label for="is_active_filter">Subscription Type</label>
				<select name="is_active_filter" id="is_active_filter">
				<?php
				$selectArray = array("0" => "Disabled", "1" => "Active", "" => "Both");
				foreach ($selectArray as $selectKey=>$selectOption) {
					echo '<option value="' . $selectKey . '"' . ($foxy_data['is_active_filter'] == $selectKey ? ' selected="selected"' : '') . '>' . $selectOption . '</option>'."\n";
				} ?>
				</select>
			</div>
			<div class="foxyshop_field_control">
				<label for="past_due_amount_filter">Past Due Status</label>
				<select name="past_due_amount_filter" id="past_due_amount_filter">
				<?php
				$selectArray = array("" => "Show All", "1" => "Show Past Due Only");
				foreach ($selectArray as $selectKey=>$selectOption) {
					echo '<option value="' . $selectKey . '"' . ($foxy_data['past_due_amount_filter'] == $selectKey ? ' selected="selected"' : '') . '>' . $selectOption . '</option>'."\n";
				} ?>
				</select>
			</div>
			<div class="foxyshop_field_control">
				<label for="frequency_filter">Frequency</label><input type="text" name="frequency_filter" id="frequency_filter" value="<?php echo $foxy_data['frequency_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="third_party_id_filter">Third Party ID</label><input type="text" name="third_party_id_filter" id="third_party_id_filter" value="<?php echo $foxy_data['third_party_id_filter']; ?>" />
				<span>PayPal</span>
			</div>
			<div class="foxyshop_field_control">
				<label for="last_transaction_id_filter">Last Transaction ID</label><input type="text" name="last_transaction_id_filter" id="last_transaction_id_filter" value="<?php echo $foxy_data['last_transaction_id_filter']; ?>" />
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
				<label for="start_date_filter_begin">Start Date</label><input type="text" name="start_date_filter_begin" id="start_date_filter_begin" value="<?php echo $foxy_data['start_date_filter_begin']; ?>" />
				<span>to</span><input type="text" name="start_date_filter_end" id="start_date_filter_end" value="<?php echo $foxy_data['start_date_filter_end']; ?>" />
				<span>YYYY-MM-DD</span>
			</div>
			<div class="foxyshop_field_control">
				<label for="next_transaction_date_filter_begin">Next Transaction Date</label><input type="text" name="next_transaction_date_filter_begin" id="next_transaction_date_filter_begin" value="<?php echo $foxy_data['next_transaction_date_filter_begin']; ?>" />
				<span>to</span><input type="text" name="next_transaction_date_filter_end" id="next_transaction_date_filter_end" value="<?php echo $foxy_data['next_transaction_date_filter_end']; ?>" />
				<span>YYYY-MM-DD</span>
			</div>
			<div class="foxyshop_field_control">
				<label for="end_date_filter_begin">End Date</label><input type="text" name="end_date_filter_begin" id="end_date_filter_begin" value="<?php echo $foxy_data['end_date_filter_begin']; ?>" />
				<span>to</span><input type="text" name="end_date_filter_end" id="end_date_filter_end" value="<?php echo $foxy_data['end_date_filter_end']; ?>" />
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
			
			<div style="clear: both;"></div>
			<button type="submit" id="foxyshop_search_submit" name="foxyshop_search_submit" class="button-primary" style="clear: both; margin-top: 10px;">Search Records Now</button>
			<button type="button" class="button" style="margin-left: 15px;" onclick="document.location.href = 'edit.php?post_type=foxyshop_product&page=foxyshop_subscription_management';">Reset Form</button>
			
		</td></tr></tbody></table>
			
		
		</form>
		<?php if (version_compare($wp_version, '3.1', '>=')) { ?>
		<script type="text/javascript" charset="utf-8">
		jQuery(document).ready(function($) {
			$("#start_date_filter_begin, #start_date_filter_end, #next_transaction_date_filter_begin, #next_transaction_date_filter_end, #end_date_filter_begin, #end_date_filter_end").datepicker({ dateFormat: 'yy-mm-dd' });
		});
		</script>
		<?php } ?>

	<?php
	if (!isset($_GET['foxyshop_search'])) return;

	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	
	if ($xml->result == "ERROR") {
		echo '<h3>' . $xml->messages->message . '</h3>';
		return;
	}
	?>
	<table cellpadding="0" cellspacing="0" border="0" class="display foxyshop_table_list" id="subscriptions">
		<thead>
			<tr>
				<th>Customer</th>
				<th>Start Date</th>
				<th>Next Date</th>
				<th>End Date</th>
				<th>Past Due</th>
				<th>Details</th>
				<th>Freq.</th>
			</tr>
		</thead>
		<tbody>
	<?php
	foreach($xml->subscriptions->subscription as $subscription) {
		$sub_token = $subscription->sub_token;
		$customer_id = (string)$subscription->customer_id;
		$customer_first_name = $subscription->customer_first_name;
		$customer_last_name = $subscription->customer_last_name;
		$start_date = $subscription->start_date;
		$next_transaction_date = $subscription->next_transaction_date;
		$end_date = $subscription->end_date;
		$frequency = $subscription->frequency;
		$past_due_amount = $subscription->past_due_amount;
		$is_active = $subscription->is_active;
		if ($foxyshop_settings['version'] == "0.7.0") {
			foreach($subscription->transaction_template->transaction_template->transaction_details->transaction_detail as $transaction_detail) {
				$product_code = $transaction_detail->product_code;
				$product_name = $transaction_detail->product_name;
				$product_price = (double)$transaction_detail->product_price;
			}
		} else {
			foreach($subscription->transaction_template->transaction_details->transaction_detail as $transaction_detail) {
				$product_code = $transaction_detail->product_code;
				$product_name = $transaction_detail->product_name;
				$product_price = (double)$transaction_detail->product_price;
			}
		}
		
		if ($customer_first_name != "") {
			$customer_name = $customer_last_name . ', ' . $customer_first_name;
		} else {
			$customer_name = $customer_id;
		}
		
		$grade = "A";
		if ($is_active == 0) $grade = "U";
		if ($past_due_amount != 0) $grade = "X";
		
		echo '<tr rel="' . $sub_token . '" class="grade' . $grade . '">';
		echo '<td><a href="edit.php?post_type=foxyshop_product&page=foxyshop_customer_management&customer_id_filter=' . $customer_id . '&foxyshop_search=1">' . $customer_name . '</a></td>';
		echo '<td>' . $start_date . '</td>';
		echo '<td>' . $next_transaction_date . '</td>';
		echo '<td>' . $end_date . '</td>';
		echo '<td>' . $past_due_amount . '</td>';
		echo '<td>' . $product_name . ': ' . foxyshop_currency($product_price) . '</td>';
		echo '<td>' . $frequency . '</td>';
		echo '</tr>'."\n";
	}
	
	echo '</tbody></table></div>';

}



?>