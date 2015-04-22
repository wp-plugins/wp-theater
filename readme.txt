=== WP Theater ===
Contributors: kentfarst
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X3FWTE2FBBTJU
Tags: video, shortcode, vimeo shortcode, youtube shortcode, embed, vimeo embed, youtube embed, channel, vimeo channel, playlist, youtube playlist, vimeo group, youtube user, vimeo album, vimeo user, youtube, vimeo, youtube api, vimeo api, video preview, vimeo preview, youtube preview, lower lights, full window, preset, shortcode preset, responsive video, responsive embed, responsive iframe,
Requires at least: 3.6
Tested up to: 4.2
Stable tag: 1.1.5
License: GPLv3

Shortcodes for YouTube and Vimeo. Includes embeds, "Theater" embed, thumbed previews, playlist, channel, user uploads and groups.

== Description ==
WP Theater provides shortcodes for integrating **YouTube** and **Vimeo** video embeds and feeds into your posts or widgets. Some options include traditional embedding, single video previews, a wrapped "Theater" embed, and video listings from playlists, channels, user uploads and groups.  WP Theater was built with developers in mind, those who need flexibility. With that said, great effort was put into making sure this plugin stayed simple.

= Requirements =

1. Current version tested on WordPress version 3.9 and later.
1. PHP 5.3 or later with cURL

= Usage =

For parameters and their usage please visit:

http://redshiftstudio.com/wp-theater/

**Boring Embed** - The classic
`
[youtube]VideoID[/youtube]
[vimeo]VideoID[/vimeo]
`

**Preview** - Thumbnail and title of a single video
`
[youtube preview]VideoID[/youtube]
[vimeo preview]VideoID[/vimeo]
`

**Theater** - Traditional embed that's wrapped for styling, has optional Lower Lights and Full Window buttons, and is responsive.
`
[youtube theater]VideoID[/youtube]
[vimeo theater]VideoID[/vimeo]
`

*The following contain a "theater" by default*

**User** - Listing of a user's videos
`
[youtube user]UserName[/youtube]
[vimeo user]UserID[/vimeo]
// for YouTube's v3 API see FAQ about user uploads vs playlists.
`

**Channel** - Listing of videos from a specific channel
`
[vimeo channel]ChannelID[/vimeo]
`

**Playlist** - Listing of videos from a user's playlist
`
[youtube playlist]PlaylistID[/youtube]
`

**Album** - Listing of videos from a specific album
`
[vimeo album]AlbumID[/vimeo]
`

**Group** - Listing of videos from a specific group
`
[vimeo group]GroupID[/vimeo]
`

**NOTICE -- YouTube has depreciated their v2 API.  It is highly recommended that you enable the v3 API to avoid and depreciation issues in the future.**


== Frequently Asked Questions ==

= How can I futher customize this plugin =
Please check the Other Notes section for futher development information.

= When using YouTube's v3 API, why does my user name not work anymore for user modes? =
This is to avoid an extra request from YouTube.  Since YouTube automatically does a Uploads playlist, it is much easier to use that instead.  So for v3, user and playlist modes are identical except for the titles.

To find your uploads playlist go to your user's channel and hover over the Uploads section (not menu button).  A play button will appear that links to your uploads playlist.  Odd, I know.  A bit of a PITA, I know.

= Can I use this plugin to show private content from my YouTube or Vimeo account? =
No, this plugin will only show publically available content.  It will skip any videos that are not publicly embeddable, even password protected videos.  If you need this feature now then you are looking for a plugin that requires OAuth2 credentials from that service.

= Can I get single previews to display in the theater when clicked? =
Not right now.  Single previews, e.g. [vimeo preview], are really only meant as a nice way to link to a video or for use with a popup.

= Can I build a listing from multiple video IDs, e.g. [youtube theater]id1,id2,id3[/youtube]? =
Not for now, we suggest using playlists, albums or channels for this task.  Benifits of both are the ability to add another user's video as well as adding and reordering them directly from YouTube or Vimeo.

= The autoscrolling is going behind my fixed header.  How can I fix this =
This is a stretch of this plugins scope but there is a possible fix.  We followed TwentyFourteens method of the body element having a "masthead-fixed" class and the main header must have either an id of "masthead" or class of "site-header".

