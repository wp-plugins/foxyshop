<?php get_header(); ?>

<?php
//Hide page content if plugin is disabled
if (function_exists('foxyshop_setup_product')) {
global $product;
?>
<div id="foxyshop_container">
	<?php foxyshop_include('header'); ?>
	<h1 id="foxyshop_category_title">Product Search</h1>

	<form class="searchform" action="/product-search/" method="get">
		<input type="text" name="search" value="" />
		<button type="submit" name="submitsearch" id="submitsearch">Search Products</button>
	</form>


	<?php
	$search = (isset($_REQUEST['search']) ? urlencode($_REQUEST['search']) : "sdafasdfasdfasdfasdfasdf");
	$args = array('post_type' => 'foxyshop_product', 'posts_per_page' => foxyshop_products_per_page(), 's' => $search, 'paged' => get_query_var('paged'));
	$args = array_merge($args,foxyshop_sort_order_array());
	query_posts($args);
	if (!have_posts() & isset($_REQUEST['search'])) {
		echo '<p style="margin-top: 20px;">No products found.</p>';
	}
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
	$(".foxyshop_product_list>li:nth-child(odd)").css("clear","left");
});
</script>


<?php get_footer(); ?>
