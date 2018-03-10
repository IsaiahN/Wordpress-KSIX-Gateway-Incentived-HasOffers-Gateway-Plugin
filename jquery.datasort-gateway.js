/* 
* KSIX Network Gateway Functions
* Last Modifed: March 21st, 2013
*/
$(document).ready(function() {
	$("#ksix_background").hide();
	$(".ksix_gateway_button").click(function () {
		$("#ksix_background").show();
		var checkuser = setTimeout(getOfferStats, 60000) // Checks status every minute
	});
	$(".ksix_gateway_close").click(function () {
		$("#ksix_background").hide("fast");
		clearInterval(checkuser);
	});
	/* http://stackoverflow.com/questions/11007279/how-to-paginate-this-jquery-filter -Written By Dips */
	var itemsNumber = 5;
	var max = itemsNumber;
	var min = 0;
	
	function pagination(action) {
		var totalItems = $("#ksix_offerlist li.ksix_offer").length;
		if (max < totalItems) {
			if (action == "next") {
				min = min + itemsNumber;
				max = max + itemsNumber;
			}
		}
		if (min > 0) {
			if (action == "prev") {
				min = min - itemsNumber;
				max = max - itemsNumber;
			}
		}
		$("#ksix_offerlist li.ksix_offer").hide();
		$("#ksix_offerlist li.ksix_offer").slice(min, max).show();
	}
	$.ajax({
		type: "POST",
		url:"ksix_offerlist-gateway.php", 
		data: "&act=list",
		success: function(list){
			$("ul#ksix_offerlist").html(list);
			$("#ksix_offerlist li.ksix_offer").hide();
			$("#ksix_offerlist li.ksix_offer").slice(0, itemsNumber).show();	
		}
	}); 
	$("#ksix_prev").click(function(){
		pagination("prev");
	});
	$("#ksix_next").click(function(){
		pagination("next");
	});
	$('#ksix_category').change(function(){
		var ksix_category  = $('#ksix_category option:selected').val();
		$.ajax({
			type: "POST",
			url:"ksix_offerlist-gateway.php", 
			data: "&act=list&categories=" + ksix_category,
			success: function(list){
				$("ul#ksix_offerlist").html(list);
				min = 0;
				max = itemsNumber;	
				$("#ksix_offerlist li.ksix_offer").hide();
				$("#ksix_offerlist li.ksix_offer").slice(min, max).show();
			}
		});
	});
	function getOfferStats() {
		$.ajax({
			type: "POST",
			url:"ksix_offerlist-gateway.php", 
			data: "&act=check",
			success: function(result){
				if ( result !== "0" ) {
					$("ul#ksix_offerlist").html(result);
					clearInterval(checkuser);
				}
			}
		});
	}
	// http://stackoverflow.com/questions/5708509/jquery-search-multidimensional-ul-list &  John Strickler
	$('#ksix_search').change(function() {
		var searchTerms = $(this).val();
		$('#ksix_offerlist li').each(function() {
			var hasMatch = searchTerms.length == 0 || $(this).is(':contains(' + searchTerms  + ')');
			$("#ksix_offerlist").html(hasMatch);
		});
	});
});