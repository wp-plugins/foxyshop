<?php
/*
This file is setup to provide you with the ability to create a product feed that can be sent out to various aggregators. This is designed specifically for Google.
*/

//Only run this if sort key is set to custom
if ($foxyshop_settings['generate_feed'] != "") {
	add_action('admin_menu', 'foxyshop_product_feed_menu');
}

//Put in Sidebar
function foxyshop_product_feed_menu() {    
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Product Feed'), __('Product Feed'), 'manage_options', 'foxyshop_product_feed', 'foxyshop_product_feed');
}

function createFeed() {
	global $product;
	
	//Field Names
	$fieldnames = array('id', 'title', 'link', 'price', 'description', 'condition', 'gtin', 'brand', 'mpn', 'image_link', 'product_type', 'quantity', 'availability', 'shipping', 'tax', 'feature', 'online_only', 'manufacturer', 'expiration_date', 'shipping_weight', 'product_review_average', 'product_review_count', 'featured_product', 'excluded_destination', 'color', 'size', 'year', 'author', 'edition');
	$lastfieldname = end($fieldnames);
	
	$write = implode("\t",$fieldnames) . "\n";
	$products = get_posts(array('post_type' => 'foxyshop_product', 'numberposts' => -1));
	foreach($products as $singleproduct) {
		$product = foxyshop_setup_product($singleproduct);
		
		foreach($fieldnames as $fieldname) {
			
			$write .= '"';
			switch ($fieldname) {
				case "id":
					$write .= dblquotes($product['code']); break;
				case "title":
					$write .= dblquotes($product['name']); break;
				case "link":
					$write .= dblquotes($product['url']); break;
				case "price":
					$write .= dblquotes($product['price']); break;
				case "description":
					$write .= dblquotes(strip_tags($product['description'])); break;
				case "condition":
					$write .= "New"; break;
				case "image_link":
					$write .= dblquotes(foxyshop_get_main_image()); break;
				default:
					$write .= dblquotes(get_post_meta($singleproduct->ID, $fieldname, true)); break;
			}
			$write .= '"';
		
			if ($fieldname != $lastfieldname) $write .= "\t";
		}
		$write .= "\n";
	}
	
	echo $write;
}

function dblquotes($str) {
	return str_replace('"','""',$str);
}


//The Main Function
function foxyshop_product_feed() {
		echo '<div class="wrap">';
		echo '<h2>' . __('Product Feed') . '</h2>'."\n";
		echo "<p>" . __('If you would like to <a href="http://www.google.com/merchants" target="_blank">submit your products to Google</a>, you may do so by copying and pasting the contents of this box into a txt file and submitting it.') . "</p>"; ?>
		<form>
			<textarea name="feedtext" style="width: 100%; height: 200px;" onclick="this.select();" wrap="off"><?php createFeed(); ?></textarea>
		</form>
		</div>
<?php
}
?>