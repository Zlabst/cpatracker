var toggle_sidebar = function(event){
		event.stopPropagation();
		"use strict";
		$(".top-navbar").toggleClass("toggle-left");
		$(".sidebar-left").toggleClass("toggle-left");
		$(".page-content").toggleClass("toggle-left");
		$(".btn-collapse-sidebar-left").toggleClass("rotate-180");
		$(".sidebar-menu").toggle();
		
		$.cookie('cpa_menu_main', $(".top-navbar").hasClass("toggle-left") ? 1 : 0, {expires: 7});
};

$(document).ready(function() {
	<!-- =============================================== -->
	<!-- ============= Sidebar functions  ============== -->
	<!-- =============================================== --> 
	$('.sidebar-left ul.sidebar-menu li a').click(function() {
		"use strict";
		if($(this).attr('id') == 'add_cat_link') return false;
		$('.sidebar-left li').removeClass('active');
		$(this).closest('li').addClass('active');	
		var checkElement = $(this).next();
			if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
				$(this).closest('li').removeClass('active');
				checkElement.slideUp('fast');
			}
			if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
				$('.sidebar-left ul.sidebar-menu ul:visible').slideUp('fast');
				checkElement.slideDown('fast');
			}
			if($(this).closest('li').find('ul').children().length == 0) {
				return true;
				} else {
				return false;	
			}		
	});

	<!-- =============================================== -->
	<!-- ============= Sidebar toggle button  ========== -->
	<!-- =============================================== --> 
	$(".btn-collapse-sidebar-left").click(toggle_sidebar);
	$(".sidebar-left").click(function(event) { 
		if($(this).hasClass('toggle-left')) {
			console.log('tl');
		toggle_sidebar(event);}
	});
	
	<!-- =============================================== -->
	<!-- =========== Icheck - CPA Skins  ========== -->
	<!-- =============================================== --> 
	if ($('.i-blue').length > 0){
		$('input.i-blue').iCheck({
			checkboxClass: 'icheckbox-cpa-blue',
			increaseArea: '-20%'
		});
	}
	if ($('.i-star').length > 0){
		$('input.i-star').iCheck({
			checkboxClass: 'icheckbox-cpa-star',
			increaseArea: '-20%'
		});
		$('input.i-blue').on('ifToggled', function(event){
		  $(this).parents('tr').toggleClass("checked")
		});
	}

	<!-- =============================================== -->
	<!-- =========== Start Tooltip  ========== -->
	<!-- =============================================== --> 
	 $('[data-toggle="tooltip"]').tooltip({
	 	container: 'body',
		delay: { "show": 1300, "hide": 100 }
	 });
	 
	<!-- =============================================== -->
	<!-- =========== Start Popover  ========== -->
	<!-- =============================================== --> 
     $('a[data-toggle="popover"]').popover({
	    container: 'body',
	    trigger: 'click',
		html: 'true',
		placement: 'bottom',
		content : function() {
		    return $('#popover-content').html();
		}
    }).on('shown.bs.popover', function(e) {
        $('.selectpicker2').selectpicker('refresh');
        
           // Define elements
		    var current_trigger=$(this);
		    var current_popover=current_trigger.data('bs.popover').tip();
		
		    // Activate close button
		    current_popover.find('#offer-form-close').click(function() {
		        current_trigger.popover('hide');
		    });
    });
	
	$('html').on('mouseup', function (e) {
	    $('a[data-toggle="popover"]').each(function () {
	        //the 'is' for buttons that trigger popups
	        //the 'has' for icons within a button that triggers a popup
	        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
	            $(this).popover('hide');
	        }
	    });
	});    

}); // Document ready