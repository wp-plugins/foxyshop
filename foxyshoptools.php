<?php
if (isset($_REQUEST['foxyshop_save_tools'])) add_action('admin_init', 'foxyshop_save_tools');
function foxyshop_save_tools() {
	global $foxyshop_settings;
	
	//Import Settings
	if (isset($_POST['foxyshop_import_settings'])) {
		if (!check_admin_referer('import-foxyshop-settings')) return;

		$encrypt_key = "foxyshop_encryption_key_16";
		$foxyshop_import_settings = str_replace("\n","",$_POST['foxyshop_import_settings']);
		$decrypted = explode("|-|", rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($encrypt_key), base64_decode($foxyshop_import_settings), MCRYPT_MODE_CBC, md5(md5($encrypt_key))), "\0"));
		if (count($decrypted) != 2) {
			header('location: edit.php?post_type=foxyshop_product&page=foxyshop_tools&importerror=1');
			die;
		} else {
			update_option("foxyshop_settings", $decrypted[0]);
			update_option("foxyshop_category_sort", $decrypted[1]);
			header('location: edit.php?post_type=foxyshop_product&page=foxyshop_tools&import=1');
			die;
		}
	
	//Scan For Old Variations
	} elseif (isset($_GET['foxyshop_old_variations_scan'])) {
		if (!check_admin_referer('foxyshop_old_variations_scan')) return;
		$foxyshop_settings['foxyshop_version'] = "2.9";
		update_option("foxyshop_settings", serialize($foxyshop_settings));
		header('location: edit.php?post_type=foxyshop_product&page=foxyshop_tools&oldvars=1');
		die;
	
	//Reset API Key
	} elseif (isset($_GET['foxyshop_api_key_reset'])) {
		if (!check_admin_referer('reset-foxyshop-api-key')) return;
		$foxyshop_settings['api_key'] = "sp92fx".hash_hmac('sha256',rand(21654,6489798),"dkjw82j1".time());
		update_option("foxyshop_settings", serialize($foxyshop_settings));
		header('location: edit.php?post_type=foxyshop_product&page=foxyshop_tools&key=1');
		die;
	}
}

