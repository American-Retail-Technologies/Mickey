define( [ "jquery" ], function ( $ ) {
	"use strict";
	console.log("ART JS FILE");
	//Reduce the number of breadcrumbs if there is more than 4 categories
	function reduceBreadCrumb(){
		var $totalBreadcrumb = $('.breadcrumbs ul li').length;
		
		//Level 1
		if($totalBreadcrumb == 2){
			$('.breadcrumbs').css('display', 'none');
		}else { //Level >2
			var $breadcrumb = $('.breadcrumbs ul li');
			$.each($breadcrumb, function(index, elem){
				if( 0 < index && index < ($totalBreadcrumb - 2)){
				  $(elem).find('a').text('...');;
				}else if( index == ($totalBreadcrumb - 2)){
				  //Previous category can only hold 22 characters for iphone6
				  //if greater than substring and add '...' to the end 
				  var $previousCategory = $(elem).find('a').text().length > 22 ? ($(elem).find('a').text().substring(0, 19) + '...') : $(elem).find('a').text();
				  $(elem).find('a').text($previousCategory);
				}
				else if( index == $totalBreadcrumb -1 ){
				  $(elem).css('display', 'none');
				}
			});
		}
	}
	
	function changeLTLShippingPrice(){
		console.log("changingLTLPrice function");
		if( $("#co-shipping-method-form")[0]){
			$(".field.choice.item [for='s_method_shipping']").text('LTL/ Freight Quote');
		}
		if( $(".data.table.totals")[0]){
			var $shippingMethod = $(".data.table.totals .totals.shipping.excl .mark .value").text();
			if($shippingMethod === "(Shipping Will Be Quoted Separately - LTL/ Freight Quote)"){
				$(".totals.shipping.excl .amount .price").text('Quoted Seperately');
			}
		}

		if($(".table-checkout-shipping-method")[0]){
			var $shippingMethod = $(".table-checkout-shipping-method #label_method_shipping_shipping").text();

			if($shippingMethod === "LTL/ Freight Quote"){
				//changed price to new text
				$(".table-checkout-shipping-method .price .price").text('Quoted Seperately');
			}

		}
		if($(".data.table.table-totals")[0]){
			var $shippingMethod = $(".data.table.table-totals .totals.shipping.excl .value").text();

			if($shippingMethod === "Shipping Will Be Quoted Separately - LTL/ Freight Quote"){
				//changed price to new text
				$(".data.table.table-totals .totals.shipping.excl .amount .price").text('Quoted Seperately');
			}
		}
	}
	
	$( document ).ready(function() {
		changeLTLShippingPrice();
		//https://stackoverflow.com/questions/9720294/jquery-how-to-detect-window-width-on-the-fly
		var $window = $(window);
		
		if ($window.width() <= 768) {
			reduceBreadCrumb();
		}
		
		function checkScreenSize() {
			var windowsize = $window.width();
			if (windowsize <= 768) {
				reduceBreadCrumb();
			}
		}
		
		// Bind event listener
		$(window).resize(checkScreenSize);
		
		
	});
});