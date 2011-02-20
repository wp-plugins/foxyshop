<?php
//Post Type
add_action('init', 'create_post_type');
function create_post_type() {
	$labels = array(
		'name' => _x('Products', 'post type general name'),
		'singular_name' => _x('Product', 'post type singular name'),
		'add_new' => _x('Add New', 'product'),
		'add_new_item' => __('Add New Product'),
		'edit_item' => __('Edit Product'),
		'new_item' => __('New Product'),
		'view_item' => __('View Product'),
		'search_items' => __('Search Products'),
		'not_found' =>  __('No products found'),
		'not_found_in_trash' => __('No products found in Trash'), 
		'parent_item_colon' => ''
	);
	register_post_type('foxyshop_product', array(
		'labels' => $labels,
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'page',
		'hierarchical' => false,
		'supports' => array('title','editor','thumbnail', 'custom-fields', 'excerpt'),
		'menu_icon' => FOXYSHOP_DIR . '/images/icon.png',
		'rewrite' => array("slug" => "products")
	));
}

//Setup Thumbnail Support
add_theme_support('post-thumbnails', array('post', 'foxyshop_product'));



//Custom Columns
add_filter('manage_edit-foxyshop_product_columns', 'add_new_foxyshop_product_columns');
function add_new_foxyshop_product_columns($cols) {
	$new_columns['cb'] = '<input type="checkbox" />';
	$new_columns['id'] = __('ID');
	$new_columns['title'] = _x('Product Title', 'column name');
	$new_columns['productcode'] = __('Code');
	$new_columns['price'] = __('Price');
	$new_columns['productcategory'] = __('Product Category');
	return $new_columns;
}

//Rewrite Columns
add_action('manage_posts_custom_column', 'manage_custom_columns', 10, 2);
function manage_custom_columns($column_name, $id) {
	global $wpdb;
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
			$beginningOK = (strtotime(now) > $salestartdate);
			$endingOK = (strtotime(now) < ($saleenddate + 86400) || $$saleenddate == 0);
			if ($beginningOK && $endingOK || ($salestartdate == 0 && $saleenddate == 0)) {
				echo '<span style="text-decoration: line-through; margin-right: 10px;">$' . number_format($originalprice,2) . '</span><span style="color: red;">$' . number_format($saleprice,2) . '</span>';
			} else {
				echo '$'.number_format($originalprice,2);
			}
		} else {
			echo '$'.number_format($originalprice,2);
		}
		break;
	default:
	}
}


//Add Filter Box to Top of Product List
add_action( 'restrict_manage_posts', 'my_restrict_manage_posts' );
function my_restrict_manage_posts() {

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
            echo "<option value=''>Show All $tax_name</option>";
            foreach ($terms as $term) {
                // output each select option line, check against the last $_GET to show the current option selected
                echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
            }
            echo "</select>";
        }
    }
}





//Custom Taxonomy: Product Categories
function productcategory_init() {
	$labels = array(
		'name' => __('Product Categories'),
		'singular_name' => __('Product Category'),
		'parent_item' => __('Parent Category'),
		'all_items' => __('All Product Categories'),
		'edit_item' => __('Edit Product Category'),
		'update_item' => __('Update Product Category'),
		'add_new_item' => __('Add New Product Category'),
		'new_item_name' => __('New Product Category Name'),
		'menu_name' => __('Product Categories')
	);
	register_taxonomy('foxyshop_categories','foxyshop_product', array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'product-cat', 'hierarchical' => true)
	));
}
add_action('init', 'productcategory_init');




//Meta Box for Product Info
add_action('admin_init','product_meta_init');
function product_meta_init() {
	add_meta_box('product_details_meta', 'Product Details', 'product_details_setup', 'foxyshop_product', 'side', 'high');
	add_meta_box('product_pricing_meta', 'Pricing Details', 'product_pricing_setup', 'foxyshop_product', 'side', 'low');
	add_meta_box('product_secondary_meta', 'Secondary Product Features', 'product_secondary_setup', 'foxyshop_product', 'normal', 'low');
	add_meta_box('product_variations_meta', 'Product Variations', 'product_variations_setup', 'foxyshop_product', 'normal', 'high');
	add_action('save_post','product_meta_save');
}

