<?php
//-------------------------------------------
//Post Type
//-------------------------------------------
add_action('init', 'foxyshop_create_post_type', 1);
function foxyshop_create_post_type() {
	$labels = array(
		'name' => FOXYSHOP_PRODUCT_NAME_PLURAL,
		'singular_name' => FOXYSHOP_PRODUCT_NAME_SINGULAR,
		'add_new' => __('Add New').' '.FOXYSHOP_PRODUCT_NAME_SINGULAR,
		'add_new_item' => __('Add New ').FOXYSHOP_PRODUCT_NAME_SINGULAR,
		'all_items' => __('Manage').' '.FOXYSHOP_PRODUCT_NAME_PLURAL,
		'edit_item' => __('Edit').' '.FOXYSHOP_PRODUCT_NAME_SINGULAR,
		'new_item' => __('New').' '.FOXYSHOP_PRODUCT_NAME_SINGULAR,
		'view_item' => __('View').' '.FOXYSHOP_PRODUCT_NAME_SINGULAR,
		'menu_name' => (function_exists("is_multi_author") ? FOXYSHOP_PRODUCT_NAME_PLURAL : FOXYSHOP_PRODUCT_NAME_PLURAL),
		'not_found' =>  __('No').' '.FOXYSHOP_PRODUCT_NAME_PLURAL.' '.__('Found'),
		'not_found_in_trash' => __('No').' '.FOXYSHOP_PRODUCT_NAME_PLURAL.' '.__('Found in Trash'), 
		'search_items' => __('Search').' '.FOXYSHOP_PRODUCT_NAME_PLURAL,
		'parent_item_colon' => ''
	);
	$post_type_support = array('title','editor','thumbnail', 'custom-fields', 'excerpt');
	if (defined('FOXYSHOP_PRODUCT_COMMENTS')) array_push($post_type_support, "comments");
	register_post_type('foxyshop_product', array(
		'labels' => $labels,
		'description' => "FoxyShop ".FOXYSHOP_PRODUCT_NAME_PLURAL,
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'page',
		'hierarchical' => false,
		'supports' => $post_type_support,
		'menu_icon' => FOXYSHOP_DIR . '/images/icon.png',
		'rewrite' => array("slug" => FOXYSHOP_PRODUCTS_SLUG),
		'taxonomies' => (defined('FOXYSHOP_PRODUCT_TAGS') ? array("post_tag") : array())
	));
}



//-------------------------------------------
//Setup Thumbnail Support
//-------------------------------------------
add_action('after_setup_theme','foxyshop_setup_post_thumbnails', 9999);
function foxyshop_setup_post_thumbnails(){
	add_theme_support('post-thumbnails');
}





//-------------------------------------------
//Custom Columns
//-------------------------------------------
add_filter('manage_edit-foxyshop_product_columns', 'add_new_foxyshop_product_columns');
function add_new_foxyshop_product_columns($cols) {
	$new_columns['cb'] = '<input type="checkbox" />';
	$new_columns['id'] = __('ID');
	$new_columns['title'] = FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Title', 'column name');
	$new_columns['productimage'] = __('Image');
	$new_columns['productcode'] = __('Code');
	$new_columns['price'] = __('Price');
	$new_columns['productcategory'] = FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Category');
	return $new_columns;
}





//-------------------------------------------
//Rewrite Columns
//-------------------------------------------
add_action('manage_posts_custom_column', 'manage_custom_columns', 10, 2);
function manage_custom_columns($column_name, $id) {
	global $wpdb, $foxyshop_settings;
	switch ($column_name) {
	case 'id':
		echo $id;
		break;
	case 'productcategory':
		$_taxonomy = 'foxyshop_categories';
		$terms = get_the_terms($id, $_taxonomy);
		if ( !empty( $terms ) ) {
			$out = array();
			foreach ( $terms as $c )
				$out[] = "<a href='edit-tags.php?action=edit&taxonomy=$_taxonomy&post_type=book&tag_ID={$c->term_id}'> " . esc_html(sanitize_term_field('name', $c->name, $c->term_id, 'category', 'display')) . "</a>";
			echo join( ', ', $out );
		}
		else {
			_e('Uncategorized');
		}
		break;
	case 'productimage':
		$featuredImageID = (has_post_thumbnail($id) ? get_post_thumbnail_id($id) : 0);
		$imageNumber = 0;
		$src = "";
		$attachments = get_posts(array('numberposts' => -1, 'post_type' => 'attachment','post_status' => null,'post_parent' => $id, 'order' => 'ASC','orderby' => 'menu_order'));
		foreach ($attachments as $attachment) {
			$thumbnailSRC = wp_get_attachment_image_src($attachment->ID, "thumbnail");
			if ($featuredImageID == $attachment->ID || ($featuredImageID == 0 && $imageNumber == 0)) $src = $thumbnailSRC[0];
			$imageNumber++;
		}
		if (!$src) $src = $foxyshop_settings['default_image'];
		echo '<a href="post.php?post=' . $id . '&amp;action=edit"><img src="' . $src . '" alt="" /></a>';
		break;
	case 'productcode':
		$productcode = get_post_meta($id, "_code", true);
		echo ($productcode ? $productcode : '(' . $id . ')');
		break;
	case 'price':

		$salestartdate = get_post_meta($id,'_salestartdate',TRUE);
		$saleenddate = get_post_meta($id,'_saleenddate',TRUE);
		if ($salestartdate == '999999999999999999') $salestartdate = 0;
		if ($saleenddate == '999999999999999999') $saleenddate = 0;
		$originalprice = get_post_meta($id,'_price', true);
		$saleprice = get_post_meta($id,'_saleprice', true);

		if ($saleprice > 0) {
			$beginningOK = (strtotime("now") > $salestartdate);
			$endingOK = (strtotime("now") < ($saleenddate + 86400) || $saleenddate == 0);
			if ($beginningOK && $endingOK || ($salestartdate == 0 && $saleenddate == 0)) {
				echo '<span style="text-decoration: line-through; margin-right: 10px;">' . foxyshop_currency($originalprice) . '</span><span style="color: red;">' . foxyshop_currency($saleprice) . '</span>';
			} else {
				echo foxyshop_currency($originalprice);
			}
		} else {
			echo foxyshop_currency($originalprice);
		}
		break;
	default:
	}
}





//-------------------------------------------
//Add Filter Box to Top of Product List
//-------------------------------------------
add_action('restrict_manage_posts', 'foxyshop_restrict_manage_posts');
function foxyshop_restrict_manage_posts() {

    // only display these taxonomy filters on desired custom post_type listings
    global $typenow;
    if ($typenow == 'foxyshop_product') {

        // create an array of taxonomy slugs you want to filter by - if you want to retrieve all taxonomies, could use get_taxonomies() to build the list
        $filters = array('foxyshop_categories');

        foreach ($filters as $tax_slug) {
            // retrieve the taxonomy object
            $tax_obj = get_taxonomy($tax_slug);
            $tax_name = $tax_obj->labels->name;
            // retrieve array of term objects per taxonomy
            $terms = get_terms($tax_slug);

            // output html for taxonomy dropdown filter
            echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
            echo '<option value="">' . __('Show All') . ' ' . $tax_name . '</option>'."\n";
            foreach ($terms as $term) {
                // output each select option line, check against the last $_GET to show the current option selected
                echo '<option value='. $term->slug, $tax_slug == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
            }
            echo "</select>";
        }
    }
}




//-------------------------------------------
//Add Filter For Language
//-------------------------------------------
add_filter('post_updated_messages', 'foxyshop_updated_messages');
function foxyshop_updated_messages($messages) {
	global $post, $post_ID;

	$messages['foxyshop_product'] = array(
		1 => FOXYSHOP_PRODUCT_NAME_SINGULAR.' updated. <a href="'.esc_url(get_permalink($post_ID)).'">View '.strtolower(FOXYSHOP_PRODUCT_NAME_SINGULAR).'</a>',
		2 => __('Custom field updated.'),
		3 => __('Custom field deleted.'),
		4 => FOXYSHOP_PRODUCT_NAME_SINGULAR.__(' updated.'),
		6 => FOXYSHOP_PRODUCT_NAME_SINGULAR.' published. <a href="' . esc_url(get_permalink($post_ID)) . '">View '.strtolower(FOXYSHOP_PRODUCT_NAME_SINGULAR).'</a>',
		7 => FOXYSHOP_PRODUCT_NAME_SINGULAR.__(' saved.'),
		8 => FOXYSHOP_PRODUCT_NAME_SINGULAR.' submitted. <a target="_blank" href="'.esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))).'">Preview '.strtolower(FOXYSHOP_PRODUCT_NAME_SINGULAR).'</a>',
		9 => FOXYSHOP_PRODUCT_NAME_SINGULAR.' scheduled for: <strong>'.date_i18n( __('M j, Y @ G:i'), strtotime($post->post_date)).'</strong>. <a target="_blank" href="'.esc_url(get_permalink($post_ID)).'">Preview '.strtolower(FOXYSHOP_PRODUCT_NAME_SINGULAR).'</a>',
		10 => FOXYSHOP_PRODUCT_NAME_SINGULAR.' draft updated. <a target="_blank" href="'.esc_url(add_query_arg( 'preview', 'true', get_permalink($post_ID))).'">Preview '.strtolower(FOXYSHOP_PRODUCT_NAME_SINGULAR).'</a>'
	);
	return $messages;
}




//-------------------------------------------
//Custom Taxonomy: Product Categories
//-------------------------------------------
add_action('init', 'foxyshop_product_category_init', 1);
function foxyshop_product_category_init() {
	$labels = array(
		'name' => FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Categories'),
		'singular_name' => FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Category'),
		'parent_item' => __('Parent Category'),
		'all_items' => __('All').' '.FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Categories'),
		'edit_item' => __('Edit').' '.FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Category'),
		'update_item' => __('Update').' '.FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Category'),
		'add_new_item' => __('Add New').' '.FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Category'),
		'new_item_name' => __('New').' '.FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Category Name'),
		'menu_name' => FOXYSHOP_PRODUCT_NAME_SINGULAR.' '.__('Categories')
	);
	register_taxonomy('foxyshop_categories', 'foxyshop_product', array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => FOXYSHOP_PRODUCT_CATEGORY_SLUG, 'hierarchical' => true)
	));
}






