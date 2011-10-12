<?php
//Insert jQuery
function foxyshop_insert_jquery() {
	wp_deregister_script('jquery');
	wp_register_script('jquery', "http".($_SERVER['SERVER_PORT'] == 443 ? 's' : '')."://ajax.googleapis.com/ajax/libs/jquery/".FOXYSHOP_JQUERY_VERSION."/jquery.min.js", array(), NULL, false);
	wp_enqueue_script('jquery');
}

//Loading in Admin Scripts
function foxyshop_load_admin_scripts($hook) {
	global $foxyshop_settings;
	
	$page = (isset($_REQUEST['page']) ? $_REQUEST['page'] : '');
	
	//Style - Always Do This
	wp_enqueue_style('foxyshop_admin_css', FOXYSHOP_DIR . '/css/foxyshop-admin.css');
	
	//Date Picker
	if ($page == "foxyshop_order_management" || $page == "foxyshop_subscription_management") foxyshop_date_picker();
	
	//Custom Sorter
	if ($page == "foxyshop_custom_sort" || $page == "foxyshop_category_sort" ||  $page == "foxyshop_tools") {
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
	}

	//Product
	if($hook != 'post.php' && $hook != 'post-new.php' && $page != 'cfbe_editor-foxyshop_product') return;
	wp_enqueue_script('swfobject');
	if ($foxyshop_settings['related_products_custom'] || $foxyshop_settings['related_products_tags'] || $foxyshop_settings['enable_addon_products']) {
		wp_enqueue_script('chosenScript', FOXYSHOP_DIR . '/js/chosen.jquery.min.js', array('jquery'));
		wp_enqueue_style('chosenStyle', FOXYSHOP_DIR . '/css/chosen.css');
	}
	foxyshop_date_picker();
}

//Loading in Public Style
function foxyshop_load_site_scripts() {
	wp_enqueue_style('foxyshop_css', FOXYSHOP_DIR . '/css/foxyshop.css', array(), FOXYSHOP_VERSION);
}

function foxyshop_date_picker() {
	global $wp_version;
	if (version_compare($wp_version, '3.1', '>=')) {
		wp_enqueue_style('datepickerStyle', FOXYSHOP_DIR . '/css/ui-smoothness/jquery-ui.custom.css');
		wp_enqueue_script('datepickerScript', FOXYSHOP_DIR . '/js/jquery-ui.datepicker.min.js', array('jquery','jquery-ui-core'));
	}
}


//Check Permalinks on all admin pages and warn if incorrect
add_action('admin_notices', 'foxyshop_check_permalinks');
function foxyshop_check_permalinks() {
	$permalink_structure = (isset($_POST['permalink_structure']) ? $_POST['permalink_structure'] : get_option('permalink_structure'));
	if ($permalink_structure == '') {
		echo '<div class="error"><p><strong>Warning:</strong> Your <a href="options-permalink.php">permalink structure</a> is set to default. It is recommend that you set to Month and Name. Other settings may cause difficulties using FoxyShop.</p></div>';
	}
}



//Insert Google Analytics
function foxyshop_insert_google_analytics() {
	global $foxyshop_settings;
	
	//Advanced
	if ($foxyshop_settings['ga_advanced']) {
		?><script type="text/javascript" charset="utf-8">

	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '<?php echo htmlspecialchars($foxyshop_settings['ga']); ?>']);
	_gaq.push(['_setDomainName', '<?php echo $_SERVER['SERVER_NAME']; ?>']);
	_gaq.push(['_setAllowHash', 'false']);
	<?php if (strpos($foxyshop_settings['domain'], '.foxycart.com') !== false) echo "_gaq.push(['_setAllowLinker', true]);\n"; ?>
	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();

	fcc.events.cart.preprocess.add(function(e, arr) {
		if (arr['cart'] == 'checkout' || arr['cart'] == 'updateinfo' || arr['output'] == 'json') {
			return true;
		}
		if (arr['cart'] == 'checkout_paypal_express') {
			_gaq.push(['_trackPageview', '/paypal_checkout']);
			return true;
		}
		_gaq.push(['_trackPageview', '/cart']);
		return true;
	});
	fcc.events.cart.process.add_pre(function(e, arr) {
		var pageTracker = _gat._getTrackerByName();
		jQuery.getJSON('https://' + storedomain + '/cart?' + fcc.session_get() + '&h:ga=' + escape(pageTracker._getLinkerUrl('', true)) + '&output=json&callback=?', function(data){});
		return true;
	});
</script><?php
	
	
	} else {
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
		} else {
			echo "<!-- Google Analytics Not Loaded Because This is a Logged-In User -->";
		}
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

function foxyshop_dblquotes($str) {
	return str_replace('"','""',$str);
}


