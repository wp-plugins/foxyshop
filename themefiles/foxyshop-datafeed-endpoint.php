<?php
//Set Globals and Get Settings
global $wpdb;
$foxyshop_settings = unserialize(get_option("foxyshop_settings"));


//Get Transaction Data Post From FoxyCart
if (isset($_POST["FoxyData"]) OR isset($_POST['FoxySubscriptionData'])) {
    	$FoxyData_received = (isset($_POST["FoxyData"])) ? urldecode($_POST["FoxyData"]) : urldecode($_POST["FoxySubscriptionData"]);
    	$FoxyData_encrypted = $FoxyData_received;
	$FoxyData_decrypted = rc4crypt::decrypt($foxyshop_settings['api_key'],$FoxyData_encrypted);

//Nothing to see here... move along
} else {
	die('No Content Received');
}



//For testing, write datafeed to file in foxyshop or theme folder
//$file = FOXYSHOP_PATH.'/themefiles/datafeed.xml';
//$file = STYLESHEETPATH.'/datafeed.xml';
//$fh = fopen($file, 'a') or die("Couldn't open $file for writing!"); 
//fwrite($fh, $FoxyData_decrypted);
//fclose($fh);


//TRANSACTION DATAFEED
if (isset($_POST["FoxyData"])) {


	/*
	-------------------------------------------
	SET THIRD-PARTY DATAFEEDS HERE
	-------------------------------------------
	If you need to use more than one datafeed with FoxyCart (let's say you have different types of integrations), you can
	use this template to resend the FoxyData to your other integrations.

	Caution: If you are using more than one integration and one of them fails, the entire process will fail and that failure will
	be sent to FoxyCart. They'll then try to submit the datafeed again. So if your integrations aren't checking to see if
	data has already been written in, they could be processing data twice.

	If you have more than one additional datafeed and one is more unreliable than another, put the most unreliable one first so that
	any failures will result in a full retry from FoxyCart.

	This template will send the WordPress admin an email if any datafeeds fail.
	*/

	$third_party_feeds = array(
		''
	);

	//If this is a subscription feed we don't want to run any third-party feeds
	if (isset($_POST["FoxySubscriptionData"])) $third_party_feeds = array();

	foreach($third_party_feeds as $feedurl) {
		if ($feedurl) {
			//Initialize cURL
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $feedurl);
			curl_setopt($ch, CURLOPT_POSTFIELDS, array("FoxyData" => urlencode($FoxyData_encrypted)));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			// If you get SSL errors, you can uncomment the following, or ask your host to add the appropriate CA bundle
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = trim(curl_exec($ch));

			//If Error, Send Email and Kill Process
			if ($response == false || $response != 'foxy') {
				$error_msg = ($response == false ? "cURL Error: " . curl_error($ch) : $response);
				$to_email = get_bloginfo('admin_email');
				$message = "A FoxyCart datafeed error was encountered at " . date("F j, Y, g:i a") . ".\n\n";
				$message .= "The feed that failed was $feedurl.\n\n";
				$message .= "The error is listed below:\n\n";
				$message .= $error_msg;
				$headers = 'From: ' . get_bloginfo('name') . ' Server Admin <' . $to_email . '>' . "\r\n";
				wp_mail($to_email, 'Data Feed Error on ' . get_bloginfo('name'), $message, $headers);
				die($error_msg);
			}
			curl_close($ch);
		}
	}
	/*
	-------------------------------------------
	END THIRD-PARTY DATAFEEDS
	-------------------------------------------
	*/





	//Import FoxyData Response and Parse with SimpleXML
	$xml = simplexml_load_string($FoxyData_decrypted, NULL, LIBXML_NOCDATA);

	//For Each Transaction
	foreach($xml->transactions->transaction as $transaction) {

		//Get FoxyCart Customer Information
		$customer_id = (string)$transaction->customer_id;
		$customer_first_name = (string)$transaction->customer_first_name;
		$customer_last_name = (string)$transaction->customer_last_name;
		$customer_email = (string)$transaction->customer_email;
		$customer_password = (string)$transaction->customer_password;

		//For Each Transaction Detail
		foreach($transaction->transaction_details->transaction_detail as $transactiondetails) {
			$product_name = (string)$transactiondetails->product_name;
			$product_code = (string)$transactiondetails->product_code;
			$product_quantity = (int)$transactiondetails->product_quantity;
			$product_quantity = (int)$transactiondetails->product_quantity;
			$sub_token_url = (string)$transactiondetails->sub_token_url;

			//Get List of Target ID's for Inventory Update
			$meta_list = $wpdb->get_results("SELECT post_id, meta_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_inventory_levels' AND meta_value LIKE '%" . str_replace("'","\'",$product_code) . "%'");
			foreach ($meta_list as $meta) {
				$productID = $meta->post_id;
				$metaID = $meta->meta_id;
				$val = unserialize(unserialize($meta->meta_value));
				foreach ($val as $ivcode => $iv) {
					if ($ivcode == $product_code) {
						$original_count = $iv['count'];
						$new_count = $original_count - $product_quantity;
						$alert_level = ($iv['alert'] == '' ? $foxyshop_settings['inventory_alert_level'] : $iv['alert']);
						$val[$ivcode]['count'] = $new_count;
						
						//Send Email Alert Email
						if ($foxyshop_settings['inventory_alert_email'] && $new_count <= $alert_level) {
							
							$subject_line = "Inventory Alert: " . $product_name;
							$to_email = get_bloginfo('admin_email');
							$message = "The inventory for one of your products is getting low:\n\n";
							$message .= "Product Name: $product_name\n";
							$message .= "Product Code: $product_code\n";
							$message .= "Current Inventory Level: $new_count\n";
							$message .= "Inventory Alert Level: $alert_level\n";
							$message .= "\n". get_bloginfo('url') . "/wp-admin/edit.php?post_type=foxyshop_product\n";
							$headers = 'From: ' . get_bloginfo('name') . ' <' . $to_email . '>' . "\r\n";
							wp_mail($to_email, $subject_line, $message, $headers);
						}
						
					}
				}
				//Run the Update
				update_post_meta($productID,"_inventory_levels",serialize($val));
			}


			//Set Subscription Features if using SSO
			if ($foxyshop_settings['enable_subscriptions'] && $foxyshop_settings['enable_sso'] && $sub_token_url != "") {

				//Get WordPress User ID
				$select_user = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'foxycart_customer_id' AND meta_value = '$customer_id'";
				$user_id = $wpdb->get_var($select_user);
				if ($user_id) {

					//Get User's Subscription Array
					$foxyshop_subscription = unserialize(get_user_meta($user_id, 'foxyshop_subscription', true));
					if (!is_array($foxyshop_subscription)) $foxyshop_subscription = array();

					//Add On To Array
					$foxyshop_subscription[$product_code] = array(
						"is_active" => 1,
						"sub_token_url" => $sub_token_url
					);

					//Write Serialized Array Back to DB
					echo update_user_meta($user_id, 'foxyshop_subscription', serialize($foxyshop_subscription));
				}		


			}

			//If you have custom code to run for each product, put it here:




		}
		
		//Add WordPress User
		if ($foxyshop_settings['checkout_customer_create'] && $customer_id != '') {
			
			//Check To See if WordPress User Already Exists
			$current_user = get_user_by_email($customer_email);
			
			//No Return, Add New User, Username will be email address
			if (!$current_user) {
				$new_user_id = wp_insert_user(array(
					'user_login' => $customer_email,
					'user_email' => $customer_email,
					'first_name' => $customer_first_name,
					'last_name' => $customer_last_name,
					'user_email' => $customer_email,
					'user_pass' => wp_generate_password(),
					'user_nicename' => $customer_first_name . ' ' . $customer_last_name,
					'dispaly_name' => $customer_first_name . ' ' . $customer_last_name,
					'nickname' => $customer_first_name . ' ' . $customer_last_name,
					'role' => 'subscriber'
				));
				add_user_meta($new_user_id, 'foxycart_customer_id', $customer_id, true);
				$wpdb->query("UPDATE $wpdb->users SET user_pass = '$customer_password' WHERE ID = $new_user_id");
			
			//Update Password and Add FoxyCart ID # if it wasn't there before
			} else {
				add_user_meta($current_user->ID, 'foxycart_customer_id', $customer_id, true);
				$wpdb->query("UPDATE $wpdb->users SET user_pass = '$customer_password' WHERE ID = $current_user->ID");
			}
		}
		
		

		//If you have custom code to run for each order, put it here:




	}
	
	//Done
	die("foxy");


