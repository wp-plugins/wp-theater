=== WP Theater ===
Contributors: kentfarst
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X3FWTE2FBBTJU
Tags: video, shortcode, vimeo shortcode, youtube shortcode, embed, vimeo embed, youtube embed, channel, vimeo channel, playlist, youtube playlist, vimeo group, youtube user, vimeo user, youtube, vimeo, youtube api, vimeo api, lower lights, full window, preset, shortcode preset, responsive video, responsive embed, responsive iframe,
Requires at least: 3.6
Tested up to: 3.8-beta-1
Stable tag: 1.1.2
License: GPLv3

Shortcodes for YouTube and Vimeo. Includes embeds, "Theater" embed, thumbed previews, playlist, channel, user uploads and groups.

== Description ==
WP Theater provides shortcodes for integrating **YouTube** and **Vimeo** video embeds and feeds into your posts or widgets. Some options include traditional embedding, single video previews, a wrapped "Theater" embed, and video listings from playlists, channels, user uploads and groups.  WP Theater was built with developers in mind, those who need flexibility. With that said, great effort was put into making sure this plugin stayed simple.

For parameters and their usage please visit:

http://redshiftstudio.com/wp-theater/

= Usage =  *ignore/remove the extra spaces that I can't get rid of here*

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
`

**Channel** - Listing of videos from a specific channel
`
[vimeo channel]ChannelID[/vimeo]
`

**Playlist** - Listing of videos from a user's playlist
`
[youtube playlist]PlaylistID[/youtube]
`

**Group** - Listing of videos from a specific group
`
[vimeo group]GroupID[/vimeo]
`


= Requirements =

1. Tested on WordPress version 3.6 and later.  Does not support Multisite, yet.
1. PHP 5.3 or later with cURL

== Frequently Asked Questions ==

= Can I use this plugin to show private content from my YouTube or Vimeo account? =
No, this plugin will only show publically available content.  This feature will be part of the advanced plugin which is currently in development along side this plugin.  If you need this feature now you are looking for a plugin that requires an API key from that service.

= Can I get single previews to display in the theater when clicked? =
Not right now.  Single previews, e.g. [vimeo preview], are really only meant as a nice way to link to a video.

= Can I build a listing from multiple video IDs, e.g. [youtube theater]id1,id2,id3,id4,etc[/youtube]? =
No, we suggest using playlists or channels for this task.  Benifits of both are the ability to add another user's video as well as reordering them directly from YouTube or Vimeo.

= What settings can be changed? =
Outside of the shortcode's parameters there are settings for you to disable the loaded assets as well setting cache expirations.

* *Use Default CSS* - You can choose to disable the built in CSS file so that you can write your own.
* *Use Default Genericons* - You can choose to disable the Genericons fallback CSS file.  You should only disable the Genericons if you've also disabled the CSS file as the characters/icons are currently hardcoded.  WP Theater's genericons will not load if genericons are already enqueued.
* *Use Default JS* - You can choose to disable the built in JS file so that you can write your own.
* *Cache Expiration* - Feeds are cached using the Transient API and this setting will set the expiration.  A value of 0 (zero) will bypass caching.

= My shortcode looks correct but does not seem to be loading or I notice that it is not making use of the transient cache =
Try changing the Transitent Expiration setting to 0.  Please inform me through the support forums if this solves the issue along with the shortcode usage.  Some issues exist with caching data when certain special characters are used in a video's description (hearts, etc.).  If you are the owner of video's with these characters I would suggest that you remove those characters until the issue is sorted out.

= How can I futher customize this plugin =
Please check the Other Notes section for futher development information.



== Developer FAQ ==

= How can I customize the output =
Filters exist that can handle complete customization of the output.  Written as "Filter_Hook" ($callback_params ... )

Display -- Override built in output

* "wp_theater-pre_video_shortcode" ( '', $feed, $atts )
* "wp_theater-pre_theater" ( '', $atts, $content, $tag )
* "wp_theater-pre_video_preview" ( '', $video, $atts, $selected )

Attributes

* "wp_theater-capture_no_value_atts" ( $atts )
* "wp_theater-format_params" ( $atts, $content, $tag )

API Feeds -- Override built in api request and parsing.  NOTE: Keep in mind that these filters will only be called when the transient cache is updated.

* "wp_theater-pre_get_api_data" ( '', $atts )
* "wp_theater-pre_get_request_url" ( '', $atts, $request, $output )
* "wp_theater-pre_parse_feed" ( '', $response, $atts )

Content

* "wp_theater-section_title" ( $title )
* "wp_theater-video_title" ( $title )
* "wp_theater-pre_get_more_link" ( '', $atts, $first_id )
* "wp_theater-get_more_link" ( $more_link, $atts, $first_id )

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
add_action('wp_theater-add_shortcodes', 'my_preset_init');
`


