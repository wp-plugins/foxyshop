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
		$fields = array("version","ship_categories","weight_type","enable_ship_to","enable_subscriptions", "enable_bundled_products", "sort_key", "default_image", "use_jquery", "ga", "ga_advanced", "generate_feed", "hide_subcat_children", "generate_product_sitemap", "manage_inventory_levels", "inventory_url_key", "inventory_alert_level", "enable_sso", "sso_account_required", "browser_title_1","browser_title_2","browser_title_3","browser_title_4","browser_title_5");
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
	
	//Products won't load until this settings page has been loaded for the first time and the rewrite rules have been flushed..
	if (get_option('foxyshop_set_rewrite_rules') == "1") {
		flush_rewrite_rules(false);
		delete_option('foxyshop_set_rewrite_rules');
	}
	
	//Set Inventory URL Key if Not Set
	if ($foxyshop_settings['inventory_url_key'] == "") {
		$foxyshop_settings['inventory_url_key'] = substr(MD5(rand(1000, 99999)."{urlkey}" . date("H:i:s")),1,12);
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
					<label for="foxyshop_domain"><?php _e('Your FoxyCart Domain'); ?>:</label> <input type="text" name="foxyshop_domain" value="<?php echo $foxyshop_settings['domain']; ?>" size="50" /> <small>(example: yourname.foxycart.com)</small>
					<div class="small"><?php _e('If you have your own custom domain, you may enter that as well (cart.yourdomain.com). Do not include the "http://". The FoxyCart include files will be inserted automatically so you won\'t need to add anything to the header of your site.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_key"><?php _e('API Key'); ?>:</label>
					<input type="text" id="foxyshop_key" name="key" value="<?php echo $foxyshop_settings['api_key']; ?>" readonly="readonly" onclick="this.select();" size="88" />
					<div class="small"><?php echo __('Note: this is a required step for security reasons and utilizes FoxyCart\'s HMAC product verification to avoid link tampering.<br /><span style="color: red;"><strong>Enter this API key on the advanced menu of your FoxyCart admin and check the box to enable cart validation.</strong></span><br />(This API key is generated automatically and cannot be edited.)'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_version"><?php _e('FoxyCart Version'); ?>:</label> 
					<select name="foxyshop_version" id="foxyshop_version">
					<?php
					$versionArray = array('0.7.0', '0.7.1');
					foreach ($versionArray as $version1) {
						echo '<option value="' . $version1 . '"' . ($foxyshop_settings['version'] == $version1 ? ' selected="selected"' : '') . '>' . $version1 . '  </option>'."\n";
					} ?>
					</select><br />
					<div class="small"><?php _e('Version 0.7.1 is recommended.'); ?></div>
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
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" /></p>
	
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
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" /></p>
	<br /><br />


	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Browser Page Titles'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<div class="small" style="margin-bottom: 8px;"><?php _e('This is what will be displayed in the title bar of your web browser.'); ?></div>

					<label for="foxyshop_browser_title_1" style="width: 112px;"><?php _e('All Products'); ?>:</label> <input type="text" name="foxyshop_browser_title_1" value="<?php echo $foxyshop_settings['browser_title_1']; ?>" size="50" />
					<div style="clear: both;"></div>
					<label for="foxyshop_browser_title_2" style="width: 112px;"><?php _e('All Categories'); ?>:</label> <input type="text" name="foxyshop_browser_title_2" value="<?php echo $foxyshop_settings['browser_title_2']; ?>" size="50" />
					<div style="clear: both;"></div>
					<label for="foxyshop_browser_title_3" style="width: 112px;"><?php _e('Single Category'); ?>:</label> <input type="text" name="foxyshop_browser_title_3" value="<?php echo $foxyshop_settings['browser_title_3']; ?>" size="50" /> <small>Use %c for Category Name</small>
					<div style="clear: both;"></div>
					<label for="foxyshop_browser_title_4" style="width: 112px;"><?php _e('Single Product'); ?>:</label> <input type="text" name="foxyshop_browser_title_4" value="<?php echo $foxyshop_settings['browser_title_4']; ?>" size="50" /> <small>Use %p for Product Name</small>
					<div style="clear: both;"></div>
					<label for="foxyshop_browser_title_5" style="width: 112px;"><?php _e('Search Results'); ?>:</label> <input type="text" name="foxyshop_browser_title_5" value="<?php echo $foxyshop_settings['browser_title_5']; ?>" size="50" />
					
				</td>
			</tr>
		</tbody>
	</table>
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" /></p>
	
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
					<div class="small"><?php _e('These categories should correspond to the category codes you set up in your FoxyCart admin and will be available in a drop-down on your product setup page. Separate each category with a line break. If you only use one category this is not required. If you would like to also display a nice name in the dropdown menu, use a pipe sign "|" like this: free_shipping|Free Shipping.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_shipto" name="foxyshop_enable_ship_to"<?php checked($foxyshop_settings['enable_ship_to'], "on"); ?> />
					<label for="foxyshop_shipto"><?php _e('Enable Multi-Ship'); ?></label>
					<div class="small"><?php _e('Remember that FoxyCart charges an extra fee for this service. You must enable it on your FoxyCart account or it will not work.'); ?></div>
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
					<input type="checkbox" id="foxyshop_enable_sso" name="foxyshop_enable_sso"<?php checked($foxyshop_settings['enable_sso'], "on"); ?> />
					<label for="foxyshop_enable_sso"><?php _e('Enable WordPress Single-Sign-On'); ?></label>
					<div class="small"><?php _e('If enabled, your WordPress users will not have to login again to complete a FoxyCart checkout. WordPress accounts and FoxyCart accounts are kept in sync. You must be using FoxyCart 0.7.1 or above and in the FoxyCart admin you must set the "customer password hash type" to "phpass, portable mode" and the hash config to 8. Check the "enable single sign on" option and put this url in the "single sign on url" box:'); ?></div>
					<div style="height: 30px;"><input type="text" name="ssourlkey_notused" value="<?php echo get_bloginfo('wpurl') . '/foxycart-sso-' . $foxyshop_settings['inventory_url_key']; ?>/" readonly="readonly" onclick="this.select();" size="88" /></div>
					<div style="padding: 0 0 0 15px;">
						<input type="checkbox" id="foxyshop_sso_account_required" name="foxyshop_sso_account_required"<?php checked($foxyshop_settings['sso_account_required'], "on"); ?> />
						<label for="foxyshop_sso_account_required"><?php _e('Require a WordPress Account to check out'); ?></label>
					</div>
				</td>
			</tr>


			<tr>
				<td>
					<input type="checkbox" id="foxyshop_manage_inventory_levels" name="foxyshop_manage_inventory_levels"<?php checked($foxyshop_settings['manage_inventory_levels'], "on"); ?> />
					<label for="foxyshop_manage_inventory_levels"><?php _e('Manage Inventory Levels'); ?></label>
					<input type="hidden" name="foxyshop_inventory_url_key" value="<?php echo $foxyshop_settings['inventory_url_key']; ?>" />
					<div class="small"><?php _e('If enabled, you will be able to set inventory levels per product code. In the FoxyCart admin, you need to check the box to enable your datafeed and enter the following url in the "datafeed url" box:'); ?></div>
					<div style="height: 30px;"><input type="text" name="inventoryurlkey_notused" value="<?php echo get_bloginfo('wpurl') . '/foxycart-datafeed-' . $foxyshop_settings['inventory_url_key']; ?>/" readonly="readonly" onclick="this.select();" size="88" /></div>
					<label for="foxyshop_inventory_alert_level"><?php _e('Default Inventory Alert Level'); ?>:</label> <input type="text" id="foxyshop_inventory_alert_level" name="foxyshop_inventory_alert_level" value="<?php echo $foxyshop_settings['inventory_alert_level']; ?>" style="width: 50px;" />
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
					<label for="foxyshop_max_variations"><?php _e('Maximum Variations'); ?>:</label> <input type="text" id="foxyshop_max_variations" name="foxyshop_max_variations" value="<?php echo $foxyshop_settings['max_variations']; ?>" style="width: 50px;" />  <small>(per product)</small>
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
					<div style="padding: 5px 0 0 15px;">
						<input type="checkbox" id="foxyshop_ga_advanced" name="foxyshop_ga_advanced"<?php checked($foxyshop_settings['ga_advanced'], "on"); ?> />
						<label for="foxyshop_ga_advanced"><?php _e('Advanced Google Analytics Code'); ?></label>
						<div class="small"><?php _e('Check this box if you are using the amazing FoxyCart Google Analytics Sync. We will put the appropriate code in your footer (but you\'ll still have to setup Google Analytics and your template). Read more about it:'); ?> <a href="http://wiki.foxycart.com/integration/googleanalytics_async" target="_blank">here</a> and see our handy code guide <a href="http://www.foxy-shop.com/wp-content/uploads/2011/02/FoxyCart_Google_Analytics.txt" target="_blank">here</a>.</div>
					</div>
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
					<div class="small"><?php _e('If checked, a sitemap file will be created in your root folder. Please make sure that the root folder is writeable or that this file exists and is writeable:'); echo ' <a href="' . get_bloginfo('url') . '/sitemap-products.xml" target="blank">' . get_bloginfo('url') . '/sitemap-products.xml</a>'; ?></div>
				</td>
			</tr>

		</tbody>
	</table>
	
	
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" /></p>

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
		"enable_subscriptions" => "",
		"enable_bundled_products" => "",
		"browser_title_1" => "Products | " . get_bloginfo("name"),
		"browser_title_2" => "Product Categories | " . get_bloginfo("name"),
		"browser_title_3" => "%c | " . get_bloginfo("name"),
		"browser_title_4" => "%p | " . get_bloginfo("name"),
		"browser_title_5" => "Product Search | " . get_bloginfo("name"),
		"weight_type" => "english",
		"default_weight" => "1 0",
		"use_jquery" => "on",
		"hide_subcat_children" => "on",
		"generate_product_sitemap" => "",
		"sort_key" => "menu_order",
		"enable_sso" => "",
		"sso_account_required" => "",
		"ga" => "",
		"ga_advanced" => "",
		"manage_inventory_levels" => "",
		"inventory_alert_level" => 3,
		"inventory_url_key" => substr(MD5(rand(1000, 99999)."{urlkey}" . date("H:i:s")),1,12),
		"generate_feed" => "",
		"default_image" => FOXYSHOP_DIR."/images/no-photo.png",
		"products_per_page" => -1,
		"api_key" => "sp92fx".hash_hmac('sha256',rand(21654,6489798),"dkjw82j1".time())
	);
	update_option("foxyshop_settings", serialize($foxyshop_settings));
}

?>