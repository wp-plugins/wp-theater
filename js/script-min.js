(function(i){var e=i("html"),f=i("body"),b=i(window);i(document).ready(function(){if(i(".wp-theater-section").length!==0){m()}if(i(".wp-theater-bigscreen").length!==0){c()}});function m(){i(".wp-theater-section").each(function(){var s=this,q=i(this),n,r,p,o=i(".video-preview",this);if(i(".video-preview").length===0){return}if(q.hasClass("youtube")){n="youtube"}else{if(q.hasClass("vimeo")){n="vimeo"}}if(i(".wp-theater-iframe",this).length!==0){p=i(".wp-theater-bigscreen",this);r=i(".wp-theater-iframe",this)}else{if(q.is("[data-theater-id]")&&i("#"+q.attr("data-theater-id")).length!==0){p=i("#"+q.attr("data-theater-id"));r=i("#"+q.attr("data-theater-id")+" .wp-theater-iframe")}else{return}}o.children("a").click(function(t){t.preventDefault()});o.click(function(t){i(".video-preview.selected",s).removeClass("selected");i(this).addClass("selected");r.attr("src",i(this).attr("data-embed-url"));h=parseInt(i(this).attr("data-embed-height"));w=parseInt(i(this).attr("data-embed-width"));ratio=w/h;r.attr("width",w);r.attr("height",h);r.trigger("changed");if(!a(p)){i("html, body").animate({scrollTop:(p.offset().top-25)+"px"},300)}})})}function c(){i(".wp-theater-bigscreen").each(function(){var u=this,q=i(this),o=i(".wp-theater-bigscreen-inner",this),s=i(".wp-theater-bigscreen-options",this),p=i("a.fullwindow-toggle",this),n=i("a.lowerlights-toggle",this),r,t=false;if(i(".wp-theater-iframe",this).length!==0){r=i(".wp-theater-iframe",this)}else{return}s.show();d(r);if(p.length!==0){p.click(function(v){if(!q.hasClass("fullwindow")){k();r.attr("style","");q.addClass("fullwindow");f.addClass("fullwindow");l(r,parseInt(o.width()),parseInt(o.height())-parseInt(n.height()),s)}else{q.removeClass("fullwindow");r.attr("style","");s.attr("style","");s.show();d(r);g()}v.preventDefault()})}b.resize(function(){if(q.hasClass("fullwindow")){t=setTimeout(function(){l(r,parseInt(o.width()),parseInt(o.height())-parseInt(n.height()),s)},100)}else{if(typeof q.attr("data-keepratio")!=="undefined"){t=setTimeout(function(){d(r)},100)}else{clearTimeout(t)}}});if(n.length!==0){n.click(function(v){if(q.hasClass("lowerlights")){q.removeClass("lowerlights");i("#wp-theater-lowerlights").fadeOut(1000,function(){i("#wp-theater-lowerlights").hide()})}else{q.addClass("lowerlights");if(i("#wp-theater-lowerlights").length==0){f.prepend('<div id="wp-theater-lowerlights">&nbsp;</div>')}i("#wp-theater-lowerlights").hide().fadeIn(1000)}v.preventDefault()})}})}function d(q){var p=parseInt(q.width()),n=parseInt(q.height()),r=parseInt(q.attr("width")),s=parseInt(q.attr("height"));var o=r/s;q.height(p/(r/s))}function l(p,r,q,o){var r=parseInt(r),q=parseInt(q);var n=j(parseInt(p.attr("width"))/parseInt(p.attr("height")),r-40,q-30);o.css("width",Math.round(n.width)+"px");p.css("width",Math.round(n.width)+"px");p.css("height",Math.round(n.height)+"px");o.css("margin-left",Math.round(n.x+20)+"px");p.css("margin-left",Math.round(n.x+20)+"px");p.css("margin-top",Math.round(n.y+15)+"px")}function j(q,p,o){var n={};if(q>=p/o){n.width=parseInt(p);n.height=parseInt(p/q);n.x=0;n.y=parseInt(o-n.height)/2}else{n.width=parseInt(o*q);n.height=parseInt(o);n.x=parseInt(p-n.width)/2;n.y=0}return n}function k(){var p=f.outerWidth(),r=f.outerHeight(),o=[self.pageXOffset||document.documentElement.scrollLeft||document.body.scrollLeft,self.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop];e.data("scroll-position",o);e.data("previous-overflow",e.css("overflow"));e.css("overflow","hidden");window.scrollTo(o[0],o[1]);var q=f.outerWidth()-p;var n=f.outerHeight()-r;f.css({"margin-right":q,"margin-bottom":n})}function g(){e.css("overflow",e.data("previous-overflow"));var n=e.data("scroll-position");window.scrollTo(n[0],n[1]);f.css({"margin-right":0,"margin-bottom":0})}function a(o){var r=b.scrollTop();var q=r+b.height();var n=o.offset().top;var p=n+o.height();return((p<=q)&&(n>=r))}})(jQuery);