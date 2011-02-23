<?php
//Import For The Header
function foxyshop_insert_foxycart_files() {
	global $foxyshop_settings;
	if ($foxyshop_settings['domain']) {
		echo '<!-- BEGIN FOXYCART FILES -->'."\n";
		echo '<script src="http://cdn.foxycart.com/' . str_replace('.foxycart.com','',$foxyshop_settings['domain']) . '/foxycart.complete.js" type="text/javascript" charset="utf-8"></script>'."\n";
		echo '<link rel="stylesheet" href="http://static.foxycart.com/scripts/colorbox/1.3.9/style1_fc/colorbox.css" type="text/css" media="screen" charset="utf-8" />'."\n";
		echo '<!-- END FOXYCART FILES -->'."\n";
	}
}



//Sets up the $product array
function foxyshop_setup_product($thepost = false) {
	global $foxyshop_settings;
	if (!$thepost) {
		global $post;
		$thepost = $post;
	}
	$product = array();
	$product['name'] = $thepost->post_title;
	$product['code'] = (get_post_meta($thepost->ID,'_code', true) ? get_post_meta($thepost->ID,'_code', true) : $thepost->ID);
	$product['description'] = apply_filters('the_content', $thepost->post_content);
	$product['short_description'] = $thepost->post_excerpt;
	$product['originalprice'] = number_format(get_post_meta($thepost->ID,'_price', true),2,".",",");
	$product['sort'] = ((int)get_post_meta($thepost->ID,'_sort', true) > 0 ? (int)get_post_meta($thepost->ID,'_sort', true) : 3);
	$product['quantity_min'] = (int)get_post_meta($thepost->ID,'_quantity_min', true);
	$product['quantity_max'] = (int)get_post_meta($thepost->ID,'_quantity_max', true);
	$product['hide_product'] = get_post_meta($thepost->ID,'_hide_product', true);
	$product['url'] = get_post_permalink($thepost->ID);

	//All fields that are loaded straight in without changing or checking data
	$fields = array('category', 'related_products', 'bundled_products', 'discount_quantity_amount', 'discount_quantity_percentage', 'discount_price_amount', 'discount_price_percentage', 'sub_frequency', 'sub_startdate', 'sub_enddate');
	foreach ($fields as $fieldname) {
		$product[$fieldname] = get_post_meta($thepost->ID,'_'.$fieldname, true);
	}

	//Convert Weight
	$weight = explode(" ", get_post_meta($thepost->ID,'_weight',TRUE));
	$lbs = (int)$weight[0];
	$oz = (int)$weight[1];
	if ($lbs == 0 && $oz == 0) $lbs = $foxyshop_settings['default_weight'];
	
	if ($foxyshop_settings['weight_type'] == 'metric') {
		if ($oz > 0) $oz = number_format(($oz / 1000), 4);
	} else {
		if ($oz > 0) $oz = number_format(($oz / 16), 4);
	}
	$oz = ((strpos($oz, '.') !== false) ? end(explode('.', $oz)) : $oz);
	$product['weight'] = $lbs . "." . $oz;
	
	//Images
	$product['images'] = array();
	$imageNumber = 0;
	if (has_post_thumbnail($thepost->ID)) {
		$featuredImageID = $image_id = get_post_thumbnail_id($thepost->ID);
	} else {
		$featuredImageID = 0;
	}
	$attachments = get_posts(array('numberposts' => -1, 'post_type' => 'attachment','post_status' => null,'post_parent' => $thepost->ID, 'order' => 'ASC','orderby' => 'menu_order'));
	foreach ($attachments as $attachment) {
		$thumbnailSRC = wp_get_attachment_image_src($attachment->ID, "thumbnail");
		$mediumSRC = wp_get_attachment_image_src($attachment->ID, "medium");
		$largeSRC = wp_get_attachment_image_src($attachment->ID, "large");
		$fullSRC = wp_get_attachment_image_src($attachment->ID, "full");
		$imageTitle = $attachment->post_title;
		$product['images'][$imageNumber] = array(
									"id" => $attachment->ID,
									"thumbnail" => $thumbnailSRC[0],
									"medium" => $mediumSRC[0],
									"large" => $largeSRC[0],
									"full" => $fullSRC[0],
									"title" => $imageTitle,
									"featured" => ($featuredImageID == $attachment->ID ? 1 : 0)
									);
		$imageNumber++;
	}
	
	//Sale Price
	$salestartdate = get_post_meta($thepost->ID,'_salestartdate',TRUE);
	$saleenddate = get_post_meta($thepost->ID,'_saleenddate',TRUE);
	if ($salestartdate == '999999999999999999') $salestartdate = 0;
	if ($saleenddate == '999999999999999999') $saleenddate = 0;
	if (get_post_meta($thepost->ID,'_saleprice', true) > 0) {
		$beginningOK = (strtotime("now") > $salestartdate);
		$endingOK = (strtotime("now") < ($saleenddate + 86400) || $saleenddate == 0);
		if ($beginningOK && $endingOK || ($salestartdate == 0 && $saleenddate == 0)) {
			$product['price'] = number_format(get_post_meta($thepost->ID,'_saleprice', true),2,".",",");
		} else {
			$product['price'] = number_format(get_post_meta($thepost->ID,'_price', true),2,".",",");
		}
	} else {
		$product['price'] = number_format(get_post_meta($thepost->ID,'_price', true),2,".",",");
	}

	//Extra Cart Parameters
	$fields = array('cart','empty','coupon','redirect','output');
	foreach ($fields as $fieldname) {
		if (get_post_meta($thepost->ID,$fieldname, true)) $product[$fieldname] = get_post_meta($thepost->ID,$fieldname, true);
	}

	return $product;
}