//SUBSCRIPTION DATAFEED
} elseif (isset($_POST["FoxySubscriptionData"])) {

	$failedDaysBeforeCancel = 7;
	$billingReminderFrequencyInDays = 3;
	$updatePaymentMethodReminderDaysOfTheMonth = array(1,15);

	// make sure we have a valid character encoding
	$enc = mb_detect_encoding($FoxyData_decrypted);
	$FoxyData_decrypted = mb_convert_encoding($FoxyData_decrypted, 'UTF-8', $enc);
	$FoxyDataArray = new SimpleXMLElement($FoxyData_decrypted);
	foreach($FoxyDataArray->subscriptions->subscription AS $subscription) {

		//Get Variables
		$customer_id = $subscription->customer_id;
		$sub_token_url = (string)$subscription->sub_token_url;
		$past_due_amount = $subscription->past_due_amount;
		$end_date = $subscription->end_date;


		foreach($subscription->transaction_details->transaction_detail AS $transaction_detail) {
			//Get Product Code
			$product_code = (string)$transaction_detail->product_code;
		}

		$canceled = 0;
		$sendReminder = 0;
		if (date("Y-m-d",strtotime("now")) == date("Y-m-d", strtotime($subscription->end_date))) {
			// this entry was canceled today...
			$canceled = 1;
		}
		if (!$canceled && $subscription->past_due_amount > 0) {
			$failedDays = floor((strtotime("now") - strtotime($subscription->transaction_date)) / (60 * 60 * 24));
			if ($failedDays > $failedDaysBeforeCancel) {
				$canceled = 1;
			} else {
				if (($failedDays % $billingReminderFrequencyInDays) == 0) {
					$sendReminder = 1;
				}
			}
		}

		//Set Subscription Inactive
		if ($canceled) {

			//Get WordPress User ID
			$select_user = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'foxycart_customer_id' AND meta_value = '$customer_id'";
			$user_id = $wpdb->get_var($select_user);
			if ($user_id) {

				//Get User's Subscription Array
				$foxyshop_subscription = unserialize(get_user_meta($user_id, 'foxyshop_subscription', true));
				if (!is_array($foxyshop_subscription)) $foxyshop_subscription = array();

				//Set To NON-ACTIVE
				$foxyshop_subscription[$product_code] = array(
					"is_active" => 0,
					"sub_token_url" => $sub_token_url
				);

				//Write Serialized Array Back to DB
				update_user_meta($user_id, 'foxyshop_subscription', serialize($foxyshop_subscription));
			}		


		}
		if ($sendReminder) {
			// send reminder emails, etc...
			// This has not been built yet. Feel free to roll your own functionality.
			
			
		}
	}
	// send emails to customers with soon to expire credit cards. Ignore already expired cards, since they should have already been
	// sent an email when their payment failed.
	if (in_array(date("j"),$updatePaymentMethodReminderDaysOfTheMonth)) {
		foreach($FoxyDataArray->payment_methods_soon_to_expire->customer AS $customer) {
			if (mktime(0,0,0,$customer->cc_exp_month+1, 1, $customer->cc_exp_year+0) > strtotime("now")) {
				// email reminders
				// This has not been built yet. Feel free to roll your own functionality.
				
				
			}
		}
	}
	echo "foxysub";
}








