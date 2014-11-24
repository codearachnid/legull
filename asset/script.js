jQuery(document).ready(function ($) {
	if( $('body').hasClass('legull-admin') ){
		if( $('body').hasClass('post-type-legull_terms') ){
			var legullMenu = jQuery('li#toplevel_page_Legull');
				legullMenu.addClass('wp-menu-open wp-has-current-submenu').removeClass('wp-not-current-submenu');
				legullMenu.find('> a.menu-top-last').addClass('wp-menu-open wp-has-current-submenu').removeClass('wp-not-current-submenu');
				legullMenu.find('.wp-submenu li').removeClass('current').eq(3).addClass('current');
		}
		$('.postbox-container .postbox .inside p').readmore({
			speed    : 75,
			maxHeight: 55
		});
	}
});