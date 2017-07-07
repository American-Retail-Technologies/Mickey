define( [ "jquery" ], function ( $ ) {
	"use strict";
	
	//Reduce the number of breadcrumbs if there is more than 4 categories
	function reduceBreadCrumb(){
		var $breadcrumb = $('.breadcrumbs ul li');
		var $disableBreadcrumb = $('.breadcrumbs ul li').length > 3 ? true : false;
		
		//console.log(disableBreadcrumb);
		
		if($disableBreadcrumb){
		  var $end = $('.breadcrumbs ul li').length - 2;
		  //console.log(end);
		  $.each($breadcrumb, function(index, elem) {
			if( index > 0 && index < $end){
				$(elem).css("display","none");
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