//Main Product Details
function product_details_setup() {
	global $post, $foxyshop_settings;
	//$_sort = (int)get_post_meta($post->ID,'_sort',TRUE);
	$_sort = (int)$post->menu_order;
	if ($_sort == 0) $_sort = 3;
	$_price = get_post_meta($post->ID,'_price',TRUE);
	$_code = get_post_meta($post->ID,'_code',TRUE);
	$_category = get_post_meta($post->ID,'_category',TRUE);
	$_quantity_min = get_post_meta($post->ID,'_quantity_min',TRUE);
	$_quantity_max = get_post_meta($post->ID,'_quantity_max',TRUE);
	$_weight = (get_post_meta($post->ID,'_weight',TRUE) ? explode(" ", get_post_meta($post->ID,'_weight',TRUE)) : "0 0");
	if ((int)$_weight[0] == 0 && (int)$_weight[1] == 0) $_weight[0] = $foxyshop_settings['default_weight'];
	$_hide_product = get_post_meta($post->ID,'_hide_product',TRUE);
	?>
	<div class="my_meta_control">
		<label>Base Price</label>
		<input type="text" name="_price" value="<?php echo $_price; ?>" onblur="foxyshop_check_number(this);" />
	</div>
	<div class="my_meta_control">
		<label>Item Code</label>
		<input type="text" name="_code" value="<?php echo $_code; ?>" />
	</div>
	<div class="my_meta_control">
		<label>Weight</label>
		<input type="text" name="_weight1" id="_weight1" value="<?php echo (int)$_weight[0]; ?>" style="width: 40px; float: left;" onblur="foxyshop_check_number_single(this);" />
		<span style="float: left; margin: 9px 0 0 5px; width: 34px;"><?php echo ($foxyshop_settings['weight_type'] == "metric" ? 'kg' : 'lbs'); ?></span>
		<input type="text" name="_weight2" value="<?php echo (int)$_weight[1]; ?>" style="width: 40px; float: left;" onblur="check_oz(this);" />
		<span style="float: left; margin: 9px 0 0 5px;"><?php echo ($foxyshop_settings['weight_type'] == "metric" ? 'gm' : 'oz'); ?></span>
	</div>
	<div class="my_meta_control">
		<label>Qty Settings</label>
		<input type="text" name="_quantity_min" id="_weight1" value="<?php echo $_quantity_min; ?>" style="width: 40px; float: left;" onblur="foxyshop_check_number_single(this);" />
		<span style="float: left; margin: 9px 0 0 5px; width: 34px;">min</span>
		<input type="text" name="_quantity_max" value="<?php echo $_quantity_max; ?>" style="width: 40px; float: left;" onblur="foxyshop_check_number_single(this);" />
		<span style="float: left; margin: 9px 0 0 5px;">max</span>
	</div>
	<div class="my_meta_control">
		<label>Sorting</label>
		<select name="menu_order">
			<?php
			$arrSorting = array(1=>'Featured','Elevated','Normal','Lower','Lowest');
			for ($i = 1; $i < count($arrSorting)+1; $i++) {
				echo '<option value="' . $i . '"';
				if ($i == $_sort) echo ' selected="selected"';
				echo '>' . $arrSorting[$i] . '</option>';
				echo "\n";
			}
			?>
		</select>
	</div>
	<?php if ($foxyshop_settings['ship_categories']) { ?>
	<div class="my_meta_control">
		<label>Shipping Cat.</label>
		<select name="_category">
			<option value="">- - Default - -</option>
			<?php
			$arrShipCategories = preg_split("/(\r\n|\n)/", $foxyshop_settings['ship_categories']);
			for ($i = 0; $i < count($arrShipCategories); $i++) {
				echo '<option value="' . esc_attr($arrShipCategories[$i]) . '"';
				if (esc_attr($arrShipCategories[$i] == $_category)) echo ' selected="selected"';
				echo '>' . esc_attr($arrShipCategories[$i]) . '</option>';
				echo "\n";
			}
			?>
		</select>
	</div>
	<?php } ?>
	<div class="my_meta_control">
		<input type="checkbox" name="_hide_product" id="_hide_product" style="float: left; margin: 5px 0 0 10px;"<?php echo checked($_hide_product,"on"); ?> />
		<label style="width: 210px;" for="_hide_product">Hide This Product From List View</label>
	</div>
	<div style="clear:both"></div>
	<?php
	echo '<input type="hidden" name="products_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
}

