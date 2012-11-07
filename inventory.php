<?php
//Exit if not called in proper context
if (!defined('ABSPATH')) exit();

add_action('admin_init', 'foxyshop_inventory_update');

function foxyshop_inventory_update() {

	//Saving Values From Page
	if (isset($_POST['variationact'])) {
		if (!check_admin_referer('update-foxyshop-inventory')) return;

		for ($i=1; $i<=(int)$_POST['total_form_fields']; $i++) {
			$code = $_POST["code_$i"];
			$productid = $_POST["productid_$i"];
			$original_count = (int)$_POST["original_count_$i"];
			$new_count = (int)$_POST["new_count_$i"];
			$count_change = $new_count - $original_count;
			if ($_POST["new_count_$i"] == "") continue;
			if ($original_count == $new_count) continue;

			$inventory = get_post_meta($productid, "_inventory_levels", 1);
			if (!is_array($inventory)) $inventory = array();

			$db_count = $inventory[$code]['count'];
			$inventory[$code]['count'] = $db_count + $count_change;
			update_post_meta($productid, '_inventory_levels', $inventory);
		}
		header('Location: edit.php?post_type=foxyshop_product&page=foxyshop_inventory_management_page&saved=1');
		die;

	//Saving Values From Uploaded Data
	} elseif (isset($_POST['foxyshop_inventory_updates'])) {
		if (!check_admin_referer('import-foxyshop-inventory-updates')) return;

		$lines = preg_split("/(\r\n|\n|\r)/", $_POST['foxyshop_inventory_updates']);
		$save_count = 0;
		foreach ($lines as $line) {
			$line = explode("\t", $line);
			if (count($line) < 5) continue;
			if ($line[0] == "ID") continue;

			$productid = (int)$line[0];
			$productcode = $line[2];
			$newcount = (int)$line[4];

			foxyshop_inventory_count_update($productcode, $newcount, $productid);
			$save_count++;
		}
		header('Location: edit.php?post_type=foxyshop_product&page=foxyshop_inventory_management_page&importcompleted='.$save_count);
		die;
	}
}


add_action('admin_menu', 'foxyshop_inventory_menu');
function foxyshop_inventory_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Inventory Management'), __('Inventory'), apply_filters('foxyshop_inventory_perm', 'manage_options'), 'foxyshop_inventory_management_page', 'foxyshop_inventory_management_page');
}

