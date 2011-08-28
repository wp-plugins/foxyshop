<?php

//Display List AJAX Functions
add_action('wp_ajax_foxyshop_display_list_ajax_action', 'foxyshop_display_ajax');
function foxyshop_display_ajax() {
	global $wpdb;
	$id = (isset($_POST['id']) ? $_POST['id'] : 0);
	check_ajax_referer('foxyshop-display-list-function', 'security');
	if (!isset($_POST['foxyshop_action'])) die;
	
	if ($_POST['foxyshop_action'] == "start_date" || $_POST['foxyshop_action'] == "end_date" || $_POST['foxyshop_action'] == "next_transaction_date") {
		$foxy_data = array("api_action" => "subscription_modify", "sub_token" => $id, $_POST['foxyshop_action'] => $_POST[$_POST['foxyshop_action']]);
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		echo "<strong>" . $xml->result . ":</strong> " . $xml->messages->message;

	} elseif ($_POST['foxyshop_action'] == "frequency" || $_POST['foxyshop_action'] == "past_due_amount") {
		$foxy_data = array("api_action" => "subscription_modify", "sub_token" => $id, $_POST['foxyshop_action'] => $_POST[$_POST['foxyshop_action']]);
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		echo "<strong>" . $xml->result . ":</strong> " . $xml->messages->message;

	} elseif ($_POST['foxyshop_action'] == "sub_on" || $_POST['foxyshop_action'] == "sub_off") {
		$foxy_data = array("api_action" => "subscription_modify", "sub_token" => $id, "is_active" => ($_POST['foxyshop_action'] == "sub_off" ? 0 : 1));
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		echo "<strong>" . $xml->result . ":</strong> " . $xml->messages->message;

	} elseif ($_POST['foxyshop_action'] == "sub_on" || $_POST['foxyshop_action'] == "hide_transaction") {
		$foxy_data = array("api_action" => "transaction_modify", "transaction_id" => $id, "hide_transaction" => 1);
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		echo "<strong>" . $xml->result . ":</strong> " . $xml->messages->message;


	} elseif ($_POST['foxyshop_action'] == "customer_detail") {

		$foxy_data = array("api_action" => "customer_get", "customer_id" => $_POST['id']);
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$customer = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		//echo $foxy_response;
		
		echo '<div class="order_details">';

		//Customer Details
		echo '<div class="foxyshop_list_col">';
		echo '<h4>Customer Details</h4>';
		echo '<ul>';
		if ((string)$customer->customer_phone != "") echo '<li>' . $customer->customer_phone . '</li>';
		echo '<li><a href="mailto:' . $customer->customer_email . '">' . $customer->customer_email . '</a></li>';
		if ((string)$customer->cc_number != "") echo '<li>' . __('Card') . ': ' . $customer->cc_number . '</li>';
		if ((string)$customer->cc_exp_month != "") echo '<li>' . __('Exp') . ': ' . $customer->cc_exp_month . '-' . $customer->cc_exp_year . '</li>';
		echo '<li>&nbsp;</li>';

		echo '</ul>';
		echo '</div>';

		//Customer Address
		echo '<div class="foxyshop_list_col">';
		echo '<h4>Customer Address</h4>';
		echo '<ul>';
		echo '<li>' . $customer->customer_first_name . ' ' . $customer->customer_last_name . '</li>';
		if ((string)$customer->customer_company != "") echo '<li>' . $customer->customer_company . '</li>';
		echo '<li>' . $customer->customer_address1 . '</li>';
		if ((string)$customer->customer_address2 != "") echo '<li>' . $customer->customer_address2 . '</li>';
		if ((string)$customer->customer_city != "") echo '<li>' . $customer->customer_city . ', ' . $customer->customer_state . ' ' . $customer->customer_postal_code . '</li>';
		if ((string)$customer->customer_country != "") echo '<li>' . $customer->customer_country . '</li>';
		echo '</ul>';
		echo '</div>';

		//Shipping Addresses (if entered)
		if ($customer->shipping_first_name != "") {
			echo '<div class="foxyshop_list_col">';
			echo '<h4>Shipping Details</h4>';
			echo '<ul>';
			echo '<li>' . $customer->shipping_first_name . ' ' . $customer->shipping_last_name . '</li>';
			if ((string)$customer->shipping_company != "") echo '<li>' . $customer->shipping_company . '</li>';
			echo '<li>' . $customer->shipping_address1 . '</li>';
			if ((string)$customer->shipping_address2 != "") echo '<li>' . $customer->shipping_address2 . '</li>';
			echo '<li>' . $customer->shipping_city . ', ' . $customer->shipping_state . ' ' . $customer->shipping_postal_code . '</li>';
			echo '<li>' . $customer->shipping_country . '</li>';
			if ((string)$customer->shipping_phone != "") echo '<li>' . $customer->shipping_phone . '</li>';
			echo '</ul>';
			echo '</div>';
		}

		//Multi-ship Addresses
		foreach($customer->shipto_addresses->shipto_address as $shipto_address) {
			echo '<div class="foxyshop_list_col">';
			echo '<h4>Shipping Details: ' . $shipto_address->address_name . '</h4>';
			echo '<ul>';
			echo '<li>' . $shipto_address->shipto_first_name . ' ' . $shipto_address->shipto_last_name . '</li>';
			if ((string)$shipto_address->shipto_company != "") echo '<li>' . $shipto_address->shipto_company . '</li>';
			echo '<li>' . $shipto_address->shipto_address1 . '</li>';
			if ((string)$shipto_address->shipto_address2 != "") echo '<li>' . $shipto_address->shipto_address2 . '</li>';
			echo '<li>' . $shipto_address->shipto_city . ', ' . $shipto_address->shipto_state . ' ' . $shipto_address->shipto_postal_code . '</li>';
			echo '<li>' . $shipto_address->shipto_country . '</li>';
			if ((string)$shipto_address->shipto_phone != "") echo '<li>' . $shipto_address->shipto_phone . '</li>';
			echo '</ul>';
			echo '</div>';
		}
		echo '<div style="clear: both; height: 20px;"></div>';
			


	} elseif ($_POST['foxyshop_action'] == "order_detail") {

		$foxy_data = array("api_action" => "transaction_get", "transaction_id" => $_POST['id']);
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		//echo $foxy_response;
		
		foreach($xml->transaction as $transaction) {
			echo '<div class="order_details">';

			echo '<div class="foxyshop_list_col">';
			echo '<h4>Transaction Details</h4>';
			echo '<ul>';
			echo '<li>Order ID: ' . $transaction->id . '</li>';
			echo '<li>Date: ' . $transaction->transaction_date. '</li>';
			echo '<li>' . $transaction->processor_response. '</li>';
			if ((string)$transaction->cc_number_masked != "") echo '<li>' . __('Card') . ': ' . $transaction->cc_number_masked. ' (' . $transaction->cc_type . ')</li>';
			if ((string)$transaction->cc_exp_month != "") echo '<li>' . __('Exp') . ': ' . $transaction->cc_exp_month . '-' . $transaction->cc_exp_year . '</li>';
			if ((string)$transaction->shipto_shipping_service_description != "") echo '<li>Shipping Type: ' . $transaction->shipto_shipping_service_description . '</li>';
			echo '</ul>';
			echo '</div>';

			echo '<div class="foxyshop_list_col">';
			echo '<h4>Order Details</h4>';
			echo '<ul>';
			echo '<li>' . FOXYSHOP_PRODUCT_NAME_PLURAL . ': ' . foxyshop_currency((double)$transaction->product_total) . '</li>';
			echo '<li>Tax: ' . foxyshop_currency((double)$transaction->tax_total) . '</li>';
			echo '<li>Shipping: ' . foxyshop_currency((double)$transaction->shipping_total) . '</li>';
			echo '<li><strong>Order Total: ' . foxyshop_currency((double)$transaction->order_total) . '</strong></li>';
			echo '<li>&nbsp;</li>';
			
			//Discounts
			foreach($transaction->discounts->discount as $discount) {
				echo '<li>' . $discount->name . ': ' . foxyshop_currency((double)$discount->amount) . '</li>';
			}
			
			echo '</ul>';
			echo '</div>';

			echo '<div class="foxyshop_list_col">';
			echo '<h4>Customer Address</h4>';
			echo '<ul>';
			echo '<li>' . $transaction->customer_first_name . ' ' . $transaction->customer_last_name . '</li>';
			if ((string)$transaction->customer_company != "") echo '<li>' . $transaction->customer_company . '</li>';
			echo '<li>' . $transaction->customer_address1 . '</li>';
			if ((string)$transaction->customer_address2 != "") echo '<li>' . $transaction->customer_address2 . '</li>';
			echo '<li>' . $transaction->customer_city . ', ' . $transaction->customer_state . ' ' . $transaction->customer_postal_code . '</li>';
			echo '<li>' . $transaction->customer_country . '</li>';
			echo '</ul>';
			echo '</div>';

			//Shipping Addresses (if entered)
			if ($transaction->shipping_first_name != "") {
				echo '<div class="foxyshop_list_col">';
				echo '<h4>Shipping Details</h4>';
				echo '<ul>';
				echo '<li>' . $transaction->shipping_first_name . ' ' . $transaction->shipping_last_name . '</li>';
				if ((string)$transaction->shipping_company != "") echo '<li>' . $transaction->shipping_company . '</li>';
				echo '<li>' . $transaction->shipping_address1 . '</li>';
				if ((string)$transaction->shipping_address2 != "") echo '<li>' . $transaction->shipping_address2 . '</li>';
				echo '<li>' . $transaction->shipping_city . ', ' . $transaction->shipping_state . ' ' . $transaction->shipping_postal_code . '</li>';
				echo '<li>' . $transaction->shipping_country . '</li>';
				if ((string)$transaction->shipping_phone != "") echo '<li>' . $transaction->shipping_phone . '</li>';
				echo '</ul>';
				echo '</div>';
			}
			
			//Multi-ship Addresses
			foreach($transaction->shipto_addresses->shipto_address as $shipto_address) {
				echo '<div class="foxyshop_list_col">';
				echo '<h4>Shipping Details: ' . $shipto_address->address_name . '</h4>';
				echo '<ul>';
				echo '<li>' . $shipto_address->shipto_first_name . ' ' . $shipto_address->shipto_last_name . '</li>';
				if ((string)$shipto_address->shipto_company != "") echo '<li>' . $shipto_address->shipto_company . '</li>';
				echo '<li>' . $shipto_address->shipto_address1 . '</li>';
				if ((string)$shipto_address->shipto_address2 != "") echo '<li>' . $shipto_address->shipto_address2 . '</li>';
				echo '<li>' . $shipto_address->shipto_city . ', ' . $shipto_address->shipto_state . ' ' . $shipto_address->shipto_postal_code . '</li>';
				echo '<li>' . $shipto_address->shipto_country . '</li>';
				if ((string)$shipto_address->shipto_phone != "") echo '<li>' . $shipto_address->shipto_phone . '</li>';
				echo '<li><br />Method: ' . $shipto_address->shipto_shipping_service_description . '</li>';
				echo '<li>Shipping: ' .  foxyshop_currency((double)$shipto_address->shipto_shipping_total) . '</li>';
				echo '</ul>';
				echo '</div>';
			}

			//Customer Details
			echo '<div class="foxyshop_list_col">';
			echo '<h4>Customer Details</h4>';
			echo '<ul>';
			if ((string)$transaction->customer_phone != "") echo '<li>' . $transaction->customer_phone . '</li>';
			echo '<li><a href="mailto:' . $transaction->customer_email . '">' . $transaction->customer_email . '</a></li>';
			echo '<li><a href="http://whatismyipaddress.com/ip/' . $transaction->customer_ip . '" target="_blank">' . $transaction->customer_ip . '</a></li>';
			echo '<li>&nbsp;</li>';

			//Custom Fields
			foreach($transaction->custom_fields->custom_field as $custom_field) {
				if ($custom_field->custom_field_name != 'ga') {
					echo '<li><strong>' . str_replace("_"," ",$custom_field->custom_field_name) . ':</strong> ' . $custom_field->custom_field_value . '</li>';
				}
			}

			echo '</ul>';
			echo '</div>';

			
			echo '<div style="clear: both; height: 20px;"></div>';
			
			foreach($transaction->transaction_details->transaction_detail as $transaction_details) {
				echo '<div class="product_listing">';
				if ($transaction_details->image != "") {
					echo '<div class="image_div">';
					if ($transaction_details->url != "") echo '<a href="' . $transaction_details->url . '" target="_blank">';
					echo '<img src="' . $transaction_details->image . '" />';
					if ($transaction_details->url != "") echo '</a>';
					echo '</div>';
				}
				echo '<div class="details_div">';
				echo '<h4>' . $transaction_details->product_name . '</h4>';
				echo '<ul>';
				if ((string)$transaction_details->shipto != "") echo '<li>Ship To: ' . $transaction_details->shipto . '</li>';
				echo '<li>Code: ' . $transaction_details->product_code . '</li>';
				echo '<li>Price: ' . foxyshop_currency((double)$transaction_details->product_price). '</li>';
				echo '<li>Qty: ' . $transaction_details->product_quantity . '</li>';
				if ((string)$transaction_details->product_weight != "0.000") echo '<li>Weight: ' . $transaction_details->product_weight . '</li>';
				if ((string)$transaction_details->category_code != "DEFAULT") echo '<li>Category: ' . $transaction_details->category_description . '</li>';
				if ((string)$transaction_details->product_delivery_type != "shipped") echo '<li>Delivery Type: ' . $transaction_details->product_delivery_type . '</li>';
				if ((string)$transaction_details->downloadable_url != "") echo '<li>Downloadable URL: <a href="' . $transaction_details->downloadable_url . '" target="_blank">Click Here</a></li>';
				if ($transaction_details->subscription_frequency != "") {
					echo '<li>Subscription Frequency: ' . $transaction_details->subscription_frequency . '</li>';
					echo '<li>Subscription Start Date: ' . $transaction_details->subscription_startdate . '</li>';
					echo '<li>Subscription Next Date: ' . $transaction_details->subscription_nextdate . '</li>';
					if ((string)$transaction_details->subscription_enddate != "0000-00-00") echo '<li>Subscription End Date: ' . $transaction_details->subscription_enddate . '</li>';
				}
				foreach($transaction_details->transaction_detail_options->transaction_detail_option as $transaction_detail_option) {
					echo '<li>';
					echo str_replace("_", " ", $transaction_detail_option->product_option_name) . ': ';
					if (substr($transaction_detail_option->product_option_value,0,5) == "file-") {
						$upload_dir = wp_upload_dir();
						echo '<a href="' . $upload_dir['baseurl'] . '/customuploads/' . $transaction_detail_option->product_option_value . '" target="_blank">' . $transaction_detail_option->product_option_value . '</a>';
					} else {
						echo $transaction_detail_option->product_option_value;
					}
					if ((string)$transaction_detail_option->price_mod != '0.000') echo ' (' . (strpos("-",$transaction_detail_option->price_mod) >= 0 ? '' : '+') . foxyshop_currency((double)$transaction_detail_option->price_mod) . ')';
					echo '</li>';
				}

				echo '</ul>';
				echo '</div>';
				echo '<div style="clear: both;"></div>';
				echo '</div>';
			}
			echo '<div style="clear: both; height: 10px;"></div>';
			
			
	}
			

		
		
		
		
		
		
		
		
		
		
		
		echo '</div>';
		
		
	}
	die;
}





