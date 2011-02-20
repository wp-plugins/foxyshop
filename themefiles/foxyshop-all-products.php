<?php get_header(); ?>

<div id="foxyshop_container">
<?php foxyshop_include('header'); ?>

<?php
//Hide page content if plugin is disabled
if (function_exists('foxyshop_breadcrumbs')) {

	//Write Category Title
	echo '<h1 id="foxyshop_category_title">Products</h1>'."\n";
	
	//Put a category description here
	//echo '<p></p>'."\n";

	//To handle the sorting on this page, just pass in a querystring variable called "sort"
	$sort = (isset($_GET['sort']) ? $_GET['sort'] : "featured");
	if ($sort == "featured") {
		$order = "menu_order";
		$orderby = "ASC";
	} elseif ($sort == "price") {
		$order = "meta_value_num";
		$orderby = "DESC";
	} elseif ($sort == "title") {
		$order = "title";
		$orderby = "ASC";
	}
	
	
	//Run the query for all products in this category
	$args = array('post_type' => 'foxyshop_product', 'posts_per_page' => -1, 'paged' => get_query_var('paged'), 'orderby' => $order, 'meta_key' => '_price', 'order' => $orderby);
	query_posts($args);
	echo '<ul class="foxyshop_product_list">';
	while (have_posts()) :

		the_post();
		global $product;
		$product = foxyshop_setup_product();
		if ($product['hide_product']) continue;

		echo '<li class="foxyshop_product_box">';
		
		//Show Image on Left
		echo '<div class="foxyshop_product_image">';
		if ($thumbnailSRC = foxyshop_get_main_image("thumbnail")) echo '<a href="' . $product['url'] . '"><img src="' . $thumbnailSRC . '" alt="' . htmlspecialchars($product['name']) . '" class="foxyshop_main_image" /></a>';
		echo "</div>\n";

		//Show Main Product Info
		echo '<div class="foxyshop_product_info">';
		echo '<h2><a href="' . $product['url'] . '">' . $product['name'] . '</a></h2>';

		echo "<p>" . $product['short_description'] . "</p>";

		//More Details Button
		echo '<a href="' . $product['url'] . '" class="foxyshop_button">More Details</a>';
		
		//Add To Cart Button
		//echo '<a href="' . foxyshop_product_link("", true) . '" class="foxyshop_button">Add To Cart</a>';
		
		//Show Price (and sale if applicable)
		foxyshop_price();
		
		echo "</div>\n";
		
		echo '<div class="clr"></div>';
		echo "</li>\n";

	endwhile;
	echo '</ul>';
	
	//Pagination (not in use by default but there's a native function for it if you want to turn it on)
	?>
	<div id="foxyshop_pagination">
		<?php foxyshop_get_pagination(); ?>
	</div>

<?php } ?>
	<?php foxyshop_include('footer'); ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
	//This is set up for a two-column display. For a three column you need to do: nth-child(3n+1)
	$(".foxyshop_product_list>li:nth-child(odd)").css("clear","left");
});
</script>


<?php get_footer(); ?>