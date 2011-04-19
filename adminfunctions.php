<?php
//Insert jQuery
function foxyshop_insert_jquery() {
	$jquery_version = "1.4.4";
	wp_deregister_script('jquery');
	wp_register_script('jquery', ("http".($_SERVER['SERVER_PORT'] == 443 ? 's' : '')."://ajax.googleapis.com/ajax/libs/jquery/".$jquery_version."/jquery.min.js"), false, $jquery_version);
	wp_enqueue_script('jquery');
}

//Insert Google Analytics
function foxyshop_insert_google_analytics() {
	global $foxyshop_settings;
	
	//Advanced
	if ($foxyshop_settings['ga_advanced']) {
		?><script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo htmlspecialchars($foxyshop_settings['ga']); ?>']);
  _gaq.push(['_setDomainName', 'none']);
  _gaq.push(['_setAllowLinker', true]);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

<script type="text/javascript" charset="utf-8">
	fcc.events.cart.preprocess.add(function(e, arr) {
		if (arr['cart'] == 'checkout' || arr['cart'] == 'updateinfo' || arr['output'] == 'json') {
			return true;
		}
		if (arr['cart'] == 'checkout_paypal_express') {
			_gaq.push(['_trackPageview', '/paypal_checkout']);
			return true;
		}
		_gaq.push(['_trackPageview', '/cart']);
		return true;
	});
	fcc.events.cart.process.add_pre(function(e, arr) {
		var pageTracker = _gat._getTrackerByName();
		jQuery.getJSON('https://' + storedomain + '/cart?' + fcc.session_get() + '&h:ga=' + escape(pageTracker._getLinkerUrl('', true)) + '&output=json&callback=?', function(data){});
		return true;
	});
</script><?php
	
	
	} else {
		if (!is_user_logged_in()) {
		?><script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?php echo htmlspecialchars($foxyshop_settings['ga']); ?>']);
_gaq.push(['_trackPageview']);
(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script><?php
		} else {
			echo "<!-- Google Analytics Not Loaded Because This is a Logged-In User -->";
		}
	}
}

//Product Category Comparison
function foxyshop_comparison($a, $b) { 
	if ($a->sort_key == $b->sort_key) { return 0; }
	return ($a->sort_key < $b->sort_key) ? -1 : 1;
}

//Sort Categories
function foxyshop_sort_categories($obj, $categoryID) {
	global $foxyshop_category_sort;
	if (array_key_exists($categoryID,$foxyshop_category_sort)) {
		$sort_array = $foxyshop_category_sort[$categoryID];
		foreach($obj as $cat) {
			$cat->sort_key = 999;
			for ($i=0;$i<count($sort_array);$i++) {
				if ($sort_array[$i] == $cat->term_id) $cat->sort_key = $i;
			}
		}
		usort($obj,'foxyshop_comparison');
	}
	return $obj;
}

//Generate Products Per Page
function foxyshop_products_per_page() {
	global $foxyshop_settings;
	return $foxyshop_settings['products_per_page'];
}

//Hide Children Array
function foxyshop_hide_children_array($currentCategoryID) {
	global $foxyshop_settings;
	if ($foxyshop_settings['hide_subcat_children']) {
		return array("post__not_in" => get_objects_in_term(get_term_children($currentCategoryID, "foxyshop_categories"), "foxyshop_categories"));
	} else {
		return array();
	}
}

//Place Plugin Activation Links
function foxyshop_plugin_action_links($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = "foxyshop/foxyshop.php";
	if ($file == $this_plugin) {
		$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/edit.php?post_type=foxyshop_product&page=foxyshop_options">Settings</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}

//Plugin Activation Function
function foxyshop_activation() {

	//Initialize Category Sort Holder If Not Set
	add_option('foxyshop_category_sort',serialize(array()));
	
	//Defaults For Settings
	$default_foxyshop_settings = array(
		"domain" => "",
		"version" => "0.7.1",
		"foxyshop_version" => FOXYSHOP_VERSION,
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
		"default_weight" => "1 0.0",
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
		"datafeed_url_key" => substr(MD5(rand(1000, 99999)."{urlkey}" . date("H:i:s")),1,12),
		"generate_feed" => "",
		"default_image" => WP_PLUGIN_URL."/foxyshop/images/no-photo.png",
		"products_per_page" => -1,
		"api_key" => "sp92fx".hash_hmac('sha256',rand(21654,6489798),"dkjw82j1".time())
	);
	
	//Set For the First Time
	if (!get_option("foxyshop_settings")) {
		update_option("foxyshop_settings", serialize($default_foxyshop_settings));
	
	//Upgrade Tasks
	} else {
		
		$foxyshop_settings = unserialize(get_option("foxyshop_settings"));
		
		//Run Some Upgrades
		if ($foxyshop_settings['version'] == "0.70") $foxyshop_settings['version'] = "0.7.0";
		if (array_key_exists('inventory_url_key',$foxyshop_settings)) {
			$foxyshop_settings['datafeed_url_key'] = $foxyshop_settings['inventory_url_key'];
			unset($foxyshop_settings['inventory_url_key']);
		}
		
		//Load in New Defaults and Save New Version
		$foxyshop_settings = wp_parse_args($foxyshop_settings,$default_foxyshop_settings);
		$foxyshop_settings['foxyshop_version'] = FOXYSHOP_VERSION;
		if (!$foxyshop_settings['datafeed_url_key']) $foxyshop_settings['datafeed_url_key'] = substr(MD5(rand(1000, 99999)."{urlkey}" . date("H:i:s")),1,12);

		//Save Settings
		update_option("foxyshop_settings", serialize($foxyshop_settings));
	}
}

//Plugin Deactivation Function
function foxyshop_deactivation() {
	global $wp_post_types;
	if (isset($wp_post_types['foxyshop_product'])) unset($wp_post_types['foxyshop_product']);
	delete_option('foxyshop_rewrite_rules');
	flush_rewrite_rules();
}

//Create Product Sitemap
function foxyshop_create_product_sitemap() {
	$args = array(
		'post_type' => array('foxyshop_product'),
		'post_status' => 'publish',
		'numberposts' => -1
	);
	$products = get_posts($args);
	$write = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$write .= '<urlset xmlns="http://www.google.com/schemas/sitemap/0.90">'."\n";
	foreach ($products as $product) {
		$write .= '<url>'."\n";
		$write .= '<loc>' . get_bloginfo('wpurl') . '/' . FOXYSHOP_PRODUCTS_SLUG . '/' . htmlspecialchars($product->post_name) . '/</loc>'."\n";
		$write .= '<lastmod>' . date('Y-m-d\TH:i:s+00:00',strtotime($product->post_modified)) . '</lastmod>'."\n";
		$write .= '<changefreq>weekly</changefreq>'."\n";
		$write .= '<priority>1.0</priority>'."\n";
		$write .= '</url>'."\n";
	}

	$termchildren = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0&orderby=name&order=ASC');
	if ($termchildren) {
		$write .= '<url>'."\n";
		$write .= '<loc>' . get_bloginfo('wpurl') . '/' . FOXYSHOP_PRODUCT_CATEGORY_SLUG . '/</loc>'."\n";
		$write .= '<lastmod>' . date('Y-m-d\TH:i:s+00:00',time()) . '</lastmod>'."\n";
		$write .= '<changefreq>weekly</changefreq>'."\n";
		$write .= '<priority>1.0</priority>'."\n";
		$write .= '</url>'."\n";
		foreach ($termchildren as $child) {
			$write .= '<url>'."\n";
			$write .= '<loc>' . get_term_link((int)$child->term_id, "foxyshop_categories") . '</loc>'."\n";
			$write .= '<lastmod>' . date('Y-m-d\TH:i:s+00:00',time()) . '</lastmod>'."\n";
			$write .= '<changefreq>weekly</changefreq>'."\n";
			$write .= '<priority>1.0</priority>'."\n";
			$write .= '</url>'."\n";
		}
	} else {
		$write .= '<url>'."\n";
		$write .= '<loc>' . get_bloginfo('wpurl') . '/' . FOXYSHOP_PRODUCTS_SLUG . '/</loc>'."\n";
		$write .= '<lastmod>' . date('Y-m-d\TH:i:s+00:00',time()) . '</lastmod>'."\n";
		$write .= '<changefreq>weekly</changefreq>'."\n";
		$write .= '<priority>1.0</priority>'."\n";
		$write .= '</url>'."\n";
	}
	$write .= '</urlset>';
	file_put_contents(FOXYSHOP_DOCUMENT_ROOT.'/sitemap-products.xml', $write);
}

//Flushes Rewrite Rules if Structure Has Changed
function foxyshop_check_rewrite_rules() {
	if (get_option('foxyshop_rewrite_rules') != FOXYSHOP_PRODUCTS_SLUG."|".FOXYSHOP_PRODUCT_CATEGORY_SLUG) {
		flush_rewrite_rules(false);
		update_option('foxyshop_rewrite_rules', FOXYSHOP_PRODUCTS_SLUG."|".FOXYSHOP_PRODUCT_CATEGORY_SLUG);
	}
}

//Access the FoxyCart API
function foxyshop_get_foxycart_data($foxyData) {
	global $foxyshop_settings;
	//print_r($foxyData);
	$foxyData = array_merge(array("api_token" => $foxyshop_settings['api_key']), $foxyData);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://" . $foxyshop_settings['domain'] . "/api");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $foxyData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	// If you get SSL errors, you can uncomment the following, or ask your host to add the appropriate CA bundle
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$response = trim(curl_exec($ch));
	 
	// The following if block will print any CURL errors you might have
	if ($response == false) {
		die("cURL Error: \n" . curl_error($ch));
	}
	curl_close($ch);
	return $response;
}

//Function to Prepare the List Tables
function foxyshop_list_table_setup($tabletype) {
	global $foxyshop_settings, $wp_version;

	echo '<link rel="stylesheet" href="' . FOXYSHOP_DIR . '/js/datatables/css/demo_table.css" type="text/css" media="screen" />'."\n";
	echo '<link rel="stylesheet" href="' . FOXYSHOP_DIR . '/js/datatables/css/demo_table_jui.css" type="text/css" media="screen" />'."\n";
	echo '<script type="text/javascript" src="' . FOXYSHOP_DIR . '/js/datatables/jquery.dataTables.min.js"></script>'."\n";
	if ($tabletype == "inventory") return false;
	
	$sortColumn = 1;
	if ($tabletype == "orders") {
		$sortColumn = 2;
		
	} elseif ($tabletype == "subscriptions") {
		$sortColumn = 2;
	
	} elseif ($tabletype == "customers") {
		$sortColumn = 1;
	}
	$ajax_nonce = wp_create_nonce("foxyshop-display-list-function");
	?>
<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function($) {

	//Details Column
	var nCloneTh = document.createElement( 'th' );
	var nCloneTd = document.createElement( 'td' );
	nCloneTd.innerHTML = '<img src="<?php echo FOXYSHOP_DIR . "/js/datatables/images/";?>details_open.png" class="openclose" style="cursor: pointer;" />';
	nCloneTd.className = "center";
	
	$('.foxyshop_table_list thead tr').each( function () {
		this.insertBefore(nCloneTh, this.childNodes[0]);
	});
	
	$('.foxyshop_table_list tbody tr').each( function () {
		this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
	});


	var oTable = $('.foxyshop_table_list').dataTable( {
		"bPaginate": false,
		"bFilter": true,
		"bSort": true,
		"bInfo": true,
		"bAutoWidth": true,
		"bJQueryUI": true,
		"aoColumnDefs": [{ "bSortable": false, "aTargets": [0<?php if ($tabletype == "orders") echo ", 4, 5"; ?>] }],
		"aaSorting": [[<?php echo $sortColumn; ?>, 'desc']]
		<?php if ($tabletype == "orders") { ?>
			,"aoColumns": [
			null,
			null,
			null,
			{ "sType": "html" },
			null,
			null
			]
		<?php } ?>
		
	});


	$('.foxyshop_table_list tbody td img.openclose').live('click', function() {
		var nTr = this.parentNode.parentNode;
		var thisImage = this;
		if (thisImage.src.match('details_close')) {
			thisImage.src = "<?php echo FOXYSHOP_DIR . "/js/datatables/images/";?>details_open.png";
			oTable.fnClose( nTr );
		} else {
			
			<?php if ($tabletype == "orders") { ?>

				thisImage.src = '<?php echo get_bloginfo("wpurl");?>/wp-admin/images/wpspin_light.gif';
				var transaction_id = $(nTr).attr("rel");
				var data = {
					action: 'foxyshop_display_list_ajax_action',
					security: '<?php echo $ajax_nonce; ?>',
					foxyshop_action: 'order_detail',
					id: transaction_id
				};
				$.post(ajaxurl, data, function(response) {
					oTable.fnOpen( nTr, fnFormatDetails_<?php echo $tabletype; ?> (oTable, nTr, response), 'details' );
					thisImage.src = '<?php echo FOXYSHOP_DIR . "/js/datatables/images/";?>details_close.png';
				});
			<?php } elseif ($tabletype == "customers") { ?>

				thisImage.src = '<?php echo get_bloginfo("wpurl");?>/wp-admin/images/wpspin_light.gif';
				var customer_id = $(nTr).attr("rel");
				var data = {
					action: 'foxyshop_display_list_ajax_action',
					security: '<?php echo $ajax_nonce; ?>',
					foxyshop_action: 'customer_detail',
					id: customer_id
				};
				$.post(ajaxurl, data, function(response) {
					oTable.fnOpen( nTr, fnFormatDetails_<?php echo $tabletype; ?> (oTable, nTr, response), 'details' );
					thisImage.src = '<?php echo FOXYSHOP_DIR . "/js/datatables/images/";?>details_close.png';
				});
			<?php } else { ?>

				thisImage.src = '<?php echo FOXYSHOP_DIR . "/js/datatables/images/";?>details_close.png';
				oTable.fnOpen( nTr, fnFormatDetails_<?php echo $tabletype; ?> (oTable, nTr), 'details' );
			
			<?php } ?>
			
			
			
			
		}
	});

	$(".archive_order").click( function() {
		var transaction_id = $(this).attr("rel");
		var data = {
			action: 'foxyshop_display_list_ajax_action',
			security: '<?php echo $ajax_nonce; ?>',
			foxyshop_action: 'hide_transaction',
			id: transaction_id
		};
		$.post(ajaxurl, data);
		$("tr[rel="+transaction_id+"]").hide();
		return false;
	});

	$('input.foxyshop_date_field').live('click', function () {
		$(this).datepicker({dateFormat: 'yy-mm-dd'}).css("z-index", "9999999").focus();
	});
    
	$(".subscriptionUpdate").live("click", function() {
		var action = $(this).attr("rel");
		var id = $(this).attr("actionid");
		$(".foxyshop_list_action_status[rel=" + id + "]").html('<div id="foxyshop_image_waiter"></div>').show();
		if (action == "start_date") {
			if (!$("#" + action + id).val()) { $(".foxyshop_list_action_status[rel=" + id + "]").css("color","red").html("No Value Entered!").delay(2000).fadeOut(); return false; }
			var data = {
				action: 'foxyshop_display_list_ajax_action',
				security: '<?php echo $ajax_nonce; ?>',
				foxyshop_action: action,
				id: id,
				start_date: $("#" + action + id).val()
			};
		} else if (action == "end_date") {
			if (!$("#" + action + id).val()) { $(".foxyshop_list_action_status[rel=" + id + "]").css("color","red").html("No Value Entered!").delay(2000).fadeOut(); return false; }
			var data = {
				action: 'foxyshop_display_list_ajax_action',
				security: '<?php echo $ajax_nonce; ?>',
				foxyshop_action: action,
				id: id,
				end_date: $("#" + action + id).val()
			};
		} else if (action == "next_transaction_date") {
			if (!$("#" + action + id).val()) { $(".foxyshop_list_action_status[rel=" + id + "]").css("color","red").html("No Value Entered!").delay(2000).fadeOut(); return false; }
			var data = {
				action: 'foxyshop_display_list_ajax_action',
				security: '<?php echo $ajax_nonce; ?>',
				foxyshop_action: action,
				id: id,
				next_transaction_date: $("#" + action + id).val()
			};
		} else if (action == "frequency") {
			if (!$("#" + action + id).val()) { $(".foxyshop_list_action_status[rel=" + id + "]").css("color","red").html("No Value Entered!").delay(2000).fadeOut(); return false; }
			var data = {
				action: 'foxyshop_display_list_ajax_action',
				security: '<?php echo $ajax_nonce; ?>',
				foxyshop_action: action,
				id: id,
				frequency: $("#" + action + id).val()
			};
		} else if (action == "past_due_amount") {
			if (!$("#" + action + id).val()) { $(".foxyshop_list_action_status[rel=" + id + "]").css("color","red").html("No Value Entered!").delay(2000).fadeOut(); return false; }
			var data = {
				action: 'foxyshop_display_list_ajax_action',
				security: '<?php echo $ajax_nonce; ?>',
				foxyshop_action: action,
				id: id,
				past_due_amount: $("#" + action + id).val()
			};
		} else if (action == "sub_on") {
			var data = {
				action: 'foxyshop_display_list_ajax_action',
				security: '<?php echo $ajax_nonce; ?>',
				foxyshop_action: action,
				id: id,
				sub_on: 1
			};
		} else if (action == "sub_off") {
			var data = {
				action: 'foxyshop_display_list_ajax_action',
				security: '<?php echo $ajax_nonce; ?>',
				foxyshop_action: action,
				id: id,
				sub_on: 0
			};
		}

		if (action) {
			$.post(ajaxurl, data, function(response) {
				if (response.indexOf('SUCCESS') != -1) {
					if (action == "sub_off") {
						$("a.button[actionid=" + id + "][rel=sub_on]").show();
						$("a.button[actionid=" + id + "][rel=sub_off]").hide();
						$("tr[rel=" + id + "]").removeClass().addClass("gradeU");
					} else if (action == "sub_on") {
						$("a.button[actionid=" + id + "][rel=sub_on]").hide();
						$("a.button[actionid=" + id + "][rel=sub_off]").show();
						$("tr[rel=" + id + "]").removeClass().addClass("gradeA");

					} else {
						if (action == "start_date") {
							$("tr[rel=" + id + "] td:eq(2)").text($("#" + action + id).val());
						} else if (action == "end_date") {
							$("tr[rel=" + id + "] td:eq(4)").text($("#" + action + id).val());
						} else if (action == "next_transaction_date") {
							$("tr[rel=" + id + "] td:eq(3)").text($("#" + action + id).val());
						} else if (action == "past_due_amount") {
							if ($("#" + action + id).val() > 0) {
								$("tr[rel=" + id + "]").removeClass().addClass("gradeX");
							} else {
								$("tr[rel=" + id + "]").removeClass().addClass("gradeA");
							}
							$("tr[rel=" + id + "] td:eq(5)").text($("#" + action + id).val());
						} else if (action == "frequency") {
							$("tr[rel=" + id + "] td:eq(7)").text($("#" + action + id).val());
						}
						$("#" + action + id).val("");
					}
				
					$(".foxyshop_list_action_status[rel=" + id + "]").css("color","green").html(response).delay(2000).fadeOut();
				} else {
					$(".foxyshop_list_action_status[rel=" + id + "]").css("color","red").html(response);
				}
			});
		}
		return false;
	});


	function fnFormatDetails_subscriptions(oTable, nTr) {
		var aData = oTable.fnGetData(nTr);
		var sub_token = $(nTr).attr("rel");
		
		var onHide = "";
		var offHide = "";
		if ($(nTr).hasClass("gradeA")) {
			onHide = ' style="display: none;"';
		} else {
			offHide = ' style="display: none;"';
		}
		
		
		
		
		var sOut = '<div class="list_detail"><form onsubmit="return false;">';
		sOut += '<div class="foxyshop_field_control" style="height: 32px;">';
		sOut += '<a href="#" rel="sub_on" actionid="' + sub_token + '"class="button subscriptionUpdate"' + onHide + '>Activate Subscription</a>';
		sOut += '<a href="#" rel="sub_off" actionid="' + sub_token + '" class="button subscriptionUpdate"' + offHide + '>Disable Subscription</a>';
		sOut += '<div class="foxyshop_list_action_status" rel="' + sub_token + '"></div>';
		sOut += '</div>';
		sOut += '<div class="foxyshop_field_control">';
		sOut += '<label>Start Date</label><input type="text" name="start_date' + sub_token + '" id="start_date' + sub_token + '" class="foxyshop_date_field" /><a href="#" class="button subscriptionUpdate" rel="start_date" actionid="' + sub_token + '">Update</a><span>(YYYY-MM-DD)</span>';
		sOut += '</div>';
		sOut += '<div class="foxyshop_field_control">';
		sOut += '<label>Next Transaction Date</label><input type="text" name="next_transaction_date' + sub_token + '" id="next_transaction_date' + sub_token + '" class="foxyshop_date_field" /><a href="#" class="button subscriptionUpdate" rel="next_transaction_date" actionid="' + sub_token + '">Update</a><span>(YYYY-MM-DD)</span>';
		sOut += '</div>';
		sOut += '<div class="foxyshop_field_control">';
		sOut += '<label>End Date</label><input type="text" name="end_date' + sub_token + '" id="end_date' + sub_token + '" class="foxyshop_date_field" /><a href="#" class="button subscriptionUpdate" rel="end_date" actionid="' + sub_token + '">Update</a><span>(YYYY-MM-DD) <a href="#" onclick="jQuery(\'#end_date' + sub_token + '\').val(\'0000-00-00\'); return false;">Never?</a></span>';
		sOut += '</div>';
		sOut += '<div class="foxyshop_field_control">';
		sOut += '<label>Frequency</label><input type="text" name="frequency' + sub_token + '" id="frequency' + sub_token + '" /><a href="#" class="button subscriptionUpdate" rel="frequency" actionid="' + sub_token + '">Update</a><span>(60d, 2w, 1m, 1y, .5m)</span>';
		sOut += '</div>';
		sOut += '<div class="foxyshop_field_control">';
		sOut += '<label>Past Due Amount</label><input type="text" name="past_due_amount' + sub_token + '" id="past_due_amount' + sub_token + '" /><a href="#" class="button subscriptionUpdate" rel="past_due_amount" actionid="' + sub_token + '">Update</a><span>(0.00)</span>';
		sOut += '</div>';
		sOut += '<div class="foxyshop_field_control">';
		sOut += '<label>Update URL</label><input type="text" name="update_url' + sub_token + '" id="update_url' + sub_token + '" value="<?php echo 'https://' . $foxyshop_settings['domain'] . '/cart?sub_token=\' + sub_token + \'&amp;cart=checkout'; ?>" style="width: 390px;" onclick="this.select();" />';
		sOut += '</div>';
		sOut += '<div class="foxyshop_field_control">';
		sOut += '<label>Cancellation URL</label><input type="text" name="cancel_url' + sub_token + '" id="cancel_url' + sub_token + '" value="<?php echo 'https://' . $foxyshop_settings['domain'] . '/cart?sub_token=\' + sub_token + \'&amp;cart=checkout&mp;sub_cancel=true'; ?>" style="width: 390px;" onclick="this.select();" />';
		sOut += '</div>';


		sOut += '</form></div>';
		
		return sOut;
	}



	function fnFormatDetails_customers(oTable, nTr, optionalReturn) {
		var aData = oTable.fnGetData(nTr);
		return optionalReturn;
	}

	function fnFormatDetails_orders(oTable, nTr, optionalReturn) {
		var aData = oTable.fnGetData(nTr);
		return optionalReturn;
	}


});
</script>
<?php

}




?>