//Starts the form
function foxyshop_start_form() {
	global $product, $foxyshop_settings;
	echo '<form action="https://' . esc_attr($foxyshop_settings['domain']) . '/cart" method="post" accept-charset="utf-8" class="foxyshop_product">'."\n";
	echo '<input type="hidden" name="price' . foxyshop_get_verification('price') . '" value="' . $product['price'] . '" id="price" />'."\n";
	echo '<input type="hidden" name="x:originalprice" value="' . $product['originalprice'] . '" id="originalprice" />'."\n";
	if (foxyshop_get_main_image() && $foxyshop_settings['version'] != "0.70") echo '<input type="hidden" name="image' . foxyshop_get_verification('image',foxyshop_get_main_image()) . '" value="' . foxyshop_get_main_image() . '" />'."\n";

	$fields = array('name','code','category','weight','discount_quantity_amount','discount_quantity_percentage','discount_price_amount','discount_price_percentage','sub_frequency','sub_startdate','sub_enddate','cart','empty','coupon','redirect','output');
	foreach ($fields as $fieldname) {
		if (array_key_exists($fieldname, $product)) {
			if ($product[$fieldname]) echo '<input type="hidden" name="' . $fieldname . foxyshop_get_verification($fieldname) . '" value="' . esc_attr($product[$fieldname]) . '" />'."\n";
		}
	}
	
	//Bundled Products
	if ($product['bundled_products']) {
		$original_product = $product;
		$bundledproducts = get_posts(array('post_type' => 'foxyshop_product', "post__in" => explode(",",$product['bundled_products']), 'numberposts' => -1));
		$num = 2;
		foreach($bundledproducts as $bundledproduct) {
			$product = foxyshop_setup_product($bundledproduct);
			echo '<input type="hidden" name="' . $num . ':price' . foxyshop_get_verification('price','0.00') . '" value="0.00" />'."\n";
			$fields = array('name','code','category','weight','discount_quantity_amount','discount_quantity_percentage','discount_price_amount','discount_price_percentage','sub_frequency','sub_startdate','sub_enddate');
			foreach ($fields as $fieldname) {
				if ($product[$fieldname]) echo '<input type="hidden" name="' . $num . ':' . $fieldname . foxyshop_get_verification($fieldname) . '" value="' . esc_attr($product[$fieldname]) . '" />'."\n";
			}
			if (foxyshop_get_main_image() && $foxyshop_settings['version'] != "0.70") echo '<input type="hidden" name="' . $num . ':image' . foxyshop_get_verification('image',foxyshop_get_main_image()) . '" value="' . foxyshop_get_main_image() . '" />'."\n";
			$num++;	
		}
		$product = $original_product;
	}

}



