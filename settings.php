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
		$fields = array("version","ship_categories","weight_type","enable_ship_to","enable_custom_file_uploads","enable_subscriptions", "enable_bundled_products", "default_image", "use_jquery", "ga");
		foreach ($fields as $field1) {
			$new_settings[$field1] = $_POST['foxyshop_'.$field1];
		}
		$new_settings["domain"] = str_replace("http://","",$_POST['foxyshop_domain']);
		$new_settings["max_variations"] = (int)$_POST['foxyshop_max_variations'];
		$new_settings["default_weight"] = (int)$_POST['foxyshop_default_weight'];
		$new_settings["api_key"] = $foxyshop_settings['api_key'];
		update_option("foxyshop_settings", serialize($new_settings));
		header('location: edit.php?post_type=foxyshop_product&page=foxyshop_options');
	}
}

function foxyshop_options() {
	global $foxyshop_settings;
	
	//This is a little redundant, but I can't get the rewrite flush to fire on plugin activation. So it fires each time the settings page is loaded. Not ideal but it does the trick.
	//print_r(get_option('rewrite_rules')); //View Rewrite Rules
	if (get_option('foxyshop_set_rewrite_rules') == "1") {
		flush_rewrite_rules(false);
		delete_option('foxyshop_set_rewrite_rules');
	}
?>
<div id="foxyshop_settings_wrap">
	
	<p><img src="<?php echo FOXYSHOP_DIR; ?>/images/logo.png" alt="FoxyShop" /></p>
	<p><?php _e('For more information on FoxyShop and complete documentation, please visit', 'welcome-2'); ?> <a href="http://www.foxy-shop.com/" target="_blank">www.foxy-shop.com</a>.<br />
	<?php _e('If you need a FoxyCart account, please <a href="http://affiliate.foxycart.com/idevaffiliate.php?id=211" target="_blank">click here</a>.', 'welcome-2'); ?></p>

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
					<div class="small"><?php _e('If you have your own custom domain, you may enter that as well (cart.yourname.com). Do not include the "http://".','foxycart-domain-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_key"><?php _e('API Key'); ?>:</label>
					<input type="text" id="foxyshop_key" name="key" readonly="readonly" value="<?php echo $foxyshop_settings['api_key']; ?>" onclick="this.select();" size="88" />
					<div class="small"><?php _e('Note: this is a required step for security reasons and utilizes FoxyCart\'s HMAC product verification to avoid link tampering. Enter this API key on the advanced menu of your FoxyCart admin and check the box to enable the cart validation option. This API key is generated automatically and cannot be edited.','api-key-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_ship_categories" style="vertical-align: top;"><?php _e('Your Shipping Categories'); ?>:</label>
					<textarea id="name="foxyshop_ship_categories" name="foxyshop_ship_categories" rows="3" cols="40" wrap="auto"><?php echo $foxyshop_settings['ship_categories']; ?></textarea><br />
					<div class="small"><?php _e('These categories should correspond to the category codes you set up in your FoxyCart admin and will be available in a drop-down on your product setup page. Separate by line break. If you only use one main, default category this is not required.','category-code-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_version"><?php _e('FoxyCart Version'); ?>:</label> 
					<select name="foxyshop_version">
					<?php
					$versionArray = array('0.70', '0.7.1');
					foreach ($versionArray as $version1) {
						echo '<option value="' . $version1 . '"' . ($foxyshop_settings['version'] == $version1 ? ' selected="selected"' : '') . '>' . $version1 . '  </option>'."\n";
					} ?>
					</select><br />
					<div class="small"><?php _e('Version 0.7.1 and up includes the images in shopping cart feature.', 'version-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_use_jquery" name="foxyshop_use_jquery"<?php echo checked($foxyshop_settings['use_jquery'], "on"); ?> />
					<label for="foxyshop_use_jquery"><?php _e('Automatically Insert jQuery 1.4.2 from Google CDN', 'jquery-insert-label'); ?></label>
					<div class="small"><?php _e('If you are already manually inserting jQuery you can uncheck this. Please note that currently FoxyCart\'s Colorbox has a problem with jQuery 1.4.3 and above so we\'ll use 1.4.2. This will be upgraded to the latest stable version once the Colorbox issue is resolved. Additionally it should be noted that jQuery needs to be inserted before wp_head() is called or FoxyCart won\'t function properly.','jquery-help'); ?></div>
				</td>
			</tr>
		</tbody>
	</table>
	
	<br /><br />

	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Advanced Settings','advanced-title'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_shipto" name="foxyshop_enable_ship_to"<?php echo checked($foxyshop_settings['enable_ship_to'], "on"); ?> />
					<label for="foxyshop_shipto"><?php _e('Enable Multiple Shipping Recipients','ship-to-label'); ?></label>
					<div class="small"><?php _e('Remember that FoxyCart charges an extra fee for this service. You must enable it on your FoxyCart account or it will not work.','ship-to-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_custom_file_uploads" name="foxyshop_enable_custom_file_uploads"<?php echo checked($foxyshop_settings['enable_custom_file_uploads'], "on"); ?> />
					<label for="foxyshop_enable_custom_file_uploads"><?php _e('Enable Custom File Uploading','file-upload-label'); ?></label>
					<div class="small"><?php _e('Allow customers to upload custom files as a product variation.','file-upload-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_bundled_products" name="foxyshop_enable_bundled_products"<?php echo checked($foxyshop_settings['enable_bundled_products'], "on"); ?> />
					<label for="foxyshop_enable_bundled_products"><?php _e('Enable Bundled Products'); ?></label>
					<div class="small"><?php _e('Allow multiple items to be added to the cart at once (extra items will be added with a price of $0.00)','bundled-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_subscriptions" name="foxyshop_enable_subscriptions"<?php echo checked($foxyshop_settings['enable_subscriptions'], "on"); ?> />
					<label for="foxyshop_enable_subscriptions"><?php _e('Enable Subscriptions'); ?></label>
					<div class="small"><?php _e('Show fields to allow the creation of subscription products.','subscription-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<span><?php _e('Default Weight Type'); ?>:</span>
					<input type="radio" id="foxyshop_weight_type_english" name="foxyshop_weight_type" value="english"<?php echo checked($foxyshop_settings['weight_type'], "english"); ?> />
					<label for="foxyshop_weight_type_english"></label>
					<input type="radio" id="foxyshop_weight_type_metric" name="foxyshop_weight_type" value="metric"<?php echo checked($foxyshop_settings['weight_type'], "metric"); ?> />
					<label for="foxyshop_weight_type_metric"><?php _e('Metric', 'weight-type-metric'); ?></label>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_default_weight"><?php _e('Default Weight'); ?>:</label> <input type="text" id="foxyshop_default_weight" name="foxyshop_default_weight" value="<?php echo $foxyshop_settings['default_weight']; ?>" size="10" /> <?php _e('(lbs/kg)','lbs/kg'); ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_max_variations"><?php _e('Maximum Variations'); ?>:</label> <input type="text" id="foxyshop_max_variations" name="foxyshop_max_variations" value="<?php echo $foxyshop_settings['max_variations']; ?>" size="10" />
					<div class="small"><?php _e('This is an arbitrary number to save resources and should cover you in most cases. Raise it only if you need to.','max-variations-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_ga"><?php _e('Google Analytics Code'); ?>:</label> <input type="text" id="foxyshop_ga" name="foxyshop_ga" value="<?php echo $foxyshop_settings['ga']; ?>" size="20" />
					<div class="small"><?php _e('Enter your UA code here (example: UA-XXXXXXXX-X) and Google Analytics tracking will be installed in the footer. Additionally, it will only run if the visitor is not a logged-in WordPress user so that admin usage won\'t be tracked.','ga-code-help'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_default_image"><?php _e('Default Image','missing-image-url-title'); ?>:</label> <input type="text" id="foxyshop_default_image" name="foxyshop_default_image" value="<?php echo $foxyshop_settings['default_image']; ?>" size="100" />
					<div class="small"><?php _e('Enter the URL for the image that will be shown if no image is loaded. Or leave the default, it\'s up to you. (If you change the website URL, though, you\'ll have to come back and change it here.)'); ?></div>
				</td>
			</tr>
		</tbody>
	</table>
	
	
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings','settings-submit') ?>" /></p>

	<input type="hidden" name="action" value="foxyshop_settings_update" />
	<?php wp_nonce_field('update-foxyshop-options'); ?>
	</form>




	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Product Feed','product-feed-title'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<?php _e('If you would like to <a href="http://www.google.com/merchants" target="_blank">submit your products to Google</a>, you may do so by copying and pasting the contents of this box into a txt file and submitting it.','product-feed-help'); ?>
					
					<form>
					<textarea name="feedtext" style="width: 100%; height: 200px;" onclick="this.select();" wrap="off"><?php include('productfeed.php'); ?></textarea>
					</form>
				</td>
			</tr>
		</tbody>
	</table>

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
		"ga" => "",
		"default_image" => FOXYSHOP_DIR."/images/no-photo.png",
		"api_key" => "sp92fx".hash_hmac('sha256',rand(21654,6489798),"dkjw82j1".time())
	);
	update_option("foxyshop_settings", serialize($foxyshop_settings));
}
?>