//Secondary Product Details
function product_pricing_setup() {
	global $post, $foxyshop_settings;
	$_saleprice = get_post_meta($post->ID,'_saleprice',TRUE);
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
	<h4>Sale</h4>
	<div class="my_meta_control">
		<label>Sale Price</label>
		<input type="text" name="_saleprice" value="<?php echo $_saleprice; ?>" onblur="foxyshop_check_number(this);" />
	</div>
	<div class="my_meta_control">
		<label>Start Date</label>
		<input type="text" name="_salestartdate" value="<?php echo $_salestartdate; ?>" />
	</div>
	<div class="my_meta_control">
		<label>End Date</label>
		<input type="text" name="_saleenddate" value="<?php echo $_saleenddate; ?>" />
	</div>
	

	<h4>Discounts <a href="http://wiki.foxycart.com/v/0.6.0/getting_started/adding_links_and_forms#discounts" target="_blank">(reference)</a></h4>
	<div class="my_meta_control discount_fields">
		<label>Quantity $</label>
		<input type="text" name="_discount_quantity_amount" value="<?php echo $_discount_quantity_amount; ?>" />
		<div style="clear:both;"></div>
	</div>
	<div class="my_meta_control discount_fields">
		<label>Quantity %</label>
		<input type="text" name="_discount_quantity_percentage" value="<?php echo $_discount_quantity_percentage; ?>" />
		<div style="clear:both;"></div>
	</div>
	<div class="my_meta_control discount_fields">
		<label>Price $</label>
		<input type="text" name="_discount_price_amount" value="<?php echo $_discount_price_amount; ?>" />
		<div style="clear:both;"></div>
	</div>
	<div class="my_meta_control discount_fields">
		<label>Price %</label>
		<input type="text" name="_discount_price_percentage" value="<?php echo $_discount_price_percentage; ?>" />
		<div style="clear:both;"></div>
	</div>
	<div style="clear:both;"></div>

	<?php if ($foxyshop_settings['enable_subscriptions']) { ?>
	<h4><a href="#" id="showsublink">Subscription Attributes</a> <a href="http://wiki.foxycart.com/v/0.6.0/getting_started/adding_links_and_forms#subscription_attributes" target="_blank">(reference)</a></h4>
	<div id="foxyshop_subscription_attributes" style="display: none;">
		<div class="my_meta_control">
			<label>Frequency</label>
			<input type="text" name="_sub_frequency" value="<?php echo $_sub_frequency; ?>" />
		</div>
		<div class="my_meta_control">
			<label>Start Date</label>
			<input type="text" name="_sub_startdate" value="<?php echo $_sub_startdate; ?>" />
		</div>
		<div class="my_meta_control">
			<label>End Date</label>
			<input type="text" name="_sub_enddate" value="<?php echo $_sub_enddate; ?>" />
		</div>
	</div>
	<?php
	}
}