//-------------------------------------------
//Meta Box for Product Info
//-------------------------------------------
add_action('admin_init','foxyshop_product_meta_init');
function foxyshop_product_meta_init() {
	global $foxyshop_settings;
	add_meta_box('product_details_meta', FOXYSHOP_PRODUCT_NAME_SINGULAR.' Details', 'foxyshop_product_details_setup', 'foxyshop_product', 'side', 'high');
	add_meta_box('product_pricing_meta', 'Pricing Details', 'foxyshop_product_pricing_setup', 'foxyshop_product', 'side', 'low');
	add_meta_box('product_images_meta', FOXYSHOP_PRODUCT_NAME_SINGULAR.' Images', 'foxyshop_product_images_setup', 'foxyshop_product', 'normal', 'high');
	add_meta_box('product_variations_meta', FOXYSHOP_PRODUCT_NAME_SINGULAR.' Variations', 'foxyshop_product_variations_setup', 'foxyshop_product', 'normal', 'high');
	add_meta_box('product_related_meta', 'Related '.FOXYSHOP_PRODUCT_NAME_PLURAL, 'foxyshop_related_products_setup', 'foxyshop_product', 'normal', 'low');
	if ($foxyshop_settings['enable_bundled_products']) add_meta_box('product_bundled_meta', 'Bundled '.FOXYSHOP_PRODUCT_NAME_PLURAL, 'foxyshop_bundled_products_setup', 'foxyshop_product', 'normal', 'low');
	add_action('save_post','foxyshop_product_meta_save');
}




//-------------------------------------------
//Main Product Details
//-------------------------------------------
function foxyshop_product_details_setup() {
	global $post, $foxyshop_settings;
	$_price = number_format((double)get_post_meta($post->ID,'_price',TRUE),2,".",",");
	$_code = get_post_meta($post->ID,'_code',TRUE);
	$_category = get_post_meta($post->ID,'_category',TRUE);
	$_quantity_min = get_post_meta($post->ID,'_quantity_min',TRUE);
	$_quantity_max = get_post_meta($post->ID,'_quantity_max',TRUE);

	$defaultweight = explode(" ",$foxyshop_settings['default_weight']);
	$defaultweight1 = (int)$defaultweight[0];
	$defaultweight2 = (count($defaultweight) > 1 ? number_format($defaultweight[1],1) : "0.0");
	$_weight = (get_post_meta($post->ID,'_weight',TRUE) ? explode(" ", get_post_meta($post->ID,'_weight',TRUE)) : array("0","0.0"));
	
	if ((int)$_weight[0] == 0 && (double)$_weight[1] == 0) {
		$_weight[0] = $defaultweight1;
		$_weight[1] = $defaultweight2;
	}
	
	$_hide_product = get_post_meta($post->ID,'_hide_product',TRUE);
	?>
	<div class="foxyshop_field_control">
		<label for="_price"><?php _e('Base Price'); ?></label>
		<input type="text" name="_price" id="_price" value="<?php echo $_price; ?>" onblur="foxyshop_check_number(this);" style="width: 90px; float: left;" />
		<span style="float: left; margin: 9px 0 0 5px;">0.00</span>
	</div>
	<div class="foxyshop_field_control">
		<label for="_code"><?php _e('Item Code'); ?></label>
		<input type="text" name="_code" id="_code" value="<?php echo $_code; ?>" />
	</div>
	<div class="foxyshop_field_control">
		<label for="_weight1"><?php _e('Weight'); ?></label>
		<input type="text" name="_weight1" id="_weight1" value="<?php echo (int)$_weight[0]; ?>" />
		<span style="float: left; margin: 9px 0 0 5px; width: 34px;"><?php echo ($foxyshop_settings['weight_type'] == "metric" ? 'kg' : 'lbs'); ?></span>
		<input type="text" name="_weight2" id="_weight2" value="<?php echo number_format($_weight[1],1); ?>" />
		<span style="float: left; margin: 9px 0 0 5px;"><?php echo ($foxyshop_settings['weight_type'] == "metric" ? 'gm' : 'oz'); ?></span>
	</div>
	<div class="foxyshop_field_control">
		<label for="_quantity_min"><?php _e('Qty Settings'); ?></label>
		<input type="text" name="_quantity_min" id="_quantity_min" value="<?php echo $_quantity_min; ?>" style="width: 46px; float: left;" onblur="foxyshop_check_number_single(this);" />
		<span style="float: left; margin: 9px 0 0 5px; width: 34px;"><?php _e('min'); ?></span>
		<input type="text" name="_quantity_max" id="_quantity_max" value="<?php echo $_quantity_max; ?>" style="width: 46px; float: left;" onblur="foxyshop_check_number_single(this);" />
		<span style="float: left; margin: 9px 0 0 5px;"><?php _e('max'); ?></span>
	</div>
	<?php if ($foxyshop_settings['ship_categories']) { ?>
	<div class="foxyshop_field_control">
		<label for="_category"><?php _e('Shipping Cat.'); ?></label>
		<select name="_category" id="_category">
			<option value=""><?php _e('Default'); ?></option>
			<?php
			$arrShipCategories = preg_split("/(\r\n|\n)/", $foxyshop_settings['ship_categories']);
			for ($i = 0; $i < count($arrShipCategories); $i++) {
				$shipping_category = explode("|", $arrShipCategories[$i]);
				if (count($shipping_category) > 1) {
					$shipping_category_code = trim($shipping_category[0]);
					$shipping_category_name = trim($shipping_category[1]);
				} else {
					$shipping_category_code = trim($shipping_category[0]);
					$shipping_category_name = trim($shipping_category[0]);
				}
				echo '<option value="' . esc_attr($shipping_category_code) . '"';
				if (esc_attr($shipping_category_code == $_category)) echo ' selected="selected"';
				echo '>' . esc_attr($shipping_category_name) . '</option>';
				echo "\n";
			}
			?>
		</select>
	</div>
	<?php } ?>
	<?php if ($foxyshop_settings['enable_sso'] && $foxyshop_settings['sso_account_required'] == 2) { ?>
	<div class="foxyshop_field_control">
		<input type="checkbox" name="_require_sso" id="_require_sso" style="float: left; margin: 5px 0 0 10px;"<?php echo checked(get_post_meta($post->ID,'_require_sso',TRUE),"on"); ?> />
		<label style="width: 210px;" for="_require_sso"><?php _e('Require Account For Checkout'); ?></label>
	</div>
	<?php } ?>
	<div class="foxyshop_field_control">
		<input type="checkbox" name="_hide_product" id="_hide_product"<?php echo checked($_hide_product,"on"); ?> />
		<label style="width: 210px;" for="_hide_product"><?php echo sprintf(__('Hide This %s From List View'), FOXYSHOP_PRODUCT_NAME_SINGULAR); ?></label>
	</div>
	<div style="clear:both"></div>
	<?php
	echo '<input type="hidden" name="products_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
	echo '<input type="hidden" name="menu_order" value="' . ($post->menu_order == 0 && $post->post_status == "auto-draft" ? $post->ID : $post->menu_order) . '" />';
}