//FoxyShop Product AJAX Functions
add_action('wp_ajax_foxyshop_product_ajax_action', 'foxyshop_product_ajax');
function foxyshop_product_ajax() {
	global $wpdb;
	$productID = (isset($_POST['foxyshop_product_id']) ? $_POST['foxyshop_product_id'] : 0);
	$imageID = (isset($_POST['foxyshop_image_id']) ? $_POST['foxyshop_image_id'] : 0);
	check_ajax_referer('foxyshop-product-image-functions-'.$productID, 'security');
	if (!isset($_POST['foxyshop_action'])) die;
	
	if ($_POST['foxyshop_action'] == "add_new_image") {

		echo foxyshop_redraw_images($productID);
		
	} elseif ($_POST['foxyshop_action'] == "delete_image") {
		wp_delete_attachment($imageID);
		echo foxyshop_redraw_images($productID);

	} elseif ($_POST['foxyshop_action'] == "featured_image") {
		delete_post_meta($productID, "_thumbnail_id");
		update_post_meta($productID,"_thumbnail_id",$imageID);
		echo foxyshop_redraw_images($productID);

	} elseif ($_POST['foxyshop_action'] == "rename_image") {
		$update_post = array();
		$update_post['ID'] = $imageID;
		$update_post['post_title'] = $_POST['foxyshop_new_name'];
		wp_update_post($update_post);
	
	} elseif ($_POST['foxyshop_action'] == "update_image_order") {

		$foxyshop_order_array = $_POST['foxyshop_order_array'];
		$IDs = explode(",", $foxyshop_order_array);
		$result = count($IDs);
		for($i = 0; $i < $result; $i++) {
			$update_post = array();
			$update_post['ID'] = str_replace("att_", "", $IDs[$i]);
			$update_post['menu_order'] = $i+1;
			wp_update_post($update_post);
		}
	
		echo foxyshop_redraw_images($productID);
	
	} elseif ($_POST['foxyshop_action'] == "refresh_images") {
		echo foxyshop_redraw_images($productID);
	}
	die();
}