//Secondary Product Features
function product_secondary_setup() {
	global $post, $foxyshop_settings;
	$_related_products = get_post_meta($post->ID,'_related_products',TRUE);
	$arr_related_products = explode(",",$_related_products);
	$related_product_list = "";
	
	$_bundled_products = get_post_meta($post->ID,'_bundled_products',TRUE);
	$arr_bundled_products = explode(",",$_bundled_products);
	$bundled_product_list = "";

	$productList = "";
	$args = array('post_type' => 'foxyshop_product', "post__not_in" => array($post->ID), 'numberposts' => -1, 'orderby' => 'title');
	$all_products = get_posts($args);
	foreach ($all_products as $product) {
		$productName = $product->post_title;
		$productList .= '<option value="' . $product->ID . '">' . $productName . '</option>'."\n";
		
		foreach ($arr_related_products as $relatedprod) {
			if ($relatedprod == $product->ID) $related_product_list .= '<span id="related_' . $product->ID . '"><a href="#" class="remove_related_product" rel="' . $product->ID . '">Delete</a>&nbsp;' . $product->post_title . '</span>'."\n";
		}

		foreach ($arr_bundled_products as $bundledprod) {
			if ($bundledprod == $product->ID) $bundled_product_list .= '<span id="bundled_' . $product->ID . '"><a href="#" class="remove_bundled_product" rel="' . $product->ID . '">Delete</a>&nbsp;' . $product->post_title . '</span>'."\n";
		}
	}
	?>
	<div class="my_meta_control" style="float: left; width: 48%;">
		<input type="hidden" name="_related_products" id="_related_products" value=",<?php echo $_related_products; ?>," />
		<div style="padding: 4px;"><strong>Related Products</strong></div>
		<select name="_related_products_list" id="_related_products_list" style="width: 210px;">
			<option value="">- - Select Products Below - -</option>
			<?php echo $productList; ?>
		</select>
		<a href="#" class="button" id="add_related_product">Add</a>
		<div id="related_product_listing" class="tagchecklist"><?php echo $related_product_list; ?></div>
	</div>
	<?php if ($foxyshop_settings['enable_bundled_products']) { ?>
	<div class="my_meta_control" style="float: right; width: 48%;">
		<input type="hidden" name="_bundled_products" id="_bundled_products" value=",<?php echo $_bundled_products; ?>," />
		<div style="padding: 4px;"><strong>Bundled Products</strong></div>
		<select name="_bundled_products_list" id="_bundled_products_list" style="width: 210px;">
			<option value="">- - Select Products Below - -</option>
			<?php echo $productList; ?>
		</select>
		<a href="#" class="button" id="add_bundled_product">Add</a>
		<div id="bundled_product_listing" class="tagchecklist"><?php echo $bundled_product_list; ?></div>
	</div>
	<?php } ?>
	<div style="clear: both;"></div>

<script type="text/javascript">
jQuery(document).ready(function($){
	$("#add_related_product").click(function() {
		thisID = $("#_related_products_list option:selected").attr("value");
		thisName = $("#_related_products_list option:selected").attr("text");
		$("#related_product_listing").append('<span id="related_' + thisID + '"><a href="#" class="remove_related_product" rel="' + thisID + '">Delete</a>&nbsp;' + thisName + '</span>');
		$("#_related_products").val($("#_related_products").val() + ',' + thisID + ',');
		return false;
	});
	$(".remove_related_product").live("click", function() {
		thisID = $(this).attr("rel");
		currentValues = $("#_related_products").val();
		$("#related_"+thisID).remove();
		$("#_related_products").val(currentValues.replace(','+thisID+',',','));
		return false;
	});
	$("#add_bundled_product").click(function() {
		thisID = $("#_bundled_products_list option:selected").attr("value");
		thisName = $("#_bundled_products_list option:selected").attr("text");
		$("#bundled_product_listing").append('<span id="bundled_' + thisID + '"><a href="#" class="remove_bundled_product" rel="' + thisID + '">Delete</a>&nbsp;' + thisName + '</span>');
		$("#_bundled_products").val($("#_bundled_products").val() + ',' + thisID + ',');
		return false;
	});
	$(".remove_bundled_product").live("click", function() {
		thisID = $(this).attr("rel");
		currentValues = $("#_bundled_products").val();
		$("#bundled_"+thisID).remove();
		$("#_bundled_products").val(currentValues.replace(','+thisID+',',','));
		return false;
	});
});
</script>

	<?php
}



