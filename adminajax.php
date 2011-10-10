<?php

//Display List AJAX Functions
add_action('wp_ajax_foxyshop_display_list_ajax_action', 'foxyshop_display_ajax');
function foxyshop_display_ajax() {
	global $wpdb, $foxyshop_settings;
	check_ajax_referer('foxyshop-display-list-function', 'security');
	$id = (isset($_POST['id']) ? $_POST['id'] : 0);
	if (!isset($_POST['foxyshop_action'])) die;
	
	//Change Subscription
	if ($_POST['foxyshop_action'] == 'subscription_modify') {
		$foxy_data = array(
			"api_action" => "subscription_modify",
			"sub_token" => $_POST['sub_token'],
			"start_date" => $_POST['start_date'],
			"frequency" => $_POST['frequency'],
			"past_due_amount" => $_POST['past_due_amount'],
			"is_active" => $_POST['is_active']
		);
		if ($_POST['end_date'] == "0000-00-00" || strtotime($_POST['end_date']) > strtotime("now")) $foxy_data['end_date'] = $_POST['end_date'];
		if (strtotime($_POST['next_transaction_date']) > strtotime("now")) $foxy_data['next_transaction_date'] = $_POST['next_transaction_date'];
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		echo (string)$xml->result . ": " . (string)$xml->messages->message;
		die;
	
	//Hide/Unhide Transaction
	} elseif ($_POST['foxyshop_action'] == 'hide_transaction') {
		$foxy_data = array("api_action" => "transaction_modify", "transaction_id" => $id, "hide_transaction" => (int)$_POST['hide_transaction']);
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		echo (string)$xml->result . ": " . (string)$xml->messages->message;
		die;
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
				$write .= '<div class="foxyshop_image_holder"><img src="' . $thumbnailSRC[0] . '" alt="' . esc_attr($attachment->post_title) . ' (' . $attachment->ID . ')" title="' . esc_attr($attachment->post_title) . ' (' . $attachment->ID . ')" /></div>';
				$write .= '<div style="clear: both;"></div>';
				$write .= '<a href="#" class="foxyshop_image_delete" rel="' . $attachment->ID . '" alt="Delete" title="Delete">Delete</a>';
				$write .= '<a href="#" class="foxyshop_image_rename" rel="' . $attachment->ID . '" alt="Rename" title="Rename">Rename</a>';
				$write .= '<a href="#" class="foxyshop_image_featured" rel="' . $attachment->ID . '" alt="Make Featured Image" title="Make Featured Image">Make Featured Image</a>';
				$write .= '<div class="renamediv" id="renamediv_' . $attachment->ID . '">';
				$write .= '<input type="text" name="rename_' . $attachment->ID . '" id="rename_' . $attachment->ID . '" rel="' . $attachment->ID . '" value="' . esc_attr($attachment->post_title) . '" />';
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