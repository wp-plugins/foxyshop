<?php
//SSO ENDPOINT TEMPLATE
if (isset($_GET['fcsid']) && isset($_GET['timestamp'])) {
	global $foxyshop_settings;
	global $current_user;
	
	$login_url = get_bloginfo('wpurl') . '/wp-login.php';
	
	//If you don't want to redirect to the wp-login screen for your login/create account page, define this constant in your wp-config.php file.
	if (defined('FOXYSHOP_SSO_REDIRECT_URL')) $login_url = FOXYSHOP_SSO_REDIRECT_URL;


	if(!is_user_logged_in()) {
		
		//Force a Straight Redirect
		if ($foxyshop_settings['sso_account_required'] == 1) {
			header('Location: ' . $login_url . '?redirect_to=' . urlencode(get_bloginfo('url') . '/foxycart-sso-' . $foxyshop_settings['inventory_url_key'] . '/?timestamp=' . $_GET['timestamp'] . '&fcsid=' . $_GET['fcsid']) . '&foxycart_checkout=1&reauth=1');
			die;

		//Check Cart Contents to Decide on Redirect
		} elseif ($foxyshop_settings['sso_account_required'] == 2) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://" . esc_attr($foxyshop_settings['domain']) . "/cart?fcsid=" . $_GET['fcsid'] . "&output=json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			// If you get SSL errors, you can uncomment the following, or ask your host to add the appropriate CA bundle
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$curlout = trim(curl_exec($ch));
			$sso_required = 0;
		
			$response = json_decode($curlout, true);
			foreach($response['products'] as $product){
				$code = $product['code'];
				$product_name = $product['name'];
				$product_id = 0;
				
				//Skip This if Login Already True
				if (!$sso_required) {
					//Lookup Product Code
					$product_check = get_posts('post_type=foxyshop_product&meta_key=_code&meta_value=' . $code);
					if ($product_check) {
						foreach($product_check as $check1) {
							$product_id = $check1->ID;
						}
					//If Not Found, Lookup ID
					} else {
						$product_check = get_posts('post_type=foxyshop_product&page_id=' . (int)$code);
						if ($product_check) {
							foreach($product_check as $check1) {
								if ($check1->ID == (int)$code) $product_id = $check1->ID;
							}
						}
					}

					if ($product_id > 0) {
						echo get_post_meta($product_id,'_require_sso', true);
						if (get_post_meta($product_id,'_require_sso', true) == "on") $sso_required = 1;
					}
				}
			}

			//Do the Signup Redirect
			if ($sso_required) {
				header('Location: ' . $login_url . '?redirect_to=' . urlencode(get_bloginfo('url') . '/foxycart-sso-' . $foxyshop_settings['inventory_url_key'] . '/?timestamp=' . $_GET['timestamp'] . '&fcsid=' . $_GET['fcsid']) . '&foxycart_checkout=1&reauth=1');
				die;
			
			//No Redirect Required
			} else {
				$customer_id = 0;
			}
		
		//No Redirect Required
		} else {
			$customer_id = 0;
		}
	
	//Already Logged In, Get Account Info.
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