//-------------------------------------------
//Product Pricing Details
//-------------------------------------------
function foxyshop_product_pricing_setup() {
	global $post, $foxyshop_settings;
	$_saleprice = number_format((double)get_post_meta($post->ID,'_saleprice',TRUE),2,".",",");
	$_salestartdate = get_post_meta($post->ID,'_salestartdate',TRUE);
	$_saleenddate = get_post_meta($post->ID,'_saleenddate',TRUE);

	//Format Sale Date
	if ($_salestartdate == '999999999999999999') $_salestartdate = "";
	if ($_salestartdate) $_salestartdate = date('n/j/Y', $_salestartdate);
	if ($_saleenddate == '999999999999999999') $_saleenddate = "";
	if ($_saleenddate) $_saleenddate = date('n/j/Y', $_saleenddate);
	
	$_discount_quantity_amount = get_post_meta($post->ID,'_discount_quantity_amount',TRUE);
	$_discount_quantity_percentage = get_post_meta($post->ID,'_discount_quantity_percentage',TRUE);
	$_discount_price_amount = get_post_meta($post->ID,'_discount_price_amount',TRUE);
	$_discount_price_percentage = get_post_meta($post->ID,'_discount_price_percentage',TRUE);
	
	$_sub_frequency = get_post_meta($post->ID,'_sub_frequency',TRUE);
	$_sub_startdate = get_post_meta($post->ID,'_sub_startdate',TRUE);
	$_sub_enddate = get_post_meta($post->ID,'_sub_enddate',TRUE);

	?>
	<h4><?php _e('Sale'); ?></h4>
	<div class="foxyshop_field_control">
		<label for="_saleprice"><?php _e('Sale Price'); ?></label>
		<input type="text" name="_saleprice" id="_saleprice" value="<?php echo $_saleprice; ?>" style="width: 87px; float: left;" />
		<span style="float: left; margin: 9px 0 0 5px;">0.00</span>
	</div>
	<div class="foxyshop_field_control">
		<label for="_salestartdate"><?php _e('Start Date'); ?></label>
		<input type="text" id="_salestartdate" name="_salestartdate" value="<?php echo $_salestartdate; ?>" style="width: 87px; float: left;" />
		<span style="float: left; margin: 9px 0 0 5px;">mm/dd/yyy</span>
	</div>
	<div class="foxyshop_field_control">
		<label for="_saleenddate"><?php _e('End Date'); ?></label>
		<input type="text" id="_saleenddate" name="_saleenddate" value="<?php echo $_saleenddate; ?>" style="width: 87px; float: left;" />
		<span style="float: left; margin: 9px 0 0 5px;">mm/dd/yyy</span>
	</div>
	<div style="clear: both;"></div>
	

	<h4><?php _e('Discounts'); ?> <a href="http://wiki.foxycart.com/v/0.7.1/coupons_and_discounts" target="_blank">(<?php _e('reference'); ?>)</a></h4>
	<div class="foxyshop_field_control discount_fields">
		<label for="_discount_quantity_amount"><?php _e('Quantity $'); ?></label>
		<input type="text" name="_discount_quantity_amount" id="_discount_quantity_amount" value="<?php echo $_discount_quantity_amount; ?>" />
		<div style="clear:both;"></div>
	</div>
	<div class="foxyshop_field_control discount_fields">
		<label for="_discount_quantity_percentage"><?php _e('Quantity %'); ?></label>
		<input type="text" name="_discount_quantity_percentage" id="_discount_quantity_percentage" value="<?php echo $_discount_quantity_percentage; ?>" />
		<div style="clear:both;"></div>
	</div>
	<div class="foxyshop_field_control discount_fields">
		<label for="_discount_price_amount"><?php _e('Price $'); ?></label>
		<input type="text" name="_discount_price_amount" id="_discount_price_amount" value="<?php echo $_discount_price_amount; ?>" />
		<div style="clear:both;"></div>
	</div>
	<div class="foxyshop_field_control discount_fields">
		<label for="_discount_price_percentage"><?php _e('Price %'); ?></label>
		<input type="text" name="_discount_price_percentage" id="_discount_price_percentage" value="<?php echo $_discount_price_percentage; ?>" />
		<div style="clear:both;"></div>
	</div>
	<div style="clear:both;"></div>

	<?php if ($foxyshop_settings['manage_inventory_levels']) { ?>
	<h4><?php _e('Set Inventory Levels'); ?></a></h4>
	<div style="float: left; width: 155px; margin-bottom: 5px; font-size: 11px;">Product Code</div>
	<div style="float: left; width: 52px; margin-bottom: 5px; font-size: 11px;">Count</div>
	<div style="float: left; width: 50px; margin-bottom: 5px; font-size: 11px;" title="<?php echo sprintf(__('If not set, default value will be used (%s)'), $foxyshop_settings['inventory_alert_level']); ?>">Alert Lvl</div>
	<ul id="inventory_levels">
		<?php
		$inventory_levels = unserialize(get_post_meta($post->ID,'_inventory_levels',TRUE));
		if (!is_array($inventory_levels)) $inventory_levels = array();
		$i = 1;
		foreach ($inventory_levels as $ivcode => $iv) {
			if ($ivcode) {
				echo '<li>';
				echo '<input type="text" id="inventory_code_' . $i . '" name="inventory_code_' . $i . '" value="' . $ivcode . '" class="inventory_code" rel="' . $i . '" style="width: 142px;" />';
				echo '<input type="text" id="inventory_count_' . $i . '" name="inventory_count_' . $i . '" value="' . $iv['count'] . '" class="inventory_count" rel="' . $i . '" />';
				echo '<input type="text" id="inventory_alert_' . $i . '" name="inventory_alert_' . $i . '" value="' . $iv['alert'] . '" class="inventory_count" rel="' . $i . '" />';
				echo "</li>\n";
				$i++;
			}
		}
		?>
		<li><input type="text" id="inventory_code_<?php echo $i; ?>" name="inventory_code_<?php echo $i; ?>" value="" class="inventory_code" rel="<?php echo $i; ?>" style="width: 142px;" /><input type="text" id="inventory_count_<?php echo $i; ?>" name="inventory_count_<?php echo $i; ?>" value="" class="inventory_count" rel="<?php echo $i; ?>" /><input type="text" id="inventory_alert_<?php echo $i; ?>" name="inventory_alert_<?php echo $i; ?>" value="" class="inventory_count" rel="<?php echo $i; ?>" /></li>
	</ul>
	<input type="hidden" name="max_inventory_count" id="max_inventory_count" value="<?php echo $i; ?>" />
	<div style="clear:both;"></div>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$("#_code").blur(function() {
			if ($("#max_inventory_count").val() == 1 && !$("#inventory_code_1").val()) {
				$("#inventory_code_1").val($("#_code").val());
				addField(2);
			}
		});
		
		$(".inventory_code, .inventory_count").live("keyup", function() {
			thisID = parseFloat($(this).attr("rel"));
			nextID = thisID + 1;
			if (parseFloat($("#max_inventory_count").val()) == thisID && $("#inventory_code_"+nextID).length == 0 && $("#inventory_code_"+thisID).val()) {
				addField(nextID);
			}
		});
		
		function addField(nextID) {
			$("#inventory_levels").append('<li><input type="text" id="inventory_code_' + nextID + '" name="inventory_code_' + nextID + '" value="" class="inventory_code" rel="' + nextID + '" style="width: 142px;" /><input type="text" id="inventory_count_' + nextID + '" name="inventory_count_' + nextID + '" value="" class="inventory_count" rel="' + nextID + '" /><input type="text" id="inventory_alert_' + nextID + '" name="inventory_alert_' + nextID + '" value="" class="inventory_count" rel="' + nextID + '" /></li>');
			$("#max_inventory_count").val(nextID);
		}
	});
	</script>
	<?php } ?>


	<?php if ($foxyshop_settings['enable_subscriptions']) { ?>
	<h4 style="margin-bottom: 3px;"><?php _e('Subscription Attributes'); ?> <a href="http://wiki.foxycart.com/v/0.7.1/cheat_sheet#subscription_product_options" target="_blank">(<?php _e('reference'); ?>)</a></h4>
	<span style="color: #999999; display: block; line-height: 15px; margin-bottom: 5px;"><?php _e('You may also enter a'); ?> <a href="http://php.net/manual/en/function.strtotime.php" target="_blank" title="PHP Docs" style="color: #999">strtotime</a> <?php _e('argument for start or end (like +3 months)'); ?></span>
	<div id="foxyshop_subscription_attributes">
		<div class="foxyshop_field_control">
			<label for="_sub_frequency"><?php _e('Frequency'); ?></label>
			<input type="text" name="_sub_frequency" id="_sub_frequency" value="<?php echo $_sub_frequency; ?>" />
			<span>60d, 2w, 1m, 1y, .5m</span>
		</div>
		<div class="foxyshop_field_control">
			<label for="_sub_startdate"><?php _e('Start Date'); ?></label>
			<input type="text" id="_sub_startdate" name="_sub_startdate" value="<?php echo $_sub_startdate; ?>" />
			<span>YYYYMMDD or D</span>
		</div>
		<div class="foxyshop_field_control">
			<label for="_sub_enddate"><?php _e('End Date'); ?></label>
			<input type="text" id="_sub_enddate" name="_sub_enddate" value="<?php echo $_sub_enddate; ?>" />
			<span>YYYYMMDD or D</span>
		</div>
		<div style="clear: both;"></div>
	</div>
	<?php
	}
}




//-------------------------------------------
//Related Products Setup
//-------------------------------------------
function foxyshop_related_products_setup() {
	global $post, $foxyshop_settings, $bundledList;
	$arr_related_products = explode(",",get_post_meta($post->ID,'_related_products',TRUE));
	if ($foxyshop_settings['enable_bundled_products']) $arr_bundled_products = explode(",",get_post_meta($post->ID,'_bundled_products',TRUE));

	$relatedList = "";
	$bundledList = "";
	$args = array('post_type' => 'foxyshop_product', "post__not_in" => array($post->ID), 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC');
	$all_products = get_posts($args);
	foreach ($all_products as $product) {
		$relatedList .= '<option value="' . $product->ID . '"'. (in_array($product->ID, $arr_related_products) ? ' selected="selected"' : '') . '>' . $product->post_title . '</option>'."\n";
		if ($foxyshop_settings['enable_bundled_products']) $bundledList .= '<option value="' . $product->ID . '"'. (in_array($product->ID, $arr_bundled_products) ? ' selected="selected"' : '') . '>' . $product->post_title . '</option>'."\n";
	} ?>
	<select name="_related_products_list[]" id="_related_products_list" data-placeholder="Search for <?php echo FOXYSHOP_PRODUCT_NAME_PLURAL; ?>" style="width: 100%;" class="chzn-select" multiple="multiple">
		<?php echo $relatedList; ?>
	</select>
	<p><?php echo sprintf(__("Click the box above for a drop-down menu showing all %s. Type to search and click or press enter to select."), strtolower(FOXYSHOP_PRODUCT_NAME_PLURAL)); ?></p>

	<?php
}




//-------------------------------------------
//Bundled Products Setup
//-------------------------------------------
function foxyshop_bundled_products_setup() {
	global $post, $foxyshop_settings, $bundledList; ?>
	<select name="_bundled_products_list[]" id="_bundled_products_list" data-placeholder="Search for <?php echo FOXYSHOP_PRODUCT_NAME_PLURAL; ?>" style="width: 100%;" class="chzn-select" multiple="multiple">
		<?php echo $bundledList; ?>
	</select>
	<p><?php echo sprintf(__("Click the box above for a drop-down menu showing all %s. Type to search and click or press enter to select."), strtolower(FOXYSHOP_PRODUCT_NAME_PLURAL)); ?></p>
	<?php
}





//-------------------------------------------
//Product Images
//-------------------------------------------
function foxyshop_product_images_setup() {
	global $post, $foxyshop_settings;
	$upload_dir = wp_upload_dir();

	//Get Max Upload Limit
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);
	$foxyshop_max_upload = $upload_mb * 1048576;
	if ($foxyshop_max_upload == 0) $foxyshop_max_upload = "8000000";

	if (array_key_exists('error', $upload_dir)) {
		if ($upload_dir['error'] != '') {
			echo '<p style="color: red;"><strong>Warning:</strong> Images cannot be uploaded at this time. The error given is below.<br />Please attempt to correct the error and reload this page.</p>';
			echo '<p>' . $upload_dir['error'] . '</p>';
			return;
		}
	}

	echo '<link rel="stylesheet" href="' . FOXYSHOP_DIR . '/js/uploadify/uploadify.css" type="text/css" media="screen" />'."\n";
	echo '<script type="text/javascript" src="' . FOXYSHOP_DIR . '/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>'."\n";
	//echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>'."\n";

	
	echo '<input type="file" id="foxyshop_new_product_image">'."\n";
	echo '<div id="foxyshop_image_waiter"></div>';
	echo '<input type="hidden" id="foxyshop_sortable_value" name="foxyshop_sortable_value" />'."\n";
	echo '<ul id="foxyshop_product_image_list"></ul>'."\n";
	echo '<div style="clear: both;"></div>';

	$ajax_nonce = wp_create_nonce("foxyshop-product-image-functions-".$post->ID);
	?>
	<script type="text/javascript">
	var renameLive = false;
	jQuery(document).ready(function($){
		$("#postimagediv").hide();
		
		$.post(ajaxurl, { action: 'foxyshop_product_ajax_action', foxyshop_action: 'refresh_images', security: '<?php echo $ajax_nonce; ?>', foxyshop_product_id: <?php echo $post->ID; ?>}, function(response) {
			$("#foxyshop_product_image_list").html(response)
		});

		$("#foxyshop_product_image_list .foxyshop_image_rename").live("click", function() {
			var thisID = $(this).attr("rel");
			$(".renamediv").removeClass('rename_active');
			$("#renamediv_" + thisID).addClass('rename_active');
			document.getElementById('rename_' + thisID).select();
			renameLive = true;
			return false;
		});

		$("form").bind("keypress", function(e) {
			if (e.keyCode == 13 && renameLive) {
				return false;
			}
		});

		$("#foxyshop_product_image_list input").live("keyup blur", function(e) {
			var thisID = $(this).attr("rel");
			var newTitle = $(this).val();
			if (e.keyCode == 27) {
				$("#renamediv_" + thisID).removeClass('rename_active');
				renameLive = false;
			} else if (e.keyCode == 13) {
				var data = {
					action: 'foxyshop_product_ajax_action',
					security: '<?php echo $ajax_nonce; ?>',
					foxyshop_action: 'rename_image',
					foxyshop_new_name: newTitle,
					foxyshop_image_id: thisID,
					foxyshop_product_id: <?php echo $post->ID; ?>
				};
				$.post(ajaxurl, data, function() {
					$("#renamediv_" + thisID).removeClass('rename_active');
					$("#att_" + thisID + " img").attr("alt",newTitle + ' (' + thisID + ')').attr("title",newTitle + ' (' + thisID + ')');
					renameLive = false;
				});
			}
			return false;
		});
		
		
		$("#foxyshop_product_image_list .foxyshop_image_delete").live("click", function() {
			var data = {
				action: 'foxyshop_product_ajax_action',
				security: '<?php echo $ajax_nonce; ?>',
				foxyshop_action: 'delete_image',
				foxyshop_image_id: $(this).attr("rel"),
				foxyshop_product_id: <?php echo $post->ID; ?>
			};
			$("#foxyshop_image_waiter").show();
			$.post(ajaxurl, data, function(response) {
				$("#foxyshop_product_image_list").html(response);
				$("#foxyshop_image_waiter").hide();
			});
			return false;
		});

		$("#foxyshop_product_image_list .foxyshop_image_featured").live("click", function() {
			var data = {
				action: 'foxyshop_product_ajax_action',
				security: '<?php echo $ajax_nonce; ?>',
				foxyshop_action: 'featured_image',
				foxyshop_image_id: $(this).attr("rel"),
				foxyshop_product_id: <?php echo $post->ID; ?>
			};
			$("#foxyshop_image_waiter").show();
			$.post(ajaxurl, data, function(response) {
				$("#foxyshop_product_image_list").html(response);
				$("#foxyshop_image_waiter").hide();
			});
			return false;
		});

		$('#foxyshop_new_product_image').show().each(function() {
			var variationID = $(this).attr("rel");
			$(this).uploadify({
				uploader  : '<?php echo FOXYSHOP_DIR; ?>/js/uploadify/uploadify.swf',
				script    : '<?php echo get_bloginfo("url") . FOXYSHOP_URL_BASE; ?>/upload-<?php echo $foxyshop_settings['datafeed_url_key']; ?>/',
				cancelImg : '<?php echo FOXYSHOP_DIR; ?>/js/uploadify/cancel.png',
				auto      : true,
				buttonImg	: '<?php echo FOXYSHOP_DIR; ?>/images/add-new-image.png',
				width     : '132',
				height    : '23',
				scriptData: {
					'foxyshop_image_uploader':'1',
					'foxyshop_product_id':'<?php echo $post->ID; ?>',
					'foxyshop_product_title': $("#title").val()
				},
				sizeLimit : '<?php echo $foxyshop_max_upload; ?>',
				onComplete: function(event,queueID,fileObj,response,data) {
						var data = {
							'action': 'foxyshop_product_ajax_action',
							'security': '<?php echo $ajax_nonce; ?>',
							'foxyshop_product_id':'<?php echo $post->ID; ?>',
							'foxyshop_action': 'add_new_image'
						};

						$("#foxyshop_image_waiter").show();
						$.post(ajaxurl, data, function(response) {
							$("#foxyshop_product_image_list").html(response)
							$("#foxyshop_image_waiter").hide();
						});
				}
			});
		});

		$("#foxyshop_product_image_list").sortable({ 
			placeholder: "sortable-placeholder", 
			revert: false,
			tolerance: "pointer",
			update: function() {
				$("#foxyshop_sortable_value").val($("#foxyshop_product_image_list").sortable("toArray"));
				var data = {
					action: 'foxyshop_product_ajax_action',
					security: '<?php echo $ajax_nonce; ?>',
					foxyshop_action: 'update_image_order',
					foxyshop_order_array: $("#foxyshop_sortable_value").val(),
					foxyshop_product_id: <?php echo $post->ID; ?>
				};
				$("#foxyshop_image_waiter").show();
				$.post(ajaxurl, data, function(response) {
					$("#foxyshop_product_image_list").html(response)
					$("#foxyshop_image_waiter").hide();
				});
			}
		});

	});	
	</script>
	<?php


}




