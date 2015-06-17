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
	if ($('[data-toggle="tooltip"]').length > 0) {
		 $('[data-toggle="tooltip"]').tooltip({
		 	container: 'body',
		 	delay: { "show": 500, "hide": 100 }
		 });
	 }
	 
	<!-- =============================================== -->
	<!-- =========== Start Popover  ========== -->
	<!-- =============================================== --> 
	if ($('a[data-toggle="popover"]').length > 0) {
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
	}
	
	$('[data-toggle="install-popover"]').popover({
		container: "body",
		placement: "right",
		trigger: "click",
		html : true,
        content: function() {
			var content = $(this).attr("data-popover-content");
			return $(content).children(".popover-body").html();
        },
		template: '<div class="popover install-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'		
	})
	
//	if ($('.condition-add').length > 0) {
//		$('.condition-add').select2({
//			theme: 'classic',
//			language: 'ru',
//		    minimumResultsForSearch: 20,
//		    placeholder: "Добавить условие",
//		});
//	}

	<!-- =============================================== -->
	<!-- =========== Bootstrap Date Picker ========== -->
	<!-- =============================================== --> 
	var tmp = $.fn.popover.Constructor.prototype.show;
	$.fn.popover.Constructor.prototype.show = function () {
	  tmp.call(this);
	  if (this.options.callback) {
	    this.options.callback();
	  }
	}
	if ($('[data-toggle="range-popover"]').length > 0) {
		$('[data-toggle="range-popover"]').popover({
			container: "body",
			placement: "right",
			trigger: "click",
			html : true,
	        content: function() {
				var content = $(this).attr("data-popover-content");
				return $(content).children(".popover-body").html();
	        },
			template: '<div class="popover range-popover" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>',		
	     
		    callback: function () {
		        $(".datepicker").datepicker({
			       format: 'dd.mm.yyyy',
			       language: 'ru',
		        });
		    }
	 	}).click(function (e) {
	        e.preventDefault();
		});
	}
	
	// Avoid dropdown menu close on click inside
//    $('.date-select .dropdown-footer a').click(function(e) {
//          e.stopPropagation();
//    });
	if ($('.date-select a').length > 0) {
	    $('.date-select a').on('click', function (event) {
		    $(this).parent().toggleClass("open");
		    return false;
		});
	}
//	$('body').on('click', function (e) {
//	    if (!$('li.dropdown.mega-dropdown').is(e.target) && $('li.dropdown.mega-dropdown').has(e.target).length === 0 && $('.open').has(e.target).length === 0) {
//	        $('li.dropdown.mega-dropdown').removeClass('open');
//	    }
//	});
	if ($("#datepicker-single").length > 0) {
		$("#datepicker-single").datepicker({
		   format: 'dd.mm.yyyy',
		   language: 'ru',
		}).on('changeDate', function(e){
	        $(this).find('h2 span').text(e.format('dd MM yyyy'));
	        window.location.href = modify_link(window.location.href, 'date', e.format('yyyy-mm-dd'));
	    });
	}
	
	<!-- =============================================== -->
	<!-- =========== Table collapse  ========== -->
	<!-- =============================================== --> 
	if ($('[data-toggle=collapse-next]').length > 0) {	
		$('body').on('click.collapse-next.data-api', '[data-toggle=collapse-next]', function (e) {
			var $target = $(this).parent().next().find('.collapse');
			$target.collapse('toggle');	
		})
	}

}); // Document ready