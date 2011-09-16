<?php
add_action('admin_menu', 'foxyshop_inventory_menu');

function foxyshop_inventory_menu() {
	add_submenu_page('edit.php?post_type=foxyshop_product', __('Inventory Management'), __('Inventory'), 'manage_options', 'foxyshop_inventory_management_page', 'foxyshop_inventory_management_page');
}

function foxyshop_inventory_management_page() {
	global $foxyshop_settings, $wp_version;
	
	foxyshop_list_table_setup("inventory");
	?>
	
	<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function($) {
		$('.foxyshop_table_list').dataTable({
			"bJQueryUI": true,
			"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
			"iDisplayLength": 25
		});
	});
	</script>

	
	<div class="wrap">
		<h2>View Inventory Levels</h2>

		<table cellpadding="0" cellspacing="0" border="0" class="display foxyshop_table_list" id="inventory_level">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Code</th>
					<th>Variation</th>
					<th>Stock Lvl</th>
					<th>Alert Lvl</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$args = array('post_type' => 'foxyshop_product', 'post_status' => 'publish', 'numberposts' => "-1", "orderby" => "id", "order" => "ASC", "meta_key" => "_inventory_levels", "meta_compare" => "!=", "meta_value" => "");
			$product_list = get_posts($args);
			foreach ($product_list as $single_product) {
				$product = foxyshop_setup_product($single_product);
				$inventory_levels = maybe_unserialize(get_post_meta($single_product->ID,'_inventory_levels',TRUE));
				if (!is_array($inventory_levels)) $inventory_levels = array();
				foreach ($inventory_levels as $ivcode => $iv) {
					
					$inventory_alert = (int)($iv['alert'] == '' ? $foxyshop_settings['inventory_alert_level'] : $iv['alert']);
					$inventory_count = $iv['count'];
					
					$variation = "";
					foreach ($product['variations'] as $product_variation) {
						$product_variation1 = preg_split("/(\r\n|\n)/", $product_variation['value']);
						foreach ($product_variation1 as $product_variation2) {
							if (strpos($product_variation2, "c:" . $ivcode) !== false) $variation = str_replace("*", "", substr($product_variation2,0,strpos($product_variation2,"{")));
						}
					}
					
					$grade = "A";
					if ($inventory_count <= $inventory_alert) $grade = "X";
					if ($inventory_count <= 0) $grade = "U";
					
					echo '<tr class="grade' . $grade . '">';
					echo '<td>' . $product['id'] . '</td>';
					echo '<td><a href="post.php?post=' . $product['id'] . '&action=edit">' . $product['name'] . '</a></td>';
					echo '<td>' . $ivcode . '</td>';
					echo '<td>' . $variation . '</td>';
					echo '<td>' . $inventory_count . '</td>';
					echo '<td>' . $inventory_alert . '</td>';
					echo '</tr>'."\n";
				}
			}
		?>
		</tbody></table>
	</div>
<?php
}
?>