= What settings can be changed? =
Outside of the shortcode's parameters there are settings for you to disable the loaded assets as well setting cache expirations, if used.

* *Use Default CSS* - You can choose to disable the built in CSS file so that you can write your own.
* *Use Default Genericons* - You can choose to disable the Genericons fallback CSS file.  You should only disable the Genericons if you've also disabled the CSS file as the characters/icons are currently hardcoded.  WP Theater's genericons will not load if genericons are already enqueued.
* *Use Default JS* - You can choose to disable the built in JS file so that you can write your own.
* *Cache Expiration* - Feeds are cached using the Transient API and this will set the expiration.  A value of 0 (zero) will bypass ALL caching.


== Developer FAQ ==

= How can I customize the output =
Filters exist that can handle complete customization of the output.  Written as "Filter_Hook" ($callback_params ... )

Display -- Override built in output

* "wp_theater-pre_video_shortcode" ( FALSE, $feed, $atts )
* "wp_theater-pre_theater" ( FALSE, $atts, $content, $tag )
* "wp_theater-pre_video_preview" ( FALSE, $video, $atts, $selected )

Attributes

* "wp_theater-format_params" ( $atts, $content, $tag )

API Feeds -- Override built in api request and parsing.  NOTE: Keep in mind that these filters will only be called when the transient cache is updated.

* "wp_theater-pre_get_request_url" ( FALSE, $atts, $request, $output )
* "wp_theater-parse_{$service}_response" ( $out, $response, $atts) // v1.1.3

Content

* "wp_theater-section_title" ( $title )
* "wp_theater-video_title" ( $title )
* "wp_theater-{$service}_more_url" ( FALSE, $atts, $data ) // v1.1.4

Presets

* "wp_theater-get_preset" ( $name )
* "wp_theater-set_preset" ( $arr, $name )

= How do I add my own preset? =
The following code will create a preset named "my_preset".  We do not currently, but are planning to, offer a method of saving presets to the database so that they stick around between theme's.

`
function my_preset_init ($presets) {
	$presets->set_preset( 'my_preset', shortcode_atts( $presets->get_preset( 'youtube' ), array(
		'embed_width' => 342,
		'embed_height' => 192,
		'max' => 9,
	) ) );
	add_shortcode( 'my_preset', array( WP_Theater::$shortcodes, 'video_shortcode' ) );
}
add_action('wp_theater-shortcodes_init', 'my_preset_init');
`


= What values can I define in presets =
Listed below are all of the possible settings you can define in a preset with their base values

`
array(
	// general options
	'preset' => '',
	'service' => '',
	'mode' => 'embed',
	'id' => '',
	'embed_width' => FALSE,
	'embed_height' => FALSE,
	'class' => '',
	'cache' => FALSE,

	// preview & listing options
	'img_size' => 'medium',
	'columns' => 3,
	'max' => 12,
	'autoplay_onclick' => TRUE,

	// Title options
	'show_title' => TRUE,
	'show_video_title' => TRUE,
	'title' => '',

	// More link options
	'show_more_link' => TRUE,
	'more_url' => FALSE,
	'more_text' => FALSE,

	// Theater options
	'show_theater' => TRUE,
	'theater_id' => FALSE,
	'show_fullwindow' => FALSE,
	'show_lowerlights' => FALSE,
	'keep_ratio' => TRUE,
	'iframe_placeholder' = > TRUE            // since 1.2.0

	// can only be defined in presets
	'modes' => array(), // the modes array with matching link formats
	'classes' => array( // the classes to apply to their respective elements
		'section' => 'entry-section wp-theater-section %service%',
		'theater' => 'wp-theater-bigscreen',
		'embed' => 'wp-theater-iframe',
		'list' => 'wp-theater-listing',
		'preview' => 'video-preview'
	)
);
`


