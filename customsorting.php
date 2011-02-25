<?php
//Only run this if sort key is set to custom
if ($foxyshop_settings['sort_key'] == "menu_order") {
	add_action('admin_menu', 'foxyshop_custom_sorting_menu');
	add_action('admin_print_scripts', 'foxyshop_custom_sort_js_libs');
}

//Put in Sidebar
function foxyshop_custom_sorting_menu() {    
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Custom Product Sorting'), __('Set Product Order'), 'manage_options', 'foxyshop_custom_sort', 'foxyshop_custom_sort');
}

//Load JS Libaries
function foxyshop_custom_sort_js_libs() {
	if ( isset($_GET['page']) && $_GET['page'] == "foxyshop_custom_sort" ) {
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
	}
}


//Update Order
function foxyshop_update_order() {
	if (isset($_POST['foxyshop_product_order_value']) && $_POST['foxyshop_product_order_value'] != "") { 
		global $wpdb;

		$foxyshop_product_order_value = $_POST['foxyshop_product_order_value'];
		$IDs = explode(",", $foxyshop_product_order_value);
		$result = count($IDs);
		for($i = 0; $i < $result; $i++) {
			$str = str_replace("id_", "", $IDs[$i]);
			$wpdb->query("UPDATE $wpdb->posts SET menu_order = '$i' WHERE id ='$str'");
		}
		return '<div id="message" class="updated fade"><p>'. __('Product order updated successfully.', 'mypageorder').'</p></div>';
	} else {
		return '<div id="message" class="updated fade"><p>'. __('An error occured, order has not been saved.', 'mypageorder').'</p></div>';
	}
}

//Rever Order to Original (set all to 0)
function foxyshop_revert_order() {
	if (isset($_POST['foxyshop_product_order_value']) && $_POST['foxyshop_product_order_value'] != "") { 
		global $wpdb;

		$foxyshop_product_order_value = $_POST['foxyshop_product_order_value'];
		$IDs = explode(",", $foxyshop_product_order_value);
		$result = count($IDs);
		for($i = 0; $i < $result; $i++) {
			$str = str_replace("id_", "", $IDs[$i]);
			$wpdb->query("UPDATE $wpdb->posts SET menu_order = '0' WHERE id ='$str'");
		}
		return '<div id="message" class="updated fade"><p>'. __('Product order reverted to original.', 'mypageorder').'</p></div>';
	} else {
		return '<div id="message" class="updated fade"><p>'. __('An error occured, order has not been saved.', 'mypageorder').'</p></div>';
	}
}

//The Main Function
function foxyshop_custom_sort() {
	global $wpdb, $product;
	$parentID = 0;
	$success = "";

	if (isset($_POST['submit_new_product_order'])) {
		if (check_admin_referer('update-foxyshop-sorting-options')) $success = foxyshop_update_order();
	} elseif (isset($_POST['submit_new_product_order'])) {
		if (check_admin_referer('update-foxyshop-sorting-options')) $success = foxyshop_revert_order();
	}
	?>

	<div class="wrap">
	<h2><?php _e('Custom Product Order'); ?></h2>
	<?php if ($success) echo $success; ?>
	<p><?php  ?></p>
	
	<?php
	$product_categories = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0&orderby=name&order=ASC');
	if ($product_categories) {
		echo '<p>' . __('Select a category from the drop down to order the products in that category.') . '</p>';
		echo '<form name="form_product_category_order" method="post" action="">';
		echo '<select name="categoryID" id="categoryID">'."\n";
		echo '<option value="0"' . ($categoryID == 0 ? ' selected="selected"' : '') . '>All Products</option>'."\n";
		foreach($product_categories as $cat) {
			echo '<option value="' . $cat->term_id . '"' . ($categoryID == $cat->term_id ? ' selected="selected"' : '') . '>' . $cat->name . ' (' . $cat->count . ')' . '</option>'."\n";
		}
		echo '</select>'."\n";
		echo '<input type="submit" name="btnSubPages" class="button" id="btnSubPages" value="' . __('Order Categories') . '" /></form>';
	} else {
		$categoryID = 0;
	}
	if (!isset($categoryID) && isset($_POST['categoryID'])) $categoryID = $_POST['categoryID'];
	
	if (isset($categoryID)) {
	
		if ($categoryID > 0) {
			$term = get_term_by('id', $categoryID, "foxyshop_categories");
			$current_category_name = $term->name;
			$current_category_slug = $term->slug;
			$unwanted_children = get_term_children($categoryID, "foxyshop_categories");
			$unwanted_post_ids = get_objects_in_term($unwanted_children, "foxyshop_categories");
			$args = array('post_type' => 'foxyshop_product', "post__not_in" => $unwanted_post_ids, "foxyshop_categories" => $current_category_slug, 'numberposts' => -1, 'orderby' => "menu_order", 'order' => "ASC");
		} else {
			$current_category_name = "All Products";
			$args = array('post_type' => 'foxyshop_product', 'numberposts' => -1, 'orderby' => "menu_order", 'order' => "ASC");
		}
	

		$product_list = get_posts($args);
		if ($product_list) {

			echo '<h3>' . $current_category_name . '</h3>'."\n";
			echo '<p>Drag products to the preferred order and then click the Save button at the bottom of the page.</p>';
			echo '<form name="form_product_order" method="post" action="">'."\n";
			echo '<ul id="foxyshop_product_order_list">'."\n";
			foreach ($product_list as $prod) {
				$product = foxyshop_setup_product($prod);
				echo '<li id="id_' . $prod->ID . '" class="lineitem">';
				echo '<img src="' . foxyshop_get_main_image() . '" />';
				echo '<h4>' . $prod->post_title . '</h4>'."\n";
				echo foxyshop_price();
				echo '<div class="counter">' . ((int)$prod->menu_order + 1) . '</div>';
				echo '<div style="clear: both; height: 1px;"></div>'."\n";
				echo '</li>'."\n";
			}
			echo '</ul>'."\n";
			?>
			<div style="width: 90%; height: 100px;">
				<input type="submit" name="submit_new_product_order" id="submit_new_product_order" class="button-primary" value="<?php _e('Save Custom Order'); ?>" onclick="javascript:orderPages(); return true;" />&nbsp;&nbsp;<strong id="updateText"></strong>
				<input type="submit" name="revert_product_order" id="revert_product_order" class="button" style="float: right;" value="<?php _e('Revert To Original'); ?>" />
			</div>
			<input type="hidden" id="foxyshop_product_order_value" name="foxyshop_product_order_value" />
			<input type="hidden" id="hdnParentID" name="hdnParentID" value="<?php echo $parentID; ?>" />
			<?php wp_nonce_field('update-foxyshop-sorting-options'); ?>
			</form>
			<?php

		} else {
			echo '<p><em>No Products Found For This Category.</em></p>';
		}

	}
	?>

</div>


	



<script type="text/javascript">
// <![CDATA[

	function foxyshop_custom_order_load_event(){
		jQuery("#foxyshop_product_order_list").sortable({ 
			placeholder: "sortable-placeholder", 
			revert: false,
			tolerance: "pointer",
			update: function() {
				var counter = 1;
				jQuery("#foxyshop_product_order_list li").each(function() {
					jQuery(this).find('.counter').html(counter);
					counter++;
				});
			}
		});
	};

	addLoadEvent(foxyshop_custom_order_load_event);
	
	function orderPages() {
		jQuery("#updateText").html("<?php _e('Updating Product Order...') ?>");
		jQuery("#foxyshop_product_order_value").val(jQuery("#foxyshop_product_order_list").sortable("toArray"));
	}

// ]]>
</script>
<?php
}
?>