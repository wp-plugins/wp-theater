(function($) {

	var $html = $('html'),
			$body = $('body'),
	    $window = $(window);

	$(document).ready(function() {

		// find placeholders and setup for those
		if($('.wp-theater-placeholder').length){
			$('.wp-theater-placeholder').each(function(){

				var $placeholder    = $(this),
				    $placeholderA   = $placeholder.find('a'),
				    $placeholderIMG = $placeholderA.find('img');

				$placeholderA.click (function(e){
					e.preventDefault();
					$placeholder.wrapAll('<div class="wp-theater-iframe-wrapper">');
					$placeholder.html('<iframe class="'+$placeholder.attr('data-embed-class')+'" width="'+$placeholderIMG.width()+'" height="'+$placeholderIMG.height()+'" src="'+$placeholder.attr('data-embed-url')+'&autoplay=1" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>');
					//initialize what must be a theater.
					// crawl up to the parent and see if its bigscreen
					$mightBeBigScreen = $placeholder.parent().parent().parent();
					if($mightBeBigScreen.hasClass('wp-theater-bigscreen')){
						wp_theater_bigscreen_init($mightBeBigScreen);
						// crawl up further to see if its a section
						$mightBeSection = $mightBeBigScreen.parent();
						if($mightBeSection.hasClass('wp-theater-section')){
							wp_theater_init($mightBeSection);
						}
				}
				});
			});
		}

		// and do a normal startup for any existing iframes
		if($('.wp-theater-section').not(':has(.wp-theater-placeholder img)').length) {
			$('.wp-theater-section').not(':has(.wp-theater-placeholder img)').each(function(){
				wp_theater_init($(this));
			});
		}

		// check if the theater is in use
		if($('.wp-theater-bigscreen').not(':has(.wp-theater-placeholder img)').length) {
			$('.wp-theater-bigscreen').not(':has(.wp-theater-placeholder img)').each(function(){
				wp_theater_bigscreen_init($(this));
			});
		}

	});

	function wp_theater_init(target) {

			var $section = target,
					$iframe, $iframewrapper, $bigscreen,
					$videos = $section.find('.video-preview'),
					external_theater = false;

			if(!$videos.length)
				return false;

			// if the theater does not exist look for the data-theater-id
			if($section.find('.wp-theater-iframe').length){
				$bigscreen = $section.find('.wp-theater-bigscreen');
				$iframe = $section.find('.wp-theater-iframe');
			}else if($section.is('[data-theater-id]') && $('#'+$section.attr('data-theater-id')).length){
				$bigscreen = $('#'+$section.attr('data-theater-id'));
				$iframe = $('#'+$section.attr('data-theater-id') + ' .wp-theater-iframe');
				external_theater = true;
			}else {
				return false;
			}

			$videos.find('a').click(function(e) { e.preventDefault(); });

			$videos.click(function(e) {
				$video = $(this);
				if (external_theater){
					$('.wp-theater-section[data-theater-id="' + $section.attr('data-theater-id') +'"]').find('.video-preview.selected').removeClass('selected');
				}else{
					$section.find('.video-preview.selected').removeClass('selected');
				}
				$video.addClass('selected');
				$iframe.attr('src', $video.attr('data-embed-url'));
				if(!wp_theater_elementIsInView($bigscreen)) {
					var h = $bigscreen.offset().top;
					if ($body.hasClass('masthead-fixed')) {
						if ($('.site-header').length)
							h -= parseInt($('.site-header:first-child').height());
						else if ($('#masthead').length)
							h -= parseInt($('#masthead').height());
					}
					if ($('#wpadminbar').length) {
						h -= parseInt($('#wpadminbar').height());
					}

					$('html, body').animate({
						scrollTop:Math.round(h-15)+'px'
					}, 300);
				}
			});
	}

	function wp_theater_bigscreen_init(target) {

			var $bigscreen = target,
					$bigscreenInner = $bigscreen.find('.wp-theater-bigscreen-inner'),
					$bigscreenOptions = $bigscreen.find('.wp-theater-bigscreen-options'),
					$toggle_fullwindow = $bigscreen.find('a.fullwindow-toggle'),
					$toggle_lights = $bigscreen.find('a.lowerlights-toggle'),
					$iframe,
					fullWindowTimeout = false;

			if ($bigscreen.find('.wp-theater-iframe').length)
				$iframe = $bigscreen.find('.wp-theater-iframe');
			else return;

			$bigscreenOptions.show();

			// check if there is a full window toggle button
			if($toggle_fullwindow.length) {
				$toggle_fullwindow.click(function(e) {
					if(!$bigscreen.hasClass('fullwindow')){
						wp_theater_openFullWindow($bigscreen);
					}else{
						wp_theater_closeFullWindow($bigscreen);
					}
					e.preventDefault();
				});
			}

			// check if there is a full lower lights toggle button
			if($toggle_lights.length) {
				$toggle_lights.click(function(e) {
					if($bigscreen.hasClass('lowerlights')){
						wp_theater_raiseLights($bigscreen);
					}else{
						wp_theater_lowerLights($bigscreen);
					}
					e.preventDefault();
				});
			}
	}

	function wp_theater_raiseLights(target){
		if(target.hasClass('lowerlights')){
			var $backgrop = $('#wp-theater-lowerlights');
			target.removeClass('lowerlights');
			$backgrop.fadeOut(1000, function() {
				$backgrop.hide();
			});
		}
	}

	function wp_theater_lowerLights(target){
		target.addClass('lowerlights');
		// make sure the overlay is only added once.
		if (!$('#wp-theater-lowerlights').length)
			$body.prepend('<div id="wp-theater-lowerlights">&nbsp;</div>')
		$('#wp-theater-lowerlights').hide(0).fadeIn(1000);
	}

	function wp_theater_openFullWindow(target){
		//has to be first
		wp_theater_lockScroll();
		target.find('.wp-theater-iframe').attr('style', '');
		target.addClass('fullwindow');
	}

	function wp_theater_closeFullWindow(target){
		target.removeClass('fullwindow');
		target.find('.wp-theater-iframe').attr('style', '');
		target.find('.wp-theater-bigscreen-options').attr('style', '').show();
		// has to be last
		wp_theater_unlockScroll();
	}

	function wp_theater_lockScroll(){

		var initWidth = $body.outerWidth(),
				initHeight = $body.outerHeight(),
				scrollPosition = [
					self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
					self.pageYOffset || document.documentElement.scrollTop  || document.body.scrollTop
				];

		$html.data('wpt-scroll-position', scrollPosition);
		$html.data('wpt-previous-overflow', $html.css('overflow'));
		$html.css('overflow', 'hidden');
		window.scrollTo(scrollPosition[0], scrollPosition[1]);

		var marginR = $body.outerWidth()-initWidth;
		var marginB = $body.outerHeight()-initHeight; 
		$body.css({'margin-right': marginR,'margin-bottom': marginB});
	} 

	function wp_theater_unlockScroll(){
		$html.css('overflow', $html.data('wpt-previous-overflow'));
		var scrollPosition = $html.data('wpt-scroll-position');
		window.scrollTo(scrollPosition[0], scrollPosition[1]);    

		$body.css({"margin-right":0,"margin-bottom":0});
	}

	function wp_theater_elementIsInView($elem) {
    var docViewTop = $window.scrollTop();
    var docViewBottom = docViewTop + $window.height();

    var elemTop = $elem.offset().top;
    var elemBottom = elemTop + $elem.height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	}

})(jQuery);(function($) {

	var $html = $('html'),
			$body = $('body'),
	    $window = $(window);

	$(document).ready(function() {

		if($('.wp-theater-section').length) {
			wp_theater_init();
		}

		// check if the theater is in use
		if($('.wp-theater-bigscreen').length) {
			wp_theater_bigscreen_init();
		}

	});

	function wp_theater_init() {
		$('.wp-theater-section').each(function(){

			var section = this,
					$section = $(this),
					$iframe, $bigscreen,
					$videos = $('.video-preview', this);
					external_theater = false;

			if(!$('.video-preview').length)
				return;

			// if the theater does not exist look for the data-theater-id
			if($('.wp-theater-iframe', this).length){
				$bigscreen = $('.wp-theater-bigscreen', this);
				$iframe = $('.wp-theater-iframe', this);
			}else if($section.is('[data-theater-id]') && $('#'+$section.attr('data-theater-id')).length){
				$bigscreen = $('#'+$section.attr('data-theater-id'));
				$iframe = $('#'+$section.attr('data-theater-id') + ' .wp-theater-iframe');
				external_theater = true;
			}else {
				return;
			}

			$videos.find('a').click(function(e) { e.preventDefault(); });

			$videos.click(function(e) {
				$video = $(this);
				if (external_theater){
					$('.wp-theater-section[data-theater-id="' + $section.attr('data-theater-id') +'"]').find('.video-preview.selected').removeClass('selected');
				}else{
					$section.find('.video-preview.selected').removeClass('selected');
				}
				$video.addClass('selected');
				$iframe.attr('src', $video.attr('data-embed-url'));
				$iframe.attr('width', parseInt($video.attr('data-embed-width')));
				$iframe.attr('height', parseInt($video.attr('data-embed-height')));
				$iframe.trigger('changed');
				if(!wp_theater_elementIsInView($bigscreen)) {
					var h = $bigscreen.offset().top;
					if ($body.hasClass('masthead-fixed')) {
						if ($('#masthead').length)
							h -= parseInt($('#masthead').height());
						else if ($('.site-header').length)
							h -= parseInt($('.site-header:first-child').height());
					}
					if ($('#wpadminbar').length) {
						h -= parseInt($('#wpadminbar').height());
					}

					// get height of masthead and add it to our 25px
					$('html, body').animate({
						scrollTop:Math.round(h-15)+'px'
					}, 300);
				}
			});
		});
	}

	function wp_theater_bigscreen_init() {
		$('.wp-theater-bigscreen').each(function(){

			var bigscreen = this,
					$bigscreen = $(this),
					$bigscreenInner = $bigscreen.find('.wp-theater-bigscreen-inner'),
					$bigscreenOptions = $bigscreen.find('.wp-theater-bigscreen-options'),
					$toggle_fullwindow = $bigscreen.find('a.fullwindow-toggle'),
					$toggle_lights = $bigscreen.find('a.lowerlights-toggle'),
					$iframe,
					fullWindowTimeout = false;

			if ($('.wp-theater-iframe', this).length)
				$iframe = $('.wp-theater-iframe', this);
			else return;

			$bigscreenOptions.show();
			wp_theater_iframeHeightAuto($iframe);

			// check if there is a full window toggle button
			if($toggle_fullwindow.length) {
				$toggle_fullwindow.click(function(e) {
					if(!$bigscreen.hasClass('fullwindow')){
						//has to be first
						wp_theater_lockScroll();
						$iframe.attr('style', '');
						$bigscreen.addClass('fullwindow');
						$body.addClass('fullwindow');
						wp_theater_keepiFrameRatio($iframe, parseInt($bigscreenInner.width()), parseInt($bigscreenInner.height()) - parseInt($toggle_lights.height()), $bigscreenOptions);
					}else{
						$bigscreen.removeClass('fullwindow');
						$iframe.attr('style', '');
						$bigscreenOptions.attr('style', '');
						$bigscreenOptions.show();
						wp_theater_iframeHeightAuto($iframe);
						// has to be last
						wp_theater_unlockScroll();
					}
					e.preventDefault();
				});
			}

			$window.resize(function(){
				if($bigscreen.hasClass('fullwindow')) {
					fullWindowTimeout = setTimeout(function() {
						wp_theater_keepiFrameRatio($iframe, parseInt($bigscreenInner.width()), parseInt($bigscreenInner.height()) - parseInt($toggle_lights.height()), $bigscreenOptions);
					}, 100);
				} else if (typeof $bigscreen.attr('data-keepratio') !== "undefined") {	
					fullWindowTimeout = setTimeout(function() {
						wp_theater_iframeHeightAuto($iframe);
					}, 100);
				} else {
					clearTimeout(fullWindowTimeout);
				}
			});

			// check if there is a full lower lights toggle button
			if($toggle_lights.length) {
				$toggle_lights.click(function(e) {

					if($bigscreen.hasClass('lowerlights')){
						$bigscreen.removeClass('lowerlights');
						$('#wp-theater-lowerlights').fadeOut(1000, function() {
							$('#wp-theater-lowerlights').hide();
						});
					}else{
						$bigscreen.addClass('lowerlights');
						// make sure the overlay is only added once.
						if ($('#wp-theater-lowerlights').length)
							$body.prepend('<div id="wp-theater-lowerlights">&nbsp;</div>')
						$('#wp-theater-lowerlights').hide(0).fadeIn(1000);
					}

					e.preventDefault();
				});
			}
		});
	}

	function wp_theater_iframeHeightAuto(iframe) {
		var width = parseInt(iframe.width()),
				height = parseInt(iframe.height()),
				awidth = parseInt(iframe.attr('width')),
				aheight = parseInt(iframe.attr('height'));
		var ratio = awidth/aheight;

		iframe.height(width/(awidth/aheight));
	}

	function wp_theater_keepiFrameRatio(iframe, maxWidth, maxHeight, details) { /* had to put details in or update it each time above ^ */

		var maxWidth = parseInt(maxWidth),
				maxHeight = parseInt(maxHeight);
		var result = wp_theater_scaleRatio(parseInt(iframe.attr('width'))/parseInt(iframe.attr('height')), maxWidth-40, maxHeight-30);
		//var result = wp_theater_scaleRatio(1.777777777, maxWidth-40, maxHeight-40);

		details.css("width" , Math.round(result.width)+"px");
		iframe.css("width" , Math.round(result.width)+"px");
		iframe.css("height" , Math.round(result.height)+"px");
		details.css("margin-left" , Math.round(result.x+20)+"px");
		iframe.css("margin-left" , Math.round(result.x+20)+"px");
		iframe.css("margin-top" , Math.round(result.y+15)+"px");
	}

	function wp_theater_scaleRatio(ratio, targetWidth, targetHeight) {
		var result = {};

		if(ratio >= targetWidth/targetHeight) {
			//parent is taller than target ratio
			result.width = parseInt(targetWidth);
			result.height = parseInt(targetWidth/ratio);
			result.x = 0;
			result.y = parseInt(targetHeight-result.height)/2;
		} else {
			//parent is wider than target ratio
			result.width = parseInt(targetHeight*ratio);
			result.height = parseInt(targetHeight);
			result.x = parseInt(targetWidth-result.width)/2;
			result.y = 0;
		}

		return result;
	}

	function wp_theater_lockScroll(){

		var initWidth = $body.outerWidth(),
				initHeight = $body.outerHeight(),
				scrollPosition = [
					self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
					self.pageYOffset || document.documentElement.scrollTop  || document.body.scrollTop
				];

		$html.data('scroll-position', scrollPosition);
		$html.data('previous-overflow', $html.css('overflow'));
		$html.css('overflow', 'hidden');
		window.scrollTo(scrollPosition[0], scrollPosition[1]);

		var marginR = $body.outerWidth()-initWidth;
		var marginB = $body.outerHeight()-initHeight; 
		$body.css({'margin-right': marginR,'margin-bottom': marginB});
	} 

	function wp_theater_unlockScroll(){
		$html.css('overflow', $html.data('previous-overflow'));
		var scrollPosition = $html.data('scroll-position');
		window.scrollTo(scrollPosition[0], scrollPosition[1]);    

		$body.css({"margin-right":0,"margin-bottom":0});
	}

	function wp_theater_elementIsInView($elem) {
    var docViewTop = $window.scrollTop();
    var docViewBottom = docViewTop + $window.height();

    var elemTop = $elem.offset().top;
    var elemBottom = elemTop + $elem.height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	}

})(jQuery);