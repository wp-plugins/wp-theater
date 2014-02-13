(function($) {

	var $html = $('html'),
			$body = $('body'),
	    $window = $(window);

	$(document).ready(function() {

		if($('.wp-theater-section').length !== 0) {
			wp_theater_init();
		}

		// check if the theater is in use
		if($('.wp-theater-bigscreen').length !== 0) {
			wp_theater_bigscreen_init();
		}

	});

	function wp_theater_init() {
		$('.wp-theater-section').each(function(){

			var section = this,
					$section = $(this),
					service, $iframe, $bigscreen,
					$videos = $('.video-preview', this);

			if($('.video-preview').length === 0)
				return;

			// would be ideal to setup a redirect to
			// capture a request for all service types.  Until then...
			if($section.hasClass('youtube'))
				service = 'youtube';
			else if($section.hasClass('vimeo'))
				service = 'vimeo';

			// if the theater does not exist look for the data-theater-id
			if($('.wp-theater-iframe', this).length !== 0){
				$bigscreen = $('.wp-theater-bigscreen', this);
				$iframe = $('.wp-theater-iframe', this);
			}else if($section.is('[data-theater-id]') && $('#'+$section.attr('data-theater-id')).length !== 0){
				$bigscreen = $('#'+$section.attr('data-theater-id'));
				$iframe = $('#'+$section.attr('data-theater-id') + ' .wp-theater-iframe');
			}else {
				return;
			}

			$videos.children('a').click(function(e) { e.preventDefault(); });

			$videos.click(function(e) {
				$('.video-preview.selected', section).removeClass('selected');
				$(this).addClass('selected');
				$iframe.attr('src', $(this).attr('data-embed-url'));
				$iframe.attr('width', parseInt($(this).attr('data-embed-width')));
				$iframe.attr('height', parseInt($(this).attr('data-embed-height')));
				$iframe.trigger('changed');
				if(!elementIsInView($bigscreen)) {
					var h = $bigscreen.offset().top;
					if ($body.hasClass('masthead-fixed')) {
						h -= parseInt($('#masthead').height());
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
					$bigscreenInner = $('.wp-theater-bigscreen-inner', this),
					$bigscreenOptions = $('.wp-theater-bigscreen-options', this),
					$toggle_fullwindow = $('a.fullwindow-toggle', this),
					$toggle_lights = $('a.lowerlights-toggle', this),
					$iframe,
					fullWindowTimeout = false;

			if ($('.wp-theater-iframe', this).length !== 0)
				$iframe = $('.wp-theater-iframe', this);
			else return;

			$bigscreenOptions.show();
			wp_theater_iframeHeightAuto($iframe);

			// check if there is a full window toggle button
			if($toggle_fullwindow.length !== 0) {
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

			// check if there is a full window toggle button
			if($toggle_lights.length !== 0) {
				$toggle_lights.click(function(e) {

					if($bigscreen.hasClass('lowerlights')){
						$bigscreen.removeClass('lowerlights');
						$('#wp-theater-lowerlights').fadeOut(1000, function() {
							$('#wp-theater-lowerlights').hide();
						});
					}else{
						$bigscreen.addClass('lowerlights');
						// NEEDS: Only add this once and reuse, IE8 bug.
						if ($('#wp-theater-lowerlights').length == 0)
							$body.prepend('<div id="wp-theater-lowerlights">&nbsp;</div>')
						$('#wp-theater-lowerlights').hide().fadeIn(1000);
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

	function elementIsInView($elem) {
    var docViewTop = $window.scrollTop();
    var docViewBottom = docViewTop + $window.height();

    var elemTop = $elem.offset().top;
    var elemBottom = elemTop + $elem.height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	}

})(jQuery);