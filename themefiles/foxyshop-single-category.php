<?php get_header(); ?>

<div id="foxyshop_container">
<?php foxyshop_include('header'); ?>
<?php
//Hide page content if plugin is disabled
if (function_exists('foxyshop_breadcrumbs')) {

	//Write Breadcrumbs
	foxyshop_breadcrumbs();

	//Get Current Page Info
	$term = get_term_by('slug', get_query_var('term'), "foxyshop_categories");
	$currentCategory = $term->name;
	$currentCategorySlug = $term->slug;
	$currentCategoryID = $term->term_id;

	//Write Category Title
	echo '<h1 id="foxyshop_category_title">' . str_replace("_","",$term->name) . '</h1>'."\n";
	
	//If there's a category description, write it here
	if ($term->description) echo '<p>' . $term->description . '</p>'."\n";

	//Show Children Categories
	foxyshop_category_children($currentCategoryID);
	
	//Run the query for all products in this category
	$unwanted_children = get_term_children($currentCategoryID, "foxyshop_categories");
	$unwanted_post_ids = get_objects_in_term($unwanted_children, "foxyshop_categories");
	$args = array('post_type' => 'foxyshop_product', "post__not_in" => $unwanted_post_ids, "foxyshop_categories" => $currentCategorySlug, 'posts_per_page' => -1, 'paged' => get_query_var('paged'));
	$args = array_merge($args,foxyshop_sort_order_array());
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
		echo '<h2><a href="' . $product['url'] . '">' . apply_filters('the_title', $product['name']) . '</a></h2>';

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