add_action('admin_menu', 'foxyshop_tools_menu');
function foxyshop_tools_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Tools'), __('Tools'), 'manage_options', 'foxyshop_tools', 'foxyshop_tools');
}
function foxyshop_tools() {
	global $foxyshop_settings;
?>
<div id="foxyshop_settings_wrap" class="wrap">

	<div class="icon32" id="icon-tools"><br></div>
	<h2>FoxyShop Tools</h2>
	

	<?php
	//Confirmation Import
	if (isset($_GET['import'])) echo '<div class="updated"><p>' . __('Your Settings Have Been Imported.') . '</p></div>';

	//Import Error
	if (isset($_GET['importerror'])) echo '<div class="error"><p>' . __('There was an error with your import settings and they could not be imported. The decrypted value was invalid.') . '</p></div>';

	//Confirmation Key Reset
	if (isset($_GET['key'])) echo '<div class="updated"><p>' . sprintf(__('Your API Key Has Been Reset: "%s". Please Update FoxyCart With Your New Key.'), $foxyshop_settings['api_key']) . '</p></div>';

	//Confirmation Old Vars
	if (isset($_GET['oldvars'])) echo '<div class="updated"><p>' . __('Scan for old variations has been successfully completed.') . '</p></div>';

	//Confirmation Old Vars
	if (isset($_GET['foxyshop_flush_rewrite_rules'])) echo '<div class="updated"><p>' . __('WordPress rewrite rules have been flushed.') . '</p></div>';


	//Get Export Settings
	$encrypt_key = "foxyshop_encryption_key_16";
	$foxyshop_export_settings = get_option('foxyshop_settings') . "|-|";
	$foxyshop_export_settings .= get_option('foxyshop_category_sort');
	$foxyshop_export_settings = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($encrypt_key), $foxyshop_export_settings, MCRYPT_MODE_CBC, md5(md5($encrypt_key))));
	$foxyshop_export_settings = wordwrap($foxyshop_export_settings, 55, "\n", true);

	$recommend_icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAApRJREFUeNqEU01IVFEUPu9n3nszvjuvGadGBpswLZIJzR8mslXQIiiICJxKpVwULiKI2oWb0iBo06YgKWih6KJFbUrDwlxYDISRkQtNKcbNkGY6+pr37rud+3xOo/Rz4Hvv3HvP951z7o8Am+z2EQlqokLxVIPDIEUZiNRhA+in1xeO99ogt+0T3QGntNSs+YwVyGF0U4eu3utYys4tjT64rrAiAW5ya63okkX8CAgJYdqF9dbyhsP1jArEH4wItg2S44nr6lqVMvzdLkmanqg4cCzJB9Pp4YmfFMa5QFQXQPWY8r/ITRe6TwsgE9sylyfHhifzlA3uDIsbAv8k0EGCpLK+vfuUICpk8cvEx2d9D6eIkLMMTejE9e9e3AvEULFADHFC1Eh13bmuNsakIKMUSnc3Js7ebEpszvKy64y0LtDIdxpLJtHqZOX25NH91AYCsLaTq4uLUBIKQW5hoUD+MTeVsR2gbgsWZS0NzVea9bKKWN40YSVnAsXtppjdQaiaBhoh7lz/rcuP10XwDJ66AjYFUrI1HsthJtuywMrnN4AYBoRw3q/r0H6j5+TyfHZ5tOfao6U8jLoCeQd8FAMcDsziAjMzx3FvFPf5+uynDwuDfXfTXvZCJSK2oPAAm5ddRGZI5neG+1w8XrUnlLrYeXCbLmT8ivAqoHgXybSAZb9OfwtGYqU2D/ZQqAD/PMHI8yeZ929HMkjrn8GDnME9Pc8FchbrGRy4r5fFd5UbkWhQUQOqviWi+9SAH0TRJ6LxtvbWJUs/j79+d+cNG5r5fSDuM5Bqy6DCUCHhk6BqhwHhqA4hTYawJIKBATJCx3ZWR2ahd2gaxpCTRcxjm9R9Rwg/IoDwwf/NQqzwK4ICzi8BBgBdLjNedsdOVgAAAABJRU5ErkJggg==";
	$google_icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABh0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzT7MfTgAAABZ0RVh0Q3JlYXRpb24gVGltZQAwNy8xNS8xMMjfMS0AAAQRdEVYdFhNTDpjb20uYWRvYmUueG1wADw/eHBhY2tldCBiZWdpbj0iICAgIiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDQuMS1jMDM0IDQ2LjI3Mjk3NiwgU2F0IEphbiAyNyAyMDA3IDIyOjM3OjM3ICAgICAgICAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4YXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iPgogICAgICAgICA8eGFwOkNyZWF0b3JUb29sPkFkb2JlIEZpcmV3b3JrcyBDUzM8L3hhcDpDcmVhdG9yVG9vbD4KICAgICAgICAgPHhhcDpDcmVhdGVEYXRlPjIwMTAtMDctMTVUMTk6MDU6MDFaPC94YXA6Q3JlYXRlRGF0ZT4KICAgICAgICAgPHhhcDpNb2RpZnlEYXRlPjIwMTAtMDctMTVUMTk6MTg6MDBaPC94YXA6TW9kaWZ5RGF0ZT4KICAgICAgPC9yZGY6RGVzY3JpcHRpb24+CiAgICAgIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiCiAgICAgICAgICAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyI+CiAgICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2UvcG5nPC9kYzpmb3JtYXQ+CiAgICAgIDwvcmRmOkRlc2NyaXB0aW9uPgogICA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDBS8igAAAJlSURBVDiNfY9PSFRhFMV/33uvcRzfzKijmQr5jyzFciGFldlGiRCCoAIhqFy0CdpE0Sao3Ei1CtdBuAjDhdBSQiw3SaM5IBpkJTlRaprydJznvO+2sNRJ68CBy733nHuuisfjLVNf4k+fReciI1+TWCa0HLA5VhIAwHOcBR48sMyVlSC/IUohhhHLTCTOWfPz80+GZndFuqdCaAxYg/GYoqd2L1UFGQA50tCwRGfnHz3aMHBs+9DP7OxuKxAI7P64Ksx5gqDWNzx49CZJ75Wi9Yv19SF97x5bEwQcB0+pWsu2bUIBF9GarXg37WwKEgm0UmlzXzJJ1vKyYZimSWNFELSXRncttRl5eJj56hKGT1cxfLqKlXAmIoLyPCyA5v1hmitt+t4vbogayrI36ktHRni+bxpPvPXGBQiuMna7z3LNoqard+srcjlTk8v3pSSLiRSNFUEenysn5DfpmuyifeIhIpL2gmuxe7TMX6jybg7KxeOlXD+RT1mub3NBu7S/a6cj1rF5+S9E1iKovBsD8kNnbTT7r1VysiJIa38r3Z+6dxRuGKQiGOgUWzk05eBql5qcGsJWGDT/pSq79VLOHy3nVHUO4UyT4rCPPaH1V+LLcdoG2uib7vtnAhWb/CoHywsBEOcDOB/AXUAVnwXTj6tdml40MfhtcGeDufiE5Cz1IrOv06f+AozqO6isUqKzUQ73HN5u4EUw9HgHMvMKRNKZ+IYeuw+Soi6/DtuyQdhGQ7tLo6Jlu8FvE3E+A+BpL02oRKFEvTWUyOVkyoztaGL4Uf4CojNREmuJNHGGlzHk077WXxkiNLGzmnadAAAAAElFTkSuQmCC";
	$export_icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACH0lEQVR4nKWSTUhUYRSGnzv33snRdEY0M0dJaSDTzRBkiZhZkNCiTZC1CIQQahFEq9q3aBFRqzZFkUQRuayFEKlZhv0ZGsUw6VD4N5rj5KjT3O9+p4VphCVKZ3PO5nl4Obzwn2M0tLSdBE6vk7vedfvETQDjQOs9qT1Suy66t72XEn8y2Hb51KglIiwk59YlEJHl29paMMPEm4/4sn2rQspOkAi2Exg9TGizQ372/ByAVZSX5tihbYTD4X/C8fkxWp80URZSjA1/JnesGiZ2LSZYS+Svs0MEcjZSWDTNpiKDl11vSXmmioHkqoKekQ4exe4ykorxLT1Dmcpg2x727PXR3Rn90PigouSvAldcLjxrITbXjzdvGl+xy3Y/OMrGMAy8tkFDY8Ds6ZqN/iEQEdJqnrOdzUwuRCktzKK8oA7LtDFNzZDqxDAMDMNDqRlGpC/LAtBa47ouIsKtwatMJL+wu6Se0fQkjwfekevNI60WCFULhqEoZAcPu185rnZLPUsCpRRKKSKJQcr9lbye6qcpeJz7+19wo66D6vydJGc8+N0Kevri5KS21Dw9Ohy3AJRSOI6D1hqfmY3X3sD58DVMLLQStHZIOd+pyjrIwKcogalKiuM1w9DNCsG5qkuICNrVKFForRERzoQucid2hXBuPbOJIBmc3z2IRCJkMhlEZBH+BS1Vdmnvoxl+wHMdWX78omA8SWT8/Vo6tWJ+AquVAo19QSjUAAAAAElFTkSuQmCC";
	$key_icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAhtJREFUeNqEUktrE1EU/iaZtDVILRMSpw9FRUipdKGWVNSFCr6gBBHERxFK2oX4D7oUpA9dunFTQVcWkaILXQhiXVlRi1JtSgO1vttUS6FmJp2Zezx3Uoc8JnrgY7j3nu9xOKMQEf7WxGAdyqqZcZWRZGiMX4wxRj8jKxtUVK8TihK8u3lvalMk3oWw3o7c4gft5/SD3oXXt5JEzjnueaoUJyhKojP5ffzMHS3cYEDkXkHk5xGo2YJAuAPGygak7/VkWWRXtQSDekevVlf7EvZy2r34MTWLxenHiLU+QePuI2js7Il+ezFyJeBDrpHxIzu3QZhM5oREAkuz89hzaRRLmU9wjAwammQbkn4Caww9GDBAwnEBRmRHM97cPAttexPIsaFYM7I3Wm2ELIl8VBKlu0wRa92KWLzFPUsBx867fb4CoY161I0u7IKAoHUh4QkaKzIoHlaM8PzaQWo7dRn57CPIDUk3ElZBTEK6Wza+Ts6scvt1tZycOJ+E+X0UtVqLeyfWeI2WyUQLgpMYyzl8mcyskqDT/DznCTwb2k/7urs8shpJIT024InnFt6625SxGUOd/eac/HNdgfFhdr5wksn3PbJaf5hfBtCemlDWNeS34q9TxocPUOJiH8zPN3ydiwT8SlERCoKsj97Mav2hcueSBTGsojOpihqCEj7KzsdZ6Dembh/7h2EJuRDh3UiiZK7/RK6oPwIMANlx/pat4bMTAAAAAElFTkSuQmCC";
	$misc_icon = "data:image/png;base64,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";
	$remove_icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAH5SURBVDiNfZJPTxNBGMZ/U7aUbaTVg/VQQyKCaEhIlAOEpAaVi0TwZEI9c/DsmS/AV5Cjxj8H5YD1I5CQtOgFTtoDGoM0oaHtbtmZ6YyHte22Jb7Je3n29zzz7jsjnr14aZRWgn91JZWyANVaraMBPHpw3964dbNHA3CUVkLrVkeQKgyLagCe54nK8Um/HwfbK9gBJKym7/Pn9/EFAf1lL47wPZ9q9Www4PHDnLbWdoISw8MABFL2gKnRS9Qb3kCAKP/8dQ4kACiXESfhf9qZGUgmQ0prRKkE1mIzGRgf704gVfckIWBk6xVojZ6fR62vAxDf3maoUEDE4zQ3NrARjxNI1Z0ncw35ZIX4p4+wu4u6ew+bTkPhC8oY9OpTWlczEPE4UkUCABYXkd++Ir7/gDevwXXRWmPv3MbkctDHO4HqXRYA+TxicxNOT8N9JJOwtgZaDaCOlIMioynM9DS2WAx3MzlJzE32jP7fCezREapYRLTfxP4+Q7OzxCYmBtiYlIqebng03r7jvNUimJoiyF6naQyN9x8IanX6+VigFNGuFz7jVSo0hxOY5WVaqyv4IoZfrVLf2aGfj0kpabd/eMjZ3h6+NdilJVRiBJ2+jFlYwLeGWqmId3BA1OMEkWtpuS5O/jlWCMzYGO1vdm4OJ5sFQLkuJuL5C0rrI1wGe+BQAAAAAElFTkSuQmCC";
	?>

	<div style="clear: both; margin-top: 14px;"></div>
	
	<table class="widefat">
		<thead>
			<tr>
				<th><img src="<?php echo $recommend_icon; ?>" alt="" /><?php _e("Recommended Companion Plugins"); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<ul id="foxyshoprecommendedplugins">
						<li><h3><a href="plugin-install.php?tab=search&type=term&s=duplicate+post">Duplicate Post</a></h3> 
						(quickly copy products)
						<?php
						if (is_plugin_active("duplicate-post/duplicate-post.php")) {
							echo '<a href="#" class="button" disabled="disabled" onclick="return false;">Already Installed</a>';
						} else {
							echo '<a href="plugin-install.php?tab=search&type=term&s=duplicate+post" class="button">Install Now</a>';
						} ?>
						</li>

						<li><h3><a href="plugin-install.php?tab=search&type=term&s=taxonomy+images">Taxonomy Images</a></h3> 
						(set category images)
						<?php
						if (is_plugin_active("taxonomy-images/taxonomy-images.php")) {
							echo '<a href="#" class="button" disabled="disabled" onclick="return false;">Already Installed</a>';
						} else {
							echo '<a href="plugin-install.php?tab=search&type=term&s=taxonomy+images" class="button">Install Now</a>';
						} ?>
						</li>

						<li><h3><a href="plugin-install.php?tab=search&type=term&s=custom+field+bulk+editor">Custom Field Bulk Editor</a></h3> 
						(change products in bulk)
						<?php
						if (is_plugin_active("custom-field-bulk-editor/custom-field-bulk-editor.php")) {
							echo '<a href="#" class="button" disabled="disabled" onclick="return false;">Already Installed</a>';
						} else {
							echo '<a href="plugin-install.php?tab=search&type=term&s=custom+field+bulk+editor" class="button">Install Now</a>';
						} ?>
						</li>

						<li><h3><a href="plugin-install.php?tab=search&type=term&s=csv+importer">CSV Importer</a></h3> 
						(import products - <a href="http://www.foxy-shop.com/2011/03/importing-products/" target="_blank">guide here</a>)
						<?php
						if (is_plugin_active("csv-importer/csv-importer.php")) {
							echo '<a href="#" class="button" disabled="disabled" onclick="return false;">Already Installed</a>';
						} else {
							echo '<a href="plugin-install.php?tab=search&type=term&s=csv+importer" class="button">Install Now</a>';
						} ?>
						</li>

						<li><h3><a href="plugin-install.php?tab=search&type=term&s=cimy+user+extra+fields">Cimy User Extra Fields</a></h3> 
						(manage registration form)
						<?php
						if (is_plugin_active("cimy-user-extra-fields/cimy-user-extra-fields.php")) {
							echo '<a href="#" class="button" disabled="disabled" onclick="return false;">Already Installed</a>';
						} else {
							echo '<a href="plugin-install.php?tab=search&type=term&s=cimy+user+extra+fields" class="button">Install Now</a>';
						} ?>
						</li>

						<li><h3><a href="plugin-install.php?tab=search&type=term&s=capsman">Capability Manager</a></h3> 
						(change roles and user abilities)
						<?php
						if (is_plugin_active("capsman/capsman.php")) {
							echo '<a href="#" class="button" disabled="disabled" onclick="return false;">Already Installed</a>';
						} else {
							echo '<a href="plugin-install.php?tab=search&type=term&s=capsman" class="button">Install Now</a>';
						} ?>
						</li>

				</ul>

				</td>
			</tr>
		</tbody>
	</table>
	
	<br /><br />

	<table class="widefat">
		<thead>
			<tr>
				<th><img src="<?php echo $google_icon; ?>" alt="" /><?php _e("Google Product Feed"); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<p>If you would like to <a href="http://www.google.com/merchants" target="_blank">submit your products to Google</a>, you may do so by creating a product feed using this tool. Make sure that you check the option that <a href="http://www.google.com/support/merchants/bin/answer.py?answer=160037" target="_blank">enables double quotes.</a> You also need to make sure that the 'google_product_category' custom field is filled out for each product.</p>
					<p><a href="edit.php?post_type=foxyshop_product&amp;create_google_product_feed=1" class="button-primary">Create Google Product Feed</a></p>

				</td>
			</tr>
		</tbody>
	</table>
	
	<br /><br />

	<form method="post" name="foxyshop_tools_form" action="">
	<table class="widefat">
		<thead>
			<tr>
				<th><img src="<?php echo $export_icon; ?>" alt="" /><?php _e('Import/Export FoxyShop Settings'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="foxyshop_export_settings"><?php echo __('Copy String To Your Clipboard to Export FoxyShop Settings'); ?>:</label> 
					<div style="clear: both;"></div>
					<textarea id="foxyshop_export_settings" name="foxyshop_export_settings" wrap="auto" readonly="readonly" onclick="this.select();" style="float: left; width:500px; line-height: 110%; resize: none; height: 80px; font-family: courier;"><?php echo $foxyshop_export_settings; ?></textarea>
				</td>
			</tr>
			<tr>
				<td>
					<label for="foxyshop_import_settings"><?php echo __('Paste Settings String to Import'); ?>:</label> 
					<div style="clear: both;"></div>
					<textarea id="name="foxyshop_import_settings" name="foxyshop_import_settings" wrap="auto" style="float: left; width:500px;height: 80px; font-family: courier; line-height: 110%; resize: none;"></textarea>
					<div style="clear: both;"></div>
					<p><input type="submit" class="button-primary" value="<?php _e('Import Settings'); ?>" /></p>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="foxyshop_save_tools" value="1" />
	<?php wp_nonce_field('import-foxyshop-settings'); ?>
	</form>
	
	<br /><br />

	<table class="widefat">
		<thead>
			<tr>
				<th><img src="<?php echo $misc_icon; ?>" alt="" /><?php _e("Misc Tools"); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<span>Product pages not showing up?</span> <a href="edit.php?post_type=foxyshop_product&amp;page=foxyshop_tools&amp;foxyshop_flush_rewrite_rules=1" class="button">Flush Rewrite Rules</a>
				</td>
			</tr>
			<tr>
				<td>
					<span>Recently imported products with old variation method?</span> <a href="edit.php?foxyshop_old_variations_scan=1&amp;foxyshop_save_tools=1&amp;_wpnonce=<?php echo wp_create_nonce('foxyshop_old_variations_scan'); ?>" class="button">Scan For Old Variations</a>
				</td>
			</tr>
			<tr>
				<td>
					<span>Need a new API key?</span> <a href="edit.php?foxyshop_api_key_reset=1&amp;foxyshop_save_tools=1&amp;_wpnonce=<?php echo wp_create_nonce('reset-foxyshop-api-key'); ?>" onclick="return apiresetcheck();" class="button">Reset API Key</a>
				</td>
			</tr>
		</tbody>
	</table>
	
	<br /><br />

	<form name="foxyshop_uninstall_form" action="" onsubmit="return false;">
	<table class="widefat">
		<thead>
			<tr>
				<th><img src="<?php echo $remove_icon; ?>" alt="" /><?php _e('Uninstall FoxyShop'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<p>There is not an automated system for uninstalling all the FoxyShop data from your system. FoxyShop only stores a small amount of data in a few option fields - there are no custom database tables. To completely uninstall, go to your Product Categories and delete them all. Then go to your products and delete them all. Then copy and paste the code below into your functions.php file. Deactivate the plugin, upload your functions.php file and load any page. Then you'll be able to remove the code from functions.php and you'll be completely clean.</p>
					<textarea name="foxyshop_uninstall_code" wrap="auto" readonly="readonly" onclick="this.select();" style="float: left; width:500px;height: 85px;">delete_option('foxyshop_settings');
delete_option('foxyshop_category_sort');
delete_option('foxyshop_rewrite_rules');
delete_option('foxyshop_setup_required');</textarea>
				</td>
			</tr>
		</tbody>
	</table>
	</form>

	

	

<script type="text/javascript">
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