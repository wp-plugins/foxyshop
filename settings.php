<?php
add_action('admin_menu', 'foxyshop_settings_menu');
add_action('admin_init', 'set_foxyshop_settings');

function foxyshop_settings_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Settings'), __('Manage Settings'), 'manage_options', 'foxyshop_options', 'foxyshop_options');
}

function set_foxyshop_settings() {
	if (get_option("foxyshop_settings") == "") set_foxyshop_defaults();
	$foxyshop_settings_update_key = (isset($_POST['action']) ? $_POST['action'] : "");
	if ($foxyshop_settings_update_key == "foxyshop_settings_update" && check_admin_referer('update-foxyshop-options')) {
		global $foxyshop_settings;
		
		//Do initial product sitemap creation
		if ($_POST['foxyshop_generate_product_sitemap'] == "on") {
			foxyshop_create_product_sitemap();
		}
		
		$new_settings = array();
		$fields = array("version","ship_categories","weight_type","enable_ship_to","enable_custom_file_uploads","enable_subscriptions", "enable_bundled_products", "sort_key", "default_image", "use_jquery", "ga", "generate_feed", "hide_subcat_children", "generate_product_sitemap");
		foreach ($fields as $field1) {
			$val = (isset($_POST['foxyshop_'.$field1]) ? $_POST['foxyshop_'.$field1] : '');
			$new_settings[$field1] = $val;
		}
		$new_settings["domain"] = str_replace("http://","",$_POST['foxyshop_domain']);
		$new_settings["api_key"] = $foxyshop_settings['api_key'];
		$new_settings["max_variations"] = (int)$_POST['foxyshop_max_variations'];
		$new_settings["default_weight"] = (int)$_POST['foxyshop_default_weight1'] . ' ' . (int)$_POST['foxyshop_default_weight2'];
		$new_settings["products_per_page"] = ((int)$_POST['foxyshop_products_per_page'] == 0 ? -1 : (int)$_POST['foxyshop_products_per_page']);

		update_option("foxyshop_settings", serialize($new_settings));
		header('location: edit.php?post_type=foxyshop_product&page=foxyshop_options&saved=1');
	}
}

