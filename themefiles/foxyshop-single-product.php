<?php get_header(); ?>

<div id="foxyshop_container">
<?php foxyshop_include('header'); ?>
<?php
if (function_exists('foxyshop_breadcrumbs')) {
while (have_posts()) : the_post();
	
	//PrettyPhoto Includes (can be removed if you want to use a different js slideshow plugin)
	echo '<script type="text/javascript" src="' . FOXYSHOP_DIR . '/js/prettyphoto/jquery.prettyPhoto.js"></script>'."\n";
	echo '<link rel="stylesheet" href="' . FOXYSHOP_DIR . '/js/prettyphoto/prettyPhoto.css" type="text/css" media="screen" />'."\n";
	echo '<script type="text/javascript">jQuery(document).ready(function($){$("a[rel^=\'foxyshop_gallery\']").prettyPhoto({theme: \'light_rounded\'});});</script>'."\n";

	//Initialize Product
	global $product;
	$product = foxyshop_setup_product();
	
	//print_r($product);
	
	//Initialize Form
	foxyshop_start_form();
	
	//Write Breadcrumbs
	foxyshop_breadcrumbs();
	

	//Show the Main Image and Slideshow if Available
	echo '<div class="foxyshop_product_image">';
	$mediumSRC = foxyshop_get_main_image("medium");
	$mediumSRCtitle = foxyshop_get_main_image("title");
	$fullSRC = foxyshop_get_main_image("full");
	$imagecount = count($product['images']);
	if ($mediumSRC != $fullSRC || $imagecount > 1) echo '<a href="' . $fullSRC . '" rel="foxyshop_gallery' . ($imagecount > 1 ? '[fs_gall]' : '') . '">';
	echo '<img src="' . $mediumSRC . '" alt="' . htmlspecialchars($mediumSRCtitle) . '" title="" /></a>';
	if ($mediumSRC != $fullSRC) echo '</a>';
	foxyshop_image_slideshow("thumbnail", false);
	echo "</div>\n";
				
	
	//Main Product Information Panel
	echo '<div class="foxyshop_product_info">';
	edit_post_link('<img src="' . FOXYSHOP_DIR . '/images/editicon.png" alt="Edit Product" width="16" height="16" />','<span class="foxyshop_edit_product">','</span>');
	echo '<h2>' . $product['name'] . '</h2>';
	
	//Show a sale tag if the product is on sale
	//if (foxyshop_is_on_sale()) echo '<p>SALE!</p>';
	
	//Main Product Description
	echo $product['description'];


	//Show Variations (showQuantity 0 = No, 1 = On Top, 2 = Below)
	foxyshop_product_variations(2);
	
	//(style) clear before the submit button
	echo '<div class="clr"></div>';

	//Add To Cart Button
	echo '<button type="submit" name="productsubmit" id="productsubmit" class="foxyshop_button">Add To Cart</button>';
	
	//Shows the Price (includes sale price if applicable)
	echo '<div id="foxyshop_main_price">';
	foxyshop_price();
	echo '</div>';

	//Shows any related products
	foxyshop_related_products();


	//Ends the form
	echo '</div>';
	echo '</form>';


endwhile;
}
?>
	<div style="clear:both;"></div>
	<?php foxyshop_include('footer'); ?>
</div>

<?php
get_footer(); ?>