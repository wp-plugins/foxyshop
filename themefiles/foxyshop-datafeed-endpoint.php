<?php
//Set Globals and Get Settings
global $wpdb;
$foxyshop_settings = unserialize(get_option("foxyshop_settings"));

//Get Post From FoxyCart
if (isset($_POST["FoxyData"])) {
    	$FoxyData_encrypted = urldecode($_POST["FoxyData"]);
	$FoxyData_decrypted = rc4crypt::decrypt($foxyshop_settings['api_key'],$FoxyData_encrypted);
} else {
	die('No Content Received');
}

//Import Response and Parse with SimpleXML
$xml = simplexml_load_string($FoxyData_decrypted, NULL, LIBXML_NOCDATA);

//For Each Transaction
foreach($xml->transactions->transaction as $transaction) {
	
	//For Each Transaction Detail
	foreach($transaction->transaction_details->transaction_detail as $transactiondetails) {
		$product_code = $transactiondetails->product_code;
		$product_quantity = $transactiondetails->product_quantity;
		
		//Get List of Target ID's for Inventory Update
		$meta_list = $wpdb->get_results("SELECT post_id, meta_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_inventory_levels' AND meta_value LIKE '%" . str_replace("'","",$product_code) . "%'");
		foreach ($meta_list as $meta) {
			$productID = $meta->post_id;
			$metaID = $meta->meta_id;
			$val = unserialize(unserialize($meta->meta_value));
			foreach ($val as $ivcode => $iv) {
				if ($ivcode == $product_code) {
					$val[$ivcode]['count'] = $iv['count'] - $product_quantity;
				}
			}
			//Run the Update
			update_post_meta($productID,"_inventory_levels",serialize($val));
		}
		
		//If you have custom code to run for each product, put it here:
		
		
		
		
	}
}
die("foxy");








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