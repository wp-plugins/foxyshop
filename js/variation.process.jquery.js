jQuery(document).ready(function($){
	$("form.foxyshop_product select").change(function(){
		updateVariations($(this));
	});
	updateVariations($("form.foxyshop_product select"));

	function updateVariations(elSelect) {
		price = $("#price").val();
		price1 = $("#originalprice").val();
		price = parseFloat(price.replace(",","")) * 100;
		price1 = parseFloat(price1.replace(",","")) * 100;
		displayKey = new Array();
		elSelect.parents("form").find(".foxyshop_variations select option:selected").each(function(){
			var thisEl = $(this);
			var selectedValue = thisEl.val();
			
			//Set Image Key
			imagekey = thisEl.attr("imagekey");
			if (imagekey != "" && typeof imagekey != "undefined") {
				for (i=0; i<ikey.length; i++) {
					if (ikey[i][0] == imagekey) {
						$("#foxyshop_main_product_image").attr("src",ikey[i][2]).attr("alt",ikey[i][4]).parent().attr("href",ikey[i][3]);
						$("#foxyshop_cart_product_image").attr("name",'image'+ikey[i][5]).val(ikey[i][1]);
					}
				}
			}


			//Set Display Key
			thisdisplaykey = thisEl.attr("displaykey");
			if (thisdisplaykey != "") displayKey[displayKey.length] = thisdisplaykey;

			//Price Change
			priceChange = thisEl.attr("pricechange");
			if (priceChange) {
				priceChangeAmount = parseFloat(priceChange);
				if (priceChange.substr(1,2) == "-") {
					price = price - priceChangeAmount;
					price1 = price1 - priceChangeAmount;
				} else {
					price = price + priceChangeAmount;
					price1 = price1 + priceChangeAmount;
				}
			}
		});
		
		$(".dkey").hide();
		for (i=0;i<displayKey.length;i++) {
			$(".dkey[dkey=" + displayKey[i] + "]").show();
		}
		$(".dkey:hidden").each(function() {
			var thisEl = $(this);
			if (thisEl.is('input') || $(this).is('textarea')) {
				thisEl.val("");
			} else if ($(this).is('select')) {
				thisEl.attr('selectedIndex', '-1');
			}

		});
		
		l18n_settings = $("#foxyshop_l18n").val();
		arrl18n_settings = l18n_settings.split("|")
		currencySymbol = arrl18n_settings[0];
		decimalSeparator = arrl18n_settings[1];
		thousandsSeparator = arrl18n_settings[2];
		p_precedes = arrl18n_settings[3];
		n_sep_by_space = arrl18n_settings[4];
		$("#foxyshop_main_price .foxyshop_currentprice").text(toCurrency(price, currencySymbol, thousandsSeparator, decimalSeparator, p_precedes, n_sep_by_space));
		$("#foxyshop_main_price .foxyshop_oldprice").text(toCurrency(price1, currencySymbol, thousandsSeparator, decimalSeparator, p_precedes, n_sep_by_space));
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