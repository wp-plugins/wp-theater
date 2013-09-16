=== WP Theater ===
Contributors: kentfarst
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X3FWTE2FBBTJU
Tags: video, shortcode, embed, channel, playlist, group, user, youtube, vimeo, lower lights, full window, preset
Requires at least: 3.6
Tested up to: 3.6.1
Stable tag: 1.0.7

Shortcodes for YouTube and Vimeo. Includes embeds, "Theater" embed, thumbed previews, playlist, channel, user uploads and groups.

== Description ==
Shortcodes for integrating YouTube and Vimeo into posts. Numerous options that include traditional embedding, single video previews, a wrapped "Theater" embed, and video listings from playlists, channels, user uploads and groups.

*I'm currently looking for any feedback.  Constructive critisism is always welcome.*


For a better looking explanation and parameter usage please visit:

http://redshiftstudio.com/wp-theater/

= Usage =
**Preview** - Thumbnail and title of a single video
`[youtube preview]VideoID[/youtube]
[vimeo preview]VideoID[/vimeo]`

**Theater** - Traditional embed that's wrapped for styling and has optional Lower Lights and Full Window buttons.
`[youtube theater]VideoID[/youtube]
[vimeo theater]VideoID[/vimeo]`

*The following contain a "theater" by default*

**User** - Listing of a user's videos
`[youtube user]UserName[/youtube]
[vimeo user]UserID[/vimeo]`

**Channel** - Listing of videos from a specific channel
`[vimeo channel]ChannelID[/vimeo]`

**Playlist** - Listing of videos from a user's playlist
`[youtube playlist]PlaylistID[/youtube]`

**Group** - Listing of vidoes from a specific group
`[vimeo group]GroupID[/vimeo]`


= Presets =
Presets fill most parameters with an appropriate default, including the service.  Built in presets are also set as their own shortcode; as such presets can be used as the shortcode's tag. e.g. *[youtube_widget ... ]*

Existing presets are:

* youtube
* vimeo
* youtube_widget
* vimeo_widget


= Requirements =

1. WordPress version 3.6 and later.  Does not support Multisite, yet
1. PHP 5 with curl



== Installation ==

1. Unpack the download-package
1. Upload all files to the `/wp-content/plugins/` directory, include folders
1. Activate the plugin through the 'Plugins' menu in WordPress



== Frequently Asked Questions ==

= What settings can be changed =
Outside of the shortcode's parameters we enable you to disable the loaded assets as well as the cache life or expiration.

* *Use Default CSS* - You can choose to disable the built in CSS file so that you can write your own.
* *Use Default Genericons* - You can also choose to disable the Genericons fallback CSS file.  You should only disable the Genericons if you've also disabled the CSS file as the characters/icons are currently hardcoded.  WP Theater's Genericons will not load if they are already enqueued.
* *Adding Custom JS* - You can choose to disable the built in JS file so that you can write your own.
* *Cache Expiration* - Feeds are cached using the Transient API.  A setting exists for defining how long the cache is stored for reuse.

= How can I customize the output =
Filters exist that can handle complete customization of the output.

Display -- override built in output

* wp_theater-pre_video_shortcode     - '', $feed, $atts
* wp_theater-pre_theater             - '', $atts, $content, $tag
* wp_theater-pre_video_preview       - '', $video, $atts, $selected

Attributes

* wp_theater-capture_no_value_atts   - $atts
* wp_theater-format_params           - $atts, $content, $tag

API Feeds -- Override built in api request and parsing

* wp_theater-pre_get_api_data        - '', $atts
* wp_theater-pre_get_request_url     - '', $atts, $request, $output
* wp_theater-pre_parse_feed          - '', $response, $atts

Content

* wp_theater-section_title           - $title
* wp_theater-video_title             - $title
* wp_theater-pre_get_more_link       - '', $atts, $first_id
* wp_theater-get_more_link           - $more_link, $atts, $first_id

Presets

* wp_theater-get_preset              - $name
* wp_theater-set_preset              - $arr, $name

= How do I change or add my own presets? =
The following code will create a preset named "my_preset"

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

= How can I modify the embed url? =
Each preset requires a modes array that stores the different link format used for each mode.  You can directly access and modify these yourself through a theme's functions.php.
e.g.
`
function my_preset_init ($presets) {
	$youtube_preset = $presets->get_preset( 'youtube' );
	// make a youtube embed use https and youtube-nocookie.com
	$youtube_preset['modes']['embed'] = 'https://www.youtube-nocookie.com/embed/%id%?wmode=transparent&autohide=1';
}
add_action('wp_theater-add_shortcodes', 'my_preset_init');
'


== Changelog ==
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

* Enabled setting for transient cache life

= 1.0.2 (09/13/2013) =

* Stupid typos


= 1.0 (09/13/2013) =

* Initial Release