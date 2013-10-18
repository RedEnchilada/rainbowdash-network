$('head').append($('<style>#content .notice > :not(.notices) {pointer-events: none !important;}</style>'));

$('#content .notice').live('click', function() {
	var notice = $(this).clone(true);
	notice.find('.notices').remove();
	var popup = $('<div id="notice-popup"></div>');
	popup.append(notice);
	popup.append($('<a id="notice-popup-close">&#xf00d;</a>'));
	$('body').append(popup);
	adjustNoticePopup();
	$(window).bind('resize.popadjust', adjustNoticePopup);
	return false;
});

$('#notice-popup-close').live('click', function() {
	if($('body#shownotice').length) {
		parent.history.back();
		return false;
	}
	$('#notice-popup, #notice-popup-style').remove();
	$(window).unbind('.popadjust');
});

if($('body#shownotice').length) {
	$('.notice').click();
	$('#notice-popup').css({'background-color':'#889'});
}

$('#notice-popup .notice_reply').live('click', function(e) {
	e.preventDefault();
	var notice = $(this).closest('li.notice');
	SN.U.NoticeInlineReplyTrigger(notice);
	return false;
});

$('#user_info_card img, #user_info_card .profile_block_name').bind('click', function() {
	$('#user_info_card').toggleClass('opened');
	$('#nav_usercard').removeClass('opened');
	$('#site_nav_global_primary').removeClass('opened');
});

$('#user_info_card').append($('#dmcounter'));

// @fixme This isn't working any more for some reason... it worked in an earlier pass of the code. :c
$('a').live('click', function() {
	if($(this).is('#notice-popup a.timestamp') || $('body#shownotice').length)
		return true;
	var link = $(this).attr('href');
	var domain = window.location.pathname.split(/\/([^\/]|$)/)[0];
	if(!link || link.indexOf(domain) != 0 || link.indexOf(/\/notice\/[0-9]/) == -1) {
		link = $(this).attr('title');
		if(!link || link.indexOf(domain) != 0 || link.indexOf(/\/notice\/[0-9]/) == -1)
			return true;
	}
	var notice = link.split('/notice/')[1].split(/[^0-9]/)[0];
	if($('#content #notice-'+notice).length) {
		if($('#notice-popup-close').length) {
			$('#notice-popup, #notice-popup-style').remove();
			$(window).unbind('.popadjust');
		}
		$('#content #notice-'+notice).click();
		return false;
	}
	$.ajax({
		url: (link.split(/[#?]/, 1)[0]+'/r'),
		dataType: 'html',
		success: function(data) {
			var page = $('<ul>'+data+'</ul>');
			if(!page.find('.notice').length)
				return;
			var notice = page.find('.notice');
			if($('#notice-popup-close').length) {
				$('#notice-popup, #notice-popup-style').remove();
				$(window).unbind('.popadjust');
			}
			$('#content').append($('<ul style="display:none"></ul>').append(notice));
			notice.click();
			//notice.remove();
		}
	});
	return false;
});

function adjustNoticePopup() {
	try{$('#notice-popup-style').remove();}catch(e) {}
	var popup = $('#notice-popup');
	popup.css({'padding-bottom':'38px', 'padding-top':'38px'});
	var pad = $(document).scrollTop()+$(window).height()-(popup.find('div.entry-content').offset().top+popup.find('div.entry-content').outerHeight());
	if(pad > 78) {
		pad = (pad-10)/2;
		padAlt = pad-8;
		popup.css({'padding-bottom':pad+'px', 'padding-top':pad+'px'});
		$('head').append($('<style id="notice-popup-style">#notice-popup div.author{top:'+pad+'px}#notice-popup div.author:before,#notice-popup-close {top:'+padAlt+'px}'
			+'#notice-popup .notice:before{bottom:'+padAlt+'px}.notice-options{bottom:'+pad+'px}</style>'));
	}
}

$('#user_info_card #form_login input.submit').live('click', function() {
	window.location.href = $('#user_info_card #form_login').attr('action');
	return false;
});

$('.entity_actions h2').live('click', function() {
	$(this).parent().toggleClass('opened');
});

$('#site_nav_local_views li.current').live('click', function() {
	$('#site_nav_local_views').toggleClass('opened');
	return false;
});