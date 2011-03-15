<?php
//FoxyShop Product AJAX Functions
add_action('wp_ajax_foxyshop_product_ajax_action', 'foxyshop_product_ajax');
function foxyshop_product_ajax() {
	global $wpdb;
	$productID = (isset($_POST['foxyshop_product_id']) ? $_POST['foxyshop_product_id'] : 0);
	$imageID = (isset($_POST['foxyshop_image_id']) ? $_POST['foxyshop_image_id'] : 0);
	check_ajax_referer('foxyshop-product-image-functions-'.$productID, 'security');
	if (!isset($_POST['foxyshop_action'])) die;
	
	if ($_POST['foxyshop_action'] == "add_new_image") {
		$filename = $_POST['foxyshop_new_product_image'];
		$upload_dir = wp_upload_dir();
		$product_count = (isset($_POST['foxyshop_product_count']) ? (int)$_POST['foxyshop_product_count'] : 0);
		$wp_filetype = wp_check_filetype(basename($filename), null);
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => $_POST['foxyshop_product_title'],
			'guid' => $upload_dir['url'] . "/" . basename($filename),
			'menu_order' => $product_count + 1,
			'post_content' => '',
			'post_status' => 'inherit'
		);
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		$attach_id = wp_insert_attachment($attachment, $filename, $productID);
		$attach_data = wp_generate_attachment_metadata($attach_id, $_SERVER['DOCUMENT_ROOT'] . $filename);
		wp_update_attachment_metadata($attach_id, $attach_data);
		
		if ($product_count == 0) {
			update_post_meta($productID,"_thumbnail_id",$attach_id);
		}

		echo foxyshop_redraw_images($productID);
		
	} elseif ($_POST['foxyshop_action'] == "delete_image") {
		wp_delete_attachment($imageID);
		echo foxyshop_redraw_images($productID);

	} elseif ($_POST['foxyshop_action'] == "featured_image") {
		delete_post_meta($productID, "_thumbnail_id");
		update_post_meta($productID,"_thumbnail_id",$imageID);
		echo foxyshop_redraw_images($productID);

	} elseif ($_POST['foxyshop_action'] == "rename_image") {
		$update_post = array();
		$update_post['ID'] = $imageID;
		$update_post['post_title'] = $_POST['foxyshop_new_name'];
		wp_update_post($update_post);
	
	} elseif ($_POST['foxyshop_action'] == "update_image_order") {

		$foxyshop_order_array = $_POST['foxyshop_order_array'];
		$IDs = explode(",", $foxyshop_order_array);
		$result = count($IDs);
		for($i = 0; $i < $result; $i++) {
			$update_post = array();
			$update_post['ID'] = str_replace("att_", "", $IDs[$i]);
			$update_post['menu_order'] = $i+1;
			wp_update_post($update_post);
		}
	
		echo foxyshop_redraw_images($productID);
	
	} elseif ($_POST['foxyshop_action'] == "refresh_images") {
		echo foxyshop_redraw_images($productID);
	}
	die();
}

function foxyshop_redraw_images($id) {
	global $wpdb;
	$write = "";
	$featuredImageID = (has_post_thumbnail($id) ? get_post_thumbnail_id($id) : 0);
	$attachments = get_posts(array('numberposts' => -1, 'post_type' => 'attachment','post_status' => null,'post_parent' => $id, 'order' => 'ASC','orderby' => 'menu_order'));
	if ($attachments) {
		$i = 0;
		foreach ($attachments as $attachment) {
			if (wp_attachment_is_image($attachment->ID)) {
				
				$thumbnailSRC = wp_get_attachment_image_src($attachment->ID, "thumbnail");
				$write .= '<li id="att_' . $attachment->ID . '"'. ($featuredImageID == $attachment->ID || ($featuredImageID == 0 && $i == 0) ? ' class="foxyshop_featured_image"' : '') . '>';
				$write .= '<div class="foxyshop_image_holder"><img src="' . $thumbnailSRC[0] . '" alt=' . htmlspecialchars($attachment->post_title) . ' (' . $attachment->ID . ')" title="' . htmlspecialchars($attachment->post_title) . ' (' . $attachment->ID . ')" /></div>';
				$write .= '<div style="clear: both;"></div>';
				$write .= '<a href="#" class="foxyshop_image_delete" rel="' . $attachment->ID . '" alt="Delete" title="Delete">Delete</a>';
				$write .= '<a href="#" class="foxyshop_image_rename" rel="' . $attachment->ID . '" alt="Rename" title="Rename">Rename</a>';
				$write .= '<a href="#" class="foxyshop_image_featured" rel="' . $attachment->ID . '" alt="Make Featured Image" title="Make Featured Image">Make Featured Image</a>';
				$write .= '<div class="renamediv" id="renamediv_' . $attachment->ID . '">';
				$write .= '<input type="text" name="rename_' . $attachment->ID . '" id="rename_' . $attachment->ID . '" rel="' . $attachment->ID . '" value="' . htmlspecialchars($attachment->post_title) . '" />';
				$write .= '</div>';
				$write .= '<div style="clear: both;"></div>';
				$write .= '</li>';
				$write .= "\n";
				$i++;
			}
		}
	}
	return $write;
}




