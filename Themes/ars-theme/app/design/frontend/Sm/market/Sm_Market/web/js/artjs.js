define( [ "jquery" ], function ( $ ) {
	"use strict";
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
	
	$( document ).ready(function() {
		
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