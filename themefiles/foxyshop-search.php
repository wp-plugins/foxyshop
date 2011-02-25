<?php get_header(); ?>

<div id="foxyshop_container">
	<?php foxyshop_include('header'); ?>
	<h1 id="foxyshop_category_title">Product Search</h1>

	<form class="searchform" action="/product-search/" method="get">
		<input type="text" name="search" value="" />
		<button type="submit" name="submitsearch" id="submitsearch">Search Products</button>
	</form>


	<?php
	if (function_exists('foxyshop_breadcrumbs')) {

	$search = (isset($_REQUEST['search']) ? urlencode($_REQUEST['search']) : "sdafasdfasdfasdfasdfasdf");
	$args = array('post_type' => 'foxyshop_product', 'posts_per_page' => -1, 's' => $search, 'paged' => get_query_var('paged'));
	$args = array_merge($args,foxyshop_sort_order_array());
	query_posts($args);
	if (!have_posts()) {
		echo '<p style="margin-top: 20px;">No products found.</p>';
	}
	echo '<ul class="foxyshop_product_list">';
	while (have_posts()) :
		the_post();
		global $product;
		$product = foxyshop_setup_product(); ?>

		<li class="foxyshop_product_box clearfix">
				<?php
				echo '<div class="foxyshop_product_image">';
				if ($thumbnailSRC = foxyshop_get_main_image("thumbnail")) echo '<a href="' . get_permalink() . '"><img src="' . $thumbnailSRC . '" alt="' . htmlspecialchars($product['name']) . '" class="foxyshop_main_image" /></a>';
				echo "</div>\n";
				
				echo '<div class="foxyshop_product_info">';
				echo '<h2><a href="' . get_permalink() . '">' . $product['name'] . '</a></h2>';
				
				echo "<p>" . $product['short_description'] . "</p>";
				
				echo '<a href="' . get_permalink() . '" class="foxyshop_button">More Details</a>';
				foxyshop_price();
				echo "</div>\n";

				echo '<div class="clr"></div>';				
		echo '</li>';
	endwhile; ?>
	</ul>
	
	<div id="foxyshop_pagination">
		<?php foxyshop_get_pagination(); ?>
	</div>
	<?php } ?>
	<?php foxyshop_include('footer'); ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
	$(".foxyshop_product_list>li:nth-child(odd)").css("clear","left");
});
</script>


<?php get_footer(); ?>
