jQuery(document).ready(function($){
	$("form.foxyshop_product select, form.foxyshop_product input:checkbox, form.foxyshop_product input:radio").change(function(){
		updateVariations($(this));
	});
	$("form.foxyshop_product").each(function() {
		updateVariations($(this).find("input"));
	});
	

	function updateVariations(elSelect) {
		var current_product_id = elSelect.parents("form").attr("rel");
		if (typeof current_product_id == 'undefined') current_product_id = elSelect.parent("form").attr("rel");
		displayKey = new Array();
		var new_price = $("#fs_price_" + current_product_id).val();
		var new_price_original = $("#originalprice_" + current_product_id).val();
		new_price = parseFloat(new_price.replace(",","")) * 100;
		new_price_original = parseFloat(new_price_original.replace(",","")) * 100;

		var new_code = '';
		var new_codeadd = '';
		var new_ikey = '';
		var new_inventory = '';


		//Parse for DKey's First
		$("#foxyshop_product_form_" + current_product_id).find(".foxyshop_variations select option:selected, .foxyshop_variations input:checkbox:checked, .foxyshop_variations input:radio:checked").each(function(){
			var thisEl = $(this);

			thisdisplaykey = thisEl.attr("displaykey");
			if (thisdisplaykey != "") displayKey[displayKey.length] = thisdisplaykey;
		});
		
		//Show and Hide the Dkey's Based on Values
		$("#foxyshop_product_form_" + current_product_id + " .dkey").hide();
		for (i=0;i<displayKey.length;i++) {
			$('#foxyshop_product_form_' + current_product_id + ' .dkey[dkey="' + displayKey[i] + '"]').show();
		}
		$("#foxyshop_product_form_" + current_product_id + " .dkey:hidden").each(function() {
			var thisEl = $(this);
			if (thisEl.is('input') || $(this).is('textarea')) {
				thisEl.val("");
			} else if ($(this).is('select')) {
				thisEl.attr('selectedIndex', '-1');
			}

		});

		//Set x: on hidden select and radio fields
		foxyshop_visible_selector = "";
		if (typeof foxyshop_skip_hidden_selects == 'undefined') {
			$("#foxyshop_product_form_" + current_product_id).find(".foxyshop_variations select, .foxyshop_variations input:radio").each(function(){
				if ($(this).attr("name") != "x:shipto_name_select") {
					if ($(this).is(":visible")) {
						foxyshop_set_field_visible($(this));
					} else {
						foxyshop_set_field_hidden($(this));
					}
				}
			});
			foxyshop_visible_selector = ":visible";
		}

		//For Each Element
		$("#foxyshop_product_form_" + current_product_id).find(".foxyshop_variations select" + foxyshop_visible_selector + " option:selected, .foxyshop_variations input:checkbox:checked, .foxyshop_variations input:radio:checked" + foxyshop_visible_selector).each(function(){
			var thisEl = $(this);
			
			//Get New Image Key
			imagekey = thisEl.attr("imagekey");
			if (imagekey != "" && typeof imagekey != "undefined") {
				for (i=0; i<ikey.length; i++) {
					if (ikey[i][0] == imagekey) new_ikey = i;
				}
			}

			//Code Additions
			varcodeadd = thisEl.attr("codeadd");
			if (varcodeadd != "" && typeof varcodeadd != 'undefined') new_codeadd += varcodeadd;

			//Check Inventory
			varcode = thisEl.attr("code");
			if (varcode != "" && typeof varcode != 'undefined') new_code = varcode;

			//Price Change
			priceChange = thisEl.attr("pricechange");
			priceSet = thisEl.attr("priceset");
			if (priceChange) {
				priceChangeAmount = parseFloat(priceChange);
				if (priceChange.substr(1,2) == "-") {
					new_price = new_price - priceChangeAmount;
					new_price_original = new_price_original - priceChangeAmount;
				} else {
					new_price = new_price + priceChangeAmount;
					new_price_original = new_price_original + priceChangeAmount;
				}
			} else if (priceSet) {
				new_price = parseFloat(priceSet);
				new_price_original = new_price;
			}
		});

		
		if (!new_code) new_code = $("#fs_code_" + current_product_id).val();
		setModifiers(new_code, new_codeadd, new_price, new_price_original, new_ikey, current_product_id);

		
	

	}

	function foxyshop_set_field_hidden(el) {
		currentname = el.attr("name");
		if (currentname.substr(0,2) != "x:") el.attr("name", "x:" + currentname);
	}
	function foxyshop_set_field_visible(el) {
		currentname = el.attr("name");
		if (currentname.substr(0,2) == "x:") el.attr("name", currentname.substr(2));
	}
	
	function setModifiers(new_code, new_codeadd, new_price, new_price_original, new_ikey, current_product_id) {
		var parentForm = "#foxyshop_product_form_" + current_product_id;
		
		//Change Image
		if (new_ikey != '' || new_ikey === 0) {
			
			//Plugin Function Here
			if (typeof window.foxyshop_before_image_change == 'function') foxyshop_before_image_change(new_ikey);

			$(parentForm + " #foxyshop_main_product_image").attr("src",ikey[new_ikey][2]).attr("alt",ikey[new_ikey][4]).parent().attr("href",ikey[new_ikey][3]);
			
			//Cloud-zoom
			if (typeof window.foxyshop_cloudzoom_image_change == 'function') {
				foxyshop_cloudzoom_image_change(new_ikey);
			
			//Replace Image
			} else {
				$("#foxyshop_cart_product_image_" + current_product_id).attr("name",'image'+ikey[new_ikey][5]).val(ikey[new_ikey][1]);
			}
			
			//Plugin Function Here
			if (typeof window.foxyshop_after_image_change == 'function') foxyshop_after_image_change(new_ikey);
		}

		//Check Inventory
		if (typeof arr_foxyshop_inventory == "undefined") arr_foxyshop_inventory = [];
		inventory_code = new_code;
		inventory_match_count = -1;
		if (new_codeadd) inventory_code = $("#fs_code_" + current_product_id).val() + new_codeadd; 
		if (inventory_code != "" && typeof arr_foxyshop_inventory[current_product_id] != 'undefined') {
			for (i=0; i<arr_foxyshop_inventory[current_product_id].length; i++) {
				if (arr_foxyshop_inventory[current_product_id][i][0] == inventory_code) inventory_match_count = i;
			}
		}

		if (inventory_match_count >= 0) {
			newcount = parseInt(arr_foxyshop_inventory[current_product_id][inventory_match_count][1]);
			newalert = parseInt(arr_foxyshop_inventory[current_product_id][inventory_match_count][2]);
			newhash = arr_foxyshop_inventory[current_product_id][inventory_match_count][3];
			if (!foxyshop_allow_backorder) $("#fs_quantity_max_" + current_product_id).attr("name","quantity_max" + newhash).val(newcount);
			if (newcount > 0 && newcount <= newalert) {
				$(parentForm + " .foxyshop_stock_alert").removeClass("foxyshop_out_of_stock").text(update_inventory_alert_language(foxyshop_inventory_stock_alert,newcount)).show();
				$(parentForm + " #productsubmit").removeAttr("disabled").removeClass("foxyshop_disabled");
			} else if (newcount <= 0) {
				$(parentForm + " .foxyshop_stock_alert").addClass("foxyshop_out_of_stock").text(update_inventory_alert_language(foxyshop_inventory_stock_none,inventory_match_count)).show();
				if (!foxyshop_allow_backorder) $(parentForm + " #productsubmit").attr("disabled","disabled").addClass("foxyshop_disabled");
			} else {
				$(parentForm + " #productsubmit").removeAttr("disabled").removeClass("foxyshop_disabled");
				$(parentForm + " .foxyshop_stock_alert").hide();
			}
		} else if (typeof arr_foxyshop_inventory[current_product_id] != 'undefined') {
			if (!foxyshop_allow_backorder) $("#fs_quantity_max_" + current_product_id).attr("name","quantity_max"+$("#original_quantity_max_" + current_product_id).attr("rel")).val($("#original_quantity_max_" + current_product_id).val());
			$(parentForm + " #productsubmit").removeAttr("disabled").removeClass("foxyshop_disabled");
			$(parentForm + " .foxyshop_stock_alert").removeClass("foxyshop_out_of_stock").hide();
		}

		//Get Price of Add-Ons
		var addOnTotal = 0;
		$(".foxyshop_quantity.foxyshop_addon_fields").prop("disabled", true);
		$("input.foxyshop_addon_checkbox:checked").each(function() {
			current_id = $(this).attr("rel");
			currentTotal =  parseFloat($("#addon_price_" + current_id).val()) * 100;
			$(".foxyshop_quantity.foxyshop_addon_fields[rel=" + current_id + "]").prop("disabled", false);
			if ($("input.foxyshop_quantity.foxyshop_addon_fields[rel=" + current_id + "]").length > 0) {
				totalQty = $("input.foxyshop_quantity.foxyshop_addon_fields[rel=" + current_id + "]").val();
			} else if ($("select.foxyshop_quantity.foxyshop_addon_fields[rel=" + current_id + "]").length > 0) {
				totalQty = $("select.foxyshop_quantity.foxyshop_addon_fields[rel=" + current_id + "] option:selected").text();
			} else {
				totalQty = 1;
			}
			addOnTotal +=  (currentTotal * totalQty);
		});

		//Change Price
		l18n_settings = $("#foxyshop_l18n_" + current_product_id).val();
		arrl18n_settings = l18n_settings.split("|");
		currencySymbol = arrl18n_settings[0];
		decimalSeparator = arrl18n_settings[1];
		thousandsSeparator = arrl18n_settings[2];
		p_precedes = arrl18n_settings[3];
		n_sep_by_space = arrl18n_settings[4];
		new_price += addOnTotal;
		new_price_original += addOnTotal;
		$(parentForm + " #foxyshop_main_price .foxyshop_currentprice").text(toCurrency(new_price, currencySymbol, thousandsSeparator, decimalSeparator, p_precedes, n_sep_by_space));
		$(parentForm + " #foxyshop_main_price .foxyshop_oldprice").text(toCurrency(new_price_original, currencySymbol, thousandsSeparator, decimalSeparator, p_precedes, n_sep_by_space));

		//Plugin Function Here - AFTER CHANGE VARIATIONS
		if (typeof window.foxyshop_after_variation_modifiers == 'function') foxyshop_after_variation_modifiers(new_code, new_codeadd, new_price, new_price_original, new_ikey, current_product_id);

	}

	function update_inventory_alert_language(strlang, itemcount) {
		strlang = strlang.replace('%c',itemcount);
		if (itemcount == 1) {
			strlang = strlang.replace('%s',"");
		} else {
			strlang = strlang.replace('%s',"s");
		}
		strlang = strlang.replace('%n',$("#input[name^='name||']").val());
		return strlang;
	}
	
	function toCurrency(n, c, g, d, first, separator) {
		var s = (0 > n) ? '-' : '';
		if (separator == 1) { separator = ' '; } else { separator = ''; }
		var m = String(Math.round(Math.abs(n)));
		var i = '', j, f; c = c || ''; g = g || ''; d = d || '.';
		while(m.length < 3) {m = '0' + m;}
		f = m.substring((j = m.length - 2));
		while(j > 3) {
			i = g + m.substring(j - 3, j) + i;
			j -= 3;
		}
		i = m.substring(0, j) + i;
		if (first == 1) {
			return s + c + separator + i + d + f;
		} else {
			return s + i + d + f + separator + c;
		}
	}

});