//-------------------------------------------
//Product Variations
//-------------------------------------------
function foxyshop_product_variations_setup() {
	global $post, $foxyshop_settings, $wp_version;
	
	$var_type_array = array('dropdown' => __("Dropdown List"), 'radio' => __("Radio Buttons"), 'checkbox' => __("Checkbox"), 'text' => __("Single Line of Text"), 'textarea' => __("Multiple Lines of Text"), 'upload' => __("Custom File Upload"), 'descriptionfield' => __("Description Field"));
	$variation_key = __('Name{p+1.50|w-1|c:product_code|y:shipping_category|dkey:display_key|ikey:image_id}');
	
	//Setup Variations
	$variations = unserialize(get_post_meta($post->ID, '_variations', 1));
	if (!is_array($variations)) $variations = array();
	
	echo '<input type="hidden" id="variation_order_value" name="variation_order_value" />'."\n";
	
	echo '<div id="variation_sortable">'."\n";
	$max_variations = count($variations);
	if ($max_variations == 0) $max_variations = 1;
	for ($i=1;$i<=$max_variations;$i++) {
		$_variationName = (array_key_exists($i, $variations) ? $variations[$i]['name'] : '');
		$_variation_type = (array_key_exists($i, $variations) ? $variations[$i]['type'] : 'dropdown');
		$_variationValue = (array_key_exists($i, $variations) ? $variations[$i]['value'] : '');
		$_variationDisplayKey = (array_key_exists($i, $variations) ? $variations[$i]['displayKey'] : '');
		$_variationRequired = (array_key_exists($i, $variations) ? $variations[$i]['required'] : '');
		?>
		<div class="product_variation" rel="<?php echo $i; ?>" id="variation<?php echo $i; ?>">
			<input type="hidden" name="sort<?php echo $i; ?>" id="sort<?php echo $i; ?>" class="variationsort" value="<?php echo $i; ?>" />
			<input type="hidden" name="dropdownradio_value_<?php echo $i; ?>" id="dropdownradio_value_<?php echo $i; ?>" value="" />
			<input type="hidden" name="text1_value_<?php echo $i; ?>" id="text1_value_<?php echo $i; ?>" value="" />
			<input type="hidden" name="text2_value_<?php echo $i; ?>" id="text2_value_<?php echo $i; ?>" value="" />
			<input type="hidden" name="textarea_value_<?php echo $i; ?>" id="textarea_value_<?php echo $i; ?>" value="" />
			<input type="hidden" name="descriptionfield_value_<?php echo $i; ?>" id="descriptionfield_value_<?php echo $i; ?>" value="" />
			<input type="hidden" name="checkbox_value_<?php echo $i; ?>" id="checkbox_value_<?php echo $i; ?>" value="" />
			<input type="hidden" name="upload_value_<?php echo $i; ?>" id="upload_value_<?php echo $i; ?>" value="" />
			
			<!-- //// VARIATION HEADER //// -->
			<div class="foxyshop_field_control">
				<label><?php _e('Variation Name'); ?></label>
				<input type="text" name="_variation_name_<?php echo $i; ?>" class="variation_name" id="_variation_name_<?php echo $i; ?>" value="<?php echo esc_attr($_variationName); ?>" />

				<label for="_variation_type_<?php echo $i; ?>" class="variationtypelabel"><?php _e('Variation Type'); ?>:</label> 
				<select name="_variation_type_<?php echo $i; ?>" id="_variation_type_<?php echo $i; ?>" class="variationtype">
				<?php
				foreach ($var_type_array as $var_name => $var_val) {
					echo '<option value="' . $var_name . '"' . ($_variation_type == $var_name ? ' selected="selected"' : '') . '>' . $var_val . '  </option>'."\n";
				} ?>
				</select>
				<a href="#" class="button deleteVariation" rel="<?php echo $i; ?>">Delete</a>
			</div>
			
			
			<div id="variation_holder_<?php echo $i; ?>">
			
				<?php if ($_variation_type == "dropdown") : ?>
					<!-- Dropdown -->
					<div class="foxyshop_field_control dropdown variationoptions">
						<label for="_variation_value_<?php echo $i; ?>"><?php _e('Items in Dropdown'); ?></label>
						<textarea name="_variation_value_<?php echo $i; ?>" id="_variation_value_<?php echo $i; ?>"><?php echo $_variationValue; ?></textarea>
						<div class="variationkey"><?php echo $variation_key; ?></div>
					</div>
				
				<?php elseif($_variation_type == "radio") : ?>
					<!-- Radio Buttons -->
					<div class="foxyshop_field_control radio variationoptions">
						<label for="_variation_radio_<?php echo $i; ?>"><?php _e('Radio Button Options'); ?></label>
						<textarea name="_variation_radio_<?php echo $i; ?>" id="_variation_radio_<?php echo $i; ?>"><?php echo $_variationValue; ?></textarea>
						<div class="variationkey"><?php echo $variation_key; ?></div>
					</div>
			
				<?php elseif($_variation_type == "text") : ?>
					<!-- Text Box -->
					<?php $arrVariationTextSize = explode("|",esc_attr($_variationValue)); ?>
					<div class="foxyshop_field_control text variationoptions">
						<div class="foxyshop_field_control">
							<label for="_variation_textsize1_<?php echo $i; ?>"><?php _e('Text Box Size'); ?></label>
							<input type="text" name="_variation_textsize1_<?php echo $i; ?>" id="_variation_textsize1_<?php echo $i; ?>" value="<?php if (isset($arrVariationTextSize)) echo $arrVariationTextSize[0]; ?>" /> <?php _e('characters'); ?>
						</div>
						<div class="foxyshop_field_control">
							<label for="_variation_textsize2_<?php echo $i; ?>"><?php _e('Maximum Chars'); ?></label>
							<input type="text" name="_variation_textsize2_<?php echo $i; ?>" id="_variation_textsize2_<?php echo $i; ?>" value="<?php if (isset($arrVariationTextSize)) echo $arrVariationTextSize[1]; ?>" /> <?php _e('characters'); ?>
						</div>
						<div style="clear: both;"></div>
					</div>
			
				<?php elseif($_variation_type == "textarea") : ?>
					<!-- Textarea -->
					<div class="foxyshop_field_control textarea variationoptions">
						<label for="_variation_textareasize_<?php echo $i; ?>"><?php _e('Lines of Text'); ?></label>
						<input type="text" name="_variation_textareasize_<?php echo $i; ?>" id="_variation_textareasize_<?php echo $i; ?>" value="<?php echo esc_attr($_variationValue); ?>" /> (default is 3)
					</div>

				<?php elseif($_variation_type == "descriptionfield") : ?>
					<!-- Description Field -->
					<div class="foxyshop_field_control descriptionfield variationoptions">
						<label for="_variation_description_<?php echo $i; ?>"><?php _e('Descriptive Text'); ?></label>
						<textarea name="_variation_description_<?php echo $i; ?>" id="_variation_description_<?php echo $i; ?>"><?php echo $_variationValue; ?></textarea>
					</div>
			
				<?php elseif($_variation_type == "checkbox") : ?>
					<!-- Checkbox -->
					<div class="foxyshop_field_control checkbox variationoptions" style="background-color: transparent;">
						<label for="_variation_checkbox_<?php echo $i; ?>"><?php _e('Value'); ?></label>
						<input type="text" name="_variation_checkbox_<?php echo $i; ?>" id="_variation_checkbox_<?php echo $i; ?>" value="<?php echo $_variationValue; ?>" class="variation_checkbox_text" />
						<div class="variationkey"><?php echo $variation_key; ?></div>
					</div>
			
				<?php elseif($_variation_type == "upload") : ?>
					<!-- Custom File Upload -->
					<div class="foxyshop_field_control upload variationoptions">
						<label for="_variation_uploadinstructions_<?php echo $i; ?>"><?php _e('Instructions'); ?></label>
						<textarea name="_variation_uploadinstructions_<?php echo $i; ?>" id="_variation_uploadinstructions_<?php echo $i; ?>"><?php echo $_variationValue; ?></textarea>
					</div>
				
				<?php endif; ?>
			</div>

			<!-- //// DISPLAY KEY //// -->
			<div class="foxyshop_field_control">
				<label class="dkeylabel" title="Enter a value here if you want your variation to be invisible until called by another variation.">Display Key</label>
				<input type="text" name="_variation_dkey_<?php echo $i; ?>" id="_variation_dkey_<?php echo $i; ?>" value="<?php echo esc_attr($_variationDisplayKey); ?>" class="dkeynamefield" />

				<!-- Required -->
				<div class="variation_required_container" rel="<?php echo $i; ?>"<?php echo ($_variation_type == 'text' || $_variation_type == 'textarea' || $_variation_type == 'upload' ? '' : ' style="display: none;"'); ?>>
					<input type="checkbox" name="_variation_required_<?php echo $i; ?>" id="_variation_required_<?php echo $i; ?>"<?php echo checked($_variationRequired,"on"); ?> />
					<label for="_variation_required_<?php echo $i; ?>"><?php _e('Make Field Required'); ?></label>
				</div>
			</div>
			
			<div class="variationsortnum"><?php echo $i; ?></div>
			<div style="clear: both;"></div>
		</div>
		<?php
	}
	echo "</div>";	
	?>
	<button type="button" id="AddVariation" class="button"><?php _e('Add Another Variation'); ?></button>
	<input type="hidden" name="max_variations" id="max_variations" value="<?php echo $max_variations; ?>" />

<script type="text/javascript">
jQuery(document).ready(function($){
	$('.deleteVariation').live("click", function() {
		variationID = $(this).attr("rel");
		$("#variation" + variationID).slideUp(function() {
			$(this).remove();
			var counter = 1;
			$("div.product_variation").each(function() {
				$(this).find('.variationsort').val(counter);
				$(this).find('.variationsortnum').html(counter);
				counter++;
			});
		});
		return false;
	});


	function foxyshop_variation_order_load_event() {
		$("#variation_sortable").sortable({ 
			placeholder: "sortable-variation-placeholder", 
			revert: false,
			items: "div.product_variation",
			tolerance: "pointer",
			distance: 30,
			update: function() {
				var counter = 1;
				$("div.product_variation").each(function() {
					$(this).find('.variationsort').val(counter);
					$(this).find('.variationsortnum').html(counter);
					counter++;
				});
			}
		});
	};
	addLoadEvent(foxyshop_variation_order_load_event);
	
	//Check For Illegal Titles
	$("input.variation_name").live("blur", function() {
		var thisval = $(this).val().toLowerCase();
		if (thisval == "code" || thisval == "codes" || thisval == "price" || thisval == "name" || thisval == "quantity" || thisval == "category" || thisval == "weight" || thisval == "shipto") {
			alert("Sorry! The title '" + thisval + "' cannot be used as a variation name.");
			return false;
		}
	});

	//Check For Illegal Titles
	$("input.variation_name").live("keypress", function(e) {
		if (e.which !== 0 && (e.charCode == 46 || e.charCode == 34)) {
			alert("Sorry! You can't use this character in a variation name: " + String.fromCharCode(e.keyCode|e.charCode));
			return false;
		}
	});

	//On Change Listener
	$(".variationtype").live("change", function() {
		new_type = $(this).val();
		this_id = $(this).parents(".product_variation").attr("rel");
		
		//Set Temp Values
		temp_dropdown = $("#_variation_value_"+this_id).val();
		temp_radio = $("#_variation_radio_"+this_id).val();
		temp_text1 = $("#_variation_textsize1_"+this_id).val();
		temp_text2 = $("#_variation_textsize2_"+this_id).val();
		temp_textarea = $("#_variation_textareasize_"+this_id).val();
		temp_descriptionfield = $("#_variation_description_"+this_id).val();
		temp_checkbox = $("#_variation_checkbox_"+this_id).val();
		temp_upload = $("#_variation_uploadinstructions_"+this_id).val();
		if (temp_dropdown) $("#dropdownradio_value_"+this_id).val(temp_dropdown);
		if (temp_radio) $("#dropdownradio_value_"+this_id).val(temp_radio);
		if (temp_text1) $("#text1_value_"+this_id).val(temp_text1);
		if (temp_text2) $("#text2_value_"+this_id).val(temp_text2);
		if (temp_textarea) $("#textarea_value_"+this_id).val(temp_textarea);
		if (temp_descriptionfield) $("#descriptionfield_value_"+this_id).val(temp_descriptionfield);
		if (temp_checkbox) $("#checkbox_value_"+this_id).val(temp_checkbox);
		if (temp_upload) $("#upload_value_"+this_id).val(temp_upload);

		//Set Contents in Container
		$("#variation_holder_"+this_id).html(getVariationContents(new_type, this_id));

		//Hide or Show Required Checkbox Option
		if (new_type == 'text' || new_type == 'textarea' || new_type == 'upload') {
			$(this).parents(".product_variation").find(".variation_required_container").show();
		} else {
			$(this).parents(".product_variation").find(".variation_required_container").hide();
			$(this).parents(".product_variation").find(".variation_required_container").find('input[type="checkbox"]').not(':checked');
		}
		
		
	});


	//New Variation
	$("#AddVariation").click(function() {
		var this_id = parseInt($("#max_variations").val()) + 1;
		
		
		new_content = '<div class="product_variation" rel="' + this_id + '" id="variation' + this_id + '">';
		new_content += '<input type="hidden" name="sort' + this_id + '" id="sort' + this_id + '" value="' + this_id + '" class="variationsort" />';
		new_content += '<input type="hidden" name="dropdownradio_value_' + this_id + '" id="dropdownradio_value_' + this_id + '" value="" />';
		new_content += '<input type="hidden" name="text1_value_' + this_id + '" id="text1_value_' + this_id + '" value="" />';
		new_content += '<input type="hidden" name="text2_value_' + this_id + '" id="text2_value_' + this_id + '" value="" />';
		new_content += '<input type="hidden" name="textarea_value_' + this_id + '" id="textarea_value_' + this_id + '" value="" />';
		new_content += '<input type="hidden" name="descriptionfield_value_' + this_id + '" id="descriptionfield_value_' + this_id + '" value="" />';
		new_content += '<input type="hidden" name="checkbox_value_' + this_id + '" id="checkbox_value_' + this_id + '" value="" />';
		new_content += '<input type="hidden" name="upload_value_' + this_id + '" id="upload_value_' + this_id + '" value="" />';
		new_content += '<!-- //// VARIATION HEADER //// -->';
		new_content += '<div class="foxyshop_field_control">';
		new_content += '<label><?php _e('Variation Name'); ?></label>';
		new_content += '<input type="text" name="_variation_name_' + this_id + '" class="variation_name" id="_variation_name_' + this_id + '" value="" />';
		new_content += '<label for="_variation_type_' + this_id + '" class="variationtypelabel"><?php _e('Variation Type'); ?>:</label> ';
		new_content += '<select name="_variation_type_' + this_id + '" id="_variation_type_' + this_id + '" class="variationtype">';
		<?php
		foreach ($var_type_array as $var_name => $var_val) {
			echo "\t\tnew_content += '<option value=\"" . $var_name . '">' . $var_val . "  </option>';\n";
		} ?>
		new_content += '</select>';
		new_content += '<a href="#" class="button deleteVariation" rel="' + this_id + '">Delete</a>';
		new_content += '</div>';
		new_content += '<div id="variation_holder_' + this_id + '"></div>';
		new_content += '<!-- //// DISPLAY KEY //// -->';
		new_content += '<div class="foxyshop_field_control">';
		new_content += '<label class="dkeylabel" title="Enter a value here if you want your variation to be invisible until called by another variation.">Display Key</label>';
		new_content += '<input type="text" name="_variation_dkey_' + this_id + '" id="_variation_dkey_' + this_id + '" value="" class="dkeynamefield" />';
		new_content += '<!-- Required -->';
		new_content += '<div class="variation_required_container" rel="' + this_id + '" style="display: none;">';
		new_content += '<input type="checkbox" name="_variation_required_' + this_id + '" id="_variation_required_' + this_id + '" />';
		new_content += '<label for="_variation_required_' + this_id + '"><?php _e('Make Field Required'); ?></label>';
		new_content += '</div>';
		new_content += '</div>';
		new_content += '<div class="variationsortnum">' + this_id + '</div>';
		new_content += '<div style="clear: both;"></div>';
		new_content += '</div>';
		
		$("#variation_sortable").append(new_content);
		$("#variation_holder_"+this_id).html(getVariationContents("dropdown", this_id));
		
		$("#max_variations").val(this_id);
		$("#variation_sortable").sortable("refresh");
		return false;
	});	





	function getVariationContents(new_type, this_id) {
		new_contents = "";
		variationkeyhtml = '<div class="variationkey"><?php echo $variation_key; ?></div>';
		
		//Dropdown
		if (new_type == "dropdown") {
			new_contents = '<div class="foxyshop_field_control dropdown variationoptions">';
			new_contents += '<label id="_variation_value_' + this_id + '"><?php _e('Items in Dropdown'); ?></label>';
			new_contents += '<textarea name="_variation_value_' + this_id + '" id="_variation_value_' + this_id + '">' + $("#dropdownradio_value_"+this_id).val() + '</textarea>';
			new_contents += variationkeyhtml;
			new_contents += '</div>';
		
		//Radio Buttons
		} else if (new_type == "radio") {
			new_contents = '<div class="foxyshop_field_control radio variationoptions">';
			new_contents += '<label for="_variation_radio_' + this_id + '"><?php _e('Radio Button Options'); ?></label>';
			new_contents += '<textarea name="_variation_radio_' + this_id + '" id="_variation_radio_' + this_id + '">' + $("#dropdownradio_value_"+this_id).val() + '</textarea>';
			new_contents += variationkeyhtml;
			new_contents += '</div>';
		
		//Text
		} else if (new_type == "text") {
			new_contents = '<div class="foxyshop_field_control text variationoptions">';
			new_contents += '<div class="foxyshop_field_control">';
			new_contents += '<label for="_variation_textsize1_' + this_id + '"><?php _e('Text Box Size'); ?></label>';
			new_contents += '<input type="text" name="_variation_textsize1_' + this_id + '" id="_variation_textsize1_' + this_id + '" value="' + $("#text1_value_"+this_id).val() + '" /> <?php _e('characters'); ?>';
			new_contents += '</div>';
			new_contents += '<div class="foxyshop_field_control">';
			new_contents += '<label for="_variation_textsize2_' + this_id + '"><?php _e('Maximum Chars'); ?></label>';
			new_contents += '<input type="text" name="_variation_textsize2_' + this_id + '" id="_variation_textsize2_' + this_id + '" value="' + $("#text2_value_"+this_id).val() + '" /> <?php _e('characters'); ?>';
			new_contents += '</div>';
			new_contents += '<div style="clear: both;"></div>';
			new_contents += '</div>';

		//Textarea
		} else if (new_type == "textarea") {
			new_contents = '<div class="foxyshop_field_control textarea variationoptions">';
			new_contents += '<label for="_variation_textareasize_' + this_id + '"><?php _e('Lines of Text'); ?></label>';
			new_contents += '<input type="text" name="_variation_textareasize_' + this_id + '" id="_variation_textareasize_' + this_id + '" value="' + $("#textarea_value_"+this_id).val() + '" /> (default is 3)';
			new_contents += '</div>';


		//Description
		} else if (new_type == "descriptionfield") {
			new_contents = '<div class="foxyshop_field_control descriptionfield variationoptions">';
			new_contents += '<label for="_variation_description_' + this_id + '"><?php _e('Descriptive Text'); ?></label>';
			new_contents += '<textarea name="_variation_description_' + this_id + '" id="_variation_description_' + this_id + '">' + $("#descriptionfield_value_"+this_id).val() + '</textarea>';
			new_contents += '</div>';

		//Checkbox
		} else if (new_type == "checkbox") {
			new_contents = '<div class="foxyshop_field_control checkbox variationoptions">';
			new_contents += '<label for="_variation_description_' + this_id + '"><?php _e('Value'); ?></label>';
			new_contents += '<input type="text" name="_variation_checkbox_' + this_id + '" id="_variation_checkbox_' + this_id + '" value="' + $("#checkbox_value_"+this_id).val() + '" class="variation_checkbox_text" />';
			new_contents += variationkeyhtml;
			new_contents += '</div>';

		//Custom File Upload
		} else if (new_type == "upload") {
			new_contents = '<div class="foxyshop_field_control upload variationoptions">';
			new_contents += '<label for="_variation_uploadinstructions_' + this_id + '"><?php _e('Instructions'); ?></label>';
			new_contents += '<textarea name="_variation_uploadinstructions_' + this_id + '" id="_variation_uploadinstructions_' + this_id + '">' + $("#upload_value_"+this_id).val() + '</textarea>';
			new_contents += '</div>';
		}
		
		return new_contents;
	}


	
	<?php if (version_compare($wp_version, '3.1', '>=')) { ?>
	$("#_salestartdate, #_saleenddate").datepicker({ dateFormat: 'm/d/yy' });
	<?php } ?>


	
	$("#_weight1").blur(function() {
		var weight = $(this).val();
		if (weight.indexOf(".") >= 0) {
			secondstring = parseFloat(weight.substr(weight.indexOf("."))) * 100;
			result = secondstring * <?php echo ($foxyshop_settings['weight_type'] == 'metric' ? 1000 : 16); ?>/100;
			result = result.toFixed(1)
			$("#_weight2").val(result);
			foxyshop_check_number_single(this);
		}
		foxyshop_check_number_single(this);
	});
	$("#_weight2").blur(function() {
		var weight = parseFloat($(this).val()).toFixed(1);
		if (weight == 'NaN') weight = "0.0";
		if (weight >= <?php echo ($foxyshop_settings['weight_type'] == 'metric' ? 1000 : 16); ?>) {
			$("#_weight1").val(parseFloat(jQuery("#_weight1").val())+1);
			$("#_weight2").val("0.0");
		} else {
			$(this).val(weight);
		}
	});
	
	$("#_saleprice").blur(function() {
		saleprice = foxyshop_format_number($(this).val());
		if (saleprice == "0.00") {
			$(this).val("");
		} else {
			$(this).val(saleprice);
		}
	});
	
	$("#_quantity_min, #_quantity_max").blur(function() {
		tempval = foxyshop_format_number_single($(this).val());
		if (tempval == "0") {
			$(this).val("");
		} else {
			$(this).val(tempval);
		}
		
	});

	$(".chzn-select").chosen();
});
function foxyshop_format_number_single(num) { num = num.toString().replace(/\$|\,/g,''); if(isNaN(num)) num = "0"; sign = (num == (num = Math.abs(num))); num = Math.floor(num*100+0.50000000001); cents = num%100; num = Math.floor(num/100).toString(); if(cents<10) cents = "0" + cents; for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++) num = num.substring(0,num.length-(4*i+3))+','+ num.substring(num.length-(4*i+3)); return (((sign)?'':'-') + num); }
function foxyshop_format_number(num) { num = num.toString().replace(/\$|\,/g,''); if(isNaN(num)) num = "0"; sign = (num == (num = Math.abs(num))); num = Math.floor(num*100+0.50000000001); cents = num%100; num = Math.floor(num/100).toString(); if(cents<10) cents = "0" + cents; for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++) num = num.substring(0,num.length-(4*i+3))+','+ num.substring(num.length-(4*i+3)); return (((sign)?'':'-') + num + '.' + cents); }
function foxyshop_check_number_single(el) { el.value = foxyshop_format_number_single(el.value); }
function foxyshop_check_number(el) { el.value = foxyshop_format_number(el.value); }