//Writes Variations (showQuantity 0 = No, 1 = On Top, 2 = Below)
function foxyshop_product_variations($showQuantity = 0, $showPriceVariations = true) {
	global $post, $product, $foxyshop_settings;
	$writeUploadInclude = 0;
	$write = "";
	
	//Show Quantity Above
	if ($showQuantity == 1) {
		$write .= foxyshop_get_shipto();
		if ($showQuantity) $write .= foxyshop_quantity();
	}
	
	//Loop Through Variations
	for ($i = 1; $i <= $foxyshop_settings['max_variations']; $i++) {
		$variationName = str_replace(' ','_',get_post_meta($post->ID,'_variation_name_'.$i,TRUE));
		$variationType = get_post_meta($post->ID,'_variation_type_'.$i,TRUE);
		$variationValue = get_post_meta($post->ID,'_variation_value_'.$i,TRUE);
		$variationDisplayKey = get_post_meta($post->ID,'_variation_dkey_'.$i,TRUE);
		$variationTextSize = "";
		if (!$variationName) break;
		if ($variationType == "text") {
			$arrVariationText = explode("|",esc_attr($variationValue));
			$variationValue = "";
		}
		if ($variationDisplayKey) {
			$dkey = ' dkey="' . $variationDisplayKey . '"';
			$dkeyclass = " dkey";
		} else {
			$dkey = "";
			$dkeyclass = "";
		}
		
		//Text
		if ($variationType == "text") {
			$write .= '<label for="' . esc_attr($product['code']) . '_' . $i . '" class="' . $dkeyclass . '"'. $dkey . '>' . esc_attr(str_replace('_',' ',$variationName)) . '</label>'."\n";
			
			$write .= '<input type="text" name="' . esc_attr($variationName) . foxyshop_get_verification($variationName,'--OPEN--') . '" id="' . esc_attr($product['code']) . '_' . $i . '" value=""';
			if ((int)$arrVariationText[0] > 0) $write .= ' style="width: ' . (int)$arrVariationText[0] * 6.5 . 'px;"';
			if ($variationDisplayKey) $write .= ' class="dkey" dkey="' . $variationDisplayKey . '"';
			if ($arrVariationText[1]) $write .= ' maxlength="' . $arrVariationText[1] . '"';
			$write .= ' />'."\n";
			$write .= '<div class="clr"></div>'."\n";
		
		//Textarea
		} elseif ($variationType == "textarea") {
			$write .= '<label for="' . esc_attr($product['code']) . '_' . $i . '" class="' . $dkeyclass . '"'. $dkey . '>' . esc_attr(str_replace('_',' ',$variationName)) . '</label>'."\n";
			$write .= '<textarea name="' . esc_attr($variationName) . foxyshop_get_verification($variationName,'--OPEN--') . '" id="' . esc_attr($product['code']) . '_' . $i . '" class="foxyshop_freetext' . $dkeyclass . '" style="height: ' . 16 * (int)$variationValue . 'px;"' . $dkey . '></textarea>'."\n";
			$write .= '<div class="clr"></div>'."\n";
		
		//Upload
		} elseif ($variationType == "upload") {
			include('customupload.php');
		
		//Dropdown Box
		} elseif ($variationType == "dropdown") {
			$write .= '<label for="' . esc_attr($product['code']) . '_' . $i . '" class="' . $dkeyclass . '"'. $dkey . '>' . esc_attr(str_replace('_',' ',$variationName)) . '</label>'."\n";

			$write .= '<select name="' . esc_attr($variationName) . '" id="' . esc_attr($product['code']) . '_' . $i . '" class="' . $dkeyclass . '"' . $dkey . '>'."\n";
			$variations = preg_split("/(\r\n|\n)/", $variationValue);
			foreach($variations as $val) {
				if ($val != '') {
					$strSelected = "";
					$displaykey = "";
					$pricechange = "";
					if (strpos($val,"*") !== false) $strSelected = ' selected="selected"';

					if (strpos($val,"{") !== false) {
						$valtemp = explode("|",substr($val, strpos($val,"{")+1, strpos($val,"}") - (strpos($val,"{")+1)));
						foreach ($valtemp as $valtemp1) {
							if (substr($valtemp1,0,4) == "dkey") $displaykey = substr($valtemp1,5);
							if (substr($valtemp1,0,1) == "p") $pricechange = substr($valtemp1,1);
						}
					}
					if ($pricechange) {
						if (substr($pricechange,1,1) == '-') {
							$displaypricechange = "-$".number_format($pricechange, 2, ".", ",");
							$pricechange = "-$".number_format($pricechange, 2, ".", "");
						} else {
							$displaypricechange = "+$".number_format($pricechange, 2, ".", ",");
							$pricechange = "+$".number_format($pricechange, 2, ".", "");
						}
					}
					$val = str_replace("*","",$val);
					$variationVal = $val;
					if (strpos($variationVal,"{") !== false) $variationVal = substr($variationVal,0,strpos($variationVal,"{"));
					$write .= '<option value="' . esc_attr($val) . foxyshop_get_verification($variationName,$val) . '"' . $strSelected . ($pricechange ? ' pricechange="' . str_replace("$","",$pricechange) . '"' : '') . ($displaykey ? ' displaykey="' . $displaykey . '"' : '') . '>' . $variationVal . ($showPriceVariations && $pricechange ? ' (' . $displaypricechange . ')' : '') . '</option>'."\n";
				}
			}
			$write .= '</select>'."\n";
			$write .= '<div class="clr"></div>'."\n";
		}
	}
	//Show Quantity Below
	if ($showQuantity == 2) {
		$write .= foxyshop_get_shipto();
		if ($showQuantity) $write .= foxyshop_quantity();
	}
	
	if ($write) {
		$write .= '<script type="text/javascript" src="' . FOXYSHOP_DIR . '/js/variation.process.jquery.js"></script>'."\n";
		echo '<div class="foxyshop_variations">' . $write . '</div>'."\n"."\n";
	}
}



