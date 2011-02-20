<?php get_header(); ?>

<div id="foxyshop_container">
	<?php foxyshop_include('header'); ?>
	
	<?php
	//echo '<h1 id="foxyshop_category_title">Products</h1>';
	
	if (function_exists('foxyshop_breadcrumbs')) {
		//Show all children that have a parent of 0 (top level ones)
		foxyshop_category_children(0, false);
	}
	?>	

	<?php foxyshop_include('footer'); ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
	$(".foxyshop_categories>li:nth-child(3n+1)").css("clear","left");
});
</script>

<?php get_footer(); ?>