//Product Variations
function product_variations_setup() {
	global $post, $foxyshop_settings;
	
	$showNew = 0;
	
	for ($i=1;$i<=$foxyshop_settings['max_variations'];$i++) {
		$_variationName = get_post_meta($post->ID,'_variation_name_'.$i,TRUE);
		$_variationType = get_post_meta($post->ID,'_variation_type_'.$i,TRUE);
		$_variationValue = get_post_meta($post->ID,'_variation_value_'.$i,TRUE);
		$_variationDisplayKey = get_post_meta($post->ID,'_variation_dkey_'.$i,TRUE);
		$variationTextSize = "";
		if ($_variationType == "text") {
			$arrVariationTextSize = explode("|",esc_attr($_variationValue));
			$_variationValue = "";
		} elseif ($_variationType == "textarea") {
			$_variationTextSize = esc_attr($_variationValue);
			$_variationValue = "";
		}
		if ($_variationName != '') $showNew = $i;
		
		?>
		<div class="product_variation" rel="<?php echo $i; ?>" id="variation<?php echo $i; ?>" <?php if (!$_variationName) echo ' style="display: none;"'; ?>>
			<div class="my_meta_control">
				<label>Variation Name</label>
				<input type="text" name="_variation_name_<?php echo $i; ?>" id="_variation_name_<?php echo $i; ?>" value="<?php echo esc_attr($_variationName); ?>" style="float: left; width: 200px;" />
				<label style="margin-left: 40px; width: auto; padding-bottom: 2px; cursor: help; border-bottom: 1px dotted darkgray;" title="Enter a value here if you want your variation to be invisible until called by another variation.">Display Key</label>
				<input type="text" name="_variation_dkey_<?php echo $i; ?>" id="_variation_dkey_<?php echo $i; ?>" value="<?php echo esc_attr($_variationDisplayKey); ?>" style="float: left; width: 100px;" />
				<a href="#" class="button deleteVariation" style="float: right;" rel="<?php echo $i; ?>">Delete</a>
			</div>
			<div class="my_meta_control variationtypes" style="clear: both;">
				<label>Variation Type</label>
				<div style="padding: 7px 0 7px 0;">
					<input type="radio" name="_variation_type_<?php echo $i; ?>" id="_variation_type_<?php echo $i; ?>_dropdown" value="dropdown" style="margin-top: -2px; float: left;"<?php if ($_variationType == 'dropdown' || $_variationType == '') echo ' checked="checked"' ?> /> <label for="_variation_type_<?php echo $i; ?>_dropdown">Dropdown</label>
					<input type="radio" name="_variation_type_<?php echo $i; ?>" id="_variation_type_<?php echo $i; ?>_text" value="text" style="margin-top: -2px; float: left;"<?php if ($_variationType == 'text') echo ' checked="checked"' ?> /> <label for="_variation_type_<?php echo $i; ?>_text">Single Line of Text</label>
					<input type="radio" name="_variation_type_<?php echo $i; ?>" id="_variation_type_<?php echo $i; ?>_textarea" value="textarea" style="margin-top: -2px; float: left;"<?php if ($_variationType == 'textarea') echo ' checked="checked"' ?> /> <label for="_variation_type_<?php echo $i; ?>_textarea">Multiple Lines of Text</label>
					<?php if ($foxyshop_settings['enable_custom_file_uploads']) { ?><input type="radio" name="_variation_type_<?php echo $i; ?>" id="_variation_type_<?php echo $i; ?>_upload" value="upload" style="margin-top: -2px; float: left;"<?php if ($_variationType == 'upload') echo ' checked="checked"' ?> /> <label for="_variation_type_<?php echo $i; ?>_upload">Custom File Upload</label><?php } ?>
				</div>
			</div>
			<div class="my_meta_control variations variationoptions">
				<label>Variations</label>
				<textarea name="_variation_value_<?php echo $i; ?>" style="width: 500px; height: 130px;"><?php echo $_variationValue; ?></textarea>
				<br />
				<div style="margin: 2px 0 0 114px; font-size: 10px;">Example: Variation Name{p+1.50|w-1|c:product_code|y:shipping_category|dkey:display_key}<br />
				Put a * in your variation name to indicate that the option will be selected by default.
				</div>
			</div>
			<div class="my_meta_control textboxsize variationoptions">
				<div class="my_meta_control">
					<label>Text Box Size</label>
					<input type="text" name="_variation_textsize1_<?php echo $i; ?>" value="<?php echo $arrVariationTextSize[0]; ?>" style="width: 45px;" /> characters
				</div>
				<div class="my_meta_control">
					<label>Maximum Chars</label>
					<input type="text" name="_variation_textsize2_<?php echo $i; ?>" value="<?php echo $arrVariationTextSize[1]; ?>" style="width: 45px;" /> characters
				</div>
				<div style="clear: both;"></div>
			</div>
			<div class="my_meta_control textareasize variationoptions">
				<label>Lines of Text</label>
				<input type="text" name="_variation_textareasize_<?php echo $i; ?>" value="<?php echo $_variationTextSize; ?>" style="width: 45px;" />
			</div>
			<div class="my_meta_control customupload variationoptions">
				<label style="width: 120px;">Special Instructions</label>
				<textarea name="_variation_uploadinstructions_<?php echo $i; ?>" style="width: 500px; height: 40px;"><?php echo $_variationValue; ?></textarea>
			</div>
		</div>
		<?php
	}
	
	?>
	<button id="AddVariation" class="button" style="display: none;">Add Another Variation</button>

<script type="text/javascript">
jQuery(document).ready(function($){
	var lastNewOne = <?php echo $showNew; ?>;
	$('.deleteVariation').click(function() {
		variationID = $(this).attr("rel");
		$("#_variation_name_" + variationID).val("");
		$("#variation" + variationID).slideUp();
		return false;
	});
	
	$('.product_variation').each(function() {
		thisID = $(this).attr("rel");
		$(this).find('.variationoptions').hide();
		if ($(this).find('input[type=radio]:checked').val() == 'text') {
			$(this).find('.textboxsize').show();
		} else if ($(this).find('input[type=radio]:checked').val() == 'textarea') {
			$(this).find('.textareasize').show();
		} else if ($(this).find('input[type=radio]:checked').val() == 'upload') {
			$(this).find('.customupload').show();
		} else if ($(this).find('input[type=radio]:checked').val() == 'dropdown') {
			$(this).find('.variations').show();
		}

	});
	
	$(".variationtypes input[type=radio]").change(function() {
		$(this).parents(".product_variation").find(".variationoptions").hide();
		if ($(this).val() == 'text') {
			$(this).parents(".product_variation").find(".textboxsize").show();
		} else if($(this).val() == "textarea") {
			$(this).parents(".product_variation").find(".textareasize").show();
		} else if($(this).val() == "upload") {
			$(this).parents(".product_variation").find(".customupload").show();
		} else if($(this).val() == "dropdown") {
			$(this).parents(".product_variation").find(".variations").show();
		}
	});
	
	if (lastNewOne <= <?php echo $foxyshop_settings['max_variations']; ?>) $("#AddVariation").show();
	<?php if ($showNew == 0) echo '$("#variation1").show(); lastNewOne=1;'; ?>
	<?php if ($showNew < $foxyshop_settings['max_variations']) { ?>
	$("#AddVariation").click(function() {
		lastNewOne++;
		$("#variation"+lastNewOne).slideDown().find('.variations').show();
		if (lastNewOne >= <?php echo $foxyshop_settings['max_variations']; ?>) $(this).hide();
		return false;
	});	
	<?php } ?>
	
	$('#showsublink').click(function() {
		$("#foxyshop_subscription_attributes").show();
		return false;
	});
	<?php if (get_post_meta($post->ID,'_sub_frequency',TRUE)) { ?>
	$("#foxyshop_subscription_attributes").show();
	<?php } ?>
	
});
function foxyshop_format_number_single(num) { num = num.toString().replace(/\$|\,/g,''); if(isNaN(num)) num = "0"; sign = (num == (num = Math.abs(num))); num = Math.floor(num*100+0.50000000001); cents = num%100; num = Math.floor(num/100).toString(); if(cents<10) cents = "0" + cents; for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++) num = num.substring(0,num.length-(4*i+3))+','+ num.substring(num.length-(4*i+3)); return (((sign)?'':'-') + num); }
function foxyshop_format_number(num) { num = num.toString().replace(/\$|\,/g,''); if(isNaN(num)) num = "0"; sign = (num == (num = Math.abs(num))); num = Math.floor(num*100+0.50000000001); cents = num%100; num = Math.floor(num/100).toString(); if(cents<10) cents = "0" + cents; for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++) num = num.substring(0,num.length-(4*i+3))+','+ num.substring(num.length-(4*i+3)); return (((sign)?'':'-') + num + '.' + cents); }
function foxyshop_check_number_single(el) { el.value = foxyshop_format_number_single(el.value); }
function foxyshop_check_number(el) { el.value = foxyshop_format_number(el.value); }
function check_oz(el) {
	oz = foxyshop_format_number_single(el.value);
	if (oz >= <?php echo ($foxyshop_settings['weight_type'] == 'metric' ? 1000 : 16); ?>) {
		el.value = 0;
		jQuery("#_weight1").val(parseFloat(jQuery("#_weight1").val())+1);
	} else {
		el.value = oz;
	}
}
</script>