//Writes the Ship To Box
function foxyshop_get_shipto() {
	global $foxyshop_settings;
	$write = "";
	if ($foxyshop_settings['enable_ship_to'] == "on") {
		$write .= '<script type="text/javascript" src="' . FOXYSHOP_DIR . '/js/multiship.jquery.js"></script>'."\n";
		$write .= '<div class="shipto_container">'."\n";
		$write .= '<div class="shipto_select" style="display:none">'."\n";
		$write .= '<label>' . __('Ship this item to') . '</label>'."\n";
		$write .= '<select name="x:shipto_name_select">'."\n";
		$write .= '</select>'."\n";
		$write .= '</div>'."\n";
		$write .= '<div class="shipto_name" style="display: none;">'."\n";
		$write .= '<label>' . __('Recipient Name') . '</label>'."\n";
		$write .= '<input type="text" name="shipto' . foxyshop_get_verification("shipto",'--OPEN--') . '" class="shiptoname" value="" />'."\n";
		$write .= '</div>'."\n";
		$write .= '<div class="clr"></div>'."\n";
		$write .= '</div>'."\n";
	}
	return $write;
}



//Writes the Quantity Box
function foxyshop_quantity($qty = "1") {
	global $product;
	if ($product['quantity_min'] > 0) $qty = $product['quantity_min'];
	$write = '<label for="quantity">' . __('Quantity') . '</label>'."\n";
	if ($product['quantity_max'] > 0) {
		$write .= '<select name="quantity">';
		for ($i=($product['quantity_min'] > 0 ? $product['quantity_min'] : 1); $i <= $product['quantity_max']; $i++) {
			$write .= '<option value="' . $i . '">' . $i . '</option>'."\n";
		}
		$write .= '</select>'."\n";
	} else {
		$write .= '<input type="text" name="quantity' . foxyshop_get_verification('quantity','--OPEN--') . '" id="quantity" value="' . esc_attr($qty) . '" class="foxyshop_quantity" />'."\n";
	}
	$write .= '<div class="clr" style="height: 1px;"></div>'."\n";
	return $write;
}