//Plugin Activation Function
function foxyshop_activation() {
	
	//Initialize Category Sort Holder If Not Set
	add_option('foxyshop_category_sort',serialize(array()));
	
	//Get Locale
	$current_locale = get_locale();
	if (!$current_locale) $current_locale = "en_US";
	
	//Defaults For Settings
	$default_foxyshop_settings = array(
		"domain" => "",
		"version" => "0.7.1",
		"foxyshop_version" => FOXYSHOP_VERSION,
		"ship_categories" => "",
		"enable_ship_to" => "",
		"enable_subscriptions" => "",
		"enable_bundled_products" => "",
		"enable_addon_products" => "",
		"enable_dashboard_stats" => "",
		"related_products_custom" => "on",
		"related_products_tags" => "",
		"browser_title_1" => FOXYSHOP_PRODUCT_NAME_SINGULAR . " | " . get_bloginfo("name"),
		"browser_title_2" => FOXYSHOP_PRODUCT_NAME_SINGULAR . " Categories | " . get_bloginfo("name"),
		"browser_title_3" => "%c | " . get_bloginfo("name"),
		"browser_title_4" => "%p | " . get_bloginfo("name"),
		"browser_title_5" => FOXYSHOP_PRODUCT_NAME_SINGULAR . " Search | " . get_bloginfo("name"),
		"weight_type" => "english",
		"default_weight" => "1 0.0",
		"use_jquery" => "on",
		"hide_subcat_children" => "on",
		"generate_product_sitemap" => "",
		"sort_key" => "menu_order",
		"enable_sso" => "",
		"sso_account_required" => "0",
		"ga" => "",
		"ga_advanced" => "",
		"locale_code" => $current_locale,
		"manage_inventory_levels" => "",
		"inventory_alert_level" => 3,
		"inventory_alert_email" => "",
		"checkout_customer_create" => "",
		"datafeed_url_key" => substr(MD5(rand(1000, 99999)."{urlkey}" . date("H:i:s")),1,12),
		"default_image" => "",
		"foxycart_include_cache" => "",
		"template_url_cart" => "",
		"template_url_checkout" => "",
		"products_per_page" => -1,
		"api_key" => "sp92fx".hash_hmac('sha256',rand(21654,6489798),"dkjw82j1".time())
	);
	
	//Set For the First Time
	if (!get_option("foxyshop_settings")) {
		update_option("foxyshop_settings", serialize($default_foxyshop_settings));
		add_option("foxyshop_setup_required", 1);
		return $default_foxyshop_settings;
	
	//Upgrade Tasks
	} else {
		
		$foxyshop_settings = maybe_unserialize(get_option("foxyshop_settings"));
		
		//Run Some Upgrades
		if (!array_key_exists('version',$foxyshop_settings)) $foxyshop_settings['version'] = "0";
		if ($foxyshop_settings['version'] == "0.70") $foxyshop_settings['version'] = "0.7.0";
		if (!array_key_exists('locale_code',$foxyshop_settings)) $foxyshop_settings['locale_code'] = $current_locale;
		if (!array_key_exists('inventory_alert_email',$foxyshop_settings)) $foxyshop_settings['inventory_alert_email'] = "on";
		if (array_key_exists('inventory_url_key',$foxyshop_settings)) { $foxyshop_settings['datafeed_url_key'] = $foxyshop_settings['inventory_url_key']; unset($foxyshop_settings['inventory_url_key']); }
		if ($foxyshop_settings['sso_account_required'] == "") $foxyshop_settings['sso_account_required'] = 0;
		if ($foxyshop_settings['sso_account_required'] == "on") $foxyshop_settings['sso_account_required'] = 1;
		if (!array_key_exists('enable_dashboard_stats',$foxyshop_settings)) $foxyshop_settings['enable_dashboard_stats'] = ""; //3.0
		if (!array_key_exists('checkout_customer_create',$foxyshop_settings)) $foxyshop_settings['checkout_customer_create'] = ""; //3.2?
		if ($foxyshop_settings['default_image'] == WP_PLUGIN_URL."/foxyshop/images/no-photo.png") $foxyshop_settings['default_image'] = ""; //3.3
		if (!$foxyshop_settings['domain'] && version_compare($foxyshop_settings['version'], '3.3', "<")) add_option("foxyshop_setup_required", 1); //3.3
		if (!array_key_exists('foxycart_include_cache',$foxyshop_settings)) $foxyshop_settings['foxycart_include_cache'] = ""; //3.3
		if (!array_key_exists('related_products_custom',$foxyshop_settings)) $foxyshop_settings['related_products_custom'] = "on"; //3.3
		if (!array_key_exists('related_products_tags',$foxyshop_settings)) $foxyshop_settings['related_products_tags'] = ""; //3.3
		if (!array_key_exists('enable_addon_products',$foxyshop_settings)) $foxyshop_settings['enable_addon_products'] = ""; //3.4
		if (!array_key_exists('template_url_cart',$foxyshop_settings)) $foxyshop_settings['template_url_cart'] = ""; //3.5.1
		if (!array_key_exists('template_url_checkout',$foxyshop_settings)) $foxyshop_settings['template_url_checkout'] = ""; //3.5.1

		//Upgrade Variations in 3.0
		if (version_compare($foxyshop_settings['foxyshop_version'], '3.0', "<")) {
			$temp_max_variations = (array_key_exists('max_variations',$foxyshop_settings) ? $foxyshop_settings['max_variations'] : 10);
			$products = get_posts(array('post_type' => 'foxyshop_product', 'numberposts' => -1, 'post_status' => null));
			foreach ($products as $product) {
				$variations = array();
				for ($i=1; $i<=$temp_max_variations; $i++) {
					$_variationName = get_post_meta($product->ID, '_variation_name_'.$i, 1);
					$_variationType = get_post_meta($product->ID, '_variation_type_'.$i, 1);
					$_variationValue = get_post_meta($product->ID, '_variation_value_'.$i, 1);
					$_variationDisplayKey = get_post_meta($product->ID, '_variation_dkey_'.$i, 1);
					$_variationRequired = get_post_meta($product->ID, '_variation_required_'.$i, 1);
					if ($_variationName) {
						$variations[$i] = array(
							"name" => $_variationName,
							"type" => $_variationType,
							"value" => $_variationValue,
							"displayKey" => $_variationDisplayKey,
							"required" => $_variationRequired
						);
					}
				}
				if (count($variations) > 0) {
					if (update_post_meta($product->ID, '_variations', serialize($variations))) {
						for ($i=1; $i<=$temp_max_variations; $i++) {
							delete_post_meta($product->ID,'_variation_name_'.$i);
							delete_post_meta($product->ID,'_variation_type_'.$i);
							delete_post_meta($product->ID,'_variation_value_'.$i);
							delete_post_meta($product->ID,'_variation_dkey_'.$i);
							delete_post_meta($product->ID,'_variation_required_'.$i);
						}
					}
				}
			}
			if (array_key_exists('max_variations', $foxyshop_settings)) unset($foxyshop_settings['max_variations']);
		}

		//Load in New Defaults and Save New Version
		$foxyshop_settings = wp_parse_args($foxyshop_settings,$default_foxyshop_settings);
		$foxyshop_settings['foxyshop_version'] = FOXYSHOP_VERSION;
		if (!$foxyshop_settings['datafeed_url_key']) $foxyshop_settings['datafeed_url_key'] = substr(MD5(rand(1000, 99999)."{urlkey}" . date("H:i:s")),1,12);

		//Save Settings
		update_option("foxyshop_settings", serialize($foxyshop_settings));
		return $foxyshop_settings;
	}
}

