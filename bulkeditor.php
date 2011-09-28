<?php
//-------------------------------------------
//Custom Field Bulk Editor Actions
//-------------------------------------------
function foxyshop_cfbe_metabox($post_type) {
	if ($post_type != 'foxyshop_product') return;
	global $foxyshop_settings;
	global $wp_version;
	?>
	<table class="widefat cfbe_table cfbe_foxyshop_table">
		<thead>
			<tr>
				<th><img src="<?php echo FOXYSHOP_DIR . '/images/icon.png'; ?>" alt="" /><?php echo sprintf(__('Set Values For Checked FoxyShop %s'), FOXYSHOP_PRODUCT_NAME_PLURAL); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($foxyshop_settings['ship_categories']) : ?>
			<tr>
				<td>
					<label for="_category" class="cfbe_special_label"><?php _e('Shipping Category'); ?></label>
					<input type="radio" name="_category_status" id="_category_status0" value="0" checked="checked" />
					<label for="_category_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_category_status" id="_category_status1" value="1" />
					<label for="_category_status1"><?php _e("Change To"); ?>:</label>

					<select name="_category" id="_category" onfocus="jQuery('#_category_status1').prop('checked', true);">
						<option value=""><?php _e('Default'); ?></option>
						<?php
						$arrShipCategories = preg_split("/(\r\n|\n)/", $foxyshop_settings['ship_categories']);
						for ($i = 0; $i < count($arrShipCategories); $i++) {
							$shipping_category = explode("|", $arrShipCategories[$i]);
							if (count($shipping_category) > 1) {
								$shipping_category_code = trim($shipping_category[0]);
								$shipping_category_name = trim($shipping_category[1]);
							} else {
								$shipping_category_code = trim($shipping_category[0]);
								$shipping_category_name = trim($shipping_category[0]);
							}
							echo '<option value="' . esc_attr($shipping_category_code) . '"';
							if (esc_attr($shipping_category_code == $_category)) echo ' selected="selected"';
							echo '>' . esc_attr($shipping_category_name) . '</option>';
							echo "\n";
						}
						?>
					</select>

					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php else : ?>
				<input type="hidden" name="_category_status" value="0" />
			<?php endif; ?>
			<tr>
				<td>
					<label for="_saleprice" class="cfbe_special_label"><?php _e('Sale Price'); ?></label>
					<input type="radio" name="_saleprice_status" id="_saleprice_status0" value="0" checked="checked" />
					<label for="_saleprice_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_saleprice_status" id="_saleprice_status1" value="1" />
					<label for="_saleprice_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_saleprice" id="_saleprice" value="" class="cfbe_field_name" onfocus="jQuery('#_saleprice_status1').prop('checked', true);" />
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_salestartdate" class="cfbe_special_label"><?php _e('Sale Start Date'); ?></label>
					<input type="radio" name="_salestartdate_status" id="_salestartdate_status0" value="0" checked="checked" />
					<label for="_salestartdate_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_salestartdate_status" id="_salestartdate_status1" value="1" />
					<label for="_salestartdate_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_salestartdate" id="_salestartdate" value="" class="cfbe_field_name" onfocus="jQuery('#_salestartdate_status1').prop('checked', true);" />
					<small>mm/dd/yy</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_saleenddate" class="cfbe_special_label"><?php _e('Sale End Date'); ?></label>
					<input type="radio" name="_saleenddate_status" id="_saleenddate_status0" value="0" checked="checked" />
					<label for="_saleenddate_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_saleenddate_status" id="_saleenddate_status1" value="1" />
					<label for="_saleenddate_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_saleenddate" id="_saleenddate" value="" class="cfbe_field_name" onfocus="jQuery('#_saleenddate_status1').prop('checked', true);" />
					<small>mm/dd/yy</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_price" class="cfbe_special_label"><?php _e('Base Price'); ?></label>
					<input type="radio" name="_price_status" id="_price_status0" value="0" checked="checked" />
					<label for="_price_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_price_status" id="_price_status1" value="1" />
					<label for="_price_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_price" id="_price" value="" class="cfbe_field_name" onfocus="jQuery('#_price_status1').prop('checked', true);" />
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_weight" class="cfbe_special_label"><?php _e('Weight'); ?></label>
					<input type="radio" name="_weight_status" id="_weight_status0" value="0" checked="checked" />
					<label for="_weight_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_weight_status" id="_weight_status1" value="1" />
					<label for="_weight_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_weight" id="_weight" value="" class="cfbe_field_name" onfocus="jQuery('#_weight_status1').prop('checked', true);" />
					<small>enter 5 lbs, 2 oz as "5 2"</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			
			
			<tr>
				<td>
					<label for="_quantity_min" class="cfbe_special_label"><?php _e('Minimum Qty'); ?></label>
					<input type="radio" name="_quantity_min_status" id="_quantity_min_status0" value="0" checked="checked" />
					<label for="_quantity_min_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_quantity_min_status" id="_quantity_min_status1" value="1" />
					<label for="_quantity_min_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_quantity_min" id="_quantity_min" value="" class="cfbe_field_name" onfocus="jQuery('#_quantity_min_status1').prop('checked', true);" style="width: 46px;" />
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_quantity_max" class="cfbe_special_label"><?php _e('Maximum Qty'); ?></label>
					<input type="radio" name="_quantity_max_status" id="_quantity_max_status0" value="0" checked="checked" />
					<label for="_quantity_max_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_quantity_max_status" id="_quantity_max_status1" value="1" />
					<label for="_quantity_max_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_quantity_max" id="_quantity_max" value="" class="cfbe_field_name" onfocus="jQuery('#_quantity_max_status1').prop('checked', true);" style="width: 46px;" />
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_quantity_hide" class="cfbe_special_label"><?php _e('Hide Quantity'); ?></label>
					<input type="radio" name="_quantity_hide_status" id="_quantity_hide_status0" value="0" checked="checked" />
					<label for="_quantity_hide_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_quantity_hide_status" id="_quantity_hide_status1" value="1" />
					<label for="_quantity_hide_status1" class="cfbe_leave_unchanged"><?php _e("Hide Quantity Box"); ?></label>
					<input type="radio" name="_quantity_hide_status" id="_quantity_hide_status2" value="2" style="margin-bottom: 11px;" />
					<label for="_quantity_hide_status2"><?php _e("Show Quantity Box"); ?></label>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_hide_product" class="cfbe_special_label"><?php _e('Hide From List View'); ?></label>
					<input type="radio" name="_hide_product_status" id="_hide_product_status0" value="0" checked="checked" />
					<label for="_hide_product_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_hide_product_status" id="_hide_product_status1" value="1" />
					<label for="_hide_product_status1" class="cfbe_leave_unchanged"><?php _e("Hide"); echo " ".FOXYSHOP_PRODUCT_NAME_SINGULAR; ?></label>
					<input type="radio" name="_hide_product_status" id="_hide_product_status2" value="2" style="margin-bottom: 11px;" />
					<label for="_hide_product_status2"><?php _e("Show"); echo " ".FOXYSHOP_PRODUCT_NAME_SINGULAR; ?></label>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_discount_quantity_amount" class="cfbe_special_label"><?php _e('Discount Qty $'); ?></label>
					<input type="radio" name="_discount_quantity_amount_status" id="_discount_quantity_amount_status0" value="0" checked="checked" />
					<label for="_discount_quantity_amount_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_discount_quantity_amount_status" id="_discount_quantity_amount_status1" value="1" />
					<label for="_discount_quantity_amount_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_discount_quantity_amount" id="_discount_quantity_amount" value="" class="cfbe_field_name" onfocus="jQuery('#_discount_quantity_amount_status1').prop('checked', true);" style="width: 300px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/coupons_and_discounts" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_discount_quantity_percentage" class="cfbe_special_label"><?php _e('Discount Qty %'); ?></label>
					<input type="radio" name="_discount_quantity_percentage_status" id="_discount_quantity_percentage_status0" value="0" checked="checked" />
					<label for="_discount_quantity_percentage_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_discount_quantity_percentage_status" id="_discount_quantity_percentage_status1" value="1" />
					<label for="_discount_quantity_percentage_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_discount_quantity_percentage" id="_discount_quantity_percentage" value="" class="cfbe_field_name" onfocus="jQuery('#_discount_quantity_percentage_status1').prop('checked', true);" style="width: 300px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/coupons_and_discounts" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_discount_price_amount" class="cfbe_special_label"><?php _e('Discount Price $'); ?></label>
					<input type="radio" name="_discount_price_amount_status" id="_discount_price_amount_status0" value="0" checked="checked" />
					<label for="_discount_price_amount_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_discount_price_amount_status" id="_discount_price_amount_status1" value="1" />
					<label for="_discount_price_amount_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_discount_price_amount" id="_discount_price_amount" value="" class="cfbe_field_name" onfocus="jQuery('#_discount_price_amount_status1').prop('checked', true);" style="width: 300px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/coupons_and_discounts" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_discount_price_percentage" class="cfbe_special_label"><?php _e('Discount Price %'); ?></label>
					<input type="radio" name="_discount_price_percentage_status" id="_discount_price_percentage_status0" value="0" checked="checked" />
					<label for="_discount_price_percentage_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_discount_price_percentage_status" id="_discount_price_percentage_status1" value="1" />
					<label for="_discount_price_percentage_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_discount_price_percentage" id="_discount_price_percentage" value="" class="cfbe_field_name" onfocus="jQuery('#_discount_price_percentage_status1').prop('checked', true);" style="width: 300px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/coupons_and_discounts" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php if ($foxyshop_settings['enable_subscriptions']) : ?>
			<tr>
				<td>
					<label for="_sub_frequency" class="cfbe_special_label"><?php _e('Sub. Frequency'); ?></label>
					<input type="radio" name="_sub_frequency_status" id="_sub_frequency_status0" value="0" checked="checked" />
					<label for="_sub_frequency_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_sub_frequency_status" id="_sub_frequency_status1" value="1" />
					<label for="_sub_frequency_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_sub_frequency" id="_sub_frequency" value="" class="cfbe_field_name" onfocus="jQuery('#_sub_frequency_status1').prop('checked', true);" style="width: 46px;" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/cheat_sheet#subscription_product_options" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_sub_startdate" class="cfbe_special_label"><?php _e('Sub. Start Date'); ?></label>
					<input type="radio" name="_sub_startdate_status" id="_sub_startdate_status0" value="0" checked="checked" />
					<label for="_sub_startdate_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_sub_startdate_status" id="_sub_startdate_status1" value="1" />
					<label for="_sub_startdate_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_sub_startdate" id="_sub_startdate" value="" class="cfbe_field_name" onfocus="jQuery('#_sub_startdate_status1').prop('checked', true);" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/cheat_sheet#subscription_product_options" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="_sub_enddate" class="cfbe_special_label"><?php _e('Sub. End Date'); ?></label>
					<input type="radio" name="_sub_enddate_status" id="_sub_enddate_status0" value="0" checked="checked" />
					<label for="_sub_enddate_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_sub_enddate_status" id="_sub_enddate_status1" value="1" />
					<label for="_sub_enddate_status1"><?php _e("Change To"); ?>:</label>
					<input type="text" name="_sub_enddate" id="_sub_enddate" value="" class="cfbe_field_name" onfocus="jQuery('#_sub_enddate_status1').prop('checked', true);" />
					<small>(<a href="http://wiki.foxycart.com/v/0.7.1/cheat_sheet#subscription_product_options" target="_blank"><?php _e('Reference'); ?></a>)</small>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php else : ?>
				<input type="hidden" name="_sub_frequency_status" value="0" />
				<input type="hidden" name="_sub_startdate_status" value="0" />
				<input type="hidden" name="_sub_enddate_status" value="0" />
			<?php endif; ?>
			<tr>
				<td>
					<?php
					$all_product_list = "";
					$args = array('post_type' => 'foxyshop_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC');
					$all_products = get_posts($args);
					foreach ($all_products as $product) {
						$all_product_list .= '<option value="' . $product->ID . '">' . $product->post_title . '</option>'."\n";
					}?>

					<label for="_related_products_list" class="cfbe_special_label"><?php _e('Related Products'); ?></label>
					<input type="radio" name="_related_products_status" id="_related_products_status0" value="0" checked="checked" />
					<label for="_related_products_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_related_products_status" id="_related_products_status1" value="1" />
					<label for="_related_products_status1"><?php _e("Change To"); ?>:</label>
					<select name="_related_products_list[]" id="_related_products_list" data-placeholder="Search for <?php echo FOXYSHOP_PRODUCT_NAME_PLURAL; ?>" style="width: 100%;" class="chzn-select" multiple="multiple">
						<?php echo $all_product_list; ?>
					</select>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php if ($foxyshop_settings['enable_bundled_products']) : ?>
			<tr>
				<td>
					<label for="_bundled_products_list" class="cfbe_special_label"><?php _e('Bundled Products'); ?></label>
					<input type="radio" name="_bundled_products_status" id="_bundled_products_status0" value="0" checked="checked" />
					<label for="_bundled_products_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_bundled_products_status" id="_bundled_products_status1" value="1" />
					<label for="_bundled_products_status1"><?php _e("Change To"); ?>:</label>
					<select name="_bundled_products_list[]" id="_bundled_products_list" data-placeholder="Search for <?php echo FOXYSHOP_PRODUCT_NAME_PLURAL; ?>" style="width: 100%;" class="chzn-select" multiple="multiple">
						<?php echo $all_product_list; ?>
					</select>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php else : ?>
				<input type="hidden" name="_bundled_products_status" value="0" />
			<?php endif; ?>

			<?php if ($foxyshop_settings['enable_addon_products']) : ?>
			<tr>
				<td>
					<label for="_addon_products_list" class="cfbe_special_label"><?php _e('Add-on Products'); ?></label>
					<input type="radio" name="_addon_products_status" id="_addon_products_status0" value="0" checked="checked" />
					<label for="_addon_products_status0" class="cfbe_leave_unchanged"><?php _e("Leave Unchanged"); ?></label>
					<input type="radio" name="_addon_products_status" id="_addon_products_status1" value="1" />
					<label for="_addon_products_status1"><?php _e("Change To"); ?>:</label>
					<select name="_addon_products_list[]" id="_addon_products_list" data-placeholder="Search for <?php echo FOXYSHOP_PRODUCT_NAME_PLURAL; ?>" style="width: 100%;" class="chzn-select" multiple="multiple">
						<?php echo $all_product_list; ?>
					</select>
					<div style="clear: both;"></div>
				</td>
			</tr>
			<?php else : ?>
				<input type="hidden" name="_addon_products_status" value="0" />
			<?php endif; ?>
		</tbody>
	</table>



<script type="text/javascript">
jQuery(document).ready(function($){

	<?php if (version_compare($wp_version, '3.1', '>=')) { ?>
	$("#_salestartdate, #_saleenddate").datepicker({ dateFormat: 'm/d/yy' });
	<?php } ?>
	$(".chzn-select").chosen();
	$(".chzn-container").css("width", "400px");
	$(".chzn-drop").css("width", "399px");
});
</script>

<?php
}
add_action('cfbe_before_metabox', 'foxyshop_cfbe_metabox');