= How can I modify the embed url? =
Each preset requires a modes array to store the different link formats used.  You can directly access and modify these yourself through a theme's functions.php.
e.g.
`
// make youtube embed with https and youtube-nocookie.com
function my_preset_init ($presets) {
	$youtube_preset = $presets->get_preset( 'youtube' );
	$youtube_preset['modes']['embed'] = 'https://www.youtube-nocookie.com/embed/%id%?wmode=transparent&autohide=1';
	$presets->set_preset( 'youtube', $youtube_preset );
}
add_action('wp_theater-shortcodes_init', 'my_preset_init');
`
NOTE:  Each mode URL must have %id% in the place of the id.  And, it's a bit dumb but, for now you must include at least one query parameter in an embed's url.


= What do the formatted feeds look like? =
Vimeo's feed will return exactly what their API states except we merge their info and video requests into one and clone values to help normalize the feeds.  Youtube on the other hand is almost completely reformatted into a format based on Vimeo's

You can count on the full feeds returning the following content with an exception being that single preview feeds do not have the feed title or url.

Also, with YouTube's v3 API enabled, rating, likeCount and viewCount will always be empty strings:
`
object
	'title' => string
	'url' => string
	'videos' => array
		0 => object
			'title' => string
			'id' => string
			'url' => string
			'upload_date' => string
			'description' => string
			'category' => string
			'duration' => string
			'rating' => string
			'likeCount' => string
			'viewCount' => string
			'width' => string
			'height' => string
			'thumbnails' => array
				'small' => string
				'medium' => string
				'large' => string
				'cover' => string       // since 1.2.0 -- set to the largest available image.

`


== Screenshots ==

1. Sample screen shot of how a Vimeo group would look.  Image shows the title, theater, lower-lights & full window buttons, videos listing with thumb & title and a link to more content.


== Changelog ==

= 1.2.0 (4/21/2015) =

* Added settings to enable YouTube's v3 API.
* Added setting to disable the default [vimeo] and [youtube] shortcodes for better compatability (use [wptheater vimeo] or [wptheater youtube] insead).
* Added option to use an `iframe_placeholder` image for playlist, user, group, album or channel modes (default: TRUE).  More testing is needed before offering the same for stand alone embeds and theaters.
* Fixed errors when using a user's uploads as a playlist in YouTube's v2 API.
* Fixed instances where lower lights and full window wouldn't appear.
* Updated styling for fully responsive iframes without JavaScript and tweaked the full window overlay.  Custom styling from previous versions may not be targeted correctly in this version.
* Updated column responsiveness to only break at 480px into two columns.
* Partial & temp fix for genericon issues where character codes have changed across different versions.  Will be switching to SVG & fallback images in the future due to this issue.

= 1.1.5 (6/02/2014) =

* Fixed bug with YouTube *more links*.
* Fixed bug where *cache* setting was ignored.

= 1.1.4 (5/18/2014) =

* Added support for Vimeo Albums.
* Added/fixed medium sized YouTube thumbnails.
* Added classes preset variable to contain all classes used and accept placeholders for %service%, %mode% and %preset%.  NOTE: Service, mode and preset classes are no longer added by default to reduce the default html to text ratio.
* Added *wp_theater-{$service}_more_url* filter.  All internal handling of more link urls are done through this filter (extensible services pt. 2a)
* Fixed PHP and cURL activation check... If not, I'm snapping a(nother) keyboard.
* Fixed error when YouTube ratings and likes data are blocked for a video.
* Fixed *cache* shorthand parameter and updated default to not cache.
* Fixed previews from remaining selected or being selected initially when no theater(embed) is preset or when using theater_id.
* Updated solo previews so they display inside a figure element with a figcaption wrapped title.
* Updated *theater_id* to cause listings to hide their theater.
* Updated more_link to not carry a nofollow rel value.
* Updated capture_no_value_atts to accept all regestered modes and services. (extensible services pt. 2b)
* Updated autoscrolling to also check for the first *.site-header* if *#masthead* doesn't exist.
* Removed *wp_theater-pre_get_api_data* filter for the time being -- useless and a security threat.
* Removed *wp_theater-capture_no_value_atts* filter for being useless
* Removed *wp_theater-pre_get_more_link* filter for being useless
* Updated transients to reset when the plugin is updated by adding a *plugin_version* variable to the API data.
* Misc cleanup and speedup.

= 1.1.3 (2/12/2014) =

