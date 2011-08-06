<?php
add_action('admin_menu', 'foxyshop_settings_menu');
add_action('admin_init', 'set_foxyshop_settings');

function foxyshop_settings_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Settings'), __('Manage Settings'), 'manage_options', 'foxyshop_options', 'foxyshop_options');
}

function set_foxyshop_settings() {
	$foxyshop_settings_update_key = (isset($_POST['action']) ? $_POST['action'] : "");
	$foxyshop_api_reset_key = (isset($_GET['action']) ? $_GET['action'] : "");
	if ($foxyshop_settings_update_key == "" && $foxyshop_api_reset_key == "") return;
	if ($foxyshop_settings_update_key == "foxyshop_settings_update" && check_admin_referer('update-foxyshop-options')) {
		global $foxyshop_settings;
		
		//Do initial product sitemap creation
		if ($_POST['foxyshop_generate_product_sitemap'] == "on") foxyshop_create_product_sitemap();
		
		$new_settings = array();
		$fields = array("version","ship_categories","weight_type","enable_ship_to","enable_subscriptions", "enable_bundled_products", "sort_key", "default_image", "use_jquery", "ga", "ga_advanced", "generate_feed", "hide_subcat_children", "generate_product_sitemap", "manage_inventory_levels", "inventory_alert_level", "inventory_alert_email", "enable_sso", "sso_account_required", "browser_title_1", "browser_title_2", "browser_title_3", "browser_title_4", "browser_title_5", "locale_code");
		foreach ($fields as $field1) {
			$val = (isset($_POST['foxyshop_'.$field1]) ? $_POST['foxyshop_'.$field1] : '');
			$new_settings[$field1] = $val;
		}
		$new_settings["domain"] = str_replace("http://","",$_POST['foxyshop_domain']);
		$new_settings["api_key"] = $foxyshop_settings['api_key'];
		$new_settings["foxyshop_version"] = $foxyshop_settings['foxyshop_version'];
		$new_settings["datafeed_url_key"] = $foxyshop_settings['datafeed_url_key'];
		$new_settings["max_variations"] = (int)$_POST['foxyshop_max_variations'];
		$new_settings["default_weight"] = (int)$_POST['foxyshop_default_weight1'] . ' ' . (double)$_POST['foxyshop_default_weight2'];
		$new_settings["products_per_page"] = ((int)$_POST['foxyshop_products_per_page'] == 0 ? -1 : (int)$_POST['foxyshop_products_per_page']);

		update_option("foxyshop_settings", serialize($new_settings));
		header('location: edit.php?post_type=foxyshop_product&page=foxyshop_options&saved=1');
	} elseif ($foxyshop_api_reset_key == "foxyshop_api_key_reset" && check_admin_referer('reset-foxyshop-api-key')) {
		global $foxyshop_settings;
		$foxyshop_settings['api_key'] = "sp92fx".hash_hmac('sha256',rand(21654,6489798),"dkjw82j1".time());
		update_option("foxyshop_settings", serialize($foxyshop_settings));
		header('location: edit.php?post_type=foxyshop_product&page=foxyshop_options&key=1');
	}
}

