<?php 
if(class_exists('WP_Theater') && !class_exists('WP_Theater_Shortcodes')) {

class WP_Theater_Shortcodes  {


	/**
	 * Registered services array
	 * @since WP Theater 1.1.4
	 */
	private $services = array();

	/**
	 * Registered modes array
	 * @since WP Theater 1.1.4
	 */
	private $modes = array();

	/**
	 * Constructs
	 * @since WP Theater 1.0.0
	 */
	public function __construct() {
		add_action('init', array($this, 'init'));
	}

	/**
	 * Constructs
	 * @since WP Theater 1.0.0
	 */
	public function init() {

		$presets = WP_Theater::$presets;

		$presets->set_preset('default', array(
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

			// can only be defined in presets
			'modes' => array(),
			'classes' => array(
				'section' => 'entry-section wp-theater-section',
				'theater' => 'wp-theater-bigscreen',
				'embed' => 'wp-theater-iframe',
				'list' => 'wp-theater-listing',
				'preview' => 'video-preview'
			),
		));

		// Add YouTube preset
		$presets->set_preset('youtube', shortcode_atts($presets->get_preset('default'), array(
			'service'  => 'youtube',
			'img'      => 0,
			'img_size' => 'medium',
			'show_fullwindow' => TRUE,
			'show_lowerlights' => TRUE,
			'modes' => array(
				'link'     => 'http://www.youtube.com/',
				'embed'    => 'http://www.youtube.com/embed/%id%?rel=0&wmode=transparent&autohide=1',
				'preview'  => 'http://gdata.youtube.com/feeds/api/videos/%id%?v=2&alt=jsonc',
				'user'     => 'http://gdata.youtube.com/feeds/api/users/%id%/uploads?v=2&alt=jsonc&max-results=20',
				'playlist' => 'http://gdata.youtube.com/feeds/api/playlists/%id%?v=2&alt=jsonc&max-results=20'
			),
		)));

		// Add Vimeo preset
		$presets->set_preset('vimeo', shortcode_atts($presets->get_preset('default'), array(
			'service'  => 'vimeo',
			'img'      => 0,
			'img_size' => 'medium',
			'show_fullwindow' => TRUE,
			'show_lowerlights' => TRUE,
			'modes' => array(
				// link -- vimeo doesn't need it because they are smart enough to include one in the response...
				'embed'    => 'http://player.vimeo.com/video/%id%?portrait=0&byline=0',
				'preview'  => 'http://vimeo.com/api/v2/video/%id%', // We add the videos.json & info.json part
				'user'     => 'http://vimeo.com/api/v2/user/%id%/',
				'channel'  => 'http://vimeo.com/api/v2/channel/%id%/',
				'album'    => 'http://vimeo.com/api/v2/album/%id%/',
				'group'    => 'http://vimeo.com/api/v2/group/%id%/'
			),
		)));

		// widget presets as a temp work around until I can make them proper -- cachable -- widgets

		// Add YouTube preset for sidebars
		$presets->set_preset('youtube_widget', shortcode_atts($presets->get_preset('youtube'), array(
			'embed_width' => 360,
			'embed_height' => 170,
			'max' => 9,
			'img_size' => 'small',
			'show_video_title'  => FALSE,
			'show_title'  => FALSE,
			'show_more_link'  => FALSE,
			'show_fullwindow' => FALSE,
			'show_lowerlights' => FALSE,
		)));

		// Add Vimeo preset for sidebars
		$presets->set_preset('vimeo_widget', shortcode_atts($presets->get_preset('vimeo'), array(
			'embed_width' => 360,
			'embed_height' => 170,
			'max' => 9,
			'img_size' => 'small',
			'show_video_title'  => FALSE,
			'show_title'  => FALSE,
			'show_more_link'  => FALSE,
			'show_fullwindow' => FALSE,
			'show_lowerlights' => FALSE,
		)));

		// register the presets as shortcodes
		add_shortcode('youtube',        array($this, 'video_shortcode'));
		add_shortcode('vimeo',          array($this, 'video_shortcode'));
		add_shortcode('youtube_widget', array($this, 'video_shortcode'));
		add_shortcode('vimeo_widget',   array($this, 'video_shortcode'));

		// add parsing filters
		add_filter( 'wp_theater-parse_youtube_response', array($this, 'parse_youtube_response'), 10, 3 );
		add_filter( 'wp_theater-parse_vimeo_response',   array($this, 'parse_vimeo_response'),   10, 3 );
		// more url filters
		add_filter( 'wp_theater-youtube_more_url',       array($this, 'get_youtube_more_url'),   10, 3 );

		//call the action for devs to do what they do
		/* [DEPRECIATED] */ do_action('wp_theater-add_shortcodes', $presets); /* [DEPRECIATED] */ 
		do_action('wp_theater-shortcodes_init');

		// build the list of available services and modes
		foreach ($presets->get_presets() as $preset) {
			 $this->services[] = $preset['service'];
			 $this->modes = array_merge(array_flip($preset['modes']), $this->modes);
			 $this->modes[] = 'theater';
		}
		$this->services = array_values(array_unique($this->services));
		$this->modes = array_values(array_unique($this->modes));
	}

	/**
	 * Main shortcode
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The shortcode's attributes
	 * @param string $content The shortcode's inner content
	 * @param string $tag The shortcode's tag
	 *
	 * @return string The string to be inserted in place of the shortcode.
	 */
	public function video_shortcode($atts, $content = '', $tag) {

		// let the plugin know that we need to load assets
		WP_Theater::enqueue_assets();

		// make sure all the atts are clean, setup correctly and extracted
		$atts = $this->format_params($atts, $content, $tag);
		if ($atts === FALSE) return '<!-- WP Theater - format_params failed -->'; // TODO: Change these over to WP_Error and catch
		elseif (is_string($atts)) return '<!-- WP Theater - ' . esc_attr($atts) . ' -->'; // this is plain stupid!
		extract($atts);

		// can we just embed an iframe?
		if('embed' == $mode){
			return $this->get_iframe($atts);
		}

		// can we just embed a theater?
		if('theater' == $mode) {
			return $this->theater($atts, $content, $tag);
		}

		//// Else we need data ////

		// get the data
		$feed = $this->get_api_data($atts);

		// make sure there is actually data
		if ($feed === FALSE || is_string($feed))
			return '<!-- WP Theater - API request failed -->';

		// make sure there are videos
		if ( !isset($feed->videos) || !($count = count($feed->videos)))
			return '<!-- WP Theater - No Videos -->';

		// check if we need to pull the title from the feed
		if(($title === FALSE || empty($title)) && isset($feed->title)) {
			$title = $feed->title;
		}

		// Do preview or limit videos to $max
		if($mode == 'preview'){
			return $this->video_preview($feed->videos[0], $atts);
		}elseif ($max !== FALSE && $max > 0 && $max < $count) {
			$max_videos = array_slice($feed->videos, 0, $max);
			$feed->videos = $max_videos;
		}


		//// Start Output ////


		// allow a filter to bypass the output.
		if ( $out = apply_filters( 'wp_theater-pre_video_shortcode', FALSE, $feed, $atts, $content, $tag ) )
			return $out;

		// build the data attr if need be
		$theater_data = $theater_id ? ' data-theater-id="' . esc_attr($theater_id) . '"' : '';

		// add section
		$out = '<section class="' . esc_attr($this->get_element_classes('section', $atts)) . '"' .  $theater_data . '>';

		// add the title
		if($show_title && !empty($title)) {
			$out .= '	<header>';
			$out .= '		<h3>' . apply_filters('wp_theater-section_title', $title) . '</h3>';
			$out .= '	</header>';
		}

		// add theater
		if($show_theater) {
			$out .= $this->theater($atts, '', $tag, $feed->videos[0]);
		}

		// add the video listing
		$out .= '	<ul class="' . esc_attr($this->get_element_classes('list', $atts) . ' ' . 'cols-' . $atts['columns']) . '">';
		$is_first = TRUE;
		foreach($feed->videos as $video) {
			$out .= $this->video_preview($video, $atts, $is_first);
			if($is_first) $is_first = FALSE;
		}
		$out .= '	</ul>';

		// add the more link
		if($show_more_link) {
			if($more_url)
				$feed->url = $more_url;

			if (isset($feed->url) && !empty($feed->url)) {
				if (!$more_text)
					$more_text = __('More', 'wptheater') . ' ' . $title;

				$out .= '	<footer>';
				$out .= '		<a href="' . esc_url($feed->url) . '" title="' . esc_attr($more_text) . '" rel="external" target="_blank" class="wp-theater-more-link"><span>' . apply_filters('wp_theater-more_text', $more_text) . '</span></a>';
				$out .= '	</footer>';
			}
		}

		// close the section
		$out .= '</section>';

		return $out;
	}

	/**
	 * Creates a theater element with wrapper, iframe and options.
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The shortcode's attributes
	 * @param string $content The shortcode's inner content
	 * @param string $tag The shortcode's tag
	 *
	 * @return string The string to be inserted in place of the shortcode.
	 */
	public function theater($atts, $content = '', $tag, $video = FALSE) {

		// allow a filter to replace the output.
		if ( $out = apply_filters( 'wp_theater-pre_theater', FALSE, $atts, $content, $tag, $video ) )
			return $out;

		// Allow for the content to contain an iframe.  Maybe for a custom first video.
		if (!preg_match('/<iframe.*>/', $content)){
			$content = $this->get_iframe($atts, $video);
		}else {
			// TODO: write a better expression to ensure that the required iframe attributes are in there.
			// and what about <video>?  Maybe check for tags, add the iframe and hide it, and let JS do the rest.
			// dont bother supporting <object>?
		}

		// construct the attributes and classes
		$theater_data = ($atts['mode'] == 'theater' && $atts['theater_id']) ? ' id="' . esc_attr($atts['theater_id']) . '"' : '';
		$theater_data .= $atts['keep_ratio'] ? ' data-keepratio=true' : '';

		//// Start Output ////

		$out  = '<div class="' . esc_attr($this->get_element_classes('theater', $atts)) . '"' . $theater_data . '>';
		$out .= '	<div class="wp-theater-bigscreen-inner">';
		$out .= 			$content;
		$out .= '		<div class="wp-theater-bigscreen-options">';

		if ($atts['show_lowerlights'])
			$out .= '		<a class="lowerlights-toggle" title="Toggle Lights" href="javascript:void(0)"><span class="icon">Toggle Lights</span></a>'; //TODO: Allow translation

		if ($atts['show_fullwindow'])
			$out .= '		<a class="fullwindow-toggle" title="Toggle Full Window" href="javascript:void(0)"><span class="icon">Toggle Full Window</span></a>'; //TODO: Allow translation

		$out .= '		</div>';
		$out .= '	</div>';
		$out .= '</div>';

		return $out;
	}

	/**
	 * Creates the iframe for embedding or theater's.
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The shortcode's attributes
	 * @param array $video A parsed video object
	 *
	 * @return string An html string with the resulting iframe
	 */
	public function get_iframe($atts, $video = FALSE) {

		if ($video !== FALSE)
			$atts['id'] = $video->id;

		$atts = $this->constrain_video_dimensions($atts, $video);
		$ratts = $atts;
		$ratts['mode'] = 'embed';

		$result = '<iframe class="' . esc_attr($this->get_element_classes('embed', $atts)) . '" width="' . esc_attr($atts['embed_width']) . '" height="' . esc_attr($atts['embed_height']) . '" src="' . esc_url($this->get_request_url($ratts)) . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>';
		return $result;
	}

	/**
	 * An html string a video preview
	 * @since WP Theater 1.0.0
	 *
	 * @param array $video The single video object
	 * @param array $atts The shortcodes attributes
	 *
	 * @return string	A formatted html string that will display a single video.
	 */
	// TODO: swap data- to be on the a element so it can be more easly picked up by lightboxes.
	public function video_preview($video, $atts, $selected = FALSE) {

		// make sure that we have an image
		if($atts['img_size'] == 'large' && (!isset($video->thumbnails['large']) || empty($video->thumbnails['large'])))
			$atts['img_size'] = 'medium';	
		if($atts['img_size'] == 'medium' && (!isset($video->thumbnails['medium']) || empty($video->thumbnails['medium'])))
			$atts['img_size'] = 'small';
		if($atts['img_size'] == 'small' && (!isset($video->thumbnails['small']) || empty($video->thumbnails['small'])))
			return '';

		// allow a filter hook here in case someone wants to replace this content.
		if ( $out = apply_filters( 'wp_theater-pre_video_preview', FALSE, $video, $atts, $selected ) )
			return $out;

		$wrapper_element = 'figure';
		$title_wrapper_element = 'figcaption';
		if ($atts['mode'] != 'preview') {
			$wrapper_element = 'li';
			$title_wrapper_element = false;
		}

		// check for a custom title if we are only showing a preview
		if($atts['mode'] == 'preview' && !empty($atts['title'])) {
			$video->title = $atts['title'];
		}

		$embed_atts = array('service' => $atts['service'], 'mode' => 'embed', 'modes' => array('embed' => $atts['modes']['embed']), 'id' => $video->id);

		$embed_url = $this->get_request_url($embed_atts);
		if ($atts['autoplay_onclick'])
			// bad idea to not parse and reformat but it's a bit costly.
			$embed_url .= '&autoplay=1';

		// constrain the video's dimensions
		$atts = $this->constrain_video_dimensions($atts, $video);
		// build the link's data attributes
		$wrapper_data = ' data-id="' . esc_attr($video->id) . '"' . ' data-embed-url="' . esc_url($embed_url) . '"' .
		                ' data-embed-width="' . esc_attr($atts['embed_width']) . '"' . ' data-embed-height="' . esc_attr($atts['embed_height']) . '"';

		/**/
		$out =   '<' . $wrapper_element . ' class=" ' . esc_attr($this->get_element_classes('preview', $atts) . ($selected && $atts['show_theater'] ? ' selected' : '')) . '"' . $wrapper_data . '>';

		$out .=    '<a class="img-link" href="' . esc_url($video->url) . '" rel="external nofollow" target="_blank" title="' . esc_attr($video->title) . '">';
		$out .=      '<img src="' . esc_url($video->thumbnails[$atts['img_size']]) . '" alt="' . esc_attr($video->title) . '" />';
		$out .=    '</a>';
		if($atts['show_video_title']) {
			$out .=   $title_wrapper_element ? '<' . $title_wrapper_element . '>' : '';
			$out .=     '<a class="title-link" href="' . esc_url($video->url) . '" rel="external nofollow" target="_blank" title="' . esc_attr($video->title) . '"><span>' . apply_filters('wp_theater-video_title', $video->title) . '</span></a>';		
			$out .=   $title_wrapper_element ? '</' . $title_wrapper_element . '>' : '';
		}
		$out .=   '</' . $wrapper_element . '>';
		/**/

		return $out;
	}

	/**
	 * Formats a shortcode's parameters to maximize the chance of success
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The shortcode's attributes
	 * @param string $content The shortcode's content
	 * @param string $tag The tag that called the shortcode
	 *
	 * @return array	A formatted array that should give the best chance of success
	 * NEEDS: strings returned swapped out with wp_error
	 */
	public function format_params($atts, $content, $tag) {

		// make attribues like [0] => 'hide_title' into ['show_title'] => FALSE
		$atts = $this->capture_no_value_atts($atts);

		// just in case someone tries to reset the modes or classes arrays
		if (isset($atts['modes']))
			unset($atts['modes']);
		if (isset($atts['classes']))
			unset($atts['classes']);

		// make sure the important things are clean.
		foreach (array('service', 'preset', 'id', 'content', 'mode') as $attr) {
		if (isset($atts['attr']))
			$atts['attr'] = trim($atts['attr']);
		}

		// check for a preset -- atts' preset || shortcode tag as preset || service as preset || die, all empty
		$presets = WP_Theater::$presets;

		if(!isset($atts['preset']) || empty($atts['preset'])) {
			if($tag != 'preview' && $presets->has_preset($tag))
				$atts['preset'] = $tag;
			elseif(isset($atts['service']) && $presets->has_preset($atts['service']))
				$atts['preset'] = $atts['service'];
			else return 'Preset not found'; 
		}

		// make sure we have an ID to go on -- atts' id || content as ID || die, both empty
		if(empty($atts['id'])) {
			if(empty($content))
				return 'ID not found';
			else {
				$atts['id'] = $content;
			}
		// or look for a title
		}elseif ( (!isset($atts['title']) || $atts['title'] === FALSE) && !empty($content)){
			$atts['title'] = $content;
		}

		// apply preset values
		$atts = apply_filters('wp_theater-format_params', shortcode_atts($presets->get_preset($atts['preset']), $atts, $tag), $content, $tag);

		// make sure a mode is provided
		if(!isset($atts['mode']) || empty($atts['mode'])) {
			return 'Mode value not found';
		}

		// make sure the link format is available for the requested mode
		$mode = $atts['mode'] == 'theater' ? 'embed' : $atts['mode'];
		if(!isset($atts['modes'][$mode]) || empty($atts['modes'][$mode])) {
			return 'Requested mode is undefined';
		}

		// hide theater if there's a theater id and we're not just a theater
		if ($atts['mode'] != 'theater' && $atts['theater_id'])
			$atts['show_theater'] = FALSE;

		return $atts;
	}

	/**
	 * Capture attributes without a value and convert to an extractable array --  $array[int] = value  =>  $array[value] = ?
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The shortcodes attributes
	 *
	 * @return array The corrected array
	 */
	public function capture_no_value_atts($atts) {

		// return if $atts is empty -- probably simple embed
		if(!is_array($atts) || !count($atts))
			return $atts;

		foreach($atts as $key => $value) {

			// if the key is not numeric then skip this
			if(!is_int($key)) continue;

			switch($value) {

				// automatically look for registered services and modes
				case in_array($value, $this->services):
					$atts['service'] = $value;
					unset($atts[$key]);
				 break;

				case in_array($value, $this->modes):
					$atts['mode'] = $value;
					unset($atts[$key]);
				 break;

				case 'show_title':
				case 'show_video_title':
				case 'show_more_link':
				case 'show_theater':
				case 'show_fullwindow':
				case 'show_lowerlights':
				case 'cache':
				case 'keep_ratio':
				case 'autoplay_onclick':
					$atts[$value] = TRUE;
					unset($atts[$key]);
				 break;

				case 'hide_title':
				case 'hide_video_title':
				case 'hide_more_link':
				case 'hide_theater':
				case 'hide_fullwindow':
				case 'hide_lowerlights':
					$atts['show' . substr($value, 4)] = FALSE;
					unset($atts[$key]);
				 break;

				case 'dont_cache':
				case 'dont_keep_ratio':
				case 'dont_autoplay_onclick':
					$atts[substr($value, 5)] = FALSE;
					unset($atts[$key]);
				 break;

				case '1cols':
				case '2cols':
				case '3cols':
				case '4cols':
				case '5cols':
				case '6cols':
					$atts['columns'] = (int) substr($value, 0, 1);
					unset($atts[$key]);
				 break;

				default:
				 break;
			}
		}

		return $atts;
	}

	/**
	 * Retrieves and formats the data from an api request into a standard object
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The shortcode's attributes
	 *
	 * @return stdClass An object with details about the feed and it's videos
	 */
	protected function get_api_data($atts) {
		$transient_name = FALSE;

		// get the cache life int
		$options = get_option('wp_theater_options');
		$cache_life = isset($options['cache_life']) ? (int) $options['cache_life'] : 0;

		// get data and maybe set transient
		if($do_cache = ($atts['mode'] != 'preview' && ($cache_life !== 0 || $atts['cache']))) {

			// get a transient name that is both (most likely) unique and under the hill
			$transient_name = 'wptheater-' . substr($atts['service'], 0, 3) . '' . substr($atts['mode'], 0, 2) . '_' . substr($atts['id'], 0, 24);			
			$out = get_transient($transient_name);

			// if we have a valid transient then return that instead.
			if ($out !== FALSE && !is_string($out) && isset($out->plugin_version) && $out->plugin_version = WP_Theater::VERSION)
				return $out;
		}

		$request_url = $this->get_request_url($atts);
		$host = parse_url($request_url, PHP_URL_HOST);
		if (strpos($host, $atts['service'] === FALSE)) return FALSE;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  	curl_setopt($ch, CURLOPT_TIMEOUT, 25);
		curl_setopt($ch, CURLOPT_URL, $request_url);

		$response = curl_exec($ch);

		// make sure that curl executed as expect
		if (curl_errno($ch) !== CURLE_OK) return FALSE; // TODO: Return WP_Error

		curl_close($ch);

		$out = new stdClass;
		$out->videos = array();

		$out = apply_filters("wp_theater-parse_{$atts['service']}_response", $out, $response, $atts);
		if ($do_cache){
			$out->plugin_version = WP_Theater::VERSION;
			set_transient($transient_name, $out, $cache_life);
		}

		return $out;
	}

	/**
	 * Get the required API request url.  NOTE: $request and $output default values will change in the future so don't forget to define them when needed.
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The attributes required to fill in an iframe
	 * @param string $request The request format (optional)
	 * @param string $output The output format (optional)
	 *
	 * @return string The complete request url
	 */
	protected function get_request_url($atts, $request = 'videos', $output = 'json')  {

		// allow a filter hook here in case someone wants to replace this content.
		if ( $out = apply_filters( 'wp_theater-pre_get_request_url', FALSE, $atts, $request, $output) )
			return $out;

		if ($atts['mode'] == 'theater') $atts['mode'] = 'embed';

		$out = str_replace('%id%', $atts['id'], $atts['modes'][$atts['mode']]);

		if($atts['service'] == 'vimeo' && $atts['mode'] != 'embed') {
			if($atts['mode'] != 'preview')
				$out .= $request;
			$out .= '.' . $output;
		}

		return $out;
	}

	/**
	 * Takes a response from YouTube's API and returns a formatted object
	 * @since WP Theater 1.1.3
	 *
	 * @param object $out The resulting object
	 * @param string $response The response from YouTube's API
	 * @param array $atts The shortcode's attributes.
	 *
	 * @return object The resulting object
	 */
	public function parse_youtube_response($out, $response, $atts) {

		if(!$response)
			return FALSE; // return ''?

		$feed = json_decode($response);

		if(!is_object($feed) || !isset($feed->data))
			return FALSE;

		if (isset($feed->data->items))
			$videos = $feed->data->items;
		else
			$videos = array($feed->data);

		foreach($videos as $video) {
			// user feeds don't have a sub listing
			$parsed_video = $this->parse_youtube_video(in_array($atts['mode'], array('user','preview')) ? $video : $video->video);
			if ($parsed_video !== FALSE)
				array_push($out->videos, (object) $parsed_video);
		}

		$out->title = $atts['mode'] == 'user' ? (string) __('Uploads by', 'wptheater') . ' ' . $atts['id'] : (string) $feed->data->title;
		// oddly enough this works even though the video id is not the author of the playlist.... 
		$out->url = apply_filters('wp_theater-youtube_more_url', FALSE, $atts, isset($out->videos[0]) ? $out->videos[0]->id : FALSE);

		return $out;
	}

	/**
	 * Takes a single youtube video entry and returns a reformatted object
	 * @since WP Theater 1.0.0
	 *
	 * @param object $video The response object for a single video
	 *
	 * @return object A formatted object for a single video
	 */
	public function parse_youtube_video($video) {

		$out = new stdClass();

		if (isset ($video->status->value) 
				&& ($video->status->value == 'rejected')
				&& (
						   !isset ($video->accessControl->embed)
				    || $video->accessControl->embed !== 'allowed'
				    || !isset ($video->accessControl->syndicate)
				    || $video->accessControl->syndicate !== 'allowed'
				   )
		   )
			return FALSE;

		$out->title = (string) $video->title;
		$out->id = (string) $video->id;
		$out->url = (string) $video->player->default;
		$out->upload_date = (string) $video->uploaded;
		$out->description = (string) $video->description;
		$out->category = (string) $video->category;
		$out->duration = (string) $video->duration;
		

		if (isset ($video->accessControl->rate) && $video->accessControl->rate == 'denied') {
			$out->rating = '';
			$out->likeCount = '';
		} else {
			$out->rating = (string) $video->rating;
			$out->likeCount = (string) $video->likeCount;
		}
		$out->viewCount = (string) $video->viewCount;

		// dimensions -- not going to bother with their aspect-ratio BS.
		$out->width = (string) '640';
		$out->height = (string) '360';

		// thumbnails
		$out->thumbnails = array();
		$out->thumbnails['small'] = (string) $video->thumbnail->sqDefault;
		$out->thumbnails['medium'] = str_replace('hqdefault', 'mqdefault', $video->thumbnail->hqDefault);
		$out->thumbnails['large'] = (string) $video->thumbnail->hqDefault;


		return $out;
	}

	/**
	 * Gets the url to more videos for YouTube
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The shortcode attributes
	 * @param array $first_id The id of the first video
	 *
	 * @return string The resulting url
	 */
	public function get_youtube_more_url($atts, $first_id = FALSE) {

		$out = $atts['modes']['link'];
		if($atts['mode'] == 'user')
			$out .= 'user/' . $atts['id'];
		else
			$out .= 'watch?';
			if($atts['mode'] == 'embed' || $atts['mode'] == 'preview')
				$out .= 'v=' . $atts['id'];
			elseif($atts['mode'] == 'playlist')
				$out .= 'v=' . $first_id . '&list=' . $atts['id'];

		return $out;
	}

	/**
	 * Takes a response from Vimeo's API and returns a formatted object
	 * @since WP Theater 1.1.3
	 *
	 * @param object $out The resulting object
	 * @param string $response The response from Vimeo's API
	 * @param array $atts The shortcode's attributes.
	 *
	 * @return object The resulting object
	 */
	public function parse_vimeo_response($out, $response, $atts) {

		//parsing begins here:
		if(!$response)
			return FALSE;

		$feed = json_decode($response);

		if(!is_array($feed) || !count($feed))
			return FALSE;

		foreach($feed as $video) {

			// make sure we can embed the video -- not doing the passworded, restricted sites etc. -> beyond scope
			if ($video->embed_privacy  != 'anywhere')
				continue;

			$video->thumbnails = array();
			$video->thumbnails['small']  = $video->thumbnail_small;
			$video->thumbnails['medium'] = $video->thumbnail_medium;
			$video->thumbnails['large']  = $video->thumbnail_large;
			unset($video->thumbnail_small, $video->thumbnail_medium, $video->thumbnail_large);
			// add the video
			array_push($out->videos, (object) $video);
		}

		// we need to request the info feed as well
		if($atts['mode'] == 'user' || $atts['mode'] == 'channel' || $atts['mode'] == 'group' || $atts['mode'] == 'album') {

			$request_url = $this->get_request_url($atts, 'info');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_URL, $request_url);

			$response = json_decode(curl_exec($ch));
			curl_close($ch);

			// bootleg switcherooo
			$videos = $out->videos;
			$out = $response;
			$out->videos = $videos;
			$out->url = apply_filters('wp_theater-vimeo_more_url', $out->url, $atts, $out->videos[0]->id);

			// just make sure we have a title named 'title'....
			if ($atts['mode'] == 'user') {
				$out->title = __('Uploads by', 'wptheater') . ' ' . $out->display_name;
			}else if ($atts['mode'] != 'album'){
				$out->title = $out->name;
			}
		}

		return $out;
	}


	/**
	 * HELERS
	 */


	/**
	 * Prepare the classes for a given element
	 * @since WP Theater 1.1.4
	 *
	 * @param array $elment The element to prepare classes for
	 * @param array $atts The attributes required to fill in an iframe
	 *
	 * @return string The resulting string of classes
	 */
	public function get_element_classes($element, $atts) {
		$out = str_replace(
			array(
				'%service%',
				'%mode%',
				'%preset%',
			),
			array(
				'service-' . $atts['service'],
				'mode-' . $atts['mode'],
				'preset-' . $atts['preset'],
			),
			$atts['classes'][$element]
		);

		// handle class attr
		if ($atts['mode'] == 'preview' && $element == 'preview' || $atts['mode'] == 'theater' && $element == 'theater' || $atts['mode'] == 'embed' && $element == 'embed' ) {
			$out .= ' ' . $atts['class'];
		}elseif ($element == 'section') {
			$out .= ' ' . $atts['class'];
		}

		return $out;
	}

	/**
	 * Get the appropriate video dimensiona based on given data and preferences
	 * TODO? This might be easier to just do as part of the parsing and stored as new var.?
	 * @since WP Theater 1.1.3
	 *
	 * @param array $atts The shortcodes attributes
	 * @param array $atts (optional) The video to constrain
	 *
	 * @return object The updated shortcode attributes
	 */
	public function constrain_video_dimensions($atts, $video = FALSE) {

		// if we have height and width settings from the shortcode then return that and be done.
		if ($atts['embed_width'] !== FALSE && $atts['embed_height'] !== FALSE) {
			return $atts;
		}

		// establish a fallback
		$dimensions = array('width' => 640, 'height' => 360);

		// if we have a video then we'll need that data
		if ($video !== FALSE) {
			$dimensions['width'] = $video->width;
			$dimensions['height'] = $video->height;
		}

		// if we just have a width setting then scale by that setting
		if ($atts['embed_width'] !== FALSE) {
			$dimensions['width'] = $atts['embed_width'];
			$ratio = $dimensions['width'] / (int) $atts['embed_width'];
			$atts['embed_width'] = $ratio * $dimensions['height'];
			return $atts;
		}

		// if we have a $content_width global then use that;
		if ( isset( $GLOBALS['content_width'] ) ) {
			global $content_width;
			$ratio = $content_width / (int) $dimensions['width'];
			$atts['embed_width'] = $content_width;
			$atts['embed_height'] = $ratio * $dimensions['height'];
			return $atts;
		}

		// all else fails we return what we got.
		$atts['embed_width'] = $dimensions['width'];
		$atts['embed_height'] = $dimensions['height'];

		return $atts;
	}
	/**/

} // END CLASS
} // END EXISTS CHECK