//Writes a Straight Text Link
function foxyshop_product_link($AddText = "-1", $linkOnly = false) {
	global $product, $foxyshop_settings;
	
	if ($AddText == "-1") $AddText = __("Add To Cart");
	$url = 'price' . foxyshop_get_verification('price') . '=' . urlencode($product['price']);
	if (foxyshop_get_main_image() && $foxyshop_settings['version'] != "0.70") $url .= '&amp;image' . foxyshop_get_verification('image',foxyshop_get_main_image()) . '=' . urlencode(foxyshop_get_main_image());
	$fields = array('name','code','category','weight','discount_quantity_amount','discount_quantity_percentage','discount_price_amount','discount_price_percentage','sub_frequency','sub_startdate','sub_enddate','cart','empty','coupon','redirect','output');
	foreach ($fields as $fieldname) {
		if ($product[$fieldname]) $url .= '&amp;' . urlencode(esc_attr($fieldname)) . foxyshop_get_verification($fieldname) . '=' . urlencode($product[$fieldname]);
	}

	//Bundled Products
	if ($product['bundled_products']) {
		$original_product = $product;
		$bundledproducts = get_posts(array('post_type' => 'foxyshop_product', "post__in" => explode(",",$product['bundled_products']), 'numberposts' => -1));
		$num = 2;
		foreach($bundledproducts as $bundledproduct) {
			$product = foxyshop_setup_product($bundledproduct);
			$url .= '&amp;' . urlencode($num . ':') . 'price'.foxyshop_get_verification('price','0.00').'='.urlencode('0.00');
			$fields = array('name','code','category','weight','discount_quantity_amount','discount_quantity_percentage','discount_price_amount','discount_price_percentage','sub_frequency','sub_startdate','sub_enddate');
			foreach ($fields as $fieldname) {
				if (array_key_exists($fieldname, $product)) {
					if ($product[$fieldname]) $url .= '&amp;'. urlencode(esc_attr($num . ':' . $fieldname)) . foxyshop_get_verification($fieldname) . '=' . urlencode($product[$fieldname]);
				}
			}
			if (foxyshop_get_main_image() && $foxyshop_settings['version'] != "0.70") $url .= '&amp;' . $num . urlencode(':image') . foxyshop_get_verification('image',foxyshop_get_main_image()) . '=' . urlencode(foxyshop_get_main_image());
			$num++;	
		}
		$product = $original_product;
	}

	if ($linkOnly) {
		return 'https://' . $foxyshop_settings['domain'] . '/cart?'.$url;
	} else {
		echo '<a href="https://' . $foxyshop_settings['domain'] . '/cart?' . $url . '" class="foxyshop_button">' . str_replace('%name%',esc_attr($product['name']),$AddText) . '</a>';
	}
}



//Writes the Price with Sale Information
function foxyshop_price($skipSalePrice = false) {
	global $product;
	$write = '<div class="foxyshop_price">';
	if ($product['price'] == $product['originalprice']) {
		$write .= '<span class="foxyshop_currentprice">$'.$product['price'] . '</span>';
	} else {
		if (!$skipSalePrice) $write .= '<span class="foxyshop_oldprice">$'.$product['originalprice'].'</span>';
		$write .= '<span class="foxyshop_currentprice foxyshop_saleprice">$'.$product['price'] . '</span>';
	}
	$write .= '</div>';
	echo $write;
}


//Returns Sale Status (true or false)
function foxyshop_is_on_sale() {
	global $product;
	return ($product['price'] != $product['originalprice']);
}


//Returns URL for main product image (or other info)
function foxyshop_get_main_image($size = "thumbnail") {
	global $product, $foxyshop_settings;
	$image = "";
	if (!$size) $size = "thumbnail";
	if (!is_array($product['images'])) return "";
	foreach ($product['images'] as $imageArray) {
		if ($imageArray['featured']) {
			$image = $imageArray[$size];
		}
	}
	if (!$image && count($product['images']) > 0) $image = $product['images'][0][$size];
	if (!$image) $image = $foxyshop_settings['default_image'];
	return $image;
}