// ======================================================================================
// RC4 ENCRYPTION CLASS
// Do not modify.
// ======================================================================================
/**
 * RC4Crypt 3.2
 *
 * RC4Crypt is a petite library that allows you to use RC4
 * encryption easily in PHP. It's OO and can produce outputs
 * in binary and hex.
 *
 * (C) Copyright 2006 Mukul Sabharwal [http://mjsabby.com]
 *     All Rights Reserved
 *
 * @link http://rc4crypt.devhome.org
 * @author Mukul Sabharwal <mjsabby@gmail.com>
 * @version $Id: class.rc4crypt.php,v 3.2 2006/03/10 05:47:24 mukul Exp $
 * @copyright Copyright &copy; 2006 Mukul Sabharwal
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package RC4Crypt
 */
 
/**
 * RC4 Class
 * @package RC4Crypt
 */
class rc4crypt {
	/**
	 * The symmetric encryption function
	 *
	 * @param string $pwd Key to encrypt with (can be binary of hex)
	 * @param string $data Content to be encrypted
	 * @param bool $ispwdHex Key passed is in hexadecimal or not
	 * @access public
	 * @return string
	 */
	function encrypt ($pwd, $data, $ispwdHex = 0)
	{
		if ($ispwdHex)
			$pwd = @pack('H*', $pwd); // valid input, please!
 
		$key[] = '';
		$box[] = '';
		$cipher = '';
 
		$pwd_length = strlen($pwd);
		$data_length = strlen($data);
 
		for ($i = 0; $i < 256; $i++)
		{
			$key[$i] = ord($pwd[$i % $pwd_length]);
			$box[$i] = $i;
		}
		for ($j = $i = 0; $i < 256; $i++)
		{
			$j = ($j + $box[$i] + $key[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for ($a = $j = $i = 0; $i < $data_length; $i++)
		{
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$k = $box[(($box[$a] + $box[$j]) % 256)];
			$cipher .= chr(ord($data[$i]) ^ $k);
		}
		return $cipher;
	}
	/**
	 * Decryption, recall encryption
	 *
	 * @param string $pwd Key to decrypt with (can be binary of hex)
	 * @param string $data Content to be decrypted
	 * @param bool $ispwdHex Key passed is in hexadecimal or not
	 * @access public
	 * @return string
	 */
	function decrypt ($pwd, $data, $ispwdHex = 0)
	{
		return rc4crypt::encrypt($pwd, $data, $ispwdHex);
	}
}

?>