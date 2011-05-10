<?php
//SSO ENDPOINT TEMPLATE
if (isset($_GET['fcsid']) && isset($_GET['timestamp'])) {
	global $foxyshop_settings;
	global $current_user;
	
	if(!is_user_logged_in()) {
		if ($foxyshop_settings['sso_account_required']) {
			header('Location: ' . get_bloginfo('wpurl') . '/wp-login.php?redirect_to=' . urlencode(get_bloginfo('url') . '/foxycart-sso-' . $foxyshop_settings['inventory_url_key'] . '/?timestamp=' . $_GET['timestamp'] . '&fcsid=' . $_GET['fcsid']) . '&foxycart_checkout=1&&reauth=1');
			die;
		} else {
			$customer_id = 0;
		}
	} else {
		get_currentuserinfo();
		$customer_id = get_user_meta($current_user->ID, "foxycart_customer_id", TRUE);
		$customer_email = $current_user->user_email;
		if (!$customer_id) $customer_id = foxyshop_check_for_customer_id($customer_email);
		if (!$customer_id) $customer_id = foxyshop_add_new_customer_id($customer_email, $current_user->user_pass, $current_user->user_firstname, $current_user->user_lastname);
	}
	
	
	$fcsid = $_GET['fcsid'];
	$timestamp = $_GET['timestamp'];
	$newtimestamp = strtotime("+60 minutes", $timestamp);

	$auth_token = sha1($customer_id . '|' . $newtimestamp . '|' . $foxyshop_settings['api_key']);

	$redirect_complete = 'https://' . $foxyshop_settings['domain'] . '/checkout?fc_auth_token=' . $auth_token . '&fcsid=' . $fcsid . '&fc_customer_id=' . $customer_id . '&timestamp=' . $newtimestamp;
	header('Location: ' . $redirect_complete);
}

function foxyshop_check_for_customer_id($email) {
	global $current_user;
	get_currentuserinfo();
	$foxy_data = array("api_action" => "customer_get", "customer_email" => $email);
	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	if ($xml->result == "SUCCESS") {
		$foxycart_customer_id = (string)$xml->customer_id;
		if ($foxycart_customer_id) add_user_meta($current_user->ID, 'foxycart_customer_id', $foxycart_customer_id, true);
		return $foxycart_customer_id;
	} else {
		return false;
	}
}

function foxyshop_add_new_customer_id($email, $pass, $first_name, $last_name) {
	global $current_user;
	get_currentuserinfo();
	$foxy_data = array("api_action" => "customer_save", "customer_email" => $email, "customer_password_hash" => $pass);
	if ($first_name != '') $foxy_data['customer_first_name'] = $first_name;
	if ($last_name != '') $foxy_data['customer_last_name'] = $last_name;
	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	$foxycart_customer_id = (string)$xml->customer_id;
	if ($foxycart_customer_id) add_user_meta($current_user->ID, 'foxycart_customer_id', $foxycart_customer_id, true);
	return $foxycart_customer_id;
}
?>