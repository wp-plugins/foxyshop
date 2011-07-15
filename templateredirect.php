<?php
//Template fallback
add_action("template_redirect", 'foxyshop_theme_redirect', 1);

function foxyshop_theme_redirect() {
	global $wp, $foxyshop_settings;

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

	//Single Product Page
	if ($currentPostType = "foxyshop_product" && $currentProduct != "" && $currentProduct != 'page') {
		$return_template = foxyshop_get_template_file('foxyshop-single-product.php');
		add_filter('wp_title', 'title_filter_single_product', 9, 3);
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
		status_header(200);
		do_theme_redirect($return_template);

	//All Categories Page
	} elseif ($currentPageName == FOXYSHOP_PRODUCT_CATEGORY_SLUG || $currentName == FOXYSHOP_PRODUCT_CATEGORY_SLUG) {
		$return_template = foxyshop_get_template_file('foxyshop-all-categories.php');
		add_filter('wp_title', 'title_filter_all_categories', 9, 3);
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
		status_header(200);
		include($return_template);
		die;

	//Single Category Page
	} elseif ($currentCategory != '') {
		$return_template = foxyshop_get_template_file('foxyshop-single-category.php');
		add_filter('wp_title', 'title_filter_single_categories', 9, 3);
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
		status_header(200);
		do_theme_redirect($return_template);
	

	//All Products Page
	} elseif ($currentPageName == FOXYSHOP_PRODUCTS_SLUG || $currentName == FOXYSHOP_PRODUCTS_SLUG || $currentPostType == 'foxyshop_product') {
		$return_template = foxyshop_get_template_file('foxyshop-all-products.php');
		add_filter('wp_title', 'title_filter_all_products', 9, 3);
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
		status_header(200);
		include($return_template);
		die;
	
	//Search Product Page
	} elseif ($currentPageName == 'product-search') {
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
		add_filter('body_class', 'foxyshop_body_class', 10, 2 );
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
	global $wp, $foxyshop_settings;
	$currentCategory = (isset($wp->query_vars["foxyshop_categories"]) ? $wp->query_vars["foxyshop_categories"] : "");
	$currentCategory = explode("/",$currentCategory);
	$currentCategory = end($currentCategory);
	$term = get_term_by('slug', $currentCategory, "foxyshop_categories");
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
	$blacklist = array('error404');
	$wp_classes[] = "foxyshop";
	$wp_classes = array_diff($wp_classes, $blacklist);
	return array_merge( $wp_classes, (array) $extra_classes );
}
?>