//Code to Check For Required Fields Before Adding to Cart
foxycart_required_fields_check = function(e, arr) {
	var current_product_id = jQuery(e).attr("rel");
	var strFailed = false;
	if (current_product_id) {
		jQuery("#foxyshop_product_form_" + current_product_id + " .foxyshop_required").filter(":visible").each(function() {

			if (jQuery(this).is('select') && jQuery('option:selected', this).index() == 0) {
					strFailed = true;
					alert("Error: You must select an option from the dropdown.");
					jQuery(this).focus();
			} else if (!jQuery(this).val() && !jQuery(this).is('label')) {
				if (jQuery(this).hasClass('hiddenimageholder') && jQuery(this).parents('.foxyshop_custom_upload_container').is(':visible')) {
					strFailed = true;
					alert('Error: You must upload a file before adding to cart.');
				} else {
					strFailed = true;
					alert("Error: You have not completed a required field.");
					jQuery(this).focus();
				}
			}
		});
	}
	if (strFailed) {
		return false;
	} else {
		return true;
	}
}
if (typeof fcc !== 'undefined') {
	fcc.events.cart.preprocess.add(foxycart_required_fields_check);
}

function foxyshop_is_array(obj) {
	if (obj.constructor.toString().indexOf("Array") == -1)
		return false;
	else
		return true;
}