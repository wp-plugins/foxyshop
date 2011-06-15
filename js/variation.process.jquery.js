jQuery(document).ready(function($){
	$("form.foxyshop_product select, form.foxyshop_product input:checkbox, form.foxyshop_product input:radio").change(function(){
		updateVariations($(this));
	});
	updateVariations($("form.foxyshop_product select, form.foxyshop_product input:checkbox, form.foxyshop_product input:radio"));

	function updateVariations(elSelect) {
		displayKey = new Array();
		var new_price = $("#price").val();
		var new_price_original = $("#originalprice").val();
		new_price = parseFloat(new_price.replace(",","")) * 100;
		new_price_original = parseFloat(new_price_original.replace(",","")) * 100;

		var new_code = '';
		var new_codeadd = '';
		var new_ikey = '';
		var new_inventory = '';

		//For Each Element
		elSelect.parents("form").find(".foxyshop_variations select option:selected, .foxyshop_variations input:checkbox:checked, .foxyshop_variations input:radio:checked").each(function(){
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

			//Set Display Key
			thisdisplaykey = thisEl.attr("displaykey");
			if (thisdisplaykey != "") displayKey[displayKey.length] = thisdisplaykey;

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

		
		$(".dkey").hide();
		for (i=0;i<displayKey.length;i++) {
			$('.dkey[dkey="' + displayKey[i] + '"]').show();
		}
		$(".dkey:hidden").each(function() {
			var thisEl = $(this);
			if (thisEl.is('input') || $(this).is('textarea')) {
				thisEl.val("");
			} else if ($(this).is('select')) {
				thisEl.attr('selectedIndex', '-1');
			}

		});
		
		setModifiers(new_code, new_codeadd, new_price, new_price_original, new_ikey);


		
	

	}
	
	function setModifiers(new_code, new_codeadd, new_price, new_price_original, new_ikey) {
		
		//Change Image
		if (new_ikey != '') {
			$("#foxyshop_main_product_image").attr("src",ikey[new_ikey][2]).attr("alt",ikey[new_ikey][4]).parent().attr("href",ikey[new_ikey][3]);
			$("#foxyshop_cart_product_image").attr("name",'image'+ikey[new_ikey][5]).val(ikey[new_ikey][1]);
		}
		//Check Inventory
		inventory_code = new_code;
		inventory_match_count = -1;
		if (new_codeadd) inventory_code = $("#fs_code").val() + new_codeadd; 
		if (inventory_code != "" && typeof arr_foxyshop_inventory != 'undefined') {
			for (i=0; i<arr_foxyshop_inventory.length; i++) {
				if (arr_foxyshop_inventory[i][0] == inventory_code) inventory_match_count = i;
			}
		}
		
		if (inventory_match_count >= 0) {
			newcount = parseInt(arr_foxyshop_inventory[inventory_match_count][1]);
			newalert = parseInt(arr_foxyshop_inventory[inventory_match_count][2]);
			newhash = arr_foxyshop_inventory[inventory_match_count][3];
			if (!foxyshop_allow_backorder) $("#fs_quantity_max").attr("name","quantity_max"+newhash).val(newcount);
			if (newcount > 0 && newcount <= newalert) {
				$(".foxyshop_stock_alert").removeClass("foxyshop_out_of_stock").text(update_inventory_alert_language(foxyshop_inventory_stock_alert,newcount)).show();
				$("#productsubmit").removeAttr("disabled").removeClass("foxyshop_disabled");
			} else if (newcount <= 0) {
				$(".foxyshop_stock_alert").addClass("foxyshop_out_of_stock").text(update_inventory_alert_language(foxyshop_inventory_stock_none,inventory_match_count)).show();
				if (!foxyshop_allow_backorder) $("#productsubmit").attr("disabled","disabled").addClass("foxyshop_disabled");
			} else {
				$("#productsubmit").removeAttr("disabled").removeClass("foxyshop_disabled");
				$(".foxyshop_stock_alert").hide();
			}
		} else if (typeof arr_foxyshop_inventory != 'undefined') {
			if (!foxyshop_allow_backorder) $("#fs_quantity_max").attr("name","quantity_max"+$("#original_quantity_max").attr("rel")).val($("#original_quantity_max").val());
			$("#productsubmit").removeAttr("disabled").removeClass("foxyshop_disabled");
			$(".foxyshop_stock_alert").removeClass("foxyshop_out_of_stock").hide();
		}


		//Change Price
		l18n_settings = $("#foxyshop_l18n").val();
		arrl18n_settings = l18n_settings.split("|");
		currencySymbol = arrl18n_settings[0];
		decimalSeparator = arrl18n_settings[1];
		thousandsSeparator = arrl18n_settings[2];
		p_precedes = arrl18n_settings[3];
		n_sep_by_space = arrl18n_settings[4];
		$("#foxyshop_main_price .foxyshop_currentprice").text(toCurrency(new_price, currencySymbol, thousandsSeparator, decimalSeparator, p_precedes, n_sep_by_space));
		$("#foxyshop_main_price .foxyshop_oldprice").text(toCurrency(new_price_original, currencySymbol, thousandsSeparator, decimalSeparator, p_precedes, n_sep_by_space));

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
	var strFailed = false;
	jQuery("form.foxyshop_product input.foxyshop_required, form.foxyshop_product textarea.foxyshop_required").each(function() {
		if (!jQuery(this).val()) {
			if (jQuery(this).hasClass('hiddenimageholder') && jQuery(this).parents('.foxyshop_custom_upload_container').is(':visible')) {
				strFailed = true;
				alert('Error: You must upload a file before adding to cart.');
			} else if (jQuery(this).is(':visible')) {
				strFailed = true;
				alert("Error: You have not completed a required field.");
				jQuery(this).focus();
			}
		}
	});
	if (strFailed) {
		return false;
	} else {
		return true;
	}
}
fcc.events.cart.preprocess.add(foxycart_required_fields_check);


function foxyshop_is_array(obj) {
	if (obj.constructor.toString().indexOf("Array") == -1)
		return false;
	else
		return true;
}