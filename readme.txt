=== WP Theater ===
Contributors: kentfarst
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X3FWTE2FBBTJU
Tags: video, shortcode, embed, channel, playlist, group, user, youtube, vimeo, lower lights, full window, preset
Requires at least: 3.6
Tested up to: 3.6.1
Stable tag: 1.0.6

Shortcodes for YouTube and Vimeo. Includes embeds, "Theater" embed, thumbed previews, playlist, channel, user uploads and groups.

== Description ==
Shortcodes for integrating YouTube and Vimeo into posts. Numerous options that include traditional embedding, single video previews, a wrapped "Theater" embed, and video listings from playlists, channels, user uploads and groups.

For a better looking explination and parameter usage please visit:
http://redshiftstudio.com/wp-theater/

= Usage =
**Preview** - Thumbnail and title of a single video
`[youtube preview]VideoID[/youtube]
[vimeo preview]VideoID[/vimeo]`

**Theater** - Traditional embed that's wrapped for styling and has optional Lower Lights and Full Window buttons.
`[youtube theater]VideoID[/youtube]
[vimeo theater]VideoID[/vimeo]`

The following contain a "theater" by default

**User** - Listing of a user's videos
`[youtube user]UserName[/youtube]
[vimeo user]UserID[/vimeo]`

**Channel** - Listing of videos from a specific channel
`[vimeo channel]ChannelID[/vimeo]`

**Playlist** - Listing of videos from a user's playlist
`[youtube playlist]PlaylistID[/youtube]`

**Group** - Listing of vidoes from a specific group
`[vimeo group]GroupID[/vimeo]`


= Shortcode Parameters =
Keep in mind that presets provide suitable default values for these parameters.

* **preset** (string) -- The preset to use for default options.  NOTE: If no preset is found then it will look for a preset that matches the shortcode's tag and if one does not exist then it will check if a preset matches the service name.  If a preset is not found the shortcode will fail and return an empty string.
* **service** ('youtube' || 'vimeo') -- The service providing the videos
* **mode** ('embed' || 'preview' || 'user' || 'playlist' || 'channel' || 'group') -- The method of handling the id and feed data.
* **id** (string) -- ID that suits the given 'mode'.
* **class** (string) -- The css classes to apply.  NOTE: The classes may be automatically added to multiple elements; The containing wp-theater-section, wp-theater-bigscreen and if mode='preview' then video-preview will get the classes.
* **img_size** ('small' || 'medium' || 'large') -- Set which thumbnail should be used.  NOTE:  This takes Vimeo's method of image sizes rather than multiple small images like youtube.  Also, if the requested size is not defined then it will fallback to a smaller size until one is found or ends with 'small'.
* **max** (int) -- The maximum number videos to show.  With 20 being the upper limit for Vimeo and 25 for YouTube being that we're using the simple APIs.
* **embed_width** (int || FALSE) -- The width of an embedded video.  If FALSE then the value will be pulled from the service's feed.  It is important that the desired ratio is provided for both embed_width and embed_height values.  Also, remember that styling takes priority over an iframe's width attribute.
* **embed_height** (int || FALSE) -- The height of an embedded video.  If FALSE then the value will be pulled from the service's feed.  It is important that the desired ratio is used for both embed_width and embed_height values.  Also, remember that styling takes priority over an iframe's height attribute.
* **show_title** (TRUE || FALSE) -- Show the title above a listing/theater?
* **show_video_title** (TRUE || FALSE) -- Show the video's title?
* **title** (string || FALSE) -- The single video title or the title of the playlist.
* **show_more_link** (TRUE || FALSE) -- Show a link for more content.
* **more_url** (string || FALSE) -- The link to more content.
* **more_text** (string || FALSE) -- Custom text for the more link title.
* **show_theater** (TRUE || FALSE) -- Should a list auto embed the first item?
* **theater_id** (string || FALSE) -- The theater elements ID, if a stand alone theater, otherwise the id of the target theater to used for this preview or list.
* **show_fullwindow** (TRUE || FALSE) -- Should we show a full window button below the theater?
* **show_lowerlights** (TRUE || FALSE) -- Should we show a lower lights button below the theater?

= Shortened Shortcode Parameters =

Shortened parameters are those without a value.  e.g. not *parameter="somevalue"* but just *somevalue*.

e.g. [youtube preview hide_title max="9" hide_more_link]


Service:

* youtube
* vimeo

Mode:

* embed
* theater
* preview
* user
* playlist
* channel
* group

Options/Toggles:

* show_title
* show_video_title
* show_more_link
* show_theater
* show_fullwindow
* show_lowerlights
* hide_title
* hide_video_title
* hide_more_link
* hide_theater
* hide_fullwindow
* hide_lowerlights

= Presets =
Presets exist that enable you to save on typing by using an option set that... is preset.  This means that, when switching between themes, you can quickly adapt existing shortcodes to the new design without editing posts, pages, or widgets.  This may be expanded upon further in the future.

Existing presets are:

* youtube
* vimeo
* youtube_widget
* vimeo_widget

= Requirements =

1. WordPress version 3.6 and later.  Does not support Multisite... yet
1. PHP 5 with curl



== Installation ==

1. Unpack the download-package
1. Upload all files to the `/wp-content/plugins/` directory, include folders
1. Activate the plugin through the 'Plugins' menu in WordPress



== Frequently Asked Questions ==

= What settings exists =
*Use Defaul CSS* - You can choose to disable the built in CSS file so that you can write your own.
*Use Defaul CSS* - You can also choose to disable the Genericons fallback CSS file.  You should only disable the Genericons if you've also diabled the CSS file as the characters/icons are currently hardcoded.
*Adding Custom JS* - You can choose to disable the built in JS file so that you can write your own.
*Caching Length* - Feeds are cached using the Transient API.  A setting exists for defining how long the cache is used.

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


= Actions =
* wp_theater-add_shortcodes          - $presets

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


== Changelog ==
= 1.0.6 (09/15/2013) =

* Removed timing code left in by mistake

= 1.0.5 (09/14/2013) =

* Enabled setting for transient cache length

= 1.0.2 (09/13/2013) =

* Stupid typos


= 1.0 (09/13/2013) =

* Initial Release