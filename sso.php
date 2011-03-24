<?php
//When Saving Profile, These Actions Sync Data to FoxyCart
add_action('profile_update', 'foxyshop_profile_update');
add_action('user_register', 'foxyshop_profile_add');
function foxyshop_profile_update($user_id) {
	
	//Get User Info
	$foxycart_customer_id = get_user_meta($user_id, 'foxycart_customer_id', TRUE) ;
	
	//Send Updated Info to FoxyCart
	$foxy_data = array("api_action" => "customer_save", "customer_id" => $foxycart_customer_id, "customer_email" => $_POST['email']);
	if (isset($_POST['pass1'])) $foxy_data['customer_password'] = $_POST['pass1'];
	if (isset($_POST['first_name'])) $foxy_data['customer_first_name'] = $_POST['first_name'];
	if (isset($_POST['last_name'])) $foxy_data['customer_last_name'] = $_POST['last_name'];
	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	$foxycart_customer_id = (string)$xml->customer_id;
	if ($foxycart_customer_id) add_user_meta($user_id, 'foxycart_customer_id', $foxycart_customer_id, true);
	var_dump($foxy_response);
	die();
}
function foxyshop_profile_add($user_id) {
	if (isset($_POST['pass1'])) {
		$foxy_data = array("api_action" => "customer_save", "customer_email" => $_POST['email'], "customer_password" => $_POST['pass1']);
		if (isset($_POST['first_name'])) $foxy_data['customer_first_name'] = $_POST['first_name'];
		if (isset($_POST['last_name'])) $foxy_data['customer_last_name'] = $_POST['last_name'];
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		$foxycart_customer_id = (string)$xml->customer_id;
		if ($foxycart_customer_id) add_user_meta($user_id, 'foxycart_customer_id', $foxycart_customer_id, true);
	}
}


//Adds a Login Message When Prompting Users to Login Before Checking Out
if (isset($_GET['foxycart_checkout'])) {
	add_filter('login_message', 'foxyshop_login_message');
	add_action('login_head','foxyshop_login_head');
}
function foxyshop_login_head() { ?>
	<style type="text/css">
	#login_error, .message { display:none; }
	.custom-message {
	-moz-border-radius:3px 3px 3px 3px;
	border-style:solid;
	border-width:1px;
	margin:0 0 16px 8px;
	padding:12px;
	}
	.login .custom-message {
	background-color:#FFFFE0;
	border-color:#E6DB55;
	}
	</STYLE><?php
}

function foxyshop_login_message() {
	$message = 'Please Login Before Checking Out';
	return '<p class="custom-message">' . $message . '</p><br />';
}

?>