</script>



<?php
}





//-------------------------------------------
//Save All Product Info
//-------------------------------------------
function foxyshop_product_meta_save($post_id) {
	global $foxyshop_settings;
	if (!wp_verify_nonce((isset($_POST['products_meta_noncename']) ? $_POST['products_meta_noncename'] : ""),__FILE__)) return $post_id;
	if (!current_user_can('edit_'.($_POST['post_type'] == 'page' ? 'page' : 'post'), $post_id)) return $post_id;
	
	$_weight = (int)$_POST['_weight1'] . ' ' . (double)$_POST['_weight2'];
	if ($_weight == ' ') $_weight = $foxyshop_settings['default_weight']; //Set Default Weight

	$_quantity_min = (int)$_POST['_quantity_min'];
	$_quantity_max = (int)$_POST['_quantity_max'];
	if ($_quantity_min > $_quantity_max) $_quantity_min = "";
	if ($_quantity_min == 0) $_quantity_min = "";
	if ($_quantity_max == 0) $_quantity_max = "";

	//Save Product Detail Data
	foxyshop_save_meta_data('_weight',$_weight);
	foxyshop_save_meta_data('_price',number_format((double)str_replace(",","",$_POST['_price']),2,".",""));
	foxyshop_save_meta_data('_code',trim($_POST['_code']));
	if (isset($_POST['_category'])) foxyshop_save_meta_data('_category',$_POST['_category']);
	foxyshop_save_meta_data('_quantity_min',$_quantity_min);
	foxyshop_save_meta_data('_quantity_max',$_quantity_max);
	
	$hide_product = "";
	if (isset($_POST['_hide_product'])) $hide_product = $_POST['_hide_product'];
	foxyshop_save_meta_data('_hide_product',$hide_product);

	//Require SSO
	if ($foxyshop_settings['enable_sso'] && $foxyshop_settings['sso_account_required'] == 2) {
		foxyshop_save_meta_data('_require_sso',$_POST['_require_sso']);
	}

	//Save Sale Pricing Data
	foxyshop_save_meta_data('_saleprice',number_format((double)str_replace(",","",$_POST['_saleprice']),2,".",""));
	if (($_salestartdate = strtotime($_POST['_salestartdate'])) === false) foxyshop_save_meta_data('_salestartdate',"999999999999999999");
	else foxyshop_save_meta_data('_salestartdate',$_salestartdate);
	if (($_saleenddate = strtotime($_POST['_saleenddate'])) === false) foxyshop_save_meta_data('_saleenddate',"999999999999999999");
	else foxyshop_save_meta_data('_saleenddate',$_saleenddate);

	//Discounts
	foxyshop_save_meta_data('_discount_quantity_amount',$_POST['_discount_quantity_amount']);
	foxyshop_save_meta_data('_discount_quantity_percentage',$_POST['_discount_quantity_percentage']);
	foxyshop_save_meta_data('_discount_price_amount',$_POST['_discount_price_amount']);
	foxyshop_save_meta_data('_discount_price_percentage',$_POST['_discount_price_percentage']);

	//Subscriptions
	if (isset($_POST['_sub_frequency'])) {
		if ($_POST['_sub_frequency'] == "") {
			foxyshop_save_meta_data('_sub_frequency',"");
			foxyshop_save_meta_data('_sub_startdate',"");
			foxyshop_save_meta_data('_sub_enddate',"");
		} else {
			foxyshop_save_meta_data('_sub_frequency',$_POST['_sub_frequency']);
			foxyshop_save_meta_data('_sub_startdate',$_POST['_sub_startdate']);
			foxyshop_save_meta_data('_sub_enddate',$_POST['_sub_enddate']);
		}
	} 

	//Save Related Product Data
	if (isset($_POST['_related_products_list'])) {
		foxyshop_save_meta_data('_related_products',implode(",",$_POST['_related_products_list']));
	} else {
		foxyshop_save_meta_data('_related_products',"");
	}

	//Save Bundled Product Data
	if (isset($_POST['_bundled_products_list'])) {
		foxyshop_save_meta_data('_bundled_products',implode(",",$_POST['_bundled_products_list']));
	} else {
		foxyshop_save_meta_data('_bundled_products',"");
	}
	
	//Inventory Levels
	if ($foxyshop_settings['manage_inventory_levels']) {
		$inventory_array = array();
		for ($i=1; $i<=$_POST['max_inventory_count']; $i++) {
			if ($_POST['inventory_code_'.$i] && $_POST['inventory_count_'.$i] != '') {
				$alert_level = $_POST['inventory_alert_'.$i];
				if ($alert_level != '') $alert_level = (int)$alert_level;
				$inventory_array[stripslashes(str_replace("'","",$_POST['inventory_code_'.$i]))] = array("count" => (int)$_POST['inventory_count_'.$i], "alert" => $alert_level);
			}
		}
		if (count($inventory_array) > 0) {
			foxyshop_save_meta_data('_inventory_levels',serialize($inventory_array));
		} else {
			foxyshop_save_meta_data('_inventory_levels',"");
		}
	}
	
	//Save Product Variations
	$currentID = 1;
	$variations = array();
	for ($i=1;$i<=(int)$_POST['max_variations'];$i++) {
		
		//Get Target From Sort Numbers
		$target_id = 0;
		for ($k=1;$k<=(int)$_POST['max_variations'];$k++) {
			$tempid = (isset($_POST['sort'.$k]) ? $_POST['sort'.$k] : 0);
			if ($tempid == $i) $target_id = $k;
		}
		
		//Set Values, Skip if Not There or Empty Name
		if ($target_id == 0) continue;
		$_variationName = trim(str_replace(".","",str_replace('"','',$_POST['_variation_name_'.$target_id])));
		$_variationType = $_POST['_variation_type_'.$target_id];
		$_variationDisplayKey = $_POST['_variation_dkey_'.$target_id];
		$_variationRequired = (isset($_POST['_variation_required_'.$target_id]) ? $_POST['_variation_required_'.$target_id] : '');
		if ($_POST['_variation_name_'.$target_id] == "") continue;

		//Get Values
		if ($_variationType == 'text') {
			$_variationValue = $_POST['_variation_textsize1_'.$target_id]."|".$_POST['_variation_textsize2_'.$target_id];
		} elseif ($_variationType == 'textarea') {
			$_variationValue = (int)$_POST['_variation_textareasize_'.$target_id];
			if ($_variationValue == 0) $_variationValue = 3;
		} elseif ($_variationType == 'upload') {
			$_variationValue = $_POST['_variation_uploadinstructions_'.$target_id];
		} elseif ($_variationType == 'descriptionfield') {
			$_variationValue = $_POST['_variation_description_'.$target_id];
		} elseif ($_variationType == 'dropdown') {
			$_variationValue = $_POST['_variation_value_'.$target_id];
		} elseif ($_variationType == 'checkbox') {
			$_variationValue = $_POST['_variation_checkbox_'.$target_id];
		} elseif ($_variationType == 'radio') {
			$_variationValue = $_POST['_variation_radio_'.$target_id];
		}

		$variations[$currentID] = array(
			"name" => stripslashes($_variationName),
			"type" => stripslashes($_variationType),
			"value" => stripslashes($_variationValue),
			"displayKey" => stripslashes($_variationDisplayKey),
			"required" => stripslashes($_variationRequired)
		);
		$currentID++;
	}
	if (count($variations) > 0) {
		foxyshop_save_meta_data('_variations', serialize($variations));
	} else {
		foxyshop_save_meta_data('_variations', "");
	}
	
	//Rewrite Product Sitemap
	if ($foxyshop_settings['generate_product_sitemap']) {
		foxyshop_create_product_sitemap();
	}

	return $post_id;
}




