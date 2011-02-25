<?php
function foxyshop_settings_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Settings'), __('Manage Settings'), 'manage_options', 'foxyshop_options', 'foxyshop_options');
}

function set_foxyshop_settings() {
	if (get_option("foxyshop_settings") == "") set_foxyshop_defaults();
	$foxyshop_settings_update_key = (isset($_POST['action']) ? $_POST['action'] : "");
	if ($foxyshop_settings_update_key == "foxyshop_settings_update" && check_admin_referer('update-foxyshop-options')) {
		global $foxyshop_settings;
		$new_settings = array();
		$fields = array("version","ship_categories","weight_type","enable_ship_to","enable_custom_file_uploads","enable_subscriptions", "enable_bundled_products", "sort_key", "default_image", "use_jquery", "ga", "generate_feed");
		foreach ($fields as $field1) {
			$new_settings[$field1] = $_POST['foxyshop_'.$field1];
		}
		$new_settings["domain"] = str_replace("http://","",$_POST['foxyshop_domain']);
		$new_settings["max_variations"] = (int)$_POST['foxyshop_max_variations'];
		$new_settings["default_weight"] = (int)$_POST['foxyshop_default_weight'];
		$new_settings["api_key"] = $foxyshop_settings['api_key'];
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
	<?php _e('If you need a FoxyCart account, please <a href="http://affiliate.foxycart.com/idevaffiliate.php?id=211" target="_blank">click here</a>.'); ?></p>

	<?php  if (isset($_GET['saved'])) echo '<div class="updated"><p>' . __('Your Settings Have Been Saved.') . '</p></div>'; ?>

	<form method="post" name="foxycart_settings_form" action="options.php">

	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('FoxyCart Settings'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="foxyshop_domain"><?php _e('Your Complete FoxyCart Domain'); ?>:</label> <input type="text" name="foxyshop_domain" value="<?php echo $foxyshop_settings['domain']; ?>" size="50" /> (example: yourname.foxycart.com)
					<div class="small"><?php _e('If you have your own custom domain, you may enter that as well (cart.yourname.com). Do not include the "http://".'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_key"><?php _e('API Key'); ?>:</label>
					<input type="text" id="foxyshop_key" name="key" readonly="readonly" value="<?php echo $foxyshop_settings['api_key']; ?>" onclick="this.select();" size="88" />
					<div class="small"><?php _e('Note: this is a required step for security reasons and utilizes FoxyCart\'s HMAC product verification to avoid link tampering. Enter this API key on the advanced menu of your FoxyCart admin and check the box to enable the cart validation option. This API key is generated automatically and cannot be edited.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_ship_categories" style="vertical-align: top;"><?php _e('Your Shipping Categories'); ?>:</label>
					<textarea id="name="foxyshop_ship_categories" name="foxyshop_ship_categories" rows="3" cols="40" wrap="auto"><?php echo $foxyshop_settings['ship_categories']; ?></textarea><br />
					<div class="small"><?php _e('These categories should correspond to the category codes you set up in your FoxyCart admin and will be available in a drop-down on your product setup page. Separate by line break. If you only use one main, default category this is not required.'); ?></div>
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
					<div class="small"><?php _e('Version 0.7.1 and up includes the images in shopping cart feature.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_use_jquery" name="foxyshop_use_jquery"<?php echo checked($foxyshop_settings['use_jquery'], "on"); ?> />
					<label for="foxyshop_use_jquery"><?php _e('Automatically Insert jQuery 1.4.2 from Google CDN'); ?></label>
					<div class="small"><?php _e('If you are already manually inserting jQuery you can uncheck this. Please note that currently FoxyCart\'s Colorbox has a problem with jQuery 1.4.3 and above so we\'ll use 1.4.2. This will be upgraded to the latest stable version once the Colorbox issue is resolved. Additionally it should be noted that jQuery needs to be inserted before wp_head() is called or FoxyCart won\'t function properly.'); ?></div>
				</td>
			</tr>
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
		</tbody>
	</table>
	
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
					<input type="checkbox" id="foxyshop_shipto" name="foxyshop_enable_ship_to"<?php echo checked($foxyshop_settings['enable_ship_to'], "on"); ?> />
					<label for="foxyshop_shipto"><?php _e('Enable Multiple Shipping Recipients'); ?></label>
					<div class="small"><?php _e('Remember that FoxyCart charges an extra fee for this service. You must enable it on your FoxyCart account or it will not work.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_custom_file_uploads" name="foxyshop_enable_custom_file_uploads"<?php echo checked($foxyshop_settings['enable_custom_file_uploads'], "on"); ?> />
					<label for="foxyshop_enable_custom_file_uploads"><?php _e('Enable Custom File Uploading'); ?></label>
					<div class="small"><?php _e('Allow customers to upload custom files as a product variation.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_bundled_products" name="foxyshop_enable_bundled_products"<?php echo checked($foxyshop_settings['enable_bundled_products'], "on"); ?> />
					<label for="foxyshop_enable_bundled_products"><?php _e('Enable Bundled Products'); ?></label>
					<div class="small"><?php _e('Allow multiple items to be added to the cart at once (extra items will be added with a price of $0.00)'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_subscriptions" name="foxyshop_enable_subscriptions"<?php echo checked($foxyshop_settings['enable_subscriptions'], "on"); ?> />
					<label for="foxyshop_enable_subscriptions"><?php _e('Enable Subscriptions'); ?></label>
					<div class="small"><?php _e('Show fields to allow the creation of subscription products.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<span><?php _e('Default Weight Type'); ?>:</span>
					<input type="radio" id="foxyshop_weight_type_english" name="foxyshop_weight_type" value="english"<?php echo checked($foxyshop_settings['weight_type'], "english"); ?> />
					<label for="foxyshop_weight_type_english"><?php _e('English'); ?></label>
					<input type="radio" id="foxyshop_weight_type_metric" name="foxyshop_weight_type" value="metric" style="margin-left: 20px;"<?php echo checked($foxyshop_settings['weight_type'], "metric"); ?> />
					<label for="foxyshop_weight_type_metric"><?php _e('Metric'); ?></label>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_default_weight"><?php _e('Default Weight'); ?>:</label> <input type="text" id="foxyshop_default_weight" name="foxyshop_default_weight" value="<?php echo $foxyshop_settings['default_weight']; ?>" style="width: 40px;" /> <?php _e('(lbs/kg)'); ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_max_variations"><?php _e('Maximum Variations'); ?>:</label> <input type="text" id="foxyshop_max_variations" name="foxyshop_max_variations" value="<?php echo $foxyshop_settings['max_variations']; ?>" style="width: 50px;" />
					<div class="small"><?php _e('This is an arbitrary number to save resources and should cover you in most cases. Raise it only if you need to.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_ga"><?php _e('Google Analytics Code'); ?>:</label> <input type="text" id="foxyshop_ga" name="foxyshop_ga" value="<?php echo $foxyshop_settings['ga']; ?>" size="20" />
					<div class="small"><?php _e('Enter your UA code here (example: UA-XXXXXXXX-X) and Google Analytics tracking will be installed in the footer. Additionally, it will only run if the visitor is not a logged-in WordPress user so that admin usage won\'t be tracked.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_default_image"><?php _e('Default Image'); ?>:</label> <input type="text" id="foxyshop_default_image" name="foxyshop_default_image" value="<?php echo $foxyshop_settings['default_image']; ?>" size="100" />
					<div class="small"><?php _e('Enter the URL for the image that will be shown if no image is loaded. Or leave the default, it\'s up to you. (If you change the website URL, though, you\'ll have to come back and change it here.)'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_generate_feed" name="foxyshop_generate_feed"<?php echo checked($foxyshop_settings['generate_feed'], "on"); ?> />
					<label for="foxyshop_generate_feed"><?php _e('Generate Product Feed'); ?></label>
					<div class="small"><?php _e('Selecting this option will add a sidebar to the menu which will allow you to export a file suitable for uploading to Google\'s Product Search system.'); ?></div>
				</td>
			</tr>

		</tbody>
	</table>
	
	
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings','settings-submit') ?>" /></p>

	<input type="hidden" name="action" value="foxyshop_settings_update" />
	<?php wp_nonce_field('update-foxyshop-options'); ?>
	</form>

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
		"default_weight" => 1,
		"use_jquery" => "on",
		"sort_key" => "menu_order",
		"ga" => "",
		"generate_feed" => "",
		"default_image" => FOXYSHOP_DIR."/images/no-photo.png",
		"api_key" => "sp92fx".hash_hmac('sha256',rand(21654,6489798),"dkjw82j1".time())
	);
	update_option("foxyshop_settings", serialize($foxyshop_settings));
}
?>