//Plugin Deactivation Function
function foxyshop_deactivation() {
	global $wp_post_types;
	if (isset($wp_post_types['foxyshop_product'])) unset($wp_post_types['foxyshop_product']);
	delete_option('foxyshop_rewrite_rules');
	flush_rewrite_rules();
}

//Create Product Sitemap
function foxyshop_create_product_sitemap() {
	$args = array(
		'post_type' => array('foxyshop_product'),
		'post_status' => 'publish',
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
	
	//Write Sitemap File If Possible
	$sitemap_filename = FOXYSHOP_DOCUMENT_ROOT.'/sitemap-products.xml';
	if (file_exists($sitemap_filename)) {
		if (is_writeable($sitemap_filename)) file_put_contents($sitemap_filename, $write);
	} else {
		if (is_writeable(FOXYSHOP_DOCUMENT_ROOT)) file_put_contents($sitemap_filename, $write);
	}
}

//Flushes Rewrite Rules if Structure Has Changed
function foxyshop_check_rewrite_rules() {
	if (get_option('foxyshop_rewrite_rules') != FOXYSHOP_PRODUCTS_SLUG."|".FOXYSHOP_PRODUCT_CATEGORY_SLUG || isset($_GET["foxyshop_flush_rewrite_rules"])) {
		flush_rewrite_rules(false);
		update_option('foxyshop_rewrite_rules', FOXYSHOP_PRODUCTS_SLUG."|".FOXYSHOP_PRODUCT_CATEGORY_SLUG);
	}
}

//Access the FoxyCart API
function foxyshop_get_foxycart_data($foxyData, $silent_fail = true) {
	global $foxyshop_settings;
	if (!defined('FOXYSHOP_CURL_CONNECTTIMEOUT')) define('FOXYSHOP_CURL_CONNECTTIMEOUT', 10); //10
	if (!defined('FOXYSHOP_CURL_TIMEOUT')) define('FOXYSHOP_CURL_TIMEOUT', 15); //15

	$foxyData = array_merge(array("api_token" => $foxyshop_settings['api_key']), $foxyData);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://" . $foxyshop_settings['domain'] . "/api");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $foxyData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, FOXYSHOP_CURL_CONNECTTIMEOUT);
	curl_setopt($ch, CURLOPT_TIMEOUT, FOXYSHOP_CURL_TIMEOUT);
	if (defined('FOXYSHOP_CURL_SSL_VERIFYPEER')) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FOXYSHOP_CURL_SSL_VERIFYPEER);
	$response = trim(curl_exec($ch));
	if (!$response) {
		if ($silent_fail) {
			$response = "<?xml version='1.0' encoding='UTF-8'?><foxydata><result>ERROR</result><messages><message>" . __('Request Timed Out. Please Try Again.') . "</message></messages></foxydata>";
		} else {
			die("cURL Error: " . curl_error($ch));
		}
	}
	curl_close($ch);
	return $response;
}
?>