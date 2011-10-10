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
		if (version_compare($foxyshop_settings['version'], '0.7.0', ">")) $foxy_data['entries_per_page'] = 50;
	}	

	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);

	if ($xml->result == "ERROR") {
		echo '<h3>' . $xml->messages->message . '</h3>';
		die;
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

	if (isset($_GET['foxyshop_search']) || !defined('FOXYSHOP_AUTO_API_DISABLED')) {
		$fields = array("is_test_filter", "hide_transaction_filter", "data_is_fed_filter", "id_filter", "order_total_filter", "coupon_code_filter", "transaction_date_filter_begin", "transaction_date_filter_end", "customer_id_filter", "customer_email_filter", "customer_first_name_filter", "customer_last_name_filter","customer_state_filter", "shipping_state_filter", "customer_ip_filter", "product_code_filter", "product_name_filter", "product_option_name_filter", "product_option_value_filter");
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
		<div class="icon32 icon32-posts-page" id="icon-edit-pages"><br></div>
		<h2>Manage Orders</h2>


		<form action="edit.php" method="get" id="foxyshop_searchform" name="foxyshop_searchform" style="display: block; margin: 14px 0 20px 0;">
		<input type="hidden" name="foxyshop_search" value="1" />
		<input type="hidden" name="post_type" value="foxyshop_product" />
		<input type="hidden" name="page" value="foxyshop_order_management" />

		<table class="widefat">
		<thead><tr><th colspan="2"><img src="<?php echo FOXYSHOP_DIR; ?>/images/search-icon.png" alt="" /><?php _e('Search Options'); ?></th></tr></thead>
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
				<label for="product_code_filter"><?php echo FOXYSHOP_PRODUCT_NAME_SINGULAR; ?> Code</label><input type="text" name="product_code_filter" id="product_code_filter" value="<?php echo $foxy_data['product_code_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="product_name_filter"><?php echo FOXYSHOP_PRODUCT_NAME_SINGULAR; ?> Name</label><input type="text" name="product_name_filter" id="product_name_filter" value="<?php echo $foxy_data['product_name_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="product_option_name_filter"><?php echo FOXYSHOP_PRODUCT_NAME_SINGULAR; ?> Option Name</label><input type="text" name="product_option_name_filter" id="product_option_name_filter" value="<?php echo $foxy_data['product_option_name_filter']; ?>" />
			</div>
			<div class="foxyshop_field_control">
				<label for="product_option_value_filter"><?php echo FOXYSHOP_PRODUCT_NAME_SINGULAR; ?> Option Value</label><input type="text" name="product_option_value_filter" id="product_option_value_filter" value="<?php echo $foxy_data['product_option_value_filter']; ?>" />
			</div>

		</td><td>

			<div class="foxyshop_field_control">
				<label for="transaction_date_filter_begin">Date Range</label><input type="text" name="transaction_date_filter_begin" id="transaction_date_filter_begin" value="<?php echo $foxy_data['transaction_date_filter_begin']; ?>" class="foxyshop_date_field" />
				<span>to</span><input type="text" name="transaction_date_filter_end" id="transaction_date_filter_end" value="<?php echo $foxy_data['transaction_date_filter_end']; ?>" class="foxyshop_date_field" />
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
			<?php do_action("foxyshop_order_search_buttons", $foxy_data); ?>
			
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
			$(".foxyshop_date_field").datepicker({ dateFormat: 'yy-mm-dd' });
		});
		</script>
		<?php } ?>

	<?php
	if (!isset($_GET['foxyshop_search']) && defined('FOXYSHOP_AUTO_API_DISABLED')) return;
	
	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	//var_dump($xml);

	if ((string)$xml->result == "ERROR") {
		echo '<h3>' . (string)$xml->messages->message . '</h3>';
		return;
	}
	?>

	<table cellpadding="0" cellspacing="0" border="0" class="wp-list-table widefat foxyshop-list-table" id="customer_table" style="margin-top: 14px;">
		<thead>
			<tr>
				<th><span><?php _e('Order ID'); ?></span><span class="sorting-indicator"></span></th>
				<th><span><?php _e('Order Date'); ?></span><span class="sorting-indicator"></span></th>
				<th><span><?php _e('Customer'); ?></span><span class="sorting-indicator"></span></th>
				<th><span><?php _e('Total'); ?></span><span class="sorting-indicator"></span></th>
				<?php do_action("foxyshop_order_table_head"); ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e('Order ID'); ?></th>
				<th><?php _e('OrderDate'); ?></th>
				<th><?php _e('Customer'); ?></th>
				<th><?php _e('Total'); ?></th>
				<?php do_action("foxyshop_order_table_foot"); ?>
			</tr>
		</tfoot>
		<tbody>

	<?php
	$holder = "";
	$hide_transaction_filter = (isset($_REQUEST['hide_transaction_filter']) ? $_REQUEST['hide_transaction_filter'] : 0);
	foreach($xml->transactions->transaction as $transaction) {
		$transaction_id = (string)$transaction->id;
		$transaction_date = (string)$transaction->transaction_date;
		$customer_first_name = (string)$transaction->customer_first_name;
		$customer_last_name = (string)$transaction->customer_last_name;
		$is_anonymous = (int)$transaction->is_anonymous;
		$customer_id = (string)$transaction->customer_id;
		
		$customer_name = $customer_last_name . ', ' . $customer_first_name;
		if ($is_anonymous != 1 && $customer_id) $customer_name = '<a href="edit.php?post_type=foxyshop_product&page=foxyshop_customer_management&customer_id_filter=' . $customer_id . '&foxyshop_search=1" title="Customer ' . $customer_id . '">' . $customer_name . '</a>';
		
		$print_receipt_link = "edit.php?foxyshop_search=1&post_type=foxyshop_product&page=foxyshop_order_management&id_filter=" . $transaction_id . "&foxyshop_print_invoice=1&is_test_filter=&skip_print=1";
		
		echo '<tr rel="' . $transaction_id . '">';
		echo '<td>';
		echo '<a href="' . (string)$transaction->receipt_url . '" title="' . __('FoxyCart Receipt') . '" target="_blank" style="float: left;"><img src="' . FOXYSHOP_DIR . '/images/foxycart-icon.png" alt="" align="top" /></a>';
		echo '<strong><a href="#" class="view_detail" style="float: left; line-height: 18px; margin: 0 0 0 5px;">' . $transaction_id . '</a></strong>';
		echo '<div class="row-actions">';
			echo '<span><a href="#" class="view_detail">View Order</a> | </span>';
			echo '<span><a href="' . $print_receipt_link . '" title="' . __('Printable Receipt') . '" target="_blank">Receipt</a></span>';
			if ($hide_transaction_filter == 1) {
				echo '<span> | <a href="#" class="set_order_hidden_status" rel="0">Un-Archive</a></span>';
			} else {
				echo '<span> | <a href="#" class="set_order_hidden_status" rel="1">Archive</a></span>';
			}
			do_action("foxyshop_order_line_item", $transaction);
		echo '</div>';
		echo '</td>';
		echo '<td>' . $transaction_date . '</td>';
		echo '<td>' . $customer_name . '</td>';
		echo '<td>' . foxyshop_currency((double)$transaction->order_total) . '</td>';
		do_action("foxyshop_order_line_end", $transaction);
		echo '</tr>'."\n";

		//Write Out Order Details Holder
		$holder .= '<div class="detail_holder" id="holder_' . $transaction_id. '">'."\n";

		$holder .= '<div class="foxyshop_list_col">';
		$holder .= '<h4>Transaction Details</h4>';
		$holder .= '<ul>';
		$holder .= '<li>Order ID: ' . $transaction->id . '</li>';
		$holder .= '<li>Date: ' . $transaction->transaction_date. '</li>';
		$holder .= '<li>' . $transaction->processor_response. '</li>';
		if ((string)$transaction->cc_number_masked != "") $holder .= '<li>' . __('Card') . ': ' . $transaction->cc_number_masked. ' (' . $transaction->cc_type . ')</li>';
		if ((string)$transaction->cc_exp_month != "") $holder .= '<li>' . __('Exp') . ': ' . $transaction->cc_exp_month . '-' . $transaction->cc_exp_year . '</li>';
		if ((string)$transaction->shipto_shipping_service_description != "") $holder .= '<li>Shipping Type: ' . $transaction->shipto_shipping_service_description . '</li>';
		$holder .= '</ul>';
		$holder .= '</div>';

		$holder .= '<div class="foxyshop_list_col">';
		$holder .= '<h4>Order Details</h4>';
		$holder .= '<ul>';
		$holder .= '<li>' . FOXYSHOP_PRODUCT_NAME_PLURAL . ': ' . foxyshop_currency((double)$transaction->product_total) . '</li>';
		$holder .= '<li>Tax: ' . foxyshop_currency((double)$transaction->tax_total) . '</li>';
		$holder .= '<li>Shipping: ' . foxyshop_currency((double)$transaction->shipping_total) . '</li>';
		$holder .= '<li><strong>Order Total: ' . foxyshop_currency((double)$transaction->order_total) . '</strong></li>';
		$holder .= '<li>&nbsp;</li>';

		//Discounts
		foreach($transaction->discounts->discount as $discount) {
			$holder .= '<li>' . $discount->name . ': ' . foxyshop_currency((double)$discount->amount) . '</li>';
		}

		$holder .= '</ul>';
		$holder .= '</div>';

		$holder .= '<div class="foxyshop_list_col">';
		$holder .= '<h4>Customer Address</h4>';
		$holder .= '<ul>';
		$holder .= '<li>' . $transaction->customer_first_name . ' ' . $transaction->customer_last_name . '</li>';
		if ((string)$transaction->customer_company != "") $holder .= '<li>' . $transaction->customer_company . '</li>';
		$holder .= '<li>' . $transaction->customer_address1 . '</li>';
		if ((string)$transaction->customer_address2 != "") $holder .= '<li>' . $transaction->customer_address2 . '</li>';
		$holder .= '<li>' . $transaction->customer_city . ', ' . $transaction->customer_state . ' ' . $transaction->customer_postal_code . '</li>';
		$holder .= '<li>' . $transaction->customer_country . '</li>';
		$holder .= '</ul>';
		$holder .= '</div>';

		//Shipping Addresses (if entered)
		if ($transaction->shipping_first_name != "") {
			$holder .= '<div class="foxyshop_list_col">';
			$holder .= '<h4>Shipping Details</h4>';
			$holder .= '<ul>';
			$holder .= '<li>' . $transaction->shipping_first_name . ' ' . $transaction->shipping_last_name . '</li>';
			if ((string)$transaction->shipping_company != "") $holder .= '<li>' . $transaction->shipping_company . '</li>';
			$holder .= '<li>' . $transaction->shipping_address1 . '</li>';
			if ((string)$transaction->shipping_address2 != "") $holder .= '<li>' . $transaction->shipping_address2 . '</li>';
			$holder .= '<li>' . $transaction->shipping_city . ', ' . $transaction->shipping_state . ' ' . $transaction->shipping_postal_code . '</li>';
			$holder .= '<li>' . $transaction->shipping_country . '</li>';
			if ((string)$transaction->shipping_phone != "") $holder .= '<li>' . $transaction->shipping_phone . '</li>';
			$holder .= '</ul>';
			$holder .= '</div>';
		}

		//Multi-ship Addresses
		foreach($transaction->shipto_addresses->shipto_address as $shipto_address) {
			$holder .= '<div class="foxyshop_list_col">';
			$holder .= '<h4>Shipping Details: ' . $shipto_address->address_name . '</h4>';
			$holder .= '<ul>';
			$holder .= '<li>' . $shipto_address->shipto_first_name . ' ' . $shipto_address->shipto_last_name . '</li>';
			if ((string)$shipto_address->shipto_company != "") $holder .= '<li>' . $shipto_address->shipto_company . '</li>';
			$holder .= '<li>' . $shipto_address->shipto_address1 . '</li>';
			if ((string)$shipto_address->shipto_address2 != "") $holder .= '<li>' . $shipto_address->shipto_address2 . '</li>';
			$holder .= '<li>' . $shipto_address->shipto_city . ', ' . $shipto_address->shipto_state . ' ' . $shipto_address->shipto_postal_code . '</li>';
			$holder .= '<li>' . $shipto_address->shipto_country . '</li>';
			if ((string)$shipto_address->shipto_phone != "") $holder .= '<li>' . $shipto_address->shipto_phone . '</li>';
			$holder .= '<li><br />Method: ' . $shipto_address->shipto_shipping_service_description . '</li>';
			$holder .= '<li>Shipping: ' .  foxyshop_currency((double)$shipto_address->shipto_shipping_total) . '</li>';
			$holder .= '</ul>';
			$holder .= '</div>';
		}

		//Customer Details
		$holder .= '<div class="foxyshop_list_col">';
		$holder .= '<h4>Customer Details</h4>';
		$holder .= '<ul>';
		if ((string)$transaction->customer_phone != "") $holder .= '<li>' . $transaction->customer_phone . '</li>';
		$holder .= '<li><a href="mailto:' . $transaction->customer_email . '">' . $transaction->customer_email . '</a></li>';
		$holder .= '<li><a href="http://whatismyipaddress.com/ip/' . $transaction->customer_ip . '" target="_blank">' . $transaction->customer_ip . '</a></li>';
		$holder .= '<li>&nbsp;</li>';

		//Custom Fields
		foreach($transaction->custom_fields->custom_field as $custom_field) {
			if ($custom_field->custom_field_name != 'ga') {
				$holder .= '<li><strong>' . str_replace("_"," ",$custom_field->custom_field_name) . ':</strong> ' . $custom_field->custom_field_value . '</li>';
			}
		}

		$holder .= '</ul>';
		$holder .= '</div>';


		$holder .= '<div style="clear: both; height: 20px;"></div>';

		foreach($transaction->transaction_details->transaction_detail as $transaction_details) {
			$holder .= '<div class="product_listing">';
			if ($transaction_details->image != "") {
				$holder .= '<div class="image_div">';
				if ($transaction_details->url != "") $holder .= '<a href="' . $transaction_details->url . '" target="_blank">';
				$holder .= '<img src="' . $transaction_details->image . '" />';
				if ($transaction_details->url != "") $holder .= '</a>';
				$holder .= '</div>';
			}
			$holder .= '<div class="details_div">';
			$holder .= '<h4>' . $transaction_details->product_name . '</h4>';
			$holder .= '<ul>';
			if ((string)$transaction_details->shipto != "") $holder .= '<li>Ship To: ' . $transaction_details->shipto . '</li>';
			$holder .= '<li>Code: ' . $transaction_details->product_code . '</li>';
			$holder .= '<li>Price: ' . foxyshop_currency((double)$transaction_details->product_price). '</li>';
			$holder .= '<li>Qty: ' . $transaction_details->product_quantity . '</li>';
			if ((string)$transaction_details->product_weight != "0.000") $holder .= '<li>Weight: ' . $transaction_details->product_weight . '</li>';
			if ((string)$transaction_details->category_code != "DEFAULT") $holder .= '<li>Category: ' . $transaction_details->category_description . '</li>';
			if ((string)$transaction_details->product_delivery_type != "shipped") $holder .= '<li>Delivery Type: ' . $transaction_details->product_delivery_type . '</li>';
			if ((string)$transaction_details->downloadable_url != "") $holder .= '<li>Downloadable URL: <a href="' . $transaction_details->downloadable_url . '" target="_blank">Click Here</a></li>';
			if ($transaction_details->subscription_frequency != "") {
				$holder .= '<li>Subscription Frequency: ' . $transaction_details->subscription_frequency . '</li>';
				$holder .= '<li>Subscription Start Date: ' . $transaction_details->subscription_startdate . '</li>';
				$holder .= '<li>Subscription Next Date: ' . $transaction_details->subscription_nextdate . '</li>';
				if ((string)$transaction_details->subscription_enddate != "0000-00-00") $holder .= '<li>Subscription End Date: ' . $transaction_details->subscription_enddate . '</li>';
			}
			foreach($transaction_details->transaction_detail_options->transaction_detail_option as $transaction_detail_option) {
				$holder .= '<li>';
				$holder .= str_replace("_", " ", $transaction_detail_option->product_option_name) . ': ';
				if (substr($transaction_detail_option->product_option_value,0,5) == "file-") {
					$upload_dir = wp_upload_dir();
					$holder .= '<a href="' . $upload_dir['baseurl'] . '/customuploads/' . $transaction_detail_option->product_option_value . '" target="_blank">' . $transaction_detail_option->product_option_value . '</a>';
				} else {
					$holder .= $transaction_detail_option->product_option_value;
				}
				if ((string)$transaction_detail_option->price_mod != '0.000') $holder .= ' (' . (strpos("-",$transaction_detail_option->price_mod) >= 0 ? '' : '+') . foxyshop_currency((double)$transaction_detail_option->price_mod) . ')';
				$holder .= '</li>';
			}

			$holder .= '</ul>';
			$holder .= '</div>';
			$holder .= '<div style="clear: both;"></div>';
			$holder .= '</div>';
		}
		$holder .= '<div style="clear: both; height: 10px;"></div>';
		$holder .= '</div>';


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



		$(".set_order_hidden_status").click( function() {
			var hide_transaction = $(this).attr("rel");
			var transaction_id = $(this).parents("tr").attr("rel");
			var data = {
				action: 'foxyshop_display_list_ajax_action',
				security: '<?php echo wp_create_nonce("foxyshop-display-list-function"); ?>',
				hide_transaction: hide_transaction,
				foxyshop_action: 'hide_transaction',
				id: transaction_id
			};
			$.post(ajaxurl, data, function(response) {
			<?php if ($hide_transaction_filter == 0) { ?>
				$("tr[rel="+transaction_id+"]").remove();
				$("#foxyshop-list-inline #holder_" + transaction_id).remove();
			<?php } else { ?>
				alert(response);
			<?php } ?>
			});
			
			return false;
		});

	});
	</script>

	
	<?php
	//Pagination
	$p = (int)(version_compare($foxyshop_settings['version'], '0.7.0', "==") ? 50 : 50);
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