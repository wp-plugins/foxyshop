<?php
//Template fallback
add_action("template_redirect", 'foxyshop_theme_redirect', 1);

function foxyshop_theme_redirect() {
	global $wp, $wp_query, $foxyshop_settings, $foxyshop_body_class_name;

	$currentName = (isset($wp->query_vars["name"]) ? $wp->query_vars["name"] : "");
	$currentPageName = (isset($wp->query_vars["pagename"]) ? $wp->query_vars["pagename"] : "");
	$currentPostType = (isset($wp->query_vars["post_type"]) ? $wp->query_vars["post_type"] : "");
	$currentCategory = (isset($wp->query_vars["foxyshop_categories"]) ? $wp->query_vars["foxyshop_categories"] : "");
	$currentTaxonomy = (isset($wp->query_vars["taxonomy"]) ? $wp->query_vars["taxonomy"] : "");
	$currentProduct = (isset($wp->query_vars["foxyshop_product"]) ? $wp->query_vars["foxyshop_product"] : "");
	

	//Uncomment to Troubleshoot
	//if (is_user_logged_in()) {
	//	echo "<pre>";print_r(get_option('rewrite_rules'));echo "</pre>"; //View Rewrite Rules
	//	echo "<pre>";print_r($wp);echo "</pre>";
	//}
	
	
	//Backup Parsing
	$request_arr = explode("/",$wp->request);
	$request_start = $request_arr[0];
	$request_end = end($request_arr);
	$foxyshop_indicators = array(FOXYSHOP_PRODUCTS_SLUG, FOXYSHOP_PRODUCT_CATEGORY_SLUG, 'product-search', 'foxycart-datafeed-'.$foxyshop_settings['datafeed_url_key'], 'foxycart-sso-'.$foxyshop_settings['datafeed_url_key'], 'upload-'.$foxyshop_settings['datafeed_url_key']);
	if (array_intersect($request_arr, $foxyshop_indicators)) {
		if (in_array(FOXYSHOP_PRODUCTS_SLUG, $request_arr) && $request_end != FOXYSHOP_PRODUCTS_SLUG) {
			$currentProduct = $request_end;
			$currentPostType = "foxyshop_product";
			query_posts("post_type=foxyshop_product&foxyshop_product=".$currentProduct);
		
		} elseif (in_array(FOXYSHOP_PRODUCT_CATEGORY_SLUG, $request_arr) && $request_end != FOXYSHOP_PRODUCT_CATEGORY_SLUG) {
			$currentCategory = $request_end;
			$paged = 1;
			
			//Check For Paging
			if (is_numeric($request_end) && $request_arr[count($request_arr) - 2] == "page") {
				$currentCategory = $request_arr[count($request_arr) - 3];
				$paged = $request_end;
			}
			query_posts("post_type=foxyshop_product&foxyshop_categories=".$currentCategory."&paged=" . $paged);
			
		} elseif ($request_start == "product-search") {
			$currentPageName = 'product-search';
		} elseif ($request_start == 'foxycart-datafeed-'.$foxyshop_settings['datafeed_url_key']) {
			$currentPageName = 'foxycart-datafeed-'.$foxyshop_settings['datafeed_url_key'];
		} elseif ($request_start == 'foxycart-sso-'.$foxyshop_settings['datafeed_url_key']) {
			$currentPageName = 'foxycart-sso-'.$foxyshop_settings['datafeed_url_key'];
		} elseif ($request_start == 'upload-'.$foxyshop_settings['datafeed_url_key']) {
			$currentPageName = 'upload-'.$foxyshop_settings['datafeed_url_key'];
		}
	}

	//Single Product Page
	if ($currentPostType == "foxyshop_product" && $currentProduct != "" && $currentProduct != 'page') {
		global $post;
		if (have_posts()) {
			while (have_posts()) the_post();
			$foxyshop_body_class_name = "foxyshop-single-product";
			$return_template = foxyshop_get_template_file('foxyshop-single-product.php');
			add_filter('wp_title', 'title_filter_single_product', 9, 3);
			add_filter('body_class', 'foxyshop_body_class', 10, 2 );
			status_header(200);
			do_theme_redirect($return_template);
		} else {
			$wp_query->is_404 = true;
		}

	//All Categories Page
	} elseif ($currentPageName == FOXYSHOP_PRODUCT_CATEGORY_SLUG || $currentName == FOXYSHOP_PRODUCT_CATEGORY_SLUG) {
		$foxyshop_body_class_name = "foxyshop-all-categories";
		$return_template = foxyshop_get_template_file('foxyshop-all-categories.php');
		add_filter('wp_title', 'title_filter_all_categories', 9, 3);
		add_filter('body_class', 'foxyshop_body_class', 10, 2);
		status_header(200);
		include($return_template);
		die;

	//Single Category Page
	} elseif ($currentCategory != '') {
		$return_template = foxyshop_get_template_file('foxyshop-single-category.php');
		global $foxyshop_single_category_name;
		$foxyshop_body_class_name = "foxyshop-single-category";
		$foxyshop_single_category_name = $currentCategory;
		add_filter('wp_title', 'title_filter_single_categories', 9, 3);
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
		status_header(200);
		do_theme_redirect($return_template);
	

	//All Products Page
	} elseif ($currentPageName == FOXYSHOP_PRODUCTS_SLUG || $currentName == FOXYSHOP_PRODUCTS_SLUG || $currentPostType == 'foxyshop_product') {
		$foxyshop_body_class_name = "foxyshop-all-products";
		$return_template = foxyshop_get_template_file('foxyshop-all-products.php');
		add_filter('wp_title', 'title_filter_all_products', 9, 3);
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
		status_header(200);
		include($return_template);
		die;
	
	//Search Product Page
	} elseif ($currentPageName == 'product-search') {
		$foxyshop_body_class_name = "foxyshop-search";
		$return_template = foxyshop_get_template_file('foxyshop-search.php');
		add_filter('wp_title', 'title_filter_product_search', 9, 3);
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
		status_header(200);
		include($return_template);
		die;

	//FoxyCart Datafeed Endpoint
	} elseif ($currentPageName == 'foxycart-datafeed-'.$foxyshop_settings['datafeed_url_key']) {
		$return_template = foxyshop_get_template_file('foxyshop-datafeed-endpoint.php');
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
		status_header(200);
		include($return_template);
		die;

	//FoxyCart SSO Endpoint
	} elseif ($currentPageName == 'foxycart-sso-'.$foxyshop_settings['datafeed_url_key']) {
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
		status_header(200);
		include(FOXYSHOP_PATH . '/ssoendpoint.php');
		die;

	//File Upload
	} elseif ($currentPageName == 'upload-'.$foxyshop_settings['datafeed_url_key']) {
		status_header(200);
		include(FOXYSHOP_PATH . '/uploadprocessor.php');
		die;
	
	}
	
	
	
	
}
function title_filter_all_products() {
	global $foxyshop_settings;
	return $foxyshop_settings['browser_title_1'];
}
function title_filter_single_categories() {
	global $wp, $foxyshop_settings, $foxyshop_single_category_name;
	$term = get_term_by('slug', $foxyshop_single_category_name, "foxyshop_categories");
	return str_replace("%c", $term->name, $foxyshop_settings['browser_title_3']);
}
function title_filter_all_categories() {
	global $foxyshop_settings;
	return $foxyshop_settings['browser_title_2'];
}
function title_filter_product_search() {
	global $foxyshop_settings;
	return $foxyshop_settings['browser_title_5'];
}

function title_filter_single_product() {
	global $foxyshop_settings, $post;
	return str_replace("%p", $post->post_title, $foxyshop_settings['browser_title_4']);
}

function do_theme_redirect($url) {
	include($url);
	die;
}
function foxyshop_body_class($wp_classes, $extra_classes) {
	global $foxyshop_body_class_name;
	$blacklist = array('error404');
	$wp_classes[] = "foxyshop";
	if ($foxyshop_body_class_name) $wp_classes[] = $foxyshop_body_class_name;
	$wp_classes = array_diff($wp_classes, $blacklist);
	return array_merge($wp_classes, (array)$extra_classes);
}
?>