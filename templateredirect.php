<?php
//Template fallback
add_action("template_redirect", 'foxyshop_theme_redirect', 1);

function foxyshop_theme_redirect() {
	global $wp;

	//print_r($wp);
	$currentPageName = (isset($wp->query_vars["pagename"]) ? $wp->query_vars["pagename"] : "");
	$currentPostType = (isset($wp->query_vars["post_type"]) ? $wp->query_vars["post_type"] : "");
	$currentCategory = (isset($wp->query_vars["foxyshop_categories"]) ? $wp->query_vars["foxyshop_categories"] : "");
	$currentTaxonomy = (isset($wp->query_vars["taxonomy"]) ? $wp->query_vars["taxonomy"] : "");
	$currentProduct = (isset($wp->query_vars["foxyshop_product"]) ? $wp->query_vars["foxyshop_product"] : "");
	
	//Single Product Page
	if ($currentPostType = "foxyshop_product" && $currentProduct != "") {
		$templatefilename = 'foxyshop-single-product.php';
		if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
			$return_template = TEMPLATEPATH . '/' . $templatefilename;
		} else {
			$return_template = FOXYSHOP_PATH . '/themefiles/' . $templatefilename;
		}
		do_theme_redirect($return_template);

	//All Categories Page
	} elseif ($currentPageName == 'product-cat') {
		$templatefilename = 'foxyshop-all-categories.php';
		if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
			$return_template = TEMPLATEPATH . '/' . $templatefilename;
		} else {
			$return_template = FOXYSHOP_PATH . '/themefiles/' . $templatefilename;
		}
		
		add_filter('wp_title', 'title_filter_productcat', 1, 3);
		include($return_template);
		die();

	//Single Category Page
	} elseif ($currentCategory != '') {
		$templatefilename = 'foxyshop-single-category.php';
		if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
			$return_template = TEMPLATEPATH . '/' . $templatefilename;
		} else {
			$return_template = FOXYSHOP_PATH . '/themefiles/' . $templatefilename;
		}
		add_filter('wp_title', 'title_filter_category', 1, 3);
		do_theme_redirect($return_template);
	

	//All Products Page
	} elseif ($currentPageName == 'products' || $currentPostType == 'foxyshop_product') {
		$templatefilename = 'foxyshop-all-products.php';
		if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
			$return_template = TEMPLATEPATH . '/' . $templatefilename;
		} else {
			$return_template = FOXYSHOP_PATH . '/themefiles/' . $templatefilename;
		}
		
		add_filter('wp_title', 'title_filter_product', 1, 3);
		include($return_template);
		die();
	
	//Search Product Page
	} elseif ($currentPageName == 'product-search') {
		$templatefilename = 'foxyshop-search.php';
		if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
			$return_template = TEMPLATEPATH . '/' . $templatefilename;
		} else {
			$return_template = FOXYSHOP_PATH . '/themefiles/' . $templatefilename;
		}
		
		add_filter('wp_title', 'title_filter_productsearch', 1, 3);
		include($return_template);
		die();
	
	}
	
	
	
	
}
function title_filter_product() { return "Products | "; }
function title_filter_category() {
	global $wp;
	$currentCategory = (isset($wp->query_vars["foxyshop_categories"]) ? $wp->query_vars["foxyshop_categories"] : "");
	$currentCategory = explode("/",$currentCategory);
	$currentCategory = end($currentCategory);
	$term = get_term_by('slug', $currentCategory, "foxyshop_categories");
	return $term->name . " | ";
}
function title_filter_productcat() { return __("Product Categories") . " | "; }
function title_filter_productsearch() { return __("Product Seach") . " | "; }


function do_theme_redirect($url) {
	global $post, $wp_query;
	if (have_posts()) {
		include($url);
		die();
	} else {
		$wp_query->is_404 = true;
	}
}
?>