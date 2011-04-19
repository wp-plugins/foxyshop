<?php get_header(); ?>

<?php
//Hide page content if plugin is disabled
if (function_exists('foxyshop_insert_foxycart_files')) {
?>
<div id="foxyshop_container">
<?php
foxyshop_include('header');
while (have_posts()) : the_post();
	
	//PrettyPhoto Includes (can be removed if you want to use a different javascript slideshow plugin)
	echo '<script type="text/javascript" src="' . FOXYSHOP_DIR . '/js/prettyphoto/jquery.prettyPhoto.js"></script>'."\n";
	echo '<link rel="stylesheet" href="' . FOXYSHOP_DIR . '/js/prettyphoto/prettyPhoto.css" type="text/css" media="screen" />'."\n";
	echo '<script type="text/javascript">jQuery(document).ready(function($){$("a[rel^=\'foxyshop_gallery\']").prettyPhoto({theme: \'light_square\', overlay_gallery: false});});</script>'."\n";

	//Initialize Product
	global $product;
	$product = foxyshop_setup_product();
	
	//print_r($product);
	
	//Initialize Form
	foxyshop_start_form();
	
	//Write Breadcrumbs
	foxyshop_breadcrumbs(" &raquo; ", "&laquo; Back to Products");
	

	//Show the Main Image and Slideshow if Available
	$mediumSRC = foxyshop_get_main_image("medium");
	$mediumSRCtitle = foxyshop_get_main_image("title");
	$largeSRC = foxyshop_get_main_image("large");
	$imagecount = count($product['images']);
	if ($mediumSRC) {
		echo '<div class="foxyshop_product_image">';
		if ($mediumSRC != $largeSRC || $imagecount > 1) echo '<a href="' . $largeSRC . '" rel="foxyshop_gallery' . ($imagecount > 1 ? '[fs_gall]' : '') . '">';
		echo '<img src="' . $mediumSRC . '" id="foxyshop_main_product_image" alt="' . htmlspecialchars($mediumSRCtitle) . '" title="" /></a>';
		if ($mediumSRC != $largeSRC || $imagecount > 1) echo '</a>';
		foxyshop_image_slideshow("thumbnail", false, "Click Below For More Images:");
		echo "</div>\n";
	}
				
	
	//Main Product Information Area
	echo '<div class="foxyshop_product_info">';
	edit_post_link('<img src="' . FOXYSHOP_DIR . '/images/editicon.png" alt="Edit Product" width="16" height="16" />','<span class="foxyshop_edit_product">','</span>');
	echo '<h2>' . apply_filters('the_title', $product['name']) . '</h2>';
	
	//Show a sale tag if the product is on sale
	//if (foxyshop_is_on_sale()) echo '<p>SALE!</p>';

	//Product Is New Tag (number of days since added)
	//if (foxyshop_is_product_new(14)) echo '<p>NEW!</p>';
	
	//Main Product Description
	echo $product['description'];


	//Show Variations (showQuantity: 0 = Do Not Show Qty, 1 = Show Before Variations, 2 = Show Below Variations)
	foxyshop_product_variations(2);
	
	//(style) clear floats before the submit button
	echo '<div class="clr"></div>';

	//Check Inventory Levels and Display Status (last variable allows ordering of out of stock items)
	foxyshop_inventory_management("There are only %c item%s left in stock.", "Item is not in stock.", false);
	
	//Add To Cart Button
	echo '<button type="submit" name="x:productsubmit" id="productsubmit" class="foxyshop_button">Add To Cart</button>';
	
	//Shows the Price (includes sale price if applicable)
	echo '<div id="foxyshop_main_price">';
	foxyshop_price();
	echo '</div>';

	//Shows any related products
	foxyshop_related_products("Related Products");


	//Ends the form
	echo '</div>';
	echo '</form>';


endwhile;
?>
	<div class="clr"></div>
	<?php foxyshop_include('footer'); ?>
</div>
<?php } ?>

<?php
get_footer(); ?>