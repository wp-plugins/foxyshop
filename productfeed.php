<?php
/*
This file is setup to provide you with the ability to create a product feed that can be sent out to various aggregators. This is designed specifically for Google.
*/

if (isset($_GET['create_google_product_feed'])) add_action('admin_init', 'foxyshop_save_feed_file');
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
	$fieldnames = array(
		'id',
		'item_group_id',
		'google_product_category',
		'product_type',
		'title',
		'link',
		'price',
		'sale_price',
		'sale_price_effective_date',
		'availability',
		'description',
		'condition',
		'gtin',
		'brand',
		'mpn',
		'image_link',
		'additional_image_link',
		'online_only',
		'expiration_date',
		'shipping_weight',
		'product_review_average',
		'product_review_count',
		'featured_product',
		'excluded_destination',
		'color',
		'size',
		'material',
		'pattern',
		'age_group',
		'gender'
	);
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
				case "availability":
					$availability = get_post_meta($product['id'],'availability',1);
					if (!$availability) $availability = "in stock";
					$write .= foxyshop_dblquotes($availability); break;
				case "price":
					$write .= foxyshop_dblquotes($product['originalprice']); break;
				case "sale_price":
					$write .= foxyshop_dblquotes(($product['originalprice'] != $product['price'] ? $product['price'] : ''));
					break;
				case "sale_price_effective_date":
					if ($product['originalprice'] != $product['price']) {
						$salestartdate = get_post_meta($product['id'],'_salestartdate',1);
						$saleenddate = get_post_meta($product['id'],'_saleenddate',1);
						if ($salestartdate == '999999999999999999') $salestartdate = 0;
						if ($saleenddate == '999999999999999999') $saleenddate = 0;
						$salestartdate = ($salestartdate == 0 ? Date("Y-m-d", strtotime("-1 day")) : Date("Y-m-d", $salestartdate));
						$saleenddate = ($saleenddate == 0 ? Date("Y-m-d", strtotime("+1 year")) : Date("Y-m-d", $saleenddate));
						$write .= foxyshop_dblquotes($salestartdate."/".$saleenddate);
					} else {
						$write .= foxyshop_dblquotes('');
					}
					break;
				case "description":
					$write .= foxyshop_dblquotes(strip_tags($product['description'])); break;

				case "product_type":
					$product_type_write = "";
					$categories = wp_get_post_terms($product['id'], 'foxyshop_categories');
					foreach ($categories as $cat) {
						if ($product_type_write) $product_type_write .= "\n";
						$breadcrumbarray = array_reverse(get_ancestors($cat->term_id, 'foxyshop_categories'));
						foreach ($breadcrumbarray as $crumb) {
							$term = get_term_by('id', $crumb, 'foxyshop_categories');
							$product_type_write .= $term->name . ' > ';
						}
						$product_type_write .= $cat->name;
					}

					$write .= foxyshop_dblquotes($product_type_write); break;

				case "condition":
					$condition = get_post_meta($product['id'],'condition',1);
					if (!$condition) $condition = "new";
					$write .= foxyshop_dblquotes($condition); break;

				case "image_link":
					$write .= foxyshop_dblquotes(foxyshop_get_main_image()); break;
				case "additional_image_link":
					$additional_images = array();
					$number_of_additional_images = 0;
					foreach($product['images'] AS $product_image) {
						$number_of_additional_images++;
						if ($product_image['featured'] == 0 && $number_of_additional_images <= 10) {
							$additional_images[] = $product_image['thumbnail'];
						}
					}
					$write .= foxyshop_dblquotes(implode(",", $additional_images)); break;

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