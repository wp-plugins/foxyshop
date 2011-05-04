<?php get_header(); ?>

<?php
//Hide page content if plugin is disabled
if (function_exists('foxyshop_insert_foxycart_files')) {
global $product;
?>
<div id="foxyshop_container">
	<?php
	//Product Page Header
	foxyshop_include('header');

	//Write Category Title
	echo '<h1 id="foxyshop_category_title">Products</h1>'."\n";

	//Write Product Sort Dropdown
	//foxyshop_sort_dropdown();
	
	//Feel free to put a store description here
	//echo '<p></p>'."\n";

	
	//Run the query for all products in this category
	$args = array('post_type' => 'foxyshop_product', 'post_status' => 'publish', 'posts_per_page' => -1);
	$args = array_merge($args,foxyshop_sort_order_array());
	query_posts($args);
	echo '<ul class="foxyshop_product_list">';
	while (have_posts()) :
		the_post();

		//Product Display
		foxyshop_include('product-loop');

	endwhile;
	echo '</ul>';
	
	//Pagination
	foxyshop_get_pagination();
	
	//Product Page Footer
	foxyshop_include('footer');
	?>
</div>
<?php } ?>

<script type="text/javascript">
jQuery(document).ready(function($){
	//This is set up for a two-column display. For a three column you need to do: nth-child(3n+1)
	$(".foxyshop_product_list>li:nth-child(odd)").css("clear","left");
});
</script>


<?php get_footer(); ?>