function foxyshop_cfbe_save($post_type, $post_id) {
	if ($post_type != "foxyshop_product") return;
	
	//Generic Fields Needing No Special Treatment
	$fields = array("category", "weight", "discount_quantity_amount", "discount_quantity_percentage", "discount_price_amount", "discount_price_percentage", "sub_frequency", "sub_startdate", "sub_enddate");
	foreach ($fields as $field) {
		if ($_POST['_' . $field . '_status'] == 1) cfbe_save_meta_data('_'.$field, $_POST['_'.$field]);
	}
	
	//All Other Fields
	if ($_POST['_quantity_min_status'] == 1) {
		cfbe_save_meta_data('_quantity_min', (int)$_POST['_quantity_min']);
	}
	if ($_POST['_quantity_max_status'] == 1) {
		cfbe_save_meta_data('_quantity_max', (int)$_POST['_quantity_max']);
	}
	if ($_POST['_price_status'] == 1) {
		$new_price = number_format((double)str_replace("$","",str_replace(",","",$_POST['_price'])),2,".","");
		cfbe_save_meta_data('_price', $new_price);
	}
	if ($_POST['_saleprice_status'] == 1) {
		$new_price = number_format((double)str_replace("$","",str_replace(",","",$_POST['_saleprice'])),2,".","");
		cfbe_save_meta_data('_saleprice', $new_price);
	}
	if ($_POST['_salestartdate_status'] == 1) {
		if (($_salestartdate = strtotime($_POST['_salestartdate'])) === false) cfbe_save_meta_data('_salestartdate',"999999999999999999");
		else cfbe_save_meta_data('_salestartdate', $_salestartdate);
	}
	if ($_POST['_saleenddate_status'] == 1) {
		if (($_saleenddate = strtotime($_POST['_saleenddate'])) === false) cfbe_save_meta_data('_saleenddate',"999999999999999999");
		else cfbe_save_meta_data('_saleenddate', $_saleenddate);
	}
	if ($_POST['_quantity_hide_status'] != 0) {
		cfbe_save_meta_data('_quantity_hide', $_POST['_quantity_hide_status'] == 1 ? "on" : "");
	}
	if ($_POST['_hide_product_status'] != 0) {
		cfbe_save_meta_data('_hide_product', $_POST['_hide_product_status'] == 1 ? "on" : "");
	}
	if ($_POST['_related_products_status'] == 1) {
		if (isset($_POST['_related_products_list'])) {
			cfbe_save_meta_data('_related_products',implode(",",$_POST['_related_products_list']));
		} else {
			cfbe_save_meta_data('_related_products',"");
		}
	}
	if ($_POST['_bundled_products_status'] == 1) {
		if (isset($_POST['_bundled_products_list'])) {
			cfbe_save_meta_data('_bundled_products',implode(",",$_POST['_bundled_products_list']));
		} else {
			cfbe_save_meta_data('_bundled_products',"");
		}
	}
	if ($_POST['_addon_products_status'] == 1) {
		if (isset($_POST['_addon_products_list'])) {
			cfbe_save_meta_data('_addon_products',implode(",",$_POST['_addon_products_list']));
		} else {
			cfbe_save_meta_data('_addon_products',"");
		}
	}
}
add_action('cfbe_save_fields', 'foxyshop_cfbe_save', 10, 2);
?>