//-------------------------------------------
//Custom Field Bulk Editor Actions
//-------------------------------------------
function foxyshop_cfbe_metabox($post_type) {
	if ($post_type != 'foxyshop_product') return;
	global $foxyshop_settings;
	global $wp_version;
	?>
	<table class="widefat cfbe_table cfbe_foxyshop_table">
		<thead>
			<tr>
				<th><img src="<?php echo FOXYSHOP_DIR . '/images/icon.png'; ?>" alt="" /><?php echo sprintf(__('Set Values For Checked FoxyShop %s'), FOXYSHOP_PRODUCT_NAME_PLURAL); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($foxyshop_settings['ship_categories']) : ?>
			<tr>
				<td>
					<label for="_category" class="cfbe_special_label"><?php _e('Shipping Category'); ?></label>
					<input type="radio" name="_category_status" id="_category_status0" value="0" checked="checked" />
					<label for="_category_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_category_status" id="_category_status1" value="1" />
					<label for="_category_status1"><?php _e("Change To"); ?>:</label>

					<select name="_category" id="_category" onfocus="jQuery('#_category_status1').prop('checked', true);">
						<option value=""><?php _e('Default'); ?></option>
						<?php
						$arrShipCategories = preg_split("/(\r\n|\n)/", $foxyshop_settings['ship_categories']);
						for ($i = 0; $i < count($arrShipCategories); $i++) {
							$shipping_category = explode("|", $arrShipCategories[$i]);
							if (count($shipping_category) > 1) {
								$shipping_category_code = trim($shipping_category[0]);
								$shipping_category_name = trim($shipping_category[1]);
							} else {
								$shipping_category_code = trim($shipping_category[0]);
								$shipping_category_name = trim($shipping_category[0]);
							}
							echo '<option value="' . esc_attr($shipping_category_code) . '"';
							if (esc_attr($shipping_category_code == $_category)) echo ' selected="selected"';
							echo '>' . esc_attr($shipping_category_name) . '</option>';
							echo "\n";
						}
						?>
					</select>

					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php else : ?>
				<input type="hidden" name="_category_status" value="0" />
			<?php endif; ?>
			<tr>
				<td>
					<label for="_saleprice" class="cfbe_special_label"><?php _e('Sale Price'); ?></label>
					<input type="radio" name="_saleprice_status" id="_saleprice_status0" value="0" checked="checked" />
					<label for="_saleprice_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_saleprice_status" id="_saleprice_status1" value="1" />
					<label for="_saleprice_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_saleprice" id="_saleprice" value="" class="cfbe_field_name" onfocus="jQuery('#_saleprice_status1').prop('checked', true);" />
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_salestartdate" class="cfbe_special_label"><?php _e('Sale Start Date'); ?></label>
					<input type="radio" name="_salestartdate_status" id="_salestartdate_status0" value="0" checked="checked" />
					<label for="_salestartdate_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_salestartdate_status" id="_salestartdate_status1" value="1" />
					<label for="_salestartdate_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_salestartdate" id="_salestartdate" value="" class="cfbe_field_name" onfocus="jQuery('#_salestartdate_status1').prop('checked', true);" />
					<small>mm/dd/yy</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_saleenddate" class="cfbe_special_label"><?php _e('Sale End Date'); ?></label>
					<input type="radio" name="_saleenddate_status" id="_saleenddate_status0" value="0" checked="checked" />
					<label for="_saleenddate_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_saleenddate_status" id="_saleenddate_status1" value="1" />
					<label for="_saleenddate_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_saleenddate" id="_saleenddate" value="" class="cfbe_field_name" onfocus="jQuery('#_saleenddate_status1').prop('checked', true);" />
					<small>mm/dd/yy</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_price" class="cfbe_special_label"><?php _e('Base Price'); ?></label>
					<input type="radio" name="_price_status" id="_price_status0" value="0" checked="checked" />
					<label for="_price_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_price_status" id="_price_status1" value="1" />
					<label for="_price_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_price" id="_price" value="" class="cfbe_field_name" onfocus="jQuery('#_price_status1').prop('checked', true);" />
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_weight" class="cfbe_special_label"><?php _e('Weight'); ?></label>
					<input type="radio" name="_weight_status" id="_weight_status0" value="0" checked="checked" />
					<label for="_weight_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_weight_status" id="_weight_status1" value="1" />
					<label for="_weight_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_weight" id="_weight" value="" class="cfbe_field_name" onfocus="jQuery('#_weight_status1').prop('checked', true);" />
					<small>enter 5 lbs, 2 oz as "5 2"</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			
			
			<tr>
				<td>
					<label for="_quantity_min" class="cfbe_special_label"><?php _e('Minimum Qty'); ?></label>
					<input type="radio" name="_quantity_min_status" id="_quantity_min_status0" value="0" checked="checked" />
					<label for="_quantity_min_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_quantity_min_status" id="_quantity_min_status1" value="1" />
					<label for="_quantity_min_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_quantity_min" id="_quantity_min" value="" class="cfbe_field_name" onfocus="jQuery('#_quantity_min_status1').prop('checked', true);" style="width: 46px;" />
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_quantity_max" class="cfbe_special_label"><?php _e('Maximum Qty'); ?></label>
					<input type="radio" name="_quantity_max_status" id="_quantity_max_status0" value="0" checked="checked" />
					<label for="_quantity_max_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_quantity_max_status" id="_quantity_max_status1" value="1" />
					<label for="_quantity_max_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_quantity_max" id="_quantity_max" value="" class="cfbe_field_name" onfocus="jQuery('#_quantity_max_status1').prop('checked', true);" style="width: 46px;" />
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_hide_product" class="cfbe_special_label"><?php _e('Hide From List View'); ?></label>
					<input type="radio" name="_hide_product_status" id="_hide_product_status0" value="0" checked="checked" />
					<label for="_hide_product_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_hide_product_status" id="_hide_product_status1" value="1" />
					<label for="_hide_product_status1" class="cfbe_leave_unchanged"><?php _e("Hide"); echo " ".FOXYSHOP_PRODUCT_NAME_SINGULAR; ?>:</label>
					<input type="radio" name="_hide_product_status" id="_hide_product_status2" value="2" style="margin-bottom: 11px;" />
					<label for="_hide_product_status2"><?php _e("Show"); echo " ".FOXYSHOP_PRODUCT_NAME_SINGULAR; ?>:</label>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_discount_quantity_amount" class="cfbe_special_label"><?php _e('Discount Qty $'); ?></label>
					<input type="radio" name="_discount_quantity_amount_status" id="_discount_quantity_amount_status0" value="0" checked="checked" />
					<label for="_discount_quantity_amount_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_discount_quantity_amount_status" id="_discount_quantity_amount_status1" value="1" />
					<label for="_discount_quantity_amount_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_discount_quantity_amount" id="_discount_quantity_amount" value="" class="cfbe_field_name" onfocus="jQuery('#_discount_quantity_amount_status1').prop('checked', true);" style="width: 300px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/coupons_and_discounts" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_discount_quantity_percentage" class="cfbe_special_label"><?php _e('Discount Qty %'); ?></label>
					<input type="radio" name="_discount_quantity_percentage_status" id="_discount_quantity_percentage_status0" value="0" checked="checked" />
					<label for="_discount_quantity_percentage_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_discount_quantity_percentage_status" id="_discount_quantity_percentage_status1" value="1" />
					<label for="_discount_quantity_percentage_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_discount_quantity_percentage" id="_discount_quantity_percentage" value="" class="cfbe_field_name" onfocus="jQuery('#_discount_quantity_percentage_status1').prop('checked', true);" style="width: 300px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/coupons_and_discounts" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_discount_price_amount" class="cfbe_special_label"><?php _e('Discount Price $'); ?></label>
					<input type="radio" name="_discount_price_amount_status" id="_discount_price_amount_status0" value="0" checked="checked" />
					<label for="_discount_price_amount_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_discount_price_amount_status" id="_discount_price_amount_status1" value="1" />
					<label for="_discount_price_amount_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_discount_price_amount" id="_discount_price_amount" value="" class="cfbe_field_name" onfocus="jQuery('#_discount_price_amount_status1').prop('checked', true);" style="width: 300px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/coupons_and_discounts" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_discount_price_percentage" class="cfbe_special_label"><?php _e('Discount Price %'); ?></label>
					<input type="radio" name="_discount_price_percentage_status" id="_discount_price_percentage_status0" value="0" checked="checked" />
					<label for="_discount_price_percentage_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_discount_price_percentage_status" id="_discount_price_percentage_status1" value="1" />
					<label for="_discount_price_percentage_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_discount_price_percentage" id="_discount_price_percentage" value="" class="cfbe_field_name" onfocus="jQuery('#_discount_price_percentage_status1').prop('checked', true);" style="width: 300px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/coupons_and_discounts" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php if ($foxyshop_settings['enable_subscriptions']) : ?>
			<tr>
				<td>
					<label for="_sub_frequency" class="cfbe_special_label"><?php _e('Sub. Frequency'); ?></label>
					<input type="radio" name="_sub_frequency_status" id="_sub_frequency_status0" value="0" checked="checked" />
					<label for="_sub_frequency_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_sub_frequency_status" id="_sub_frequency_status1" value="1" />
					<label for="_sub_frequency_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_sub_frequency" id="_sub_frequency" value="" class="cfbe_field_name" onfocus="jQuery('#_sub_frequency_status1').prop('checked', true);" style="width: 46px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/cheat_sheet#subscription_product_options" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_sub_startdate" class="cfbe_special_label"><?php _e('Sub. Start Date'); ?></label>
					<input type="radio" name="_sub_startdate_status" id="_sub_startdate_status0" value="0" checked="checked" />
					<label for="_sub_startdate_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_sub_startdate_status" id="_sub_startdate_status1" value="1" />
					<label for="_sub_startdate_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_sub_startdate" id="_sub_startdate" value="" class="cfbe_field_name" onfocus="jQuery('#_sub_startdate_status1').prop('checked', true);" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/cheat_sheet#subscription_product_options" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_sub_enddate" class="cfbe_special_label"><?php _e('Sub. End Date'); ?></label>
					<input type="radio" name="_sub_enddate_status" id="_sub_enddate_status0" value="0" checked="checked" />
					<label for="_sub_enddate_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_sub_enddate_status" id="_sub_enddate_status1" value="1" />
					<label for="_sub_enddate_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_sub_enddate" id="_sub_enddate" value="" class="cfbe_field_name" onfocus="jQuery('#_sub_enddate_status1').prop('checked', true);" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/cheat_sheet#subscription_product_options" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php else : ?>
				<input type="hidden" name="_sub_frequency_status" value="0" />
				<input type="hidden" name="_sub_startdate_status" value="0" />
				<input type="hidden" name="_sub_enddate_status" value="0" />
			<?php endif; ?>
			<tr>
				<td>
					<?php
					$all_product_list = "";
					$args = array('post_type' => 'foxyshop_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC');
					$all_products = get_posts($args);
					foreach ($all_products as $product) {
						$all_product_list .= '<option value="' . $product->ID . '">' . $product->post_title . '</option>'."\n";
					}?>

					<label for="_related_products_list" class="cfbe_special_label"><?php _e('Related Products'); ?></label>
					<input type="radio" name="_related_products_status" id="_related_products_status0" value="0" checked="checked" />
					<label for="_related_products_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_related_products_status" id="_related_products_status1" value="1" />
					<label for="_related_products_status1"><?php _e("Change To"); ?>:</label>
					<select name="_related_products_list[]" id="_related_products_list" data-placeholder="Search for <?php echo FOXYSHOP_PRODUCT_NAME_PLURAL; ?>" style="width: 100%;" class="chzn-select" multiple="multiple">
						<?php echo $all_product_list; ?>
					</select>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php if ($foxyshop_settings['enable_bundled_products']) : ?>
			<tr>
				<td>
					<label for="_bundled_products_list" class="cfbe_special_label"><?php _e('Bundled Products'); ?></label>
					<input type="radio" name="_bundled_products_status" id="_bundled_products_status0" value="0" checked="checked" />
					<label for="_bundled_products_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_bundled_products_status" id="_bundled_products_status1" value="1" />
					<label for="_bundled_products_status1"><?php _e("Change To"); ?>:</label>
					<select name="_bundled_products_list[]" id="_bundled_products_list" data-placeholder="Search for <?php echo FOXYSHOP_PRODUCT_NAME_PLURAL; ?>" style="width: 100%;" class="chzn-select" multiple="multiple">
						<?php echo $all_product_list; ?>
					</select>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php else : ?>
				<input type="hidden" name="_bundled_products_status" value="0" />
			<?php endif; ?>
		</tbody>
	</table>



<script type="text/javascript">
jQuery(document).ready(function($){

	<?php if (version_compare($wp_version, '3.1', '>=')) { ?>
	$("#_salestartdate, #_saleenddate").datepicker({ dateFormat: 'm/d/yy' });
	<?php } ?>
	$(".chzn-select").chosen();
	$(".chzn-container").css("width", "400px");
	$(".chzn-drop").css("width", "399px");
});
</script>

<?php
}
add_action('cfbe_before_metabox', 'foxyshop_cfbe_metabox');