* Fixed PHP 5.4 compatibility
* Fixed PHP and cURL activation check.
* Fixed shortcode_atts call to include the tag name.
* Fixed autoscroll for themes that apply a *masthead-fixed* class and when the adminbar is there.
* Fixed embeds so they make use of *$content_width*, are generated around that ratio, and don't rely on a iframe{width:100%} style.
* Added back in embed_width and embed_height parameters -- these will take priority but the plugin will handle fine without them.
* Enabled transient cache + final fix for transient names exceeding max characters for multisite and non-multisite WP installs
* Added option for disabling transient cache per shortcode usage with *cache* and *dont_cache* parameters.
* Removed 'wp_theater-pre_parse_feed' filter
* Added 'wp_theater-parse_{$service}_response' filter -- all internal parsing is now done through this filter. (extensible services pt. 1)
* Updated 'wp_theater-pre_get_api_data' filter to be applied after the *get_request_url* and added the *$request_url* as a parameter to *get_request_url*
* Removed most instances of static text to reduce the need for front-end translations. (translations pt. 1)

= 1.1.2 (12/11/2013) =

* Fixed transient names exceeding the 45 character limit.
* Fixed transient cache to bypass cached data when that data is malformed. Usually caused by special characters in a video's description -- hearts etc.)
* Fixed shorthand paramaters *dont_keep_ratio* and *dont_autoplay_onclick* to work as expected.
* Fixed shorthand columns parameters -- 1cols, 2cols 3cols, etc.

= 1.1.0 (10/17/2013) =

* Fixed instances where private YouTube videos would cause an error.
* Fixed embed_width and embed_height to use feed data when set to FALSE. (Updated default to: FALSE with a 640x360 fallback)
* Updated use-specific class names to contain a prefix in order to avoid duplicates and inheritance conflicts. e.g. *"service-youtube mode-channel preset-youtube"* instead of just *"youtube channel youtube"*.
* Updated feed link rel attributes to carry an "external nofollow" value.
* Added columns parameter to set how many videos should be in a row. (range: 1-6, default: 3)
* Added some responsiveness to the columns.  All columns break to two column at 480px and single column at 320px.
* Added keep_ratio parameter to let JS keep embeds in their correct aspect ratio. (Default: TRUE)  NOTE:  This does not track traditional embeds, only theater embeds.
* Added error notices as html comments in place of the shortcode if it fails. e.g. `<!-- WP Theater - This is why it failed -->'
* Added PHP version and cURL check upon activation
* Added admin notice upon activation to subtly hint towards --> DONATING!!! <--- ... subtle, huh?
* Removed the *clear* class from a section's footer element to avoid inheritance conflicts.
* Misc other tweaks to improve code -- mostly styling.

= 1.0.9 (10/08/2013) =

* Fixed *class* parameter so it is applied to previews when the mode is preview.
* Fixed *class* parameter so it is not applied to multiple elements, just the leading parent element for a given mode.
* Added the preset name as a class for the section, theater and preview.
* Added *classes* array parameter for presets to define default classes for the different areas -- section, theater, embed, list and	preview, when used.

= 1.0.8 (09/17/2013) =

* Fixed validation of *modes'* link formats against requested mode
* Fixed instance where embeds would get query params added which are only meant for API requests
* Fixed WP Theater's version of shortcode_atts, which removes the filter hook, to only be used when setting up presets
* Removed plugin related constants in favor of class variables
* Added screenshot

= 1.0.7 (09/16/2013) =

* Added autoplay_onclick parameter (default: TRUE)
* Fixed CSS & JS assets so they load only when the shortcode is in use
* Fixed videos so they get trimmed to $max after caching instead of before.
* Fixed auto scrolling to only happen if the theater is not in view
* Fixed lower lights so they don't keep adding an element on IE8
* Removed the useless [video] shortcode as it conflicts with WP's built in shortcode by the same name
* Removed admin files from being included when on the front-end and vice versa
* Removed style coloring on links so a theme's styles take priority

= 1.0.5 (09/14/2013) =

* Enabled setting for transient cache expiration (default is 4 hrs. -- 14400 seconds)

= 1.0 (09/13/2013) =

* Initial Release