function foxyshop_options() {
	global $foxyshop_settings;
?>
<div id="foxyshop_settings_wrap" class="wrap">
	

	<?php
	//Confirmation Saved
	if (isset($_GET['saved'])) echo '<div class="updated"><p>' . __('Your Settings Have Been Saved.') . '</p></div>';
	
	//Confirmation Key Reset
	if (isset($_GET['key'])) echo '<div class="updated"><p>' . __('Your API Key Has Been Reset. Please Update FoxyCart With Your New Key.') . '</p></div>';
	
	//Warning PHP Version
	if (version_compare(PHP_VERSION, '5.1.2', "<")) echo '<div class="error"><p>' . __('<strong>Warning:</strong> You are using PHP version ') . PHP_VERSION . __('. FoxyShop requires PHP version 5.1.2 or higher to utilize the required hmac_has() functions. Without upgrading you will experience problems adding items to the cart and completing other tasks. After upgrading, make sure that you reset your API key (scroll to the bottom of the page) to ensure that you have a fully secure key.') . '</p></div>';

	//Warning Header/Footer Missing
	if (!file_exists(TEMPLATEPATH.'/header.php') || !file_exists(TEMPLATEPATH.'/footer.php')) echo '<div class="error"><p>' . __('<strong>Warning:</strong> Your theme does not appear to be using header.php or footer.php. Without these files FoxyShop pages will show up unstyled. This error can often show up if you are using a WordPress framework that is bypassing the get_header() and get_footer() functions.') . '</p></div>';
	
	//Warning Sitemap Not Writeable
	if ($foxyshop_settings['generate_product_sitemap']) {
		$sitemap_filename = FOXYSHOP_DOCUMENT_ROOT.'/sitemap-products.xml';
		if (file_exists($sitemap_filename)) {
			if (!is_writeable($sitemap_filename)) echo '<div class="error"><p><strong>Warning:</strong> ' . FOXYSHOP_DOCUMENT_ROOT.'/sitemap-products.xml not writeable.</p></div>';
		} else {
			$sitemap_directory = FOXYSHOP_DOCUMENT_ROOT;
			if (!is_writeable($sitemap_directory)) echo '<div class="error"><p><strong>Warning:</strong> ' . FOXYSHOP_DOCUMENT_ROOT.'/sitemap-products.xml does not exist and the directory is not writeable.</p></div>';
		}
	}

	//Warning Permalinks
	
	$permalink_structure = get_option('permalink_structure');
	if ($permalink_structure == '/archives/%post_id%' || $permalink_structure == "") {
		echo '<div class="error"><p><strong>Warning:</strong> Your <a href="/wp-admin/options-permalink.php">permalink structure</a> should be set to Day and Name or Month and Name. Other settings will cause difficulties using FoxyShop.</p></div>';
	}
	
	
	
	//Warning Upload Folders
	$upload_dir = wp_upload_dir();
	if ($upload_dir['error'] != '') {
		echo '<div class="error"><p><strong>Warning:</strong> ' . $upload_dir['error'].'</p></div>';
	} elseif (!file_exists($upload_dir['basedir'] . '/customuploads')) {
		if (!is_writeable($upload_dir['basedir'])) echo '<div class="error"><p><strong>Warning:</strong> ' . $upload_dir['basedir'].' is not writeable. You may encounter problems uploading images or allowing the custom upload of files by customers. (To hide this notice, add a folder under <em>wp-content/uploads</em> called <em>customupload</em>.)</p></div>';
	}
	
	?>

	<table class="widefat infoonly" id="foxyshop_settings_header">
		<thead>
			<tr>
				<th>
					<div id="settings_title">FoxyShop <?php echo $foxyshop_settings['foxyshop_version']; ?></div>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="border-bottom: 0 none;">
					<a href="http://www.foxy-shop.com/" target="_blank"><img src="<?php echo FOXYSHOP_DIR; ?>/images/logo.png" alt="FoxyShop" style="float: right; margin-left: 20px;" /></a>
					
					<p>Stay up to date with the latest updates from FoxyShop by following on Twitter and Facebook.</p>
					<a href="http://twitter.com/FoxyShopWP" class="twitter-follow-button" data-show-count="false">Follow @FoxyShopWP</a>
					<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
					<iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode('https://www.facebook.com/pages/FoxyShop/188079417920111'); ?>&amp;layout=button_count&amp;show_faces=false&amp;width=190&amp;action=like&amp;colorscheme=light&amp;font=arial" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:190px; height:26px;"></iframe>
					
					<p>
					<a href="http://www.foxy-shop.com/documentation/" target="_blank" class="button"><?php _e('FoxyShop Documentation'); ?></a>
					<a href="http://affiliate.foxycart.com/idevaffiliate.php?id=211&url=http://www.foxycart.com/" target="_blank" class="button"><?php _e('FoxyCart Information'); ?></a>
					<a href="http://affiliate.foxycart.com/idevaffiliate.php?id=211&url=http://wiki.foxycart.com/" target="_blank" class="button"><?php _e('FoxyCart Wiki'); ?></a>
					<a href="http://affiliate.foxycart.com/idevaffiliate.php?id=211&url=http://admin.foxycart.com/" target="_blank" class="button"><?php _e('FoxyCart Admin Panel'); ?></a>
					
					</p>
				</td>
			</tr>
		</tbody>
	</table>

	<br /><br />
	<form>
	
	<table class="widefat infoonly">
		<thead>
			<tr>
				<th><?php _e('Setup Information (Version '); echo $foxyshop_settings['foxyshop_version'] .")"; ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="border-bottom: 0 none;">
					<label for="foxyshop_key"><?php _e('API Key'); ?>:</label>
					<input type="text" id="foxyshop_key" name="key" value="<?php echo $foxyshop_settings['api_key']; ?>" readonly="readonly" onclick="this.select();" />
					<div class="small" style="margin-bottom: 5px;"><?php echo __('Note: this is a required step for security reasons and utilizes FoxyCart\'s HMAC product verification to avoid link tampering.<br /><span style="color: red;"><strong>Enter this API key on the advanced menu of your FoxyCart admin and check the box to enable cart validation.</strong></span><br />This API key is generated automatically and cannot be edited. Scroll to the bottom of the page if you need to reset the key.'); ?></div>
					
					<div style="clear: both;"></div>

					<label for="foxyshop_datafeed_url"><?php _e('Datafeed URL'); ?>:</label>
					<input type="text" id="foxyshop_datafeed_url" name="foxyshop_datafeed_url" value="<?php echo get_bloginfo('url') . '/foxycart-datafeed-' . $foxyshop_settings['datafeed_url_key']; ?>/" readonly="readonly" onclick="this.select();" />
					
					<div style="clear: both;margin-bottom: 5px;"></div>

					<label for="foxyshop_sso_url"><?php _e('SSO Endpoint'); ?>:</label>
					<input type="text" id="foxyshop_sso_url" name="foxyshop_sso_url" value="<?php echo get_bloginfo('url') . '/foxycart-sso-' . $foxyshop_settings['datafeed_url_key']; ?>/" readonly="readonly" onclick="this.select();" />
					
					<div style="clear: both;margin-bottom: 5px;"></div>

					<label for="foxyshop_theme_dir"><?php _e('Template Path'); ?>:</label>
					<input type="text" id="foxyshop_theme_dir" name="foxyshop_theme_dir" value="<?php echo STYLESHEETPATH; ?>/" readonly="readonly" />
					<div class="small" style="margin-bottom: 5px;"><?php echo __('FoxyShop will look in this folder for customized theme files.'); ?></div>

					<?php if ($foxyshop_settings['generate_product_sitemap']) { ?>
						<label for="foxyshop_sitemap"><?php _e('Sitemap'); ?>:</label>
						<input type="text" id="foxyshop_sitemap" name="foxyshop_sitemap" value="http://<?php echo $_SERVER['SERVER_NAME'] . '/sitemap-products.xml'; ?>" readonly="readonly" onclick="this.select();" />
					<?php } ?>

				</td>
			</tr>
		</tbody>
	</table>
	</form>

	<br /><br />

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
					<label for="foxyshop_use_jquery"><?php echo __('Automatically Insert jQuery ') . FOXYSHOP_JQUERY_VERSION . __(' from Google CDN'); ?></label>
					<div class="small"><?php _e('If you are already manually inserting jQuery you can uncheck this option. If you need a different version, you can define the constant FOXYSHOP_JQUERY_VERSION in your wp-config file with the version you want and we will fetch it from Google.'); ?></div>
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
					<label for="sort_key"><?php echo FOXYSHOP_PRODUCT_NAME_SINGULAR . ' ' . __('Sorting'); ?>:</label> 
					<select name="foxyshop_sort_key" id="sort_key">
					<?php
					$sortArray = array("menu_order" => "Custom Order", "name" => FOXYSHOP_PRODUCT_NAME_SINGULAR . " Name", "price_asc" => "Price (Lowest to Highest)", "price_desc" => "Price (Highest to Lowest)", "date_asc" => "Date (Oldest to Newest)", "date_desc" => "Date (Newest to Oldest)");
					foreach ($sortArray as $key=>$val) {
						echo '<option value="' . $key . '"' . ($foxyshop_settings['sort_key'] == $key ? ' selected="selected"' : '') . '>' . $val . '  </option>'."\n";
					} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_products_per_page"><?php echo FOXYSHOP_PRODUCT_NAME_PLURAL . ' ' . __('Per Page'); ?>:</label> <input type="text" id="foxyshop_products_per_page" name="foxyshop_products_per_page" value="<?php echo ($foxyshop_settings['products_per_page'] < 0 ? 0 : $foxyshop_settings['products_per_page']); ?>" style="width: 50px;" /> <small><?php _e('Enter 0 to show all products (no paging). Paging does not apply to the All ') . FOXYSHOP_PRODUCT_NAME_SINGULAR . __(' page.'); ?></small>
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

					<label for="foxyshop_browser_title_1" style="width: 112px;"><?php echo __('All ') . FOXYSHOP_PRODUCT_NAME_PLURAL; ?>:</label> <input type="text" name="foxyshop_browser_title_1" value="<?php echo $foxyshop_settings['browser_title_1']; ?>" size="50" />
					<div style="clear: both;"></div>
					<label for="foxyshop_browser_title_2" style="width: 112px;"><?php _e('All Categories'); ?>:</label> <input type="text" name="foxyshop_browser_title_2" value="<?php echo $foxyshop_settings['browser_title_2']; ?>" size="50" />
					<div style="clear: both;"></div>
					<label for="foxyshop_browser_title_3" style="width: 112px;"><?php _e('Single Category'); ?>:</label> <input type="text" name="foxyshop_browser_title_3" value="<?php echo $foxyshop_settings['browser_title_3']; ?>" size="50" /> <small>Use %c for Category Name</small>
					<div style="clear: both;"></div>
					<label for="foxyshop_browser_title_4" style="width: 112px;"><?php echo __('Single ') . FOXYSHOP_PRODUCT_NAME_SINGULAR; ?>:</label> <input type="text" name="foxyshop_browser_title_4" value="<?php echo $foxyshop_settings['browser_title_4']; ?>" size="50" /> <small>Use %p for <?php echo FOXYSHOP_PRODUCT_NAME_SINGULAR; ?> Name</small>
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
					<textarea id="name="foxyshop_ship_categories" name="foxyshop_ship_categories" wrap="auto" style="width:500px;height: 80px;"><?php echo $foxyshop_settings['ship_categories']; ?></textarea><br />
					<div class="small"><?php _e('These categories should correspond to the category codes you set up in your FoxyCart admin and will be available in a drop-down on your ') . strtolower(FOXYSHOP_PRODUCT_NAME_SINGULAR) . __(' setup page. Separate each category with a line break. If you only use one category this is not required. If you would like to also display a nice name in the dropdown menu, use a pipe sign "|" like this: free_shipping|Free Shipping.'); ?></div>
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
					<label for="foxyshop_enable_bundled_products"><?php echo __('Enable Bundled ').FOXYSHOP_PRODUCT_NAME_PLURAL; ?></label>
					<div class="small"><?php _e('Allow multiple items to be added to the cart at once (extra items will be added with a price of $0.00)'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_subscriptions" name="foxyshop_enable_subscriptions"<?php checked($foxyshop_settings['enable_subscriptions'], "on"); ?> />
					<label for="foxyshop_enable_subscriptions"><?php _e('Enable Subscriptions'); ?></label>
					<div class="small"><?php _e('Show fields to allow the creation of subscription ').strtolower(FOXYSHOP_PRODUCT_NAME_PLURAL); ?>.</div>
				</td>
			</tr>

			<tr>
				<td>
					<input type="checkbox" id="foxyshop_enable_sso" name="foxyshop_enable_sso"<?php checked($foxyshop_settings['enable_sso'], "on"); ?> />
					<label for="foxyshop_enable_sso"><?php _e('Enable WordPress Single-Sign-On'); ?></label>
					<div class="small"><?php _e('If enabled, your WordPress users will not have to login again to complete a FoxyCart checkout. WordPress accounts and FoxyCart accounts are kept in sync. You must be using FoxyCart 0.7.1 or above and in the FoxyCart admin you must set the "customer password hash type" to "phpass, portable mode" and the hash config to 8. Check the "enable single sign on" option and put the SSO Endpoint url in the appropriate box.'); ?></div>
					<div style="padding: 0 0 0 15px;">

						<label for="foxyshop_sso_account_required"><?php _e('SSO Type'); ?>:</label> 
						<select name="foxyshop_sso_account_required" id="sort_key">
						<?php
						$sortArray = array("WordPress account optional", "Require WordPress account to check out", "Account required on product-by-product basis");
						foreach ($sortArray as $key=>$val) {
							echo '<option value="' . $key . '"' . ($foxyshop_settings['sso_account_required'] == $key ? ' selected="selected"' : '') . '>' . $val . '  </option>'."\n";
						} ?>
						</select>
					</div>
				</td>
			</tr>


			<tr>
				<td>
					<input type="checkbox" id="foxyshop_manage_inventory_levels" name="foxyshop_manage_inventory_levels"<?php checked($foxyshop_settings['manage_inventory_levels'], "on"); ?> />
					<label for="foxyshop_manage_inventory_levels"><?php _e('Manage Inventory Levels'); ?></label>
					<div class="small"><?php _e('If enabled, you will be able to set inventory levels per ').strtolower(FOXYSHOP_PRODUCT_NAME_SINGULAR). __('code. In the FoxyCart admin, you need to check the box to enable your datafeed and enter the datafeed url from the top of this page in the "datafeed url" box.'); ?></div>
					<div style="padding: 0 0 0 15px;">
						<label for="foxyshop_inventory_alert_level"><?php _e('Default Inventory Alert Level'); ?>:</label> <input type="text" id="foxyshop_inventory_alert_level" name="foxyshop_inventory_alert_level" value="<?php echo $foxyshop_settings['inventory_alert_level']; ?>" style="width: 50px;" />
						<input type="checkbox" id="foxyshop_inventory_alert_email" name="foxyshop_inventory_alert_email"<?php checked($foxyshop_settings['inventory_alert_email'], "on"); ?> style="clear: left;" /><label for="foxyshop_inventory_alert_email"><?php _e('Send Email to Admin When Alert Level Reached'); ?></label>
					</div>
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
					$weight2 = (count($arrweight) > 1 ? (double)$arrweight[1] : "0.0");
					if ($weight1 == 0 && $weight2 == 0) $weight1 = 1;
					?>
					<input type="text" id="foxyshop_default_weight1" name="foxyshop_default_weight1" value="<?php echo $weight1; ?>" style="width: 46px;" /><small id="weight_title1" style="width: 28px;">lbs</small>
					<input type="text" id="foxyshop_default_weight2" name="foxyshop_default_weight2" value="<?php echo $weight2; ?>" style="width: 46px;" /><small id="weight_title2">oz</small>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_max_variations"><?php _e('Maximum Variations'); ?>:</label> <input type="text" id="foxyshop_max_variations" name="foxyshop_max_variations" value="<?php echo $foxyshop_settings['max_variations']; ?>" style="width: 50px;" />  <small>(per <?php echo strtolower(FOXYSHOP_PRODUCT_NAME_SINGULAR); ?>)</small>
					<div class="small"><?php _e('This is an arbitrary number to save resources and should be sufficient in most cases. Raise only if necessary.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_locale_code"><?php _e('Currency Locale Code'); ?>:</label> <input type="text" id="foxyshop_locale_code" name="foxyshop_locale_code" value="<?php echo $foxyshop_settings['locale_code']; ?>" style="width: 150px;" />  <small>(Default: en_US)</small>
					<div class="small"><?php _e('If you would like to use something other than $ for your currency, enter your locale code here. For the British Pound, enter "en_GB". <a href="http://www.roseindia.net/tutorials/I18N/locales-list.shtml" target="_blank">Full list of locale codes here.</a>'); ?></div>
					<?php if (!function_exists('money_format')) echo '<div>' . __('Attention, you are using Windows which does not support internationalization. You will be limited to $ or £.') . '</div>'; ?>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_hide_subcat_children" name="foxyshop_hide_subcat_children"<?php checked($foxyshop_settings['hide_subcat_children'], "on"); ?> />
					<label for="foxyshop_hide_subcat_children"><?php _e('Hide Child ') . FOXYSHOP_PRODUCT_NAME_PLURAL . __(' From Parent Categories (recommended)'); ?></label>
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
					<label for="foxyshop_generate_feed"><?php echo __('Generate ') . FOXYSHOP_PRODUCT_NAME_SINGULAR . __(' Feed'); ?></label>
					<div class="small"><?php echo __('Selecting this option will add a sidebar to the menu which will allow you to export a file suitable for uploading to Google\'s Product Search system.'); ?></div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" id="foxyshop_generate_product_sitemap" name="foxyshop_generate_product_sitemap"<?php checked($foxyshop_settings['generate_product_sitemap'], "on"); ?> />
					<label for="foxyshop_generate_product_sitemap"><?php echo __('Generate ') . FOXYSHOP_PRODUCT_NAME_SINGULAR . __(' Sitemap'); ?></label>
					<div class="small"><?php echo __('If checked, a sitemap file with all of your ') . strtolower(FOXYSHOP_PRODUCT_NAME_SINGULAR) . __(' will be created in your root folder.'); ?></div>
				</td>
			</tr>

		</tbody>
	</table>
	
	
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" /></p>
	
	<p>
		<strong>Reset API Key</strong><br />
		If you believe that your API key has been compromised or would like to reset it with a fresh one, please click this link below and a new one will be created for you: <a href="options.php?action=foxyshop_api_key_reset&_wpnonce=<?php echo wp_create_nonce('reset-foxyshop-api-key'); ?>" onclick="return apiresetcheck();">Reset API Key</a>
	</p>
	
	<p>&nbsp;</p>

	<input type="hidden" name="action" value="foxyshop_settings_update" />
	<?php wp_nonce_field('update-foxyshop-options'); ?>
	</form>

<script type="text/javascript">
jQuery(document).ready(function($){
	$("input[name='foxyshop_weight_type']").change(function() {
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
function apiresetcheck() {
	if (confirm ("Are you sure you want to reset your API Key?\nYou will not be able to recover your old key.")) {
		return true;
	} else {
		return false;
	}
}
</script>
<?php }
?>