//Function to redraw images
function foxyshop_redraw_images($id) {
	global $wpdb;
	$write = "";
	$featuredImageID = (has_post_thumbnail($id) ? get_post_thumbnail_id($id) : 0);
	$attachments = get_posts(array('numberposts' => -1, 'post_type' => 'attachment','post_status' => null,'post_parent' => $id, 'order' => 'ASC','orderby' => 'menu_order'));
	if ($attachments) {
		$i = 0;
		foreach ($attachments as $attachment) {
			if (wp_attachment_is_image($attachment->ID)) {
				
				$thumbnailSRC = wp_get_attachment_image_src($attachment->ID, "thumbnail");
				$write .= '<li id="att_' . $attachment->ID . '"'. ($featuredImageID == $attachment->ID || ($featuredImageID == 0 && $i == 0) ? ' class="foxyshop_featured_image"' : '') . '>';
				$write .= '<div class="foxyshop_image_holder"><img src="' . $thumbnailSRC[0] . '" alt="' . htmlspecialchars($attachment->post_title) . ' (' . $attachment->ID . ')" title="' . htmlspecialchars($attachment->post_title) . ' (' . $attachment->ID . ')" /></div>';
				$write .= '<div style="clear: both;"></div>';
				$write .= '<a href="#" class="foxyshop_image_delete" rel="' . $attachment->ID . '" alt="Delete" title="Delete">Delete</a>';
				$write .= '<a href="#" class="foxyshop_image_rename" rel="' . $attachment->ID . '" alt="Rename" title="Rename">Rename</a>';
				$write .= '<a href="#" class="foxyshop_image_featured" rel="' . $attachment->ID . '" alt="Make Featured Image" title="Make Featured Image">Make Featured Image</a>';
				$write .= '<div class="renamediv" id="renamediv_' . $attachment->ID . '">';
				$write .= '<input type="text" name="rename_' . $attachment->ID . '" id="rename_' . $attachment->ID . '" rel="' . $attachment->ID . '" value="' . htmlspecialchars($attachment->post_title) . '" />';
				$write .= '</div>';
				$write .= '<div style="clear: both;"></div>';
				$write .= '</li>';
				$write .= "\n";
				$i++;
			}
		}
	}
	return $write;
}
?>