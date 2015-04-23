(function($) {

	var $html = $('html'),
			$body = $('body'),
	    $window = $(window);

	$(document).ready(function() {


		// find placeholders and set those up
		if($('.wp-theater-placeholder > a').length){
			$('.wp-theater-placeholder').each(function(){

				var $placeholder = $(this);

				$placeholder.find('a').click (function(e){
					e.preventDefault();
					wp_theater_replace_placeholder($placeholder, $placeholder.attr('data-embed-url')+'&autoplay=1')
				});
			});
		}

		// Startup listings
		if($('.wp-theater-section').length) {
			$('.wp-theater-section').each(function(){
				wp_theater_init($(this));
			});
		}

		// And Theaters/Bigscreens
		if($('.wp-theater-bigscreen').not(':has(.wp-theater-placeholder img)').length) {
			$('.wp-theater-bigscreen').not(':has(.wp-theater-placeholder img)').each(function(){
				wp_theater_bigscreen_init($(this));
			});
		}

	});

	function wp_theater_replace_placeholder(placeholder, src){
		var placeholderIMG = placeholder.find('img');
		placeholder.wrapAll('<div class="wp-theater-iframe-wrapper">');
		placeholder.html('<iframe class="'+placeholder.attr('data-embed-class')+'" width="'+placeholderIMG.width()+'" height="'+placeholderIMG.height()+'" src="'+ src +'" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>');			
		// setup the theater if its there.
		mightBeBigScreen = placeholder.closest('.wp-theater-bigscreen');
		if(placeholder.closest('.wp-theater-bigscreen').length){
			wp_theater_bigscreen_init(placeholder.closest('.wp-theater-bigscreen'));
		}
	}

	function wp_theater_change_video(bigscreen, src){
		// check for and replace the placeholder.
		if(bigscreen.find('.wp-theater-placeholder > a').length){
			wp_theater_replace_placeholder(bigscreen.find('.wp-theater-placeholder'), src);
		}
		if(bigscreen.find('.wp-theater-iframe').length){
			iframe = bigscreen.find('.wp-theater-iframe');
			iframe.attr('src', src);
		}
	}


	function wp_theater_init(target) {

			var $section = target,
					$iframe, $iframewrapper, $bigscreen,
					$videos = $section.find('.video-preview'),
					external_theater = false;

			if(!$videos.length)
				return false;

			// set the target theater/bigscreen data-theater-id
			if($section.find('.wp-theater-bigscreen').length){
				$bigscreen = $section.find('.wp-theater-bigscreen');
			}else if($section.is('[data-theater-id]') && $('#'+$section.attr('data-theater-id')).length){
				$bigscreen = $('#'+$section.attr('data-theater-id'));
				external_theater = true;
			}else {
				return false;
			}

			$videos.find('a').click(function(e) { e.preventDefault(); });

			$videos.click(function(e) {
				$video = $(this);

				// update the selected style
				if (external_theater){
					// find any section with the same theater targeted and remove the selected item.
					$('.wp-theater-section[data-theater-id="' + $section.attr('data-theater-id') +'"]').find('.video-preview.selected').removeClass('selected');
				}else{
					$section.find('.video-preview.selected').removeClass('selected');
				}
				$video.addClass('selected');


				// change the video
				wp_theater_change_video($bigscreen, $video.attr('data-embed-url'));


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

})(jQuery);