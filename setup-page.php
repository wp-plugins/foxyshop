<?php
if (get_option("foxyshop_setup_required")) {
	add_action('admin_notices', 'foxyshop_show_setup_prompt');
}
function foxyshop_show_setup_prompt() {
	if (isset($_GET['hide_setup_prompt']) || isset($_GET['setup'])) return;
	$page = (isset($_GET['page']) ? $_GET['page'] : "");
	if ($page != "foxyshop_setup") echo '<div class="error"><p style="height: 16px;"><img src="' . FOXYSHOP_DIR . '/images/icon.png" alt="" style="float: left; margin: 0 4px 0 0;" /><strong style="float: left; margin-top: 1px;">Your FoxyShop store needs to be synced with your FoxyCart account: <a href="admin.php?page=foxyshop_setup">Setup Now</a></strong><small style="float: right;"><a href="edit.php?post_type=foxyshop_product&page=foxyshop_options&hide_setup_prompt=1">I&rsquo;ll Do It Later</a></small></p></div>';
}


add_action('admin_menu', 'foxyshop_setup_menu');
add_action('admin_init', 'save_foxyshop_setup');

function foxyshop_setup_menu() {
	add_submenu_page(NULL, __('FoxyShop Setup Wizard'), NULL, 'manage_options', 'foxyshop_setup', 'foxyshop_setup');
}

function save_foxyshop_setup() {
	$foxyshop_settings_update_key = (isset($_POST['action']) ? $_POST['action'] : "");
	if ($foxyshop_settings_update_key != "foxyshop_setup_save") return;
	if (!check_admin_referer('save-foxyshop-setup')) return;

	global $foxyshop_settings;

	$foxyshop_settings['domain'] = trim(stripslashes(str_replace("http://","",$_POST['foxyshop_domain'])));
	$foxyshop_settings['version'] = $_POST['foxyshop_version'];

	update_option("foxyshop_settings", serialize($foxyshop_settings));
	delete_option("foxyshop_setup_required");
	header('location: edit.php?post_type=foxyshop_product&page=foxyshop_options&setup=1');
	die;
}


function foxyshop_setup() {
	global $foxyshop_settings;
?>
<div class="wrap">
<div class="icon32" id="icon-options-general"><br></div>
<h2>FoxyShop Setup Wizard</h2>

<a href="http://www.foxy-shop.com/" target="_blank"><img src="<?php echo FOXYSHOP_DIR; ?>/images/logo.png" alt="FoxyShop" style="float: right; margin-left: 20px;" /></a>
<h3>Cool! You've got your new FoxyShop store installed and you are ready to get started.</h3>

<p>The first thing you'll need to do is open up your FoxyCart account in another window so we can copy some information over there. If you don't have a FoxyCart account yet, that's no problem. Here's a short video overview that may help.</p>

<iframe width="640" height="390" src="http://www.youtube.com/embed/TaW1yLbURfc" frameborder="0" allowfullscreen></iframe>

<table width="640" style="margin:10px 0 15px 0;">
<tr>
<td align="center" width="50%" style="border-right: 1px solid lightgray;">
<h3 style="margin-top: .5em;">I haven't created an account yet</h3>
<p><a href="http://affiliate.foxycart.com/idevaffiliate.php?id=211_2_3_3" target="_blank" class="button"><?php _e('Create New FoxyCart Account'); ?></a></p>
</td>
<td align="center" width="50%">
<h3 style="margin-top: .5em;">I already have an account</h3>
<p><a href="http://affiliate.foxycart.com/idevaffiliate.php?id=211&url=http://admin.foxycart.com/" target="_blank" class="button"><?php _e('Login To FoxyCart Account'); ?></a></p>
</td>
</tr>
</table>


<form method="post" name="foxycart_settings_form" action="admin.php" onsubmit="return foxyshop_check_settings_form();">
<input type="hidden" name="action" value="foxyshop_setup_save" />

<table class="widefat foxyshopsetup">
	<thead>
		<tr>
			<th colspan="2"><h2 style="margin: 0; padding: 0;";><?php _e('Step 1: Click on Store / Settings'); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><h3>1A</h3></td>
			<td>
				<label for="foxyshop_domain"><?php _e('Enter Your Full FoxyCart Domain'); ?>:</label>
				<input type="text" name="foxyshop_domain" id="foxyshop_domain" value="<?php echo $foxyshop_settings['domain']; ?>" size="50" />
				<small>Example: yourname.foxycart.com</small>
			</td>
		</tr>
		<tr>
			<td><h3>1B</h3></td>
			<td>
				<label for="foxyshop_version">What FoxyCart version are you using?</label> 
				<select name="foxyshop_version" id="foxyshop_version" style="min-width: 100px;">
				<?php
				$versionArray = array('0.7.2' => '0.7.2', '0.7.1' => '0.7.1', '0.7.0' => '0.7.0');
				foreach ($versionArray as $key => $val) {
					echo '<option value="' . $key . '"' . ($foxyshop_settings['version'] == $key ? ' selected="selected"' : '') . '>' . $val . '  </option>'."\n";
				} ?>
				</select>
				<small>Version 0.7.1 is recommended.</small>
			</td>
		</tr>
	</tbody>
</table>

<br /><br />

<table class="widefat foxyshopsetup infoonly">
	<thead>
		<tr>
			<th colspan="2"><h2 style="margin: 0; padding: 0;";><?php _e('Step 2: Click on Store / Advanced'); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><h3>2A</h3></td>
			<td>
				<div>Check the Box to Enable Cart Validation (REQUIRED). Then replace the existing API key with this one:</div>
				<input type="text" id="foxyshop_key" name="key" value="<?php echo $foxyshop_settings['api_key']; ?>" readonly="readonly" onclick="this.select();" />
			</td>
		</tr>
		<tr>
			<td><h3>2B</h3></td>
			<td>
				<div>Check the Box to Enable Store Datafeed (RECOMMENDED). Then enter this datafeed URL:</div>
				<input type="text" id="foxyshop_datafeed_url" name="foxyshop_datafeed_url" value="<?php echo get_bloginfo('url') . '/foxycart-datafeed-' . $foxyshop_settings['datafeed_url_key']; ?>/" readonly="readonly" onclick="this.select();" />
			</td>
		</tr>
		<tr>
			<td><h3>2C</h3></td>
			<td>
				<div>Set Customer Password Hash Type to "phpass" (STRONGLY RECOMMENDED).</div>
			</td>
		</tr>

	</tbody>
</table>



<p><input type="submit" class="button-primary" value="<?php _e('Save and Get Started!'); ?>" /></p>

<?php wp_nonce_field('save-foxyshop-setup'); ?>
</form>

<script type="text/javascript">
function foxyshop_check_settings_form() {
	var domain_name = jQuery("#foxyshop_domain").val();
	if (domain_name && domain_name.indexOf('.') <= 0) {
		alert('Uh oh! It looks like your domain name might not be entered correctly.\nIt should be your full foxycart domain like this: "yourname.foxycart.com".\nPlease try again or remove your entry for now.');
		jQuery("#foxyshop_domain").focus();
		return false;
	}
	return true;
}
</script>

</div>
<?php }
?>