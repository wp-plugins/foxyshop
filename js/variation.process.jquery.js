jQuery(document).ready(function($){
	$("form.foxyshop_product select").change(function(){
		updateVariations($(this));
	});
	updateVariations($("form.foxyshop_product select"));

	function updateVariations(elSelect) {
		price = parseFloat($("#price").val());
		price1 = parseFloat($("#originalprice").val());
		displayKey = new Array();
		elSelect.parents("form").find(".foxyshop_variations select option:selected").each(function(){
			var thisEl = $(this);
			var selectedValue = thisEl.val();
			
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
		$("#foxyshop_main_price .foxyshop_currentprice").text("$"+price.toFixed(2));
		$("#foxyshop_main_price .foxyshop_oldprice").text("$"+price1.toFixed(2));
		//alert('done');
	}
});