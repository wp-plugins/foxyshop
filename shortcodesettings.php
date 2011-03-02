<?php
/*
Examples:

[productlink name="product-slug"]
http://yoursite.com/products/product-slug/

[product name="product-slug"]Add XYZ Product To Cart[/product]
<a href="http://yoursite.com/products/product-slug/" class="foxyshop_sc_product_link">Add XYZ Product To Cart</a>

*/

add_shortcode('product', 'foxyshop_product_shortcode');

function foxyshop_product_shortcode($atts, $content = null) {
	global $product;
	$original_product = $product;
	extract(shortcode_atts(array(
		"name" => ''
	), $atts));


	$prod = get_product_by_name($name);
	if (!$prod || !$name) return "";
	$product = foxyshop_setup_product($prod);
	$write = '<a href="' . foxyshop_product_link("", true) . '" class="foxyshop_sc_product_link">' . $content . '</a>';
	$product = $original_product;
	return $write;
}

add_shortcode('productlink', 'foxyshop_productlink_shortcode');

function foxyshop_productlink_shortcode($atts, $content = null) {
	global $product;
	$original_product = $product;
	extract(shortcode_atts(array(
		"name" => ''
	), $atts));
	
	$prod = get_product_by_name($name);
	if (!$prod || !$name) return "";
	$product = foxyshop_setup_product($prod);
	$write = foxyshop_product_link("", true);
	$product = $original_product;
	return $write;
}

function get_product_by_name($post_name, $output = OBJECT) {
    global $wpdb;
    $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='foxyshop_product'", $post_name ));
    if ($post) return get_post($post, $output);
    return null;
}


?>