function mmiOptions(){
			
	// show 1st at start	
	if( jQuery('#last_tab').val() == 0 ){
		this.tabid = jQuery('.mmi-submenu-a:first').attr('data-rel');
	} else {
		this.tabid = jQuery('#last_tab').val();
	}

	jQuery('#'+this.tabid+'-mmi-section').show();
	jQuery('#'+this.tabid+'-mmi-submenu-li').addClass('active').parent('ul').show().parent('li').addClass('active');
	
	// parent manu click - show childrens and select 1st
	jQuery('.mmi-menu-a').click(function(){
		
		if( ! jQuery(this).parent().hasClass('active') ) {
			
			jQuery('.mmi-menu-li').removeClass('active');
			jQuery('.mmi-submenu').slideUp('fast');
			
			jQuery(this).next('ul').stop().slideDown('fast');
			jQuery(this).parent('li').addClass('active');
			
			jQuery('.mmi-submenu-li').removeClass('active');
			jQuery('.mmi-section').hide();
			
			this.tabid = jQuery(this).next('ul').children('li:first').addClass('active').children('a').attr('data-rel');
			jQuery('#'+this.tabid+'-mmi-section').stop().fadeIn(1200);
			jQuery('#last_tab').val(this.tabid);
		}
		
	});
	
	// submenu click
	jQuery('.mmi-submenu-a').click(function(){
		
		if( ! jQuery(this).parent().hasClass('active') ) {

			jQuery('.mmi-submenu-li').removeClass('active');
			jQuery(this).parent('li').addClass('active');
			
			jQuery('.mmi-section').hide();
			
			this.tabid = jQuery(this).attr('data-rel');
			jQuery('#'+this.tabid+'-mmi-section').stop().fadeIn(1200);
			jQuery('#last_tab').val(this.tabid);
		}
		
	});
	
	// last w menu
	jQuery('.mmi-submenu .mmi-submenu-li:last-child').addClass('last');

	// reset
	jQuery('.mmi-popup-reset').click(function(){
		if( confirm( "Are you sure? Resetting will loose all custom values!" ) ){
			return true;
	    } else {
	    	return false;
	    }
	});
	
	// import code button
	jQuery('.mmi-import-imp-code-btn').click(function(){
		jQuery('.mmi-import-imp-link-wrapper').hide();
		jQuery('.mmi-import-imp-code-wrapper').stop().fadeIn(500);
	});
	
	// import link button
	jQuery('.mmi-import-imp-link-btn').click(function(){
		jQuery('.mmi-import-imp-code-wrapper').hide();
		jQuery('.mmi-import-imp-link-wrapper').stop().fadeIn(500);
	});
	
	// export code button
	jQuery('.mmi-import-exp-code-btn').click(function(){
		jQuery('.mmi-import-exp-link').hide();
		jQuery('.mmi-import-exp-code').stop().fadeIn(500);
	});
	
	// export link button
	jQuery('.mmi-import-exp-link-btn').click(function(){
		jQuery('.mmi-import-exp-code').hide();
		jQuery('.mmi-import-exp-link').stop().fadeIn(500);
	});
	
}
	
jQuery(document).ready(function(){
	new mmiOptions();
});