function foxyshop_options() {
	global $foxyshop_settings;
	
	//This is a little awkward because products won't load until this settings page has been loaded for the first time.
	//print_r(get_option('rewrite_rules')); //View Rewrite Rules
	if (get_option('foxyshop_set_rewrite_rules') == "1") {
		flush_rewrite_rules(false);
		delete_option('foxyshop_set_rewrite_rules');
	}
?>
<div id="foxyshop_settings_wrap">
	

	<p><img src="<?php echo FOXYSHOP_DIR; ?>/images/logo.png" alt="FoxyShop" /></p>
	<p><?php _e('For more information on FoxyShop and complete documentation, please visit'); ?> <a href="http://www.foxy-shop.com/" target="_blank">www.foxy-shop.com</a>.<br />
	<?php _e('If you need a FoxyCart account or to access your admin panel, please <a href="http://affiliate.foxycart.com/idevaffiliate.php?id=211" target="_blank">click here</a>.'); ?></p>

	<?php  if (isset($_GET['saved'])) echo '<div class="updated"><p>' . __('Your Settings Have Been Saved.') . '</p></div>'; ?>

	<form method="post" name="foxycart_settings_form" action="options.php">

	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('FoxyCart Basic Settings'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="foxyshop_domain"><?php _e('Your FoxyCart Domain'); ?>:</label> <input type="text" name="foxyshop_domain" value="<?php echo $foxyshop_settings['domain']; ?>" size="50" /> (example: yourname.foxycart.com)
					<div class="small"><?php _e('If you have your own custom domain, you may enter that as well (cart.yourdomain.com). Do not include the "http://". The FoxyCart include files will be inserted automatically so you won\'t need to add anything to the header of your site.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_key"><?php _e('API Key'); ?>:</label>
					<input type="text" id="foxyshop_key" name="key" readonly="readonly" value="<?php echo $foxyshop_settings['api_key']; ?>" onclick="this.select();" size="88" />
					<div class="small"><?php echo __('Note: this is a required step for security reasons and utilizes FoxyCart\'s HMAC product verification to avoid link tampering.<br /><span style="color: red;"><strong>Enter this API key on the advanced menu of your FoxyCart admin and check the box to enable cart validation.</strong></span><br />(This API key is generated automatically and cannot be edited.)'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_version"><?php _e('FoxyCart Version'); ?>:</label> 
					<select name="foxyshop_version" id="foxyshop_version">
					<?php
					$versionArray = array('0.70', '0.7.1');
					foreach ($versionArray as $version1) {
						echo '<option value="' . $version1 . '"' . ($foxyshop_settings['version'] == $version1 ? ' selected="selected"' : '') . '>' . $version1 . '  </option>'."\n";
					} ?>
					</select><br />
					<div class="small"><?php _e('Version 0.7.1 and up includes the product image in the shopping cart.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_use_jquery" name="foxyshop_use_jquery"<?php checked($foxyshop_settings['use_jquery'], "on"); ?> />
					<label for="foxyshop_use_jquery"><?php _e('Automatically Insert jQuery 1.4.4 from Google CDN'); ?></label>
					<div class="small"><?php _e('If you are already manually inserting jQuery you can uncheck this option. It should be noted that jQuery needs to be inserted before wp_head() is called or FoxyCart won\'t function properly. Please note that currently FoxyCart\'s Colorbox has a problem with jQuery 1.5 and above so we\'ll use 1.4.4. This will be upgraded to the latest stable version once the Colorbox issue is resolved.'); ?></div>
				</td>
			</tr>
		</tbody>
	</table>
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings','settings-submit') ?>" /></p>
	
	<br /><br />


	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Display Settings'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="sort_key"><?php _e('Product Sorting'); ?>:</label> 
					<select name="foxyshop_sort_key" id="sort_key">
					<?php
					$sortArray = array("menu_order" => "Custom Order", "name" => "Product Name", "price_asc" => "Price (Lowest to Highest)", "price_desc" => "Price (Highest to Lowest)", "date_asc" => "Date (Oldest to Newest)", "date_desc" => "Date (Newest to Oldest)");
					foreach ($sortArray as $key=>$val) {
						echo '<option value="' . $key . '"' . ($foxyshop_settings['sort_key'] == $key ? ' selected="selected"' : '') . '>' . $val . '  </option>'."\n";
					} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_products_per_page"><?php _e('Products Per Page'); ?>:</label> <input type="text" id="foxyshop_products_per_page" name="foxyshop_products_per_page" value="<?php echo ($foxyshop_settings['products_per_page'] < 0 ? 0 : $foxyshop_settings['products_per_page']); ?>" style="width: 50px;" /> <small><?php _e('Enter 0 to show all products (no paging). Paging does not apply to the All Products page.'); ?></small>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_default_image"><?php _e('Default Image'); ?>:</label> <input type="text" id="foxyshop_default_image" name="foxyshop_default_image" value="<?php echo $foxyshop_settings['default_image']; ?>" style="width:544px;" /><small><a href="#" id="resetimage">Reset To Default</a></small>
					<div class="small"><?php _e('Enter the URL for the image that will be shown if no image is loaded. Or leave the default, it\'s up to you. (If you change the website URL, though, you\'ll have to come back and change it here.)'); ?></div>
				</td>
			</tr>



		</tbody>
	</table>
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings','settings-submit') ?>" /></p>
	
	<br /><br />

	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Advanced Settings'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="foxyshop_ship_categories" style="vertical-align: top;"><?php _e('Your Shipping Categories'); ?>:</label>
					<textarea id="name="foxyshop_ship_categories" name="foxyshop_ship_categories" rows="3" cols="40" wrap="auto"><?php echo $foxyshop_settings['ship_categories']; ?></textarea><br />
					<div class="small"><?php _e('These categories should correspond to the category codes you set up in your FoxyCart admin and will be available in a drop-down on your product setup page. Separate each category with a line break. If you only use one category this is not required.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_shipto" name="foxyshop_enable_ship_to"<?php checked($foxyshop_settings['enable_ship_to'], "on"); ?> />
					<label for="foxyshop_shipto"><?php _e('Enable Multiple Shipping Recipients'); ?></label>
					<div class="small"><?php _e('Remember that FoxyCart charges an extra fee for this service. You must enable it on your FoxyCart account or it will not work.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_custom_file_uploads" name="foxyshop_enable_custom_file_uploads"<?php checked($foxyshop_settings['enable_custom_file_uploads'], "on"); ?> />
					<label for="foxyshop_enable_custom_file_uploads"><?php _e('Enable Custom File Uploading'); ?></label>
					<div class="small"><?php _e('Allow customers to upload custom files as a product variation.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_bundled_products" name="foxyshop_enable_bundled_products"<?php checked($foxyshop_settings['enable_bundled_products'], "on"); ?> />
					<label for="foxyshop_enable_bundled_products"><?php _e('Enable Bundled Products'); ?></label>
					<div class="small"><?php _e('Allow multiple items to be added to the cart at once (extra items will be added with a price of $0.00)'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_subscriptions" name="foxyshop_enable_subscriptions"<?php checked($foxyshop_settings['enable_subscriptions'], "on"); ?> />
					<label for="foxyshop_enable_subscriptions"><?php _e('Enable Subscriptions'); ?></label>
					<div class="small"><?php _e('Show fields to allow the creation of subscription products.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<span style="float: left;font-weight: bold; margin: 2px 10px 0 0;"><?php _e('Default Weight Type'); ?>:</span>
					<input type="radio" id="foxyshop_weight_type_english" name="foxyshop_weight_type" value="english"<?php if ($foxyshop_settings['weight_type'] == "english" || $foxyshop_settings['weight_type'] == "") echo ' checked="checked"'; ?> />
					<label for="foxyshop_weight_type_english" style="font-weight: normal;"><?php _e('English'); ?></label>
					<input type="radio" id="foxyshop_weight_type_metric" name="foxyshop_weight_type" value="metric" style="margin-left: 20px;"<?php checked($foxyshop_settings['weight_type'], "metric"); ?> />
					<label for="foxyshop_weight_type_metric" style="font-weight: normal;"><?php _e('Metric'); ?></label>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_default_weight"><?php _e('Default Weight'); ?>:</label>
					<?php
					$arrweight = explode(" ",$foxyshop_settings['default_weight']);
					$weight1 = (int)$arrweight[0];
					$weight2 = (count($arrweight) > 1 ? (int)$arrweight[1] : 0);
					if ($weight1 == 0 && $weight2 == 0) $weight1 = 1;
					?>
					<input type="text" id="foxyshop_default_weight1" name="foxyshop_default_weight1" value="<?php echo $weight1; ?>" style="width: 25px;" /><small id="weight_title1" style="width: 28px;">lbs</small>
					<input type="text" id="foxyshop_default_weight2" name="foxyshop_default_weight2" value="<?php echo $weight2; ?>" style="width: 25px;" /><small id="weight_title2">oz</small>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_max_variations"><?php _e('Maximum Variations'); ?>:</label> <input type="text" id="foxyshop_max_variations" name="foxyshop_max_variations" value="<?php echo $foxyshop_settings['max_variations']; ?>" style="width: 50px;" />
					<div class="small"><?php _e('This is an arbitrary number to save resources and should be sufficient in most cases. Raise only if necessary.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_hide_subcat_children" name="foxyshop_hide_subcat_children"<?php checked($foxyshop_settings['hide_subcat_children'], "on"); ?> />
					<label for="foxyshop_hide_subcat_children"><?php _e('Hide Child Products From Parent Categories (recommended)'); ?></label>
					<div class="small"><?php _e('By default, WordPress treats children a little differently than you would expect in that products in child categories also show up in parent categories. FoxyShop removes these products, but if you would like to have all products from sub-categories show up in parent categories, uncheck this box.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_ga"><?php _e('Google Analytics Code'); ?>:</label> <input type="text" id="foxyshop_ga" name="foxyshop_ga" value="<?php echo $foxyshop_settings['ga']; ?>" size="20" /> <small>(UA-XXXXXXXX-X)</small>
					<div class="small"><?php _e('Enter your UA code here and Google Analytics tracking will be installed in the footer. Tracking will only be initiated if the visitor is not a logged-in WordPress user so that admin usage won\'t be tracked.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_generate_feed" name="foxyshop_generate_feed"<?php checked($foxyshop_settings['generate_feed'], "on"); ?> />
					<label for="foxyshop_generate_feed"><?php _e('Generate Product Feed'); ?></label>
					<div class="small"><?php _e('Selecting this option will add a sidebar to the menu which will allow you to export a file suitable for uploading to Google\'s Product Search system.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_generate_product_sitemap" name="foxyshop_generate_product_sitemap"<?php checked($foxyshop_settings['generate_product_sitemap'], "on"); ?> />
					<label for="foxyshop_generate_product_sitemap"><?php _e('Generate Product Sitemap'); ?></label>
					<div class="small"><?php _e('If checked, a sitemap file called \'sitemap-products.xml\' will be created in your root folder. Please make sure that the root folder is writeable or that the file exists and is writeable. File Here: '); echo '<a href="' . get_bloginfo('url') . '/sitemap-products.xml" target="blank">' . get_bloginfo('url') . '/sitemap-products.xml</a>'; ?></div>
				</td>
			</tr>

		</tbody>
	</table>
	
	
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings','settings-submit') ?>" /></p>

	<input type="hidden" name="action" value="foxyshop_settings_update" />
	<?php wp_nonce_field('update-foxyshop-options'); ?>
	</form>

<script type="text/javascript">
jQuery(document).ready(function($){
	$("input[name=foxyshop_weight_type]").change(function() {
		if ($("#foxyshop_weight_type_english").is(":checked")) {
			$("#weight_title1").text("lbs");
			$("#weight_title2").text("oz");
		} else {
			$("#weight_title1").text("kg");
			$("#weight_title2").text("gm");
		}
	});

	if ($("#foxyshop_weight_type_english").is(":checked")) {
		$("#weight_title1").text("lbs");
		$("#weight_title2").text("oz");
	} else {
		$("#weight_title1").text("kg");
		$("#weight_title2").text("gm");
	}
	
	$("#resetimage").click(function() {
		$("#foxyshop_default_image").val("<?php echo FOXYSHOP_DIR."/images/no-photo.png"; ?>");
		return false;
	});
	
	

});
</script>
<?php }

function set_foxyshop_defaults() {
	global $foxyshop_settings;
	$foxyshop_settings = array(
		"domain" => "",
		"version" => "0.7.1",
		"ship_categories" => "",
		"max_variations" => 10,
		"enable_ship_to" => "",
		"enable_custom_file_uploads" => "",
		"enable_subscriptions" => "",
		"enable_bundled_products" => "",
		"weight_type" => "english",
		"default_weight" => "1 0",
		"use_jquery" => "on",
		"hide_subcat_children" => "on",
		"generate_product_sitemap" => "",
		"sort_key" => "menu_order",
		"ga" => "",
		"generate_feed" => "",
		"default_image" => FOXYSHOP_DIR."/images/no-photo.png",
		"products_per_page" => -1,
		"api_key" => "sp92fx".hash_hmac('sha256',rand(21654,6489798),"dkjw82j1".time())
	);
	update_option("foxyshop_settings", serialize($foxyshop_settings));
}

?>