= What values can I define in presets =
Listed below are all of the possible settings you can define in a preset with their base values

`
array(
	// general options
	'preset' => '',
	'service' => 'youtube',
	'mode' => 'embed',
	'id' => '',
	'class'=> '',

	// preview & listing options
	'img_size' => 'small',
	'columns' => 6,
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

	// can only be defined in presets
	'modes' => array(), // can only be defined in presets
	'classes' => array(
		'section' => '',
		'theater' => '',
		'embed' => '',
		'list' => '',
		'preview' => ''
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
add_action('wp_theater-add_shortcodes', 'my_preset_init');
`
NOTE:  Each mode URL must have %id% in the place of the id.  And, it's a bit dumb but, for now you must include at least one query parameter in an embed's url.


= What do the formatted feeds look like? =
Vimeo's feed will return exactly what their API states except we merge their info and video requests into one and clone values to help normalize the feeds.  Youtube on the other hand is almost completely reformatted into a format based on Vimeo's

As of v1.0.0 you can count on the full feeds returning the following content with an exception being that single preview feeds do not have the feed title or url:
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

// needs author info, I know.
`



== Screenshots ==

1. Sample screen shot of how a Vimeo group would look.  Image shows the title, theater, lower-lights & full window buttons, videos listing with thumb & title and a link to more content.


== Changelog ==


= 1.1.2 (12/11/2013) =

* Fixed transient names exceeding the 45 character limit.
* Fixed transient cache to bypass cached data when that data is malformed. Usually caused by special characters in a video's description -- hearts etc.)
* Fixed shorthand paramaters *dont_keep_ratio* and *dont_autoplay_onclick* to work as expected.
* Fixed shorthand columns parameters -- 1cols, 2cols 3cols, etc.

= 1.1.1 (11/18/2013) =

* Fumbled update with a side of facepalm.

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
* Updated JS to make it less likely to conflict with other plugins.

= 1.0.8 (09/17/2013) =

* Fixed validation of *modes'* link formats against requested mode
* Fixed instance where embeds would get query params added which are only meant for API requests
* Fixed WP Theater's version of shortcode_atts, which removes the filter hook, to only be used when setting up presets
* Removed plugin related constants in favor of class variables
* More readme fixes with screenshot

= 1.0.7 (09/16/2013) =

* Added autoplay_onclick parameter (default: TRUE)
* Fixed CSS & JS assets so they load only when the shortcode is in use
* Fixed videos so they get trimmed to $max after caching instead of before.
* Fixed auto scrolling to only happen if the theater is not in view
* Fixed lower lights so they don't keep adding an element on IE8
* Removed the useless [video] shortcode as it conflicts with WP's built in shortcode by the same name
* Removed admin files from being included when on the front-end and vice versa
* Removed style coloring on links so a theme's styles take priority
* More readme fixes

= 1.0.6 (09/15/2013) =

* Removed timing code left in by mistake

= 1.0.5 (09/14/2013) =

* Enabled setting for transient cache expiration (default is 4 hrs. -- 14400 seconds)

= 1.0.2 (09/13/2013) =

* Stupid typos

= 1.0 (09/13/2013) =

* Initial Release



== Upgrade Notice ==

= 1.1.1 =
Fixes for cache expiration