//Writes Image Slideshow (if available)
function foxyshop_image_slideshow($size = "thumbnail", $includeFeatured = true) {
	global $product;
	$write = "";
	if (!$size) $size = "thumbnail";
	foreach ($product['images'] as $imageArray) {
		if (!$imageArray['featured'] || $includeFeatured) {
			$write .= '<li><a href="' . $imageArray['full'] . '" rel="foxyshop_gallery[fs_gall]"><img src="' . $imageArray[$size] . '" alt="' . esc_attr($imageArray['title']) . '" /></a></li>'."\n";
		}
	}
	if ($write && (count($product['images']) != 1 && includeFeatured)) {
		echo '<div class="foxyshop_slideshow_title">' . __('Click Below For More Images:','image_slideshow_instructions') . '</div>';
		echo '<ul class="foxyshop_slideshow">' . $write . '</ul>'."\n";
		echo '<div class="clr"></div>';
	}
}


//Writes the Children Categories of a Category (if available)
function foxyshop_category_children($categoryID = 0, $showCount = false) {
	$write = "";
	if ($categoryID == 0) {
		$termchildren = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0&parent=0&orderby=name&order=ASC&fields=ids');
	} else {
		$termchildren = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0&parent='.$categoryID.'&orderby=name&order=ASC&fields=ids');
	}
	foreach ($termchildren as $child) {
		$term = get_term_by('id', $child, "foxyshop_categories");
		if (substr($term->name,0,1) != "_") {
			$productCount = ($showCount ? " (" . $term->count . ")" : "");
			$write .= '<li>';
			$write .= '<h2><a href="' . get_term_link($term, "foxyshop_categories") . '">' . $term->name . '</a>' . $productCount . '</h2>';
			if ($term->description) $write .= apply_filters('the_content', $term->description);
			$write .= '</li>'."\n";
		}
	}
	if ($write) echo '<ul class="foxyshop_categories">' . $write . '</ul><div class="clr"></div>';
}



//Generates Verification Code for HMAC Anti-Tampering
function foxyshop_get_verification($varname, $varvalue = "") {
	global $product, $foxyshop_settings;
	$encodingval = $product['code'] . esc_attr($varname) . esc_attr($varvalue ? $varvalue : $product[$varname]);
	return '||'.hash_hmac('sha256', $encodingval, $foxyshop_settings['api_key']).($varvalue == "--OPEN--" ? "||open" : "");
}



//Writes Breadcrumbs For Products and Categories
function foxyshop_breadcrumbs($sep = " 	&raquo; ") {
	global $post, $product;
	
	//Category Page
	if (get_query_var('taxonomy') == "foxyshop_categories") {
		$term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
		$breadcrumbarray[] = $term->term_id;
		$this_term_id = $term->term_id;
		$tempterm = $term;
		
	//Product Page
	} elseif ($post->ID) {
		$term = wp_get_post_terms($post->ID, 'foxyshop_categories');
		$matchedTerm = 0;
		
		//If in multiple categories, check referrer to see which one we should pick, otherwise first one found
		$referrer_category = (array_key_exists('HTTP_REFERER', $_SERVER) ? $referrer_category = basename($_SERVER['HTTP_REFERER']) : "");
		foreach ($term as $tempterm1) {
			if ($tempterm1->slug == $referrer_category) {
				$matchedTerm = $tempterm1->term_id;
				$tempterm = $tempterm1;
			}
		}
		if ($matchedTerm == 0) {
			$matchedTerm = $term[0]->term_id;
			$tempterm = $term[0];
		}
		$breadcrumbarray[] = $matchedTerm;
		$this_term_id = $tempterm->term_id;
	}
	
	//Do The Write
	if ($tempterm) {

		//WP >= 3.1
		if (function_exists('get_ancestors')) {
			$breadcrumbarray = array_merge($breadcrumbarray,get_ancestors($tempterm->term_id, 'foxyshop_categories'));
		
		//WP <= 3.0
		} else {
			while ($tempterm->parent != 0) {
				$tempterm = get_term_by('id',$tempterm->parent,'foxyshop_categories');
				$breadcrumbarray[] .= $tempterm->term_id;
			}
		}
		$breadcrumbarray = array_reverse($breadcrumbarray);

		$write1 = '<li><a href="' . get_bloginfo('url') . '/product-cat/">'. __('Products') . '</a></li>';
		foreach($breadcrumbarray as $termid) {
			$write1 .= '<li class="foxyshop_category_separator">' . $sep .'</li>';
			$terminfo = get_term_by('id',$termid,"foxyshop_categories");
			if ($terminfo->term_id != $this_term_id || get_query_var('taxonomy') != "foxyshop_categories") {
				$url = get_term_link($terminfo, "foxyshop_categories");
				$write1 .= '<li><a href="' . $url . '">' . str_replace("_","",$terminfo->name) . '</a></li>';
			} else {
				$write1 .= '<li>' . str_replace("_","",$terminfo->name) . '</li>';
			}
		}
		//Put product at end if this is a product page
		if (get_query_var('taxonomy') != "foxyshop_categories") {
			$write1 .= '<li class="foxyshop_category_separator">' . $sep .'</li>';
			$write1 .= '<li>'.$post->post_title.'</li>';
		}
		
		if ($write1) echo '<ul id="foxyshop_breadcrumbs">' . $write1 . '<li style="float: none; text-indent: -99999px; width: 1px; margin: 0;">-</li></ul>';
	}
}



//Shows a Featured Category
function foxyshop_featured_category($categoryName, $showAddToCart = false, $showMoreDetails = false, $showMax = -1, $simpleList = false) {
	global $product;
	$term = get_term_by('slug', $categoryName, "foxyshop_categories");
	$currentCategorySlug = $term->slug;
	$currentCategoryID = $term->term_id;
	$unwanted_children = get_term_children($currentCategoryID, "foxyshop_categories");
	$unwanted_post_ids = get_objects_in_term($unwanted_children, "foxyshop_categories");

	$args = array('post_type' => 'foxyshop_product', "post__not_in" => $unwanted_post_ids, "foxyshop_categories" => $currentCategorySlug, 'paged' => get_query_var('paged'), 'orderby' => 'meta_value_num', 'numberposts' => $showMax, 'meta_key' => '_price', 'order' => 'ASC');
	echo '<ul class="foxyshop_featured_product_list' . ($simpleList ? "_simple" : "") . '">';
	$featuredlist = get_posts($args);
	foreach($featuredlist as $featuredprod) {
		$product = foxyshop_setup_product($featuredprod);
		if ($product['hide_product']) continue;
		
		if ($simpleList) {
			echo '<li><a href="' . $product['url'] . '">' . $product['name'] . '</a></li>'."\n";
		} else {
			$thumbnailSRC = foxyshop_get_main_image("thumbnail");
			echo '<li class="foxyshop_product_box">'."\n";
			echo '<div class="foxyshop_product_image">';
			echo '<a href="' . $product['url'] . '"><img src="' . $thumbnailSRC . '" alt="' . esc_attr($product['name']) . '" class="foxyshop_main_image" /></a>';
			echo "</div>\n";

			echo '<div class="foxyshop_product_info">';
			echo '<h2><a href="' . $product['url'] . '">' . $product['name'] . '</a></h2>';

			foxyshop_price();

			if ($showMoreDetails) echo '<a href="' . $product['url'] . '" class="foxyshop_button">' . __('More Details') . '</a>';
			if ($showAddToCart) echo '<a href="' . foxyshop_product_link("", true) . '" class="foxyshop_button">' . __('Add To Cart') . '</a>';

			echo "</div>\n";
			echo '<div class="clr"></div>';
			echo "</li>\n";
		}
	}
	echo "</ul><div class=\"clr\"></div>\n";
}



//Shopping Cart Link
function foxyshop_cart_link($linkText = "-1", $hideEmpty = false) {
	global $foxyshop_settings;
	if ($linkText == "-1") $linkText = __("View Cart");
	$linkText = str_replace('%q%','<span id="fc_quantity">0</span>',$linkText);
	$linkText = str_replace('%p%','<span id="fc_total_price">0.00</span>',$linkText);
	if ($hideEmpty) echo '<div id="fc_minicart">';
	echo '<a href="https://' . $foxyshop_settings['domain'] . '/cart?cart=view" class="foxycart">' . $linkText . '</a>';
	if ($hideEmpty) echo '</div>';
}