function foxyshop_cfbe_save($post_type, $post_id) {
	if ($post_type != "foxyshop_product") return;
	
	if ($_POST['_category_status'] == 1) {
		cfbe_save_meta_data('_category', $_POST['_category']);
	}
	if ($_POST['_quantity_min_status'] == 1) {
		cfbe_save_meta_data('_quantity_min', (int)$_POST['_quantity_min']);
	}
	if ($_POST['_quantity_max_status'] == 1) {
		cfbe_save_meta_data('_quantity_max', (int)$_POST['_quantity_max']);
	}
	if ($_POST['_price_status'] == 1) {
		$new_price = number_format((double)str_replace("$","",str_replace(",","",$_POST['_price'])),2,".","");
		cfbe_save_meta_data('_price', $new_price);
	}
	if ($_POST['_weight_status'] == 1) {
		cfbe_save_meta_data('_weight', $_POST['_weight']);
	}
	if ($_POST['_saleprice_status'] == 1) {
		$new_price = number_format((double)str_replace("$","",str_replace(",","",$_POST['_saleprice'])),2,".","");
		cfbe_save_meta_data('_saleprice', $new_price);
	}
	if ($_POST['_discount_quantity_amount_status'] == 1) {
		cfbe_save_meta_data('_discount_quantity_amount', $_POST['_discount_quantity_amount']);
	}
	if ($_POST['_discount_quantity_percentage_status'] == 1) {
		cfbe_save_meta_data('_discount_quantity_percentage', $_POST['_discount_quantity_percentage']);
	}
	if ($_POST['_discount_price_amount_status'] == 1) {
		cfbe_save_meta_data('_discount_price_amount', $_POST['_discount_price_amount']);
	}
	if ($_POST['_discount_price_percentage_status'] == 1) {
		cfbe_save_meta_data('_discount_price_percentage', $_POST['_discount_price_percentage']);
	}
	if ($_POST['_sub_frequency_status'] == 1) {
		cfbe_save_meta_data('_sub_frequency', $_POST['_sub_frequency']);
	}
	if ($_POST['_sub_startdate_status'] == 1) {
		cfbe_save_meta_data('_sub_startdate', $_POST['_sub_startdate']);
	}
	if ($_POST['_sub_enddate_status'] == 1) {
		cfbe_save_meta_data('_sub_enddate', $_POST['_sub_enddate']);
	}
	if ($_POST['_salestartdate_status'] == 1) {
		if (($_salestartdate = strtotime($_POST['_salestartdate'])) === false) cfbe_save_meta_data('_salestartdate',"999999999999999999");
		else cfbe_save_meta_data('_salestartdate', $_salestartdate);
	}
	if ($_POST['_saleenddate_status'] == 1) {
		if (($_saleenddate = strtotime($_POST['_saleenddate'])) === false) cfbe_save_meta_data('_saleenddate',"999999999999999999");
		else cfbe_save_meta_data('_saleenddate', $_saleenddate);
	}
	if ($_POST['_hide_product_status'] != 0) {
		cfbe_save_meta_data('_hide_product', $_POST['_hide_product_status'] == 1 ? "on" : "");
	}
	if ($_POST['_related_products_status'] == 1) {
		if (isset($_POST['_related_products_list'])) {
			cfbe_save_meta_data('_related_products',implode(",",$_POST['_related_products_list']));
		} else {
			cfbe_save_meta_data('_related_products',"");
		}
	}
	if ($_POST['_bundled_products_status'] == 1) {
		if (isset($_POST['_bundled_products_list'])) {
			cfbe_save_meta_data('_bundled_products',implode(",",$_POST['_bundled_products_list']));
		} else {
			cfbe_save_meta_data('_bundled_products',"");
		}
	}
}
add_action('cfbe_save_fields', 'foxyshop_cfbe_save', 10, 2);




//-------------------------------------------
//Extra Functions
//-------------------------------------------
function foxyshop_save_meta_data($fieldname,$input) {
	global $post_id;
	$current_data = get_post_meta($post_id, $fieldname, TRUE);	
 	$new_data = $input;
 	if (!$new_data) $new_data = NULL;
 	foxyshop_meta_clean($new_data);
	if ($current_data) {
		if (is_null($new_data)) delete_post_meta($post_id,$fieldname);
		else update_post_meta($post_id,$fieldname,$new_data);
	} elseif (!is_null($new_data)) {
		add_post_meta($post_id,$fieldname,$new_data);
	}
}

function foxyshop_meta_clean(&$arr) {
	if (is_array($arr)) {
		foreach ($arr as $i => $v) {
			if (is_array($arr[$i]))  {
				foxyshop_meta_clean($arr[$i]);
				if (!count($arr[$i])) unset($arr[$i]);
			} else  {
				if (trim($arr[$i]) == '') unset($arr[$i]);
			}
		}
		if (!count($arr)) $arr = NULL;
	}
}

?>