function foxyshop_inventory_management_page() {
	global $foxyshop_settings, $wp_version;
	?>
	<div class="wrap">
		<div class="icon32" id="icon-tools"><br></div>
		<h2>Manage Inventory Levels</h2>

		<?php
		//Confirmation Saved
		if (isset($_GET['saved'])) echo '<div class="updated"><p>' . __('Your New Inventory Levels Have Been Saved.') . '</p></div>';

		//Import Completed
		if (isset($_GET['importcompleted'])) echo '<div class="updated"><p>' . sprintf(__('Import completed: %s records updated.'), (int)$_GET['importcompleted']) . '</p></div>';
		?>

		<form action="edit.php" method="post">

		<table cellpadding="0" cellspacing="0" border="0" class="wp-list-table widefat foxyshop-list-table" id="inventory_level" style="margin-top: 14px;">
			<thead>
				<tr>
					<th><span><?php _e('ID'); ?></span><span class="sorting-indicator"></span></th>
					<th><span><?php _e('Name'); ?></span><span class="sorting-indicator"></span></th>
					<th><span><?php _e('Code'); ?></span><span class="sorting-indicator"></span></th>
					<th><span><?php _e('Variation'); ?></span><span class="sorting-indicator"></span></th>
					<th><span><?php _e('Update'); ?></span><span class="sorting-indicator"></span></th>
					<th><span><?php _e('Stock Lvl'); ?></span><span class="sorting-indicator"></span></th>
					<th><span><?php _e('Alert Lvl'); ?></span><span class="sorting-indicator"></span></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php _e('ID'); ?></th>
					<th><?php _e('Name'); ?></th>
					<th><?php _e('Code'); ?></th>
					<th><?php _e('Variation'); ?></th>
					<th><?php _e('Update'); ?></th>
					<th><?php _e('Stock Lvl'); ?></th>
					<th><?php _e('Alert Lvl'); ?></th>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$args = array('post_type' => 'foxyshop_product', 'post_status' => 'publish', 'numberposts' => "-1", "orderby" => "id", "order" => "ASC", "meta_key" => "_inventory_levels", "meta_compare" => "!=", "meta_value" => "");
			$product_list = get_posts($args);
			$exported = "ID\tName\tCode\tVariation\tInventory";
			$i = 0;
			$alternate = "";
			foreach ($product_list as $single_product) {
				$product = foxyshop_setup_product($single_product, true);
				$inventory_levels = get_post_meta($single_product->ID,'_inventory_levels',TRUE);
				if (!is_array($inventory_levels)) $inventory_levels = array();
				foreach ($inventory_levels as $ivcode => $iv) {

					$i++;
					if (!isset($iv['alert'])) $iv['alert'] = $foxyshop_settings['inventory_alert_level'];
					$inventory_alert = (int)($iv['alert'] == '' ? $foxyshop_settings['inventory_alert_level'] : $iv['alert']);
					$inventory_count = $iv['count'];

					$variation = "&nbsp;";
					foreach ($product['variations'] as $product_variation) {
						$product_variation1 = preg_split("/(\r\n|\n)/", $product_variation['value']);
						foreach ($product_variation1 as $product_variation2) {
							if (strpos($product_variation2, "c:" . $ivcode) !== false) $variation = str_replace("*", "", substr($product_variation2,0,strpos($product_variation2,"{")));
						}
					}

					$exported .= "\n";
					$exported .= $product['id'] . "\t";
					$exported .= str_replace("\t", "", $product['name']) . "\t";
					$exported .= str_replace("\t", "", $ivcode) . "\t";
					$exported .= str_replace("\t", "", $variation) . "\t";
					$exported .= $inventory_count;

					$grade = "A";
					if ($inventory_count <= $inventory_alert) $grade = "X";
					if ($inventory_count <= 0) $grade = "U";
					echo '<tr>'."\n";
					echo '<td><strong>' . $product['id'] . '</strong></td>'."\n";
					echo '<td><strong><a href="post.php?post=' . $product['id'] . '&action=edit" tabindex="1">' . $product['name'] . '</a></strong></td>'."\n";
					echo '<td>' . $ivcode . '<input type="hidden" name="original_count_' . $i . '" value="' . $inventory_count . '" /><input type="hidden" name="productid_' . $i . '" value="' . $single_product->ID . '" /><input type="hidden" name="code_' . $i . '" value="' . $ivcode . '" /></td>'."\n";
					echo '<td>' . $variation . '</td>'."\n";
					echo '<td>' . '<input type="text" name="new_count_' . $i . '" value="' . (int)$inventory_count . '" class="inventory_update_width" autocomplete="off" /></td>'."\n";
					echo '<td class="inventory' . $grade . '">' . $inventory_count . '</td>'."\n";
					echo '<td>' . $inventory_alert . '</td>'."\n";
					echo '</tr>'."\n";
				}
			}
			?>
			</tbody>
		</table>
		<input type="hidden" name="variationact" value="save" />
		<input type="hidden" name="total_form_fields" value="<?php echo $i; ?>" />
		<?php wp_nonce_field('update-foxyshop-inventory'); ?>

		<div style="clear: both;"></div>
		<p style="clear: both; margin-top: 15px;"><input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /> <em><strong>SmartUpdate:</strong> Saving will not overwrite any inventory values that have changed since the page was loaded (new orders, other updates).</em></p>
		</form>

		<br /><br />

		<?php
		$export_icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACH0lEQVR4nKWSTUhUYRSGnzv33snRdEY0M0dJaSDTzRBkiZhZkNCiTZC1CIQQahFEq9q3aBFRqzZFkUQRuayFEKlZhv0ZGsUw6VD4N5rj5KjT3O9+p4VphCVKZ3PO5nl4Obzwn2M0tLSdBE6vk7vedfvETQDjQOs9qT1Suy66t72XEn8y2Hb51KglIiwk59YlEJHl29paMMPEm4/4sn2rQspOkAi2Exg9TGizQ372/ByAVZSX5tihbYTD4X/C8fkxWp80URZSjA1/JnesGiZ2LSZYS+Svs0MEcjZSWDTNpiKDl11vSXmmioHkqoKekQ4exe4ykorxLT1Dmcpg2x727PXR3Rn90PigouSvAldcLjxrITbXjzdvGl+xy3Y/OMrGMAy8tkFDY8Ds6ZqN/iEQEdJqnrOdzUwuRCktzKK8oA7LtDFNzZDqxDAMDMNDqRlGpC/LAtBa47ouIsKtwatMJL+wu6Se0fQkjwfekevNI60WCFULhqEoZAcPu185rnZLPUsCpRRKKSKJQcr9lbye6qcpeJz7+19wo66D6vydJGc8+N0Kevri5KS21Dw9Ohy3AJRSOI6D1hqfmY3X3sD58DVMLLQStHZIOd+pyjrIwKcogalKiuM1w9DNCsG5qkuICNrVKFForRERzoQucid2hXBuPbOJIBmc3z2IRCJkMhlEZBH+BS1Vdmnvoxl+wHMdWX78omA8SWT8/Vo6tWJ+AquVAo19QSjUAAAAAElFTkSuQmCC";
		?>
		<form method="post" name="foxyshop_inventory_import_form" action="">
		<table class="widefat">
			<thead>
				<tr>
					<th><img src="<?php echo $export_icon; ?>" alt="" /><?php _e('Import New Inventory Values'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<p>
							Copy and paste these values into Excel. Make your changes, then copy and paste back in and click update.<br />
							You can also add new inventory levels by using the template to add new rows with code and inventory fields.
						</p>
						<textarea id="name="foxyshop_inventory_updates" name="foxyshop_inventory_updates" wrap="auto" style="float: left; width:650px;height: 200px;"><?php echo $exported; ?></textarea>
						<div style="clear: both;"></div>
						<p><input type="submit" class="button-primary" value="<?php _e('Update Inventory Values'); ?>" /></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php wp_nonce_field('import-foxyshop-inventory-updates'); ?>
		</form>


	</div>

<script type="text/javascript" src="<?php echo FOXYSHOP_DIR; ?>/js/jquery.tablesorter.js"></script>
<script type="text/javascript">
jQuery(document).ready(function($){
	$(".inventory_update_width").blur(function() {
		$(this).val(foxyshop_format_number_single($(this).val()));
		$(this).parents("tr").removeClass("inventory_update_width_highlight");
	});
	$(".inventory_update_width").focus(function() {
		$(this).parents("tr").addClass("inventory_update_width_highlight");
	});
	$("#inventory_level").tablesorter({
		'cssDesc': 'asc sorted',
		'cssAsc': 'desc sorted'
	});
});
function foxyshop_format_number_single(num) { num = num.toString().replace(/\$|\,/g,''); if(isNaN(num)) num = "0"; sign = (num == (num = Math.abs(num))); num = Math.floor(num*100+0.50000000001); cents = num%100; num = Math.floor(num/100).toString(); if(cents<10) cents = "0" + cents; for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++) num = num.substring(0,num.length-(4*i+3))+','+ num.substring(num.length-(4*i+3)); return (((sign)?'':'-') + num); }
</script>


<?php
}
?>