//Insert jQuery
function foxyshop_insert_jquery() {
	$jquery_version = "1.4.2";
	wp_deregister_script('jquery');
	wp_register_script('jquery', ("http".($_SERVER['SERVER_PORT'] == 443 ? 's' : '')."://ajax.googleapis.com/ajax/libs/jquery/".$jquery_version."/jquery.min.js"), false, $jquery_version);
	wp_enqueue_script('jquery');
}

//Insert Google Analytics
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

//Product Category Comparison
function foxyshop_comparison($a, $b) { 
	if ($a->sort_key == $b->sort_key) { return 0; }
	return ($a->sort_key < $b->sort_key) ? -1 : 1;
}

//Sort Categories
function foxyshop_sort_categories($obj, $categoryID) {
	global $foxyshop_category_sort;
	if (array_key_exists($categoryID,$foxyshop_category_sort)) {
		$sort_array = $foxyshop_category_sort[$categoryID];
		foreach($obj as $cat) {
			$cat->sort_key = 999;
			for ($i=0;$i<count($sort_array);$i++) {
				if ($sort_array[$i] == $cat->term_id) $cat->sort_key = $i;
			}
		}
		usort($obj,'foxyshop_comparison');
	}
	return $obj;
}

//Generate Products Per Page
function foxyshop_products_per_page() {
	global $foxyshop_settings;
	return $foxyshop_settings['products_per_page'];
}

//Hide Children Array
function foxyshop_hide_children_array($currentCategoryID) {
	global $foxyshop_settings;
	if ($foxyshop_settings['hide_subcat_children']) {
		return array("post__not_in" => get_objects_in_term(get_term_children($currentCategoryID, "foxyshop_categories"), "foxyshop_categories"));
	} else {
		return array();
	}
}

//Place Plugin Activation Links
function foxyshop_plugin_action_links($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = "foxyshop/foxyshop.php";
	if ($file == $this_plugin) {
		$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/edit.php?post_type=foxyshop_product&page=foxyshop_options">Settings</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}

//Plugin Activation Function
function foxyshop_activation() {
	add_option('foxyshop_set_rewrite_rules',"1");
}

//Plugin Deactivation Function
function foxyshop_deactivation() {
	flush_rewrite_rules();
}

//Create Product Sitemap
function foxyshop_create_product_sitemap() {
	$args = array(
		'post_type' => array('foxyshop_product'),
		'numberposts' => -1
	);
	$products = get_posts($args);
	$write = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$write .= '<urlset xmlns="http://www.google.com/schemas/sitemap/0.90">'."\n";
	foreach ($products as $product) {
		$write .= '<url>'."\n";
		$write .= '<loc>' . get_bloginfo('wpurl') . '/' . FOXYSHOP_PRODUCTS_SLUG . '/' . htmlspecialchars($product->post_name) . '/</loc>'."\n";
		$write .= '<lastmod>' . date('Y-m-d\TH:i:s+00:00',strtotime($product->post_modified)) . '</lastmod>'."\n";
		$write .= '<changefreq>weekly</changefreq>'."\n";
		$write .= '<priority>1.0</priority>'."\n";
		$write .= '</url>'."\n";
	}

	$termchildren = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0&orderby=name&order=ASC');
	if ($termchildren) {
		$write .= '<url>'."\n";
		$write .= '<loc>' . get_bloginfo('wpurl') . '/' . FOXYSHOP_PRODUCT_CATEGORY_SLUG . '/</loc>'."\n";
		$write .= '<lastmod>' . date('Y-m-d\TH:i:s+00:00',time()) . '</lastmod>'."\n";
		$write .= '<changefreq>weekly</changefreq>'."\n";
		$write .= '<priority>1.0</priority>'."\n";
		$write .= '</url>'."\n";
		foreach ($termchildren as $child) {
			$write .= '<url>'."\n";
			$write .= '<loc>' . get_term_link((int)$child->term_id, "foxyshop_categories") . '</loc>'."\n";
			$write .= '<lastmod>' . date('Y-m-d\TH:i:s+00:00',time()) . '</lastmod>'."\n";
			$write .= '<changefreq>weekly</changefreq>'."\n";
			$write .= '<priority>1.0</priority>'."\n";
			$write .= '</url>'."\n";
		}
	} else {
		$write .= '<url>'."\n";
		$write .= '<loc>' . get_bloginfo('wpurl') . '/' . FOXYSHOP_PRODUCTS_SLUG . '/</loc>'."\n";
		$write .= '<lastmod>' . date('Y-m-d\TH:i:s+00:00',time()) . '</lastmod>'."\n";
		$write .= '<changefreq>weekly</changefreq>'."\n";
		$write .= '<priority>1.0</priority>'."\n";
		$write .= '</url>'."\n";
	}
	$write .= '</urlset>';
	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/sitemap-products.xml', $write);
}


?>