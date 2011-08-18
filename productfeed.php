<?php
/*
This file is setup to provide you with the ability to create a product feed that can be sent out to various aggregators. This is designed specifically for Google.
*/

//Only run this if generate feed option is turned on
if ($foxyshop_settings['generate_feed']) add_action('admin_menu', 'foxyshop_product_feed_menu');

if (isset($_GET['create_google_product_feed'])) {
	add_action('admin_init', 'foxyshop_save_feed_file');
}

//Put in Sidebar
function foxyshop_product_feed_menu() {    
	add_submenu_page('edit.php?post_type=foxyshop_product', FOXYSHOP_PRODUCT_NAME_SINGULAR . ' ' . __('Feed'), FOXYSHOP_PRODUCT_NAME_SINGULAR . ' ' . __('Feed'), 'manage_options', 'foxyshop_product_feed', 'foxyshop_product_feed');
}

function foxyshop_dblquotes($str) {
	return str_replace('"','""',$str);
}


//The Main Function
function foxyshop_product_feed() {
		echo '<div class="wrap">';
		echo '<h2>' . FOXYSHOP_PRODUCT_NAME_SINGULAR . ' ' . __('Feed') . '</h2>'."\n";
		echo "<p>" . __('If you would like to <a href="http://www.google.com/merchants" target="_blank">submit your products to Google</a>, you may do so by creating a product feed on this page. Make sure that you check the option that <a href="http://www.google.com/support/merchants/bin/answer.py?answer=160037" target="_blank">enables double quotes</a>.') . "</p>";
		echo '<p><a href="edit.php?post_type=foxyshop_product&amp;page=foxyshop_product_feed&amp;create_google_product_feed=1" class="button">Create Google Product Feed</a></p>';
}



function foxyshop_save_feed_file() {
	// Define the path to file
	$filename = 'Google-Product-Feed.txt';

	// Set headers
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
	header("Content-Type: text/plain");
	header("Content-Transfer-Encoding: binary");
	//header('Content-Length: '. filesize($filename)); 

	foxyshop_create_feed();
	die;
}

function foxyshop_create_feed() {
	global $product;
	
	//Field Names
	$fieldnames = array('id', 'title', 'link', 'price', 'description', 'condition', 'gtin', 'brand', 'mpn', 'image_link', 'product_type', 'quantity', 'availability', 'feature', 'online_only', 'manufacturer', 'expiration_date', 'shipping_weight', 'product_review_average', 'product_review_count', 'featured_product', 'excluded_destination', 'color', 'size', 'year', 'author', 'edition');
	$lastfieldname = end($fieldnames);
	
	$write = "";
	foreach($fieldnames as $field) {
		if ($field != $fieldnames[0]) $write .= "\t";
		$write .= '"' . $field . '"';
	}
	$write .= "\n";
	
	$products = get_posts(array('post_type' => 'foxyshop_product', 'post_status' => "publish", 'numberposts' => -1));
	foreach($products as $singleproduct) {
		$product = foxyshop_setup_product($singleproduct);
		
		foreach($fieldnames as $fieldname) {
			
			$write .= '"';
			switch ($fieldname) {
				case "id":
					$write .= foxyshop_dblquotes($product['code']); break;
				case "title":
					$write .= foxyshop_dblquotes($product['name']); break;
				case "link":
					$write .= foxyshop_dblquotes($product['url']); break;
				case "price":
					$write .= foxyshop_dblquotes($product['price']); break;
				case "description":
					$write .= foxyshop_dblquotes(strip_tags($product['description'])); break;
				case "condition":
					$write .= "New"; break;
				case "image_link":
					$write .= foxyshop_dblquotes(foxyshop_get_main_image()); break;
				default:
					$write .= foxyshop_dblquotes(get_post_meta($singleproduct->ID, $fieldname, true)); break;
			}
			$write .= '"';
		
			if ($fieldname != $lastfieldname) $write .= "\t";
		}
		$write .= "\n";
	}
	
	echo $write;
}

?>