<?php
}

//Save All Product Info
function product_meta_save($post_id) {
	global $foxyshop_settings;
	if (!wp_verify_nonce((isset($_POST['products_meta_noncename']) ? $_POST['products_meta_noncename'] : ""),__FILE__)) return $post_id;
	if (!current_user_can('edit_'.($_POST['post_type'] == 'page' ? 'page' : 'post'), $post_id)) return $post_id;
	
	$_weight = (int)$_POST['_weight1'] . ' ' . (int)$_POST['_weight2'];
	if ($_weight == ' ') $_weight = $foxyshop_settings['default_weight'].' 0'; //Set Default Weight

	//Save Product Detail Data
	saveFoxyShopMetaData('_price',number_format($_POST['_price'],2,".",""));
	saveFoxyShopMetaData('_code',$_POST['_code']);
	saveFoxyShopMetaData('_category',$_POST['_category']);
	saveFoxyShopMetaData('_weight',$_weight);
	saveFoxyShopMetaData('_quantity_min',(int)$_POST['_quantity_min']);
	saveFoxyShopMetaData('_quantity_max',(int)$_POST['_quantity_max']);
	saveFoxyShopMetaData('_hide_product',$_POST['_hide_product']);


	//Save Product Pricing Data
	if (($_salestartdate = strtotime($_POST['_salestartdate'])) === false) saveFoxyShopMetaData('_salestartdate',"999999999999999999");
	else saveFoxyShopMetaData('_salestartdate',$_salestartdate);
	if (($_saleenddate = strtotime($_POST['_saleenddate'])) === false) saveFoxyShopMetaData('_saleenddate',"999999999999999999");
	else saveFoxyShopMetaData('_saleenddate',$_saleenddate);

	saveFoxyShopMetaData('_saleprice',number_format($_POST['_saleprice'],2,".",""));
	saveFoxyShopMetaData('_discount_quantity_amount',$_POST['_discount_quantity_amount']);
	saveFoxyShopMetaData('_discount_quantity_percentage',$_POST['_discount_quantity_percentage']);
	saveFoxyShopMetaData('_discount_price_amount',$_POST['_discount_price_amount']);
	saveFoxyShopMetaData('_discount_price_percentage',$_POST['_discount_price_percentage']);

	saveFoxyShopMetaData('_sub_frequency',$_POST['_sub_frequency']);
	saveFoxyShopMetaData('_sub_startdate',$_POST['_sub_startdate']);
	saveFoxyShopMetaData('_sub_enddate',$_POST['_sub_enddate']);

	//Save Related Product Data
	$_related_products = "";
	$arr_related = explode(",",$_POST['_related_products']);
	foreach($arr_related as $arr_related_single) {
		if ($arr_related_single) {
			if ($_related_products) $_related_products .= ",";
			$_related_products .= $arr_related_single;
		}
	}
	saveFoxyShopMetaData('_related_products',$_related_products);

	//Save Bundled Product Data
	$_bundled_products = "";
	$arr_bundled = explode(",",$_POST['_bundled_products']);
	foreach($arr_bundled as $arr_bundled_single) {
		if ($arr_bundled_single) {
			if ($_bundled_products) $_bundled_products .= ",";
			$_bundled_products .= $arr_bundled_single;
		}
	}
	saveFoxyShopMetaData('_bundled_products',$_bundled_products);

	//Save Product Variations
	$currentID = 0;
	for ($i=1;$i<=$foxyshop_settings['max_variations'];$i++) {
		$_variationName = $_POST['_variation_name_'.$i];
		$_variationType = $_POST['_variation_type_'.$i];
		$_variationDisplayKey = $_POST['_variation_dkey_'.$i];
		$writeID = $i;
		if ($_variationName != '') {
			$currentID++;
			$writeID = $currentID;
			$_variationDisplayKey = $_POST['_variation_dkey_'.$i];
			if ($_variationType == 'text') {
				$_variationValue = $_POST['_variation_textsize1_'.$i]."|".$_POST['_variation_textsize2_'.$i];
			} elseif ($_variationType == 'textarea') {
				$_variationValue = (int)$_POST['_variation_textareasize_'.$i];
				if ($_variationValue == 0) $_variationValue = 3;
			} elseif ($_variationType == 'upload') {
				$_variationValue = $_POST['_variation_uploadinstructions_'.$i];
			} elseif ($_variationType == 'dropdown') {
				$_variationValue = $_POST['_variation_value_'.$i];
			}
		} else {
			$_variationName = "";
			$_variationType = "";
			$_variationValue = "";
			$_variationDisplayKey = "";
		}
		saveFoxyShopMetaData('_variation_type_'.$writeID,$_variationType);
		saveFoxyShopMetaData('_variation_name_'.$writeID,$_variationName);
		saveFoxyShopMetaData('_variation_value_'.$writeID,$_variationValue);
		saveFoxyShopMetaData('_variation_dkey_'.$writeID,$_variationDisplayKey);
	}

	return $post_id;
}



function saveFoxyShopMetaData($fieldname,$input) {
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