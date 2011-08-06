<?php
//Import For The Header
function foxyshop_insert_foxycart_files() {
	global $foxyshop_settings;
	if ($foxyshop_settings['domain']) {
		echo '<!-- BEGIN FOXYCART FILES -->'."\n";
		if ($foxyshop_settings['version'] == "0.7.1") {
			echo '<link rel="stylesheet" href="http://static.foxycart.com/scripts/colorbox/1.3.16/style1_fc/colorbox.css" type="text/css" media="screen" charset="utf-8" />'."\n";
			echo '<script src="http://cdn.foxycart.com/' . str_replace('.foxycart.com','',$foxyshop_settings['domain']) . '/foxycart.complete.3.js" type="text/javascript" charset="utf-8"></script>'."\n";
		} else {
			echo '<link rel="stylesheet" href="http://static.foxycart.com/scripts/colorbox/1.3.16/style1_fc/colorbox.css" type="text/css" media="screen" charset="utf-8" />'."\n";
			echo '<script src="http://cdn.foxycart.com/' . str_replace('.foxycart.com','',$foxyshop_settings['domain']) . '/foxycart.complete.2.js" type="text/javascript" charset="utf-8"></script>'."\n";
		}
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
	$product['id'] = $thepost->ID;
	$product['name'] = $thepost->post_title;
	$product['code'] = (get_post_meta($thepost->ID,'_code', true) ? get_post_meta($thepost->ID,'_code', true) : $thepost->ID);
	$product['description'] = apply_filters('the_content', $thepost->post_content);
	$product['short_description'] = $thepost->post_excerpt;
	$product['originalprice'] = number_format((double)get_post_meta($thepost->ID,'_price', true), 2,".","");
	$product['quantity_min'] = (int)get_post_meta($thepost->ID,'_quantity_min', true);
	$product['quantity_max'] = (int)get_post_meta($thepost->ID,'_quantity_max', true);
	$product['quantity_max_original'] = $product['quantity_max'];
	$product['hide_product'] = get_post_meta($thepost->ID,'_hide_product', true);
	$product['url'] = get_bloginfo("url") . FOXYSHOP_URL_BASE . '/' . FOXYSHOP_PRODUCTS_SLUG . '/' . $thepost->post_name . '/';
	$product['post_date'] = strtotime($thepost->post_date);

	//All fields that are loaded straight in without changing or checking data
	$fields = array('category', 'related_products', 'bundled_products', 'discount_quantity_amount', 'discount_quantity_percentage', 'discount_price_amount', 'discount_price_percentage', 'sub_frequency');
	foreach ($fields as $fieldname) {
		$product[$fieldname] = get_post_meta($thepost->ID,'_'.$fieldname, true);
	}
	
	//Calculate Subscription Start
	$sub_startdate = get_post_meta($thepost->ID,'_sub_startdate', true);
	if ($sub_startdate) {
		if ($sub_startdate != preg_replace("/[^0-9]/","", $sub_startdate)) $sub_startdate = date("Ymd", strtotime($sub_startdate));
	}
	$product['sub_startdate'] = $sub_startdate;
	
	//Calculate Subscription End
	$sub_enddate = get_post_meta($thepost->ID,'_sub_enddate', true);
	if ($sub_enddate) {
		if ($sub_enddate != preg_replace("/[^0-9]/","", $sub_enddate)) $sub_enddate = date("Ymd", strtotime($sub_enddate));
	}
	$product['sub_enddate'] = $sub_enddate;


	//Convert Weight
	$weight = explode(" ", get_post_meta($thepost->ID,'_weight',TRUE));
	$weight1 = (int)$weight[0];
	$weight2 = (double)$weight[1];
	if ($weight1 == 0 && $weight2 == 0) {
		$defaultweight = explode(" ",$foxyshop_settings['default_weight']);
		$weight1 = (int)$defaultweight[0];
		$weight2 = (count($defaultweight) > 1 ? (double)$defaultweight[1] : 0);
	}
	
	if ($weight2 > 0) $weight2 = number_format($weight2 / ($foxyshop_settings['weight_type'] == 'metric' ? 1000 : 16), 3);
	$weight2 = ((strpos($weight2, '.') !== false) ? end(explode('.', $weight2)) : $weight2);
	$product['weight'] = $weight1 . "." . $weight2;

	
	//Variations
	$product['variations'] = array();
	$i = 1;
	while (get_post_meta($thepost->ID,'_variation_name_'.$i,TRUE)) {
		$product['variations'][$i] = array(
			"name" => str_replace(' ','_',get_post_meta($thepost->ID,'_variation_name_'.$i,TRUE)),
			"type" => get_post_meta($thepost->ID,'_variation_type_'.$i,TRUE),
			"value" => get_post_meta($thepost->ID,'_variation_value_'.$i,TRUE),
			"displayKey" => get_post_meta($thepost->ID,'_variation_dkey_'.$i,TRUE),
			"required" => get_post_meta($thepost->ID,'_variation_required_'.$i,TRUE)
		);
		$i++;
	}
	
	//Set Advanced Variations
	$product['advanced_variations'] = unserialize(get_post_meta($thepost->ID,'_advanced_variations',TRUE));

	//Inventory
	$inventory_levels = unserialize(get_post_meta($thepost->ID,'_inventory_levels',TRUE));
	if (!is_array($inventory_levels)) $inventory_levels = array();
	$product['inventory_levels'] = $inventory_levels;
	if (array_key_exists($product['code'], $product['inventory_levels'])) {
		if ($product['inventory_levels'][$product['code']]['count'] > $product['quantity_max']) $product['quantity_max'] = $product['inventory_levels'][$product['code']]['count'];
	}
	
	//Images
	$product['images'] = array();
	$imageNumber = 0;
	$featuredImageID = (has_post_thumbnail($thepost->ID) ? get_post_thumbnail_id($thepost->ID) : 0);
	$attachments = get_posts(array('numberposts' => -1, 'post_type' => 'attachment','post_status' => null,'post_parent' => $thepost->ID, "post_mime_type" => "image", 'order' => 'ASC','orderby' => 'menu_order'));
	$sizes = get_intermediate_image_sizes();
	$sizes[] = 'full';
	foreach ($attachments as $attachment) {
		$thumbnailSRC = wp_get_attachment_image_src($attachment->ID, "thumbnail");
		$mediumSRC = wp_get_attachment_image_src($attachment->ID, "medium");
		$largeSRC = wp_get_attachment_image_src($attachment->ID, "large");
		$fullSRC = wp_get_attachment_image_src($attachment->ID, "full");
		$imageTitle = $attachment->post_title;
		$product['images'][$imageNumber] = array(
			"id" => $attachment->ID,
			"title" => $imageTitle,
			"featured" => ($featuredImageID == $attachment->ID || ($featuredImageID == 0 && $imageNumber == 0) ? 1 : 0)
		);
		foreach($sizes as $size) {
			$sizearray = wp_get_attachment_image_src($attachment->ID, $size);
			$product['images'][$imageNumber][$size] = $sizearray[0];
		}
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
			$product['price'] = number_format((double)get_post_meta($thepost->ID,'_saleprice', true),2,".","");
		} else {
			$product['price'] = number_format((double)get_post_meta($thepost->ID,'_price', true),2,".","");
		}
	} else {
		$product['price'] = number_format((double)get_post_meta($thepost->ID,'_price', true),2,".","");
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
	global $product, $foxyshop_settings, $foxyshop_skip_url_link;
	$localsettings = localeconv();
	echo '<form action="https://' . esc_attr($foxyshop_settings['domain']) . '/cart" method="post" accept-charset="utf-8" class="foxyshop_product" id="foxyshop_product_form_' . $product['id'] . '">'."\n";
	echo '<input type="hidden" name="price' . foxyshop_get_verification('price') . '" value="' . $product['price'] . '" id="price" />'."\n";
	echo '<input type="hidden" name="x:originalprice" value="' . $product['originalprice'] . '" id="originalprice" />'."\n";
	echo '<input type="hidden" name="x:l18n" value="' . utf8_encode($localsettings['currency_symbol'] . '|' . $localsettings['mon_decimal_point'] . '|' . $localsettings['mon_thousands_sep'] . '|' . $localsettings['p_cs_precedes'] . '|' . $localsettings['n_sep_by_space']) . '" id="foxyshop_l18n" />'."\n";
	if (foxyshop_get_main_image() && $foxyshop_settings['version'] != "0.7.0") echo '<input type="hidden" name="image' . foxyshop_get_verification('image',foxyshop_get_main_image()) . '" value="' . foxyshop_get_main_image() . '" id="foxyshop_cart_product_image" />'."\n";
	if ($foxyshop_settings['version'] != "0.7.0" && !isset($foxyshop_skip_url_link)) echo '<input type="hidden" name="url' . foxyshop_get_verification('url') . '" value="' . $product['url'] . '" />'."\n";

	echo '<input type="hidden" name="quantity_min' . foxyshop_get_verification('quantity_min') . '" value="' . $product['quantity_min'] . '" id="fs_quantity_min" />'."\n";
	echo '<input type="hidden" name="quantity_max' . foxyshop_get_verification('quantity_max') . '" value="' . $product['quantity_max'] . '" id="fs_quantity_max" />'."\n";
	echo '<input type="hidden" name="x:quantity_max" value="' . $product['quantity_max_original'] . '" rel="' . foxyshop_get_verification('quantity_max') . '" id="original_quantity_max" />'."\n";

	$fields = array('name','code','category','weight','discount_quantity_amount','discount_quantity_percentage','discount_price_amount','discount_price_percentage','sub_frequency','sub_startdate','sub_enddate','cart','empty','coupon','redirect','output');
	foreach ($fields as $fieldname) {
		if (array_key_exists($fieldname, $product)) {
			if ($product[$fieldname]) echo '<input type="hidden" name="' . $fieldname . foxyshop_get_verification($fieldname) . '" id="fs_' . esc_attr($fieldname) . '" value="' . esc_attr($product[$fieldname]) . '" />'."\n";
		}
	}
	
	//Bundled Products
	if ($product['bundled_products']) {
		$original_product = $product;
		$bundledproducts = get_posts(array('post_type' => 'foxyshop_product', "post__in" => explode(",",$product['bundled_products']), 'numberposts' => -1));
		$num = 2;
		foreach($bundledproducts as $bundledproduct) {
			$product = foxyshop_setup_product($bundledproduct);
			$fields = array('name','code','category','weight','discount_quantity_amount','discount_quantity_percentage','discount_price_amount','discount_price_percentage','sub_frequency','sub_startdate','sub_enddate');
			if (defined('FOXYSHOP_BUNDLED_PRODUCT_FULL_PRICE')) {
				$fields[] = 'price';
			} else {
				echo '<input type="hidden" name="' . $num . ':price' . foxyshop_get_verification('price','0.00') . '" value="0.00" />'."\n";
			}
			foreach ($fields as $fieldname) {
				if ($product[$fieldname]) echo '<input type="hidden" name="' . $num . ':' . $fieldname . foxyshop_get_verification($fieldname) . '" value="' . esc_attr($product[$fieldname]) . '" />'."\n";
			}
			if (foxyshop_get_main_image() && $foxyshop_settings['version'] != "0.7.0") echo '<input type="hidden" name="' . $num . ':image' . foxyshop_get_verification('image',foxyshop_get_main_image()) . '" value="' . foxyshop_get_main_image() . '" />'."\n";
			if ($foxyshop_settings['version'] != "0.7.0" && !isset($foxyshop_skip_url_link)) echo '<input type="hidden" name="' . $num . ':url' . foxyshop_get_verification('url') . '" value="' . $product['url'] . '" />'."\n";
			$num++;	
		}
		$product = $original_product;
	}

}



//Writes Variations (showQuantity 0 = Not Shown, 1 = Above, 2 = Below)
function foxyshop_product_variations($showQuantity = 0, $showPriceVariations = true) {
	global $post, $product, $foxyshop_settings;
	$writeUploadInclude = 0;
	$write = "";
	
	//Show Quantity Before Variations
	if ($showQuantity == 1) {
		$write .= foxyshop_get_shipto();
		$write .= foxyshop_quantity();
	}
	
	//Loop Through Variations
	$i = 1;
	foreach ($product['variations'] as $product_variation) {
		$variationName = $product_variation['name'];
		$variationType = $product_variation['type'];
		$variationValue = $product_variation['value'];
		$variationDisplayKey = $product_variation['displayKey'];
		$variationRequired = $product_variation['required'];

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

		$className = "variation-" . sanitize_title_with_dashes($variationName);
		if ($variationRequired) {
			$className .= ' foxyshop_required';
		}
		
		//Text
		if ($variationType == "text") {
			$write .= '<label for="' . esc_attr($product['code']) . '_' . $i . '" class="' . $className . $dkeyclass . '"'. $dkey . '>' . esc_attr(str_replace('_',' ',$variationName)) . '</label>'."\n";
			
			$write .= '<input type="text" name="' . esc_attr($variationName) . foxyshop_get_verification($variationName,'--OPEN--') . '" id="' . esc_attr($product['code']) . '_' . $i . '" value="" class="' . $className . $dkeyclass . '"';
			if ((int)$arrVariationText[0] > 0) $write .= ' style="width: ' . (int)$arrVariationText[0] * 6.5 . 'px;"';
			if ($variationDisplayKey) $write .= ' dkey="' . $variationDisplayKey . '"';
			if ($arrVariationText[1]) $write .= ' maxlength="' . $arrVariationText[1] . '"';
			$write .= ' />'."\n";
			$write .= '<div class="clr"></div>'."\n";
		
		//Textarea
		} elseif ($variationType == "textarea") {
			$write .= '<label for="' . esc_attr($product['code']) . '_' . $i . '" class="' . $className . $dkeyclass . '"'. $dkey . '>' . esc_attr(str_replace('_',' ',$variationName)) . '</label>'."\n";
			$write .= '<textarea name="' . esc_attr($variationName) . foxyshop_get_verification($variationName,'--OPEN--') . '" id="' . esc_attr($product['code']) . '_' . $i . '" class="foxyshop_freetext ' . $className . $dkeyclass . '" style="height: ' . 16 * (int)$variationValue . 'px;"' . $dkey . '></textarea>'."\n";
			$write .= '<div class="clr"></div>'."\n";
		
		//Upload
		} elseif ($variationType == "upload") {
			include(foxyshop_get_template_file('foxyshop-custom-upload.php'));
		
		//Description Field
		} elseif ($variationType == "descriptionfield") {
			$write .= '<div id="fs_title_' . esc_attr($product['code']) . '_' . $i . '" class="foxyshop_descriptionfield_title ' . $className . $dkeyclass . '"'. $dkey . '>' . esc_attr(str_replace('_',' ',$variationName)) . '</div>'."\n";
			$write .= '<div id="fs_text_' . esc_attr($product['code']) . '_' . $i . '" class="foxyshop_descriptionfield_text ' . $className . $dkeyclass . '"'. $dkey . '>' . $variationValue . '</div>'."\n";
			$write .= '<div class="clr"></div>'."\n";

		//Select, Checkbox, Radio
		} elseif ($variationType == "dropdown" || $variationType == "checkbox" || $variationType == "radio") {
			
			//Select
			if ($variationType == "dropdown") {
				$write .= '<label for="' . esc_attr($product['code']) . '_' . $i . '" class="' . $className . $dkeyclass . '"'. $dkey . '>' . esc_attr(str_replace('_',' ',$variationName)) . '</label>'."\n";
				$write .= '<select name="' . esc_attr($variationName) . '" id="' . esc_attr($product['code']) . '_' . $i . '" class="' . $className . $dkeyclass . '"' . $dkey . '>'."\n";
				$write .= foxyshop_run_variations($variationValue, $variationName, $showPriceVariations, $variationType, $dkey, $dkeyclass, $i, $className);
				$write .= "</select>\n";
			
			//Radio Buttons
			} elseif ($variationType == "radio") {
				$write .= '<div class="foxyshop_radio_wrapper">';
				$write .= '<div class="foxyshop_radio_title">' . str_replace("_", " ", $variationName) . '</div>';
				$write .= foxyshop_run_variations($variationValue, $variationName, $showPriceVariations, $variationType, $dkey, $dkeyclass, $i, $className);
				$write .= '</div>';
			
			//Checkbox
			} elseif ($variationType == "checkbox") {
				$write .= foxyshop_run_variations($variationValue, $variationName, $showPriceVariations, $variationType, $dkey, $dkeyclass, $i, $className);
			}
			$write .= '<div class="clr"></div>'."\n";
		}
		$i++;
	}
	//Show Quantity After Variations
	if ($showQuantity != 1) {
		$write .= foxyshop_get_shipto();
		if ($showQuantity == 2) $write .= foxyshop_quantity();
	}
	
	if ($write) {
		$write .= '<script type="text/javascript" src="' . FOXYSHOP_DIR . '/js/variation.process.jquery.js" charset="utf-8"></script>'."\n";
		echo '<div class="foxyshop_variations">' . $write . '</div>'."\n"."\n";
	}

}




function foxyshop_run_variations($variationValue, $variationName, $showPriceVariations, $variationType, $dkey, $dkeyclass, $i, $className) {
	global $product, $foxyshop_settings;

	$write1 = "";
	$variations = preg_split("/(\r\n|\n)/", $variationValue);
	$k = 0;
	foreach($variations as $val) {
		if ($val == '') continue;
		$option_attributes = "";
		$option_show_price_change = "";
		$displaykey = "";
		$imagekey = "";
		$pricechange = "";
		$displaypricechange = "";
		$priceset = "";
		$code = "";
		$codeadd = "";
		if (strpos($val,"*") !== false) {
			$val = str_replace("*","",$val);
			if ($variationType == "dropdown") {
				$option_attributes .= ' selected="selected"';
			} else {
				$option_attributes .= ' checked="checked"';
			}
		}
		if ($variationType == "radio" && $k == 0) $option_attributes .= ' checked="checked"';
		$variation_display_name = $val;
		if (strpos($val,"{") !== false) {
			$variation_display_name = substr($variation_display_name,0,strpos($variation_display_name,"{"));
			$valtemp = explode("|",substr($val, strpos($val,"{")+1, strpos($val,"}") - (strpos($val,"{")+1)));
			foreach ($valtemp as $valtemp1) {
				if (substr($valtemp1,0,4) == "dkey") {
					$displaykey = substr($valtemp1,5);
				} elseif (substr($valtemp1,0,2) == "p:") {
					$priceset = substr($valtemp1,2);
				} elseif (substr($valtemp1,0,1) == "p") {
					$pricechange = substr($valtemp1,1);
					if (substr($valtemp1,0,7) == "price:x") $pricechange = substr($valtemp1,7);
				} elseif (substr($valtemp1,0,2) == "c:") {
					$code = substr($valtemp1,2);
				} elseif (substr($valtemp1,0,2) == "c+") {
					$codeadd = substr($valtemp1,2);
				} elseif (substr($valtemp1,0,4) == "ikey") {
					$imagekey = substr($valtemp1,5);
				}
			}

			if ($pricechange != "") {
				if (substr($pricechange,0,1) == '-') {
					$displaypricechange = foxyshop_currency($pricechange);
					$pricechange = $pricechange * 100;
				} else {
					$displaypricechange = "+" . foxyshop_currency($pricechange);
					$pricechange = "+" . ($pricechange * 100);
				}
			} elseif ($priceset != "") {
				$displaypricechange = foxyshop_currency($priceset);
				$priceset = $priceset * 100;
			}
			if ($showPriceVariations && $displaypricechange) $option_show_price_change = ' (' . $displaypricechange . ')';

			if ($priceset) $option_attributes .= ' priceset="' . $priceset . '"';
			if ($pricechange) $option_attributes .= ' pricechange="' . $pricechange . '"';
			if ($displaykey) $option_attributes .= ' displaykey="' . $displaykey . '"';
			if ($imagekey) $option_attributes .= ' imagekey="' . $imagekey . '"';
			if ($code && $foxyshop_settings['manage_inventory_levels']) $option_attributes .= ' code="' . htmlspecialchars($code) . '"';
			if ($codeadd && $foxyshop_settings['manage_inventory_levels']) $option_attributes .= ' codeadd="' . htmlspecialchars($codeadd) . '"';
		}


		//Write the Line
		if ($variationType == "dropdown") {
			$write1 .= '<option value="' . esc_attr($val) . foxyshop_get_verification($variationName,$val) . '"' . $option_attributes;
			$write1 .= '>' . $variation_display_name . $option_show_price_change . '</option>'."\n";
		} elseif ($variationType == "checkbox") {
			$write1 .= '<div class="foxyshop_short_element_holder"><input type="checkbox" name="' . esc_attr($variationName) . '" value="' . esc_attr($val) . foxyshop_get_verification($variationName,$val) . '" id="' . esc_attr($product['code']) . '_' . $i . '" class="' . $className . $dkeyclass . '"' . $dkey . $option_attributes . '></div>'."\n";
			$write1 .= '<label for="' . esc_attr($product['code']) . '_' . $i . '" class="' . $className . $dkeyclass . ' foxyshop_no_width foxyshop_radio_margin"'. $dkey . '>' . $variation_display_name . $option_show_price_change . '</label>'."\n";

		} elseif ($variationType == "radio") {
			$write1 .= '<div class="foxyshop_short_element_holder"><input type="radio" name="' . esc_attr($variationName) . '" value="' . esc_attr($val) . foxyshop_get_verification($variationName,$val) . '" id="' . esc_attr($product['code']) . '_' . $i . '_' . $k . '" class="' . $className . $dkeyclass . '"' . $dkey . $option_attributes . '></div>'."\n";
			$write1 .= '<label for="' . esc_attr($product['code']) . '_' . $i . '_' . $k . '" class="' . $className . $dkeyclass . ' foxyshop_no_width"'. $dkey . '>' . $variation_display_name . $option_show_price_change . '</label>'."\n";
			$write1 .= '<div class="clr"></div>';

		}
		$k++;
	}
	return $write1;
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
	$write = '<label class="foxyshop_quantity" for="quantity">' . __('Quantity') . '</label>'."\n";
	if ($product['quantity_max_original'] > 0) {
		$write .= '<select class="foxyshop_quantity" name="quantity">';
		for ($i=($product['quantity_min'] > 0 ? $product['quantity_min'] : 1); $i <= $product['quantity_max_original']; $i++) {
			$write .= '<option value="' . $i . foxyshop_get_verification('quantity',$i) . '">' . $i . '</option>'."\n";
		}
		$write .= '</select>'."\n";
	} else {
		$write .= '<input type="text" name="quantity' . foxyshop_get_verification('quantity','--OPEN--') . '" id="quantity" value="' . esc_attr($qty) . '" class="foxyshop_quantity" />'."\n";
	}
	$write .= '<div class="clr"></div>'."\n";
	return $write;
}



//Writes a Straight Text Link
function foxyshop_product_link($AddText = "Add To Cart", $linkOnly = false) {
	global $product, $foxyshop_settings;
	
	$url = 'price' . foxyshop_get_verification('price') . '=' . urlencode($product['price']);
	if (foxyshop_get_main_image() && $foxyshop_settings['version'] != "0.7.0") $url .= '&amp;image' . foxyshop_get_verification('image',foxyshop_get_main_image()) . '=' . urlencode(foxyshop_get_main_image());
	if ($foxyshop_settings['version'] != "0.7.0") $url .= '&amp;url' . foxyshop_get_verification('url') . '=' . urlencode($product['url']);
	$fields = array('name','code','category','weight','discount_quantity_amount','discount_quantity_percentage','discount_price_amount','discount_price_percentage','sub_frequency','sub_startdate','sub_enddate','cart','empty','coupon','redirect','output');
	foreach ($fields as $fieldname) {
		if (array_key_exists($fieldname, $product)) {
			if ($product[$fieldname]) $url .= '&amp;' . urlencode(esc_attr($fieldname)) . foxyshop_get_verification($fieldname) . '=' . urlencode($product[$fieldname]);
		}
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
			if (foxyshop_get_main_image() && $foxyshop_settings['version'] != "0.7.0") $url .= '&amp;' . $num . urlencode(':image') . foxyshop_get_verification('image',foxyshop_get_main_image()) . '=' . urlencode(foxyshop_get_main_image());
			if ($foxyshop_settings['version'] != "0.7.0") $url .= '&amp;url' . foxyshop_get_verification('url') . '=' . urlencode($product['url']);
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
		$write .= '<span class="foxyshop_currentprice">' . foxyshop_currency($product['price']) . '</span>';
	} else {
		if (!$skipSalePrice) $write .= '<span class="foxyshop_oldprice">' . foxyshop_currency($product['originalprice']) . '</span>';
		$write .= '<span class="foxyshop_currentprice foxyshop_saleprice">' . foxyshop_currency($product['price']) . '</span>';
	}
	$write .= '</div>';
	echo $write;
}


//Returns Sale Status (true or false)
function foxyshop_is_on_sale() {
	global $product;
	return ($product['price'] != $product['originalprice']);
}


//Returns Product NEW Status (true or false)
function foxyshop_is_product_new($number_of_days = 14) {
	global $product;
	return ($product['post_date'] >= strtotime("-$number_of_days days"));
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
function foxyshop_image_slideshow($size = "thumbnail", $includeFeatured = true, $titleText = "Click Below For More Images:") {
	global $product;
	$write = "";
	$ikey = "";
	$useikey = 0;
	
	//Check for imagekey usage
	foreach ($product['variations'] as $product_variation) {
		if (strrpos($product_variation['value'],"ikey:") > 0) $useikey = 1;
	}
	if ($useikey) $includeFeatured = true;
	
	if (!$size) $size = "thumbnail";
	foreach ($product['images'] as $imageArray) {
		if ($useikey) {
			$ikey .= "ikey.push(['" . $imageArray['id'] . "'";
			$ikey .= ",'" . $imageArray['thumbnail'] . "'";
			$ikey .= ",'" . $imageArray['medium'] . "'";
			$ikey .= ",'" . $imageArray['large'] . "'";
			$ikey .= ",'" . str_replace("'","\'",$imageArray['title']) . "'";
			$ikey .= ",'" . foxyshop_get_verification('image',$imageArray['thumbnail']) . "'";
			$ikey .= "]);\n";
		}
		if (!$imageArray['featured'] || $includeFeatured) {
			$write .= '<li><a href="' . $imageArray['large'] . '" rel="foxyshop_gallery[fs_gall]" title=""><img src="' . $imageArray[$size] . '" alt="' . esc_attr($imageArray['title']) . '" /></a></li>'."\n";
		}
	}
	if ($write && (count($product['images']) != 1 || $includeFeatured)) {
		if ($titleText) echo '<div class="foxyshop_slideshow_title">' . $titleText . '</div>';
		echo '<ul class="foxyshop_slideshow">' . $write . '</ul>'."\n";
		echo '<div class="clr"></div>'."\n";
		if ($ikey) {
			echo '<script type="text/javascript">'."\n";
			echo "var ikey = [];\n";
			echo $ikey;
			echo '</script>'."\n";
		}
	}
}


//Writes the Children Categories of a Category (if available)
function foxyshop_category_children($categoryID = 0, $showCount = false, $showDescription = true, $categoryImageSize = "thumbnail") {
	global $taxonomy_images_plugin;
	$write = "";
	if ($categoryID == 0) {
		$termchildren = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0&parent=0&orderby=name&order=ASC');
	} else {
		$termchildren = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0&parent='.$categoryID.'&orderby=name&order=ASC');
	}

	if ($termchildren) {
		//Sort Categories
		$termchildren = foxyshop_sort_categories($termchildren, $categoryID);

		foreach ($termchildren as $child) {
			$term = get_term_by('id', $child->term_id, "foxyshop_categories");
			if (substr($term->name,0,1) != "_") {
				$productCount = ($showCount ? " (" . $term->count . ")" : "");
				$url = get_term_link($term, "foxyshop_categories");
				$write .= '<li id="foxyshop_category_' . $term->term_id . '">';
				$write .= '<h2><a href="' . $url . '">' . $term->name . '</a>' . $productCount . '</h2>';
				if ($showDescription && $term->description) $write .= apply_filters('the_content', $term->description);

				if (isset($taxonomy_images_plugin)) {
					$img = $taxonomy_images_plugin->get_image_html($categoryImageSize, $term->term_taxonomy_id);
					if(!empty($img)) $write .= '<a href="' . $url . '" class="foxyshop_category_image">' . $img . '</a>';
				}


				$write .= '</li>'."\n";
			}
		}
		if ($write) echo '<ul class="foxyshop_categories">' . $write . '</ul><div class="clr"></div>';
	}
}


//Writes a Simple List of Children Categories of a Category (if available)
function foxyshop_simple_category_children($categoryID = 0) {
	$write = "";
	if ($categoryID == 0) {
		$termchildren = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0&parent=0&orderby=name&order=ASC');
	} else {
		$termchildren = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0&parent='.$categoryID.'&orderby=name&order=ASC');
	}

	if ($termchildren) {
		//Sort Categories
		$termchildren = foxyshop_sort_categories($termchildren, $categoryID);

		foreach ($termchildren as $child) {
			$term = get_term_by('id', $child->term_id, "foxyshop_categories");
			if (substr($term->name,0,1) != "_") {
				$url = get_term_link($term, "foxyshop_categories");
				$write .= '<li id="foxyshop_category_' . $term->term_id . '">';
				$write .= '<a href="' . $url . '">' . $term->name . '</a>';
				$write .= "</li>\n";
			}
		}
		if ($write) echo $write;
	}
}



//Generates Verification Code for HMAC Anti-Tampering
function foxyshop_get_verification($varname, $varvalue = "") {
	global $product, $foxyshop_settings;
	if (defined('FOXYSHOP_SKIP_VERIFICATION')) return "";
	$encodingval = $product['code'] . htmlspecialchars($varname) . htmlspecialchars($varvalue ? $varvalue : $product[$varname]);
	return '||'.hash_hmac('sha256', $encodingval, $foxyshop_settings['api_key']).($varvalue == "--OPEN--" ? "||open" : "");
}



//Writes Breadcrumbs For Products and Categories
//If there are no categories, $product_fallback indicates whether a link back to the product list should be shown.
//If it is entered, the link text is shown. If the string is blank, the fallback breadcrumb bar will not be shown.
function foxyshop_breadcrumbs($sep = " &raquo; ", $product_fallback = "&laquo; Back to Products", $base_name = "Products") {
	global $post, $product;
	$this_term_id = 0;
	
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
		if ($term) {
			if ($matchedTerm == 0) {
				$matchedTerm = $term[0]->term_id;
				$tempterm = $term[0];
			}
			$breadcrumbarray[] = $matchedTerm;
			$this_term_id = $tempterm->term_id;
		}
	}
	
	//Do The Write
	if ($this_term_id > 0) {

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

		$write1 = '<li><a href="' . get_bloginfo('url') . FOXYSHOP_URL_BASE . '/' . FOXYSHOP_PRODUCT_CATEGORY_SLUG . '/">'. $base_name . '</a></li>';
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
	
	//Product Fallback
	} elseif ($post->ID && $product_fallback != "") {
		echo '<ul id="foxyshop_breadcrumbs"><li><a href="' . get_bloginfo('url') . FOXYSHOP_URL_BASE . '/' . FOXYSHOP_PRODUCTS_SLUG . '/">'. $product_fallback . '</a></li><li style="float: none; text-indent: -99999px; width: 1px; margin: 0;">-</li></ul>';
	}

}



//Checks Inventory Status
// %c = Item Count, %s = plural indicator (no s for 1), %n = product name
function foxyshop_inventory_management($alertMessage = "There are %c of these item%s left in stock.", $noStockMessage = "This item is no longer in stock.", $allowBackOrder = false) {
	global $product, $foxyshop_settings;
	if (!$foxyshop_settings['manage_inventory_levels']) return false;
	if (count($product['inventory_levels']) == 0) return false;
	$stockStatus = foxyshop_check_inventory();
	$currentCount = "-1";
	if (array_key_exists($product['code'],$product['inventory_levels'])) $currentCount = $product['inventory_levels'][$product['code']]['count'];
	
	//Writes Javascript
	echo '<script type="text/javascript">'."\n";
	echo "var foxyshop_inventory_stock_alert = '" . str_replace("'","\'",$alertMessage) . "';\n";
	echo "var foxyshop_inventory_stock_none = '" . str_replace("'","\'",$noStockMessage) . "';\n";
	echo "var foxyshop_allow_backorder = " . ($allowBackOrder ? "true" : "false") . ";\n";
	echo "var arr_foxyshop_inventory = [];\n";
	$i = 0;
	foreach ($product['inventory_levels'] as $ivcode => $iv) {
		echo "arr_foxyshop_inventory[" . $i . "] = ['" . str_replace("'","\'",$ivcode) . "','" . $iv['count'] . "','" . ($iv['alert'] == '' ? $foxyshop_settings['inventory_alert_level'] : $iv['alert']) . "','" . foxyshop_get_verification("quantity_max", $iv['count']) . "'];\n";
		$i++;
	}
	if ($stockStatus == -1 && !$allowBackOrder) {
		echo 'jQuery(document).ready(function($){'."\n";
		echo '$("#productsubmit").attr("disabled","disabled").addClass("foxyshop_disabled");'."\n";
		echo '});'."\n";
	}
	echo '</script>'."\n";
	
	$alertMessage = str_replace('%n',$product['name'],$alertMessage);
	$alertMessage = str_replace('%c',$currentCount,$alertMessage);
	$alertMessage = str_replace('%s',($currentCount != 1 ? 's' : ''),$alertMessage);
	$noStockMessage = str_replace('%n',$product['name'],$noStockMessage);
	$noStockMessage = str_replace('%c',$currentCount,$noStockMessage);
	$noStockMessage = str_replace('%s',($currentCount != 1 ? 's' : ''),$noStockMessage);

	if ($stockStatus == 0) {
		echo '<div class="foxyshop_stock_alert">' . $alertMessage . '</div>';
	} elseif ($stockStatus == -1) {
		echo '<div class="foxyshop_stock_alert foxyshop_out_of_stock">' . $noStockMessage . '</div>';
	} else {
		echo '<div class="foxyshop_stock_alert" style="display: none;"></div>';
	}
}


//Checks Inventory Status For a Main Product Code
//Returns -1 (not in stock), 0 (stock alert), 1 (no alert)
function foxyshop_check_inventory() {
	global $product, $foxyshop_settings;
	if (!$foxyshop_settings['manage_inventory_levels']) return 1;
	if (count($product['inventory_levels']) == 0) return 1;
	foreach ($product['inventory_levels'] as $ivcode => $iv) {
		if ($ivcode == $product['code']) {
			$alert = ($iv['alert'] == '' ? $foxyshop_settings['inventory_alert_level'] : $iv['alert']);
			if ((int)$iv['count'] <= (int)$alert) {
				if ((int)$iv['count'] <= 0) {
					return -1;
				} else {
					return 0;
				}
			}
		}
	}
	return 1;
}


//Shows a Featured Category
function foxyshop_featured_category($categoryName, $showAddToCart = false, $showMoreDetails = false, $showMax = -1, $simpleList = false) {
	global $product;
	$term = get_term_by('slug', $categoryName, "foxyshop_categories");
	$currentCategorySlug = $term->slug;
	$currentCategoryID = $term->term_id;

	$args = array('post_type' => 'foxyshop_product', "foxyshop_categories" => $currentCategorySlug, 'numberposts' => $showMax);
	$args = array_merge($args,foxyshop_sort_order_array());
	$args = array_merge($args,foxyshop_hide_children_array($currentCategoryID));
	
	echo '<ul class="foxyshop_featured_product_list' . ($simpleList ? "_simple" : "") . '">';
	$featuredlist = get_posts($args);
	foreach($featuredlist as $featuredprod) {
		$product = foxyshop_setup_product($featuredprod);
		if ($product['hide_product']) continue;
		
		if ($simpleList) {
			echo '<li><a href="' . $product['url'] . '">' . apply_filters('the_title', $product['name']) . '</a></li>'."\n";
		} else {
			$thumbnailSRC = foxyshop_get_main_image("thumbnail");
			echo '<li class="foxyshop_product_box">'."\n";
			echo '<div class="foxyshop_product_image">';
			echo '<a href="' . $product['url'] . '"><img src="' . $thumbnailSRC . '" alt="' . esc_attr($product['name']) . '" class="foxyshop_main_image" /></a>';
			echo "</div>\n";

			echo '<div class="foxyshop_product_info">';
			echo '<h2><a href="' . $product['url'] . '">' . apply_filters('the_title', $product['name']) . '</a></h2>';

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
function foxyshop_cart_link($linkText = "View Cart", $hideEmpty = false) {
	global $foxyshop_settings;
	$linkText = str_replace('%q%','<span id="fc_quantity">0</span>',$linkText);
	$linkText = str_replace('%p%','<span id="fc_total_price">' . number_format(0,2) . '</span>',$linkText);
	if ($hideEmpty) echo '<div id="fc_minicart">';
	echo '<a href="https://' . $foxyshop_settings['domain'] . '/cart?cart=view" class="foxycart">' . $linkText . '</a>';
	if ($hideEmpty) echo '</div>';
}



//Shows Related Products
function foxyshop_related_products($sectiontitle = "Related Products") {
	global $product, $post;
	$args = array('post_type' => 'foxyshop_product', "post__not_in" => array($post->ID), 'posts_per_page' => -1, 'post__in' => explode(",",$product['related_products']));
	$args = array_merge($args,foxyshop_sort_order_array());
	$relatedlist = get_posts($args);
	if ($relatedlist) {
		$original_product = $product;
		echo '<ul class="foxyshop_related_product_list">';
		echo '<li class="titleline"><h3>' . $sectiontitle . '</h3></li>';
		foreach($relatedlist as $relatedprod) {
			$product = foxyshop_setup_product($relatedprod);
			$thumbnailSRC = foxyshop_get_main_image("thumbnail");
			echo '<li class="foxyshop_product_box">'."\n";
			echo '<div class="foxyshop_product_image">';
			echo '<a href="' . $product['url'] . '"><img src="' . $thumbnailSRC . '" alt="' . esc_attr($product['name']) . '" class="foxyshop_main_image" /></a>';
			echo "</div>\n";

			echo '<div class="foxyshop_product_info">';
			echo '<h2><a href="' . $product['url'] . '">' . apply_filters('the_title', $product['name']) . '</a></h2>';

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


//Get Sort Order
function foxyshop_sort_order_array() {
	global $foxyshop_settings;
	if (isset($_COOKIE['sort_key'])) $foxyshop_settings['sort_key'] = $_COOKIE['sort_key'];
	if (isset($_GET['sort_key'])) $foxyshop_settings['sort_key'] = $_GET['sort_key'];
	if ($foxyshop_settings['sort_key'] == "name") {
		return array('orderby' => 'title', 'order' => 'ASC');
	} elseif ($foxyshop_settings['sort_key'] == "price_asc") {
		return array('orderby' => 'meta_value_num', 'meta_key' => '_price', 'order' => 'ASC');
	} elseif ($foxyshop_settings['sort_key'] == "price_desc") {
		return array('orderby' => 'meta_value_num', 'meta_key' => '_price', 'order' => 'DESC');
	} elseif ($foxyshop_settings['sort_key'] == "date_asc") {
		return array('orderby' => 'date', 'order' => 'ASC');
	} elseif ($foxyshop_settings['sort_key'] == "date_desc") {
		return array('orderby' => 'date', 'order' => 'DESC');
	} else {
		return array('orderby' => 'menu_order', 'order' => 'ASC');
	}
}


//Product Sort Dropdown
function foxyshop_sort_dropdown($title = "Sort Products") {
	global $arr_dropdown_sort, $foxyshop_settings;
	if (!isset($arr_dropdown_sort)) $arr_dropdown_sort = array(
		"default" => 'Default',
		"price_asc" => 'Price (Low to High)',
		"price_desc" => 'Price (High to Low)',
		"date_desc" => 'Newer Products First',
		"date_asc" => 'Older Products First'
	);
	if (isset($_COOKIE['sort_key'])) $current_sort_key = $_COOKIE['sort_key'];
	if (isset($_GET['sort_key'])) $current_sort_key = $_GET['sort_key'];
	if (!isset($current_sort_key)) $current_sort_key = $foxyshop_settings['sort_key'];
	echo '<form id="foxyshop_sort_dropdown">'."\n";
	echo '<label for="sort_key">' . $title . '</label>'."\n";
	echo '<select name="sort_key" id="sort_key" onchange="foxyshop_sort_dropdown(this);">'."\n";
	foreach ($arr_dropdown_sort AS $key=>$val) {
		echo '<option value="' . $key . '"' . ($current_sort_key == $key ? ' selected="selected"' : '') . '>' . $val . '</option>'."\n";
	}
	echo '</select>'."\n";
	echo '</form>'."\n";
	
	?>
	<script type="text/javascript">
	function foxyshop_sort_dropdown(el) {
		var current_url = document.location.href;
		var current_sort_key = el.options[el.selectedIndex].value;
		foxyshop_set_cookie('sort_key',current_sort_key,1);
		document.location.href = current_url.split('?')[0] + '?sort_key=' + current_sort_key;
	}
	function foxyshop_set_cookie(c_name,value,exdays) { var exdate=new Date();exdate.setDate(exdate.getDate() + exdays);var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString()) + '; path=/';document.cookie=c_name + "=" + c_value; }
	</script>
	<?php
	
}


//Includes Header and Footer Files
function foxyshop_include($filename = "header") {
	include(foxyshop_get_template_file('/foxyshop-' . $filename . '.php'));
}

//Function to pick which template file to use
function foxyshop_get_template_file($filename) {
	if (!defined('FOXYSHOP_TEMPLATE_PATH')) define('FOXYSHOP_TEMPLATE_PATH',STYLESHEETPATH);
	if (file_exists(FOXYSHOP_TEMPLATE_PATH . '/' . $filename)) return FOXYSHOP_TEMPLATE_PATH . '/' . $filename;
	if (STYLESHEETPATH != TEMPLATEPATH) if (file_exists(TEMPLATEPATH . '/' . $filename)) return TEMPLATEPATH . '/' . $filename;
	return FOXYSHOP_PATH . '/themefiles/' . $filename;
}



//Show Orders For A Customer
//Sample Usage: foxyshop_customer_order_history(get_user_meta(wp_get_current_user()->ID, 'foxycart_customer_id', TRUE));
function foxyshop_customer_order_history($customer_id = 0, $date_filter = 'n/j/Y', $no_results_message = "No Records Found.") {
	global $foxyshop_settings;

	//Setup Fields and Defaults
	$foxy_data_defaults = array("customer_id_filter" => $customer_id);
	$foxy_data = wp_parse_args(array("api_action" => "transaction_list"), $foxy_data_defaults);
	$foxy_data['pagination_start'] = (isset($_GET['pagination_start']) ? $_GET['pagination_start'] : 0);
	if ($foxyshop_settings['version'] != "0.7.0") $foxy_data['entries_per_page'] = 50;
	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	
	//No Results
	if ($xml->result == "ERROR") {
		$msg = $xml->messages->message;
		if ($msg == "No transactions found. Please double check your filter fields.") $msg = $no_results_message;
		echo '<div class="foxyshop_customer_order_history_no_results">' . $msg . '</div>';
		return;
	}

	//Table Header
	echo '<table cellpadding="0" cellspacing="0" border="0" class="foxyshop_table_list" id="foxyshop_customer_order_history">'."\n";
	echo '<thead>'."\n";
	echo '<tr>'."\n";
	echo '<th>Order ID</th>'."\n";
	echo '<th>Date</th>'."\n";
	echo '<th>Total</th>'."\n";
	echo '<th>&nbsp;</th>'."\n";
	echo '</tr>'."\n";
	echo '</thead>'."\n";
	echo '<tbody>'."\n";
	foreach($xml->transactions->transaction as $transaction) {
		$transaction_id = $transaction->id;
		echo '<tr rel="' . $transaction_id . '">';
		echo '<td class="order_id">' . $transaction_id . '</td>';
		echo '<td class="order_date">' . date($date_filter, strtotime($transaction->transaction_date)) . '</td>';
		echo '<td class="order_total">' . foxyshop_currency((double)$transaction->order_total) . '</td>';
		echo '<td class="order_receipt"><a href="' . $transaction->receipt_url . '" target="_blank">Show Receipt</a></td>';
		echo '</tr>'."\n";
	}
	
	echo '</tbody></table>';
	
	//Pagination
	$p = (int)($foxyshop_settings['version'] == "0.7.0" ? 50 : 50);
	$total_records = (int)$xml->statistics->total_orders;
	$filtered_total = (int)$xml->statistics->filtered_total;
	$pagination_start = (int)$xml->statistics->pagination_start;
	$pagination_end = (int)$xml->statistics->pagination_end;
	if ($pagination_start > 1 || $filtered_total > $pagination_end) {
		echo '<div id="foxyshop_list_pagination">';
		echo $xml->messages->message[1] . '<br />';
		if ($pagination_start > 1) echo '<a href="edit.php' . $querystring . '&amp;pagination_start=' . ($pagination_start - $p - 1) . '">&laquo; Previous</a>';
		if ($pagination_end < $filtered_total) {
			if ($pagination_start > 1) echo ' | ';
			echo '<a href="edit.php' . $querystring . '&amp;pagination_start=' . $pagination_end . '">Next &raquo;</a>';
		}
		echo '</div>';
	}

}
	

//Gets Subscription Status (0 = Not Found Or Inactive, 1 = active)
function foxyshop_subscription_active($product_code) {
	if (!function_exists('wp_get_current_user')) return 0;
	$current_user = wp_get_current_user();
	$current_user_id = $current_user->ID;
	if ($current_user_id == 0) return 0;
	$foxyshop_subscription = unserialize(get_user_meta($current_user_id, 'foxyshop_subscription', true));
	if (!is_array($foxyshop_subscription)) return 0;
	if (array_key_exists($product_code,$foxyshop_subscription)) {
		if ($foxyshop_subscription[$product_code]['is_active'] == 1) {
			return 1;
		} else {
			return 0;
		}
	} else {
		return 0;
	}
}



//Pagination Function
function foxyshop_get_pagination($range = 4) {
	global $paged, $wp_query;
	if (!isset($max_page)) $max_page = $wp_query->max_num_pages;
	if($max_page > 1) {
		echo '<div id="foxyshop_pagination">';
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
		echo '</div>';
	}
}

function foxyshop_currency($input, $currencysymbol = true) {
	global $foxyshop_settings;
	if (function_exists('money_format')) {
		$currency = utf8_encode(money_format("%" . ($currencysymbol ? "" : "!") . ".2n", (double)$input));
	} else {
		//Windows: no internationalisation support
		$currency_code = ($foxyshop_settings['locale_code'] == "en_GB" ? "£" : "$");
		$currency = utf8_encode($currency_code.number_format((double)$input,2,".",","));
	}
	return $currency;
}
?>