//Shows Related Products
function foxyshop_related_products() {
	global $product, $post;
	$args = array('post_type' => 'foxyshop_product', "post__not_in" => array($post->ID), 'posts_per_page' => -1, 'post__in' => explode(",",$product['related_products']), 'orderby' => 'meta_value_num', 'meta_key' => '_price', 'order' => 'ASC');
	$relatedlist = get_posts($args);
	if ($relatedlist) {
		$original_product = $product;
		echo '<ul class="foxyshop_related_product_list">';
		echo '<li class="titleline"><h3>' . __('Related Products') . '</h3></li>';
		foreach($relatedlist as $relatedprod) {
			$product = foxyshop_setup_product($relatedprod);
			$thumbnailSRC = foxyshop_get_main_image("thumbnail");
			echo '<li class="foxyshop_product_box">'."\n";
			echo '<div class="foxyshop_product_image">';
			echo '<a href="' . $product['url'] . '"><img src="' . $thumbnailSRC . '" alt="' . esc_attr($product['name']) . '" class="foxyshop_main_image" /></a>';
			echo "</div>\n";

			echo '<div class="foxyshop_product_info">';
			echo '<h2><a href="' . $product['url'] . '">' . $product['name'] . '</a></h2>';

			foxyshop_price();

			echo "</div>\n";
			echo '<div class="clr"></div>';
			echo "</li>\n";
		}
		echo "</ul>\n";
		echo '<div class="clr"></div>';
		$product = $original_product;
	}
}



//Includes Header and Footer Files
function foxyshop_include($filename = "header") {
	if (file_exists(TEMPLATEPATH . '/foxyshop-' . $filename . '.php')) {
		include(TEMPLATEPATH . '/foxyshop-' . $filename . '.php');
	} else {
		include(FOXYSHOP_PATH . '/themefiles/foxyshop-' . $filename . '.php');
	}
}



//Pagination Function
function foxyshop_get_pagination($range = 4) {
	global $paged, $wp_query;
	if (!isset($max_page)) $max_page = $wp_query->max_num_pages;
	if($max_page > 1) {
		if(!$paged) $paged = 1;
		if($paged != 1) echo "<a href=" . get_pagenum_link(1) . "> First </a>";
		previous_posts_link(' &laquo; ');
		if($max_page > $range) {
			if($paged < $range) {
				for($i = 1; $i <= ($range + 1); $i++) {
					echo "<a href='" . get_pagenum_link($i) ."'";
					if ($i==$paged) echo "class='current'";
					echo ">$i</a>";
				}
			} elseif ($paged >= ($max_page - ceil(($range/2)))) {
				for($i = $max_page - $range; $i <= $max_page; $i++) {
					echo "<a href='" . get_pagenum_link($i) ."'";
					if($i==$paged) echo "class='current'";
					echo ">$i</a>";
				}
			} elseif ($paged >= $range && $paged < ($max_page - ceil(($range/2)))) {
				for($i = ($paged - ceil($range/2)); $i <= ($paged + ceil(($range/2))); $i++){
					echo "<a href='" . get_pagenum_link($i) ."'";
					if($i==$paged) echo "class='current'";
					echo ">$i</a>";
				}
			}
		} else {
			for($i = 1; $i <= $max_page; $i++) {
				echo "<a href='" . get_pagenum_link($i) ."'";
				if ($i==$paged) echo "class='current'";
				echo ">$i</a>";
			}
		}
		next_posts_link(' &raquo; ');
		if($paged != $max_page) echo " <a href=" . get_pagenum_link($max_page) . "> Last </a>";
	}
}

//Other Admin Functions
function foxyshop_insert_jquery() {
	$jquery_version = "1.4.2";
	wp_deregister_script('jquery');
	wp_register_script('jquery', ("http".($_SERVER['SERVER_PORT'] == 443 ? 's' : '')."://ajax.googleapis.com/ajax/libs/jquery/".$jquery_version."/jquery.min.js"), false, $jquery_version);
	wp_enqueue_script('jquery');
}
function foxyshop_insert_google_analytics() {
	global $foxyshop_settings;
	if (!is_user_logged_in()) {
	?><script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?php echo htmlspecialchars($foxyshop_settings['ga']); ?>']);
_gaq.push(['_trackPageview']);
(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script><?php
	}
}

?>