<?php 
if(class_exists('WP_Theater') && !class_exists('WP_Theater_Shortcodes')) {

class WP_Theater_Shortcodes  {

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
			'service' => 'youtube',
			'mode' => 'embed',
			'id' => '',
			'embed_width' => FALSE,
			'embed_height' => FALSE,
			'class' => '',
			'cache' => TRUE,

			// preview & listing options
			'img_size' => 'small',
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
			'modes' => array(), // can only be defined in presets
			'classes' => array(
				'section' => '',
				'theater' => '',
				'embed' => '',
				'list' => '',
				'preview' => ''
			),
		));

		// Add YouTube preset
		$presets->set_preset('youtube', shortcode_atts($presets->get_preset('default'), array(
			'service'  => 'youtube',
			'img'      => 0,
			'img_size' => 'medium', // tests fallback -- YouTube has only small and large
			'show_fullwindow' => TRUE,
			'show_lowerlights' => TRUE,
			'modes' => array(
				'link'     => 'http://www.youtube.com/',
				'embed'    => 'http://www.youtube.com/embed/%id%?wmode=transparent&autohide=1',
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
				'embed'    => 'http://player.vimeo.com/video/%id%?portrait=0&byline=0',
				'preview'  => 'http://vimeo.com/api/v2/video/%id%', // We add the videos.json & info.json part
				'user'     => 'http://vimeo.com/api/v2/user/%id%/',
				'channel'  => 'http://vimeo.com/api/v2/channel/%id%/',
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

		// add out parsing filters
		add_filter( "wp_theater-parse_youtube_response", array($this, 'parse_youtube_response'), 10, 3 );
		add_filter( "wp_theater-parse_vimeo_response", array($this, 'parse_vimeo_response'), 10, 3 );

		// call the action for devs to do what they do
		do_action('wp_theater-add_shortcodes', $presets);
	}

	/**
	 * Main shortcode for all YouTube integration
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The shortcode's attributes
	 * @param string $content The shortcode's inner content
	 *
	 * @return string The string to be inserted in place of the shortcode.
	 */
	public function video_shortcode($atts, $content = '', $tag) {

		// let the plugin know that we need to load assets
		WP_Theater::enqueue_assets();

		// make sure all the atts are clean, setup correctly and extracted
		$atts = $this->format_params($atts, $content, $tag);
		if ($atts === FALSE) return '<!-- WP Theater - format_params failed -->'; // TODO: Change these over to WP_Error
		elseif (is_string($atts)) return '<!-- WP Theater - ' . esc_attr($atts) . ' -->';
		extract($atts);

		// can we just embed an iframe?
		if('embed' == $mode){
			// figure out the embed dimensions
			$atts = $this->constrain_video_dimensions($atts, FALSE);
			// return the iframe
			return $this->get_iframe($atts);
		}

		// can we just embed a theater?
		if('theater' == $mode) {
			// figure out the embed dimensions
			$atts = $this->constrain_video_dimensions($atts, FALSE);
			// return the theater
			return $this->theater($atts, $content, $tag);
		}

		//// Else we need data ////

		// get the cache life int
		$options = get_option('wp_theater_options');
		$cache_life = isset($options['cache_life']) ? (int) $options['cache_life'] : 0;

		// set the transient data for this feed
		// skip if is preview, no cache life, or no cache for shortcode
		if($mode != 'preview' && ($cache_life !== 0 || $cache)) {
			// get a transient name that is both unique and under 40
			$transient_name = 'wptheater-' . substr($service, 0, 3) . '' . substr($mode, 0, 2) . '_' . substr($id, 0, 24);			
			$feed = get_transient($transient_name);
			// some feeds cause serialization errors and any fix results in unreliable data
			// source seems to be special characters in video descriptions ~ solution?... bypass & don't cache
			if (false === $feed || is_string($feed)) {
				$feed = $this->get_api_data($atts);
				if(!isset($feed->videos) || !count($feed->videos)) {
					return '<!-- WP Theater - API request failed -->';
				}
				set_transient($transient_name, $feed, $cache_life);
			}
		}else{
			$feed = $this->get_api_data($atts);
		}

		if (!isset($feed->videos))
			return '<!-- WP Theater - Response contained no videos -->';;

		// Figure out the title -- title attr first, content second, api feed third
		if(empty($title)) {
			if(!empty($content) && $id != $content) {
				$title = $content;
				$content = '';
			}elseif(isset($feed->title)) $title = $feed->title;
		}

		// make sure we only have as many as we want
		if (isset($max) && $max !== FALSE && $max > 0 && $max < count($feed->videos)) {
			$max_videos = array_slice($feed->videos, 0, $max);
			$feed->videos = $max_videos;
		}

		//// Feed is formatted & ready to display ////

		// do we just need one video?
		if($mode == 'preview') {
			if (count($feed->videos >= 1))
				return $this->video_preview($feed->videos[0], $atts);
			else return '<!-- WP Theater - Not enough data for preview -->';
		}

		// allow a filter to replace the output.

		/* Update to follow conventions used
		if ( $out = apply_filters( 'wp_theater-pre_video_shortcode', false, $feed, $atts, $content, $tag ) )
					return $out;
		*/
		$result = apply_filters('wp_theater-pre_video_shortcode', '', $feed, $atts, $content, $tag);
		if(!empty($result)) return $result;

		// add the data attr if need be
		$theater_data = !$show_theater && $theater_id ? ' data-theater-id="' . esc_attr($theater_id) . '"' : '';

		// build the classes
		$class = ' service-' . $service . ' mode-' . $mode . ' preset-' . $preset . ' ' . $atts['classes']['section'] . ' ' . $atts['class'];

		//// Start Output ////

		$result = '<section class="entry-section wp-theater-section' . esc_attr($class) . '"' .  $theater_data . '>';

		// insert the title
		if($show_title && !empty($title)) {
			$result .= '	<header>';
			$result .= '		<h3>' . apply_filters('wp_theater-section_title', $title) . '</h3>';
			$result .= '	</header>';
		}

		if($show_theater) {
			// needs to get info from the video
			$vid = $feed->videos[0];
			// do this dumb stuff until I get it worked out...
			$tempatts = array_merge($atts, array('mode' => 'embed', 'id' => $vid->id)); // TODO: don't reset this as embed
			// figure out the embed dimensions
			$tempatts = $this->constrain_video_dimensions($tempatts, $vid);
			// add the theater
			$result .= $this->theater($tempatts, '', $tag);
		}

		// start the listing of videos
		$result .= '	<ul class="wp-theater-listing ' . esc_attr('cols-' . $atts['columns'] . ' ' . $atts['classes']['list']) . '">';
		$is_first = TRUE;
		foreach($feed->videos as $video) {
			$result .= $this->video_preview($video, $atts, $is_first);
			if($is_first) $is_first = FALSE;
		}
		$result .= '	</ul>';

		// handle the more link
		if($show_more_link) {
			if($more_url)
				$feed->url = $more_url;

			if (isset($feed->url) && !empty($feed->url)) {
				if (!$more_text) $more_text = 'More ' . $title;

				$result .= '	<footer>';
				$result .= '		<a href="' . esc_url($feed->url) . '" title="' . esc_attr($more_text) . '" rel="external nofollow" target="_blank" class="wp-theater-more-link">' . apply_filters('wp_theater-text', $more_text) . '</span></a>';
				$result .= '	</footer>';
			}
		}

		$result .= '</section>';

		return $result;
	}

	/**
	 * Creates a wrapper for an embedded video to sit in.
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The shortcode's attributes
	 * @param string $content The shortcode's inner content
	 * @param string $tag The shortcode's tag
	 *
	 * @return string The string to be inserted in place of the shortcode.
	 */
	public function theater($atts, $content = '', $tag) {

		// allow a filter to replace the output.
		$result = apply_filters('wp_theater-pre_theater', '', $atts, $content, $tag);
		if (!empty($result))
			return $result;

		// Allow for the content to contain an iframe -- e.g. If someone wants to first show a different video that's not in the feed.
		if (!preg_match('/<iframe.*>/', $content))
				$content = $this->get_iframe($atts);
		else {
			// TODO: make sure the rest of the default iframe stuff exists
			// and what about <video>?
		}

		// construct the attributes and classes
		$theater_data = ($atts['mode'] == 'theater' && $atts['theater_id']) ? ' id="' . esc_attr($atts['theater_id']) . '"' : '';
		$theater_data .= $atts['keep_ratio'] ? ' data-keepratio=true' : '';
		$class = ' service-' . $atts['service'] . ' mode-' . $atts['mode'] . ' preset-' . $atts['preset'] . ' ' . $atts['classes']['theater']; 
		if ($atts['mode'] == 'theater')
			$class .= ' ' . $atts['class'];

		//// Start Output ////

		$result  = '<div class="wp-theater-bigscreen ' . esc_attr($class) . '"' . $theater_data . '>';
		$result .= '	<div class="wp-theater-bigscreen-inner">';
		$result .= 			$content;
		$result .= '		<div class="wp-theater-bigscreen-options">';

		if ($atts['show_lowerlights'])
			$result .= '		<a class="lowerlights-toggle" title="Toggle Lights" href="javascript:void(0)"><span class="icon">Toggle Lights</span></a>'; //TODO: Allow translation

		if ($atts['show_fullwindow'])
			$result .= '		<a class="fullwindow-toggle" title="Toggle Full Window" href="javascript:void(0)"><span class="icon">Toggle Full Window</span></a>'; //TODO: Allow translation

		$result .= '		</div>';
		$result .= '	</div>';
		$result .= '</div>';

		return $result;
	}

	/**
	 * An html string a video preview -- ONLY DOES YOUTUBE for now
	 * @since WP Theater 1.0.0
	 *
	 * @param array $video The single video's array of attributes
	 * @param array $atts The shortcodes attributes parameter
	 *
	 * @return string	A formatted html string that will display a single video.
	 */
	public function video_preview($video, $atts, $selected = FALSE) {

		// make sure that we have an image
		if($atts['img_size'] == 'large' && (!isset($video->thumbnails['large']) || empty($video->thumbnails['large' ])))
			$atts['img_size'] = 'medium';	
		if($atts['img_size'] == 'medium' && (!isset($video->thumbnails['medium']) || empty($video->thumbnails['medium' ])))
			$atts['img_size'] = 'small';
		if($atts['img_size'] == 'small' && (!isset($video->thumbnails['small']) || empty($video->thumbnails['small' ])))
			return '';

		// allow a filter hook here in case someone wants to replace this content.
		$result = apply_filters('wp_theater-pre_video_preview', '', $video, $atts, $selected);
		if(!empty($result)) return $result;

		$wrapper_element = 'div';
		if ($atts['mode'] != 'preview') {
			$atts['class'] = '';
			$wrapper_element = 'li';
		}

		if($atts['mode'] == 'preview' && !empty($atts['title'])) {
			$video->title = $atts['title'];
		}

		$embed_atts = array('mode' => 'embed', 'modes' => array('embed' => $atts['modes']['embed']), 'id' => $video->id, 'autoplay_onclick' => $atts['autoplay_onclick']);

		$embed_url = $this->get_request_url($embed_atts);
		if ($atts['autoplay_onclick'])
			// bad idea to not parse and reformat but it's a bit costly.
			$embed_url .= '&autoplay=1';

		$class = ' service-' . $atts['service'] . ' mode-' . $atts['mode'] . ' preset-' . $atts['preset'] . ' ' . $atts['classes']['preview'] . ($selected ? ' selected' : ''); 
		if ($atts['mode'] == 'preview')
			$class .= ' ' . $atts['class'];

		$result .= '	<' . $wrapper_element . ' class="video-preview' . esc_attr($class) . '"';
		if ($atts['mode'] !== 'preview') {
			// figure out the embed dimensions
			$atts = $this->constrain_video_dimensions($atts, $video);
			$result .= ' data-id="' . esc_attr($video->id) . '"' . 
								 ' data-embed-url="' . esc_url($embed_url) . '"' .
								 ' data-embed-width="' . esc_attr($atts['embed_width']) . '"' .
								 ' data-embed-height="' . esc_attr($atts['embed_height']) . '"';
		}
		$result .= '>';

		$result .= '		<a class="img-link" href="' . esc_url($video->url) . '" rel="external nofollow" target="_blank" title="' . esc_attr($video->title) . '">';
		$result .= '			<img src="' . esc_url($video->thumbnails[$atts['img_size']]) . '" alt="' . esc_attr($video->title) . '" />';
		$result .= '		</a>';
		if($atts['show_video_title'])
			$result .= '		<a class="title-link" href="' . esc_url($video->url) . '" rel="external nofollow" target="_blank" title="' . esc_attr($video->title) . '">' . apply_filters('wp_theater-video_title', $video->title) . '</a>';
		$result .= '	</' . $wrapper_element . '>';

		return $result;
	}

	/**
	 * Get a formatted string with the necessary html to embed an iframe
	 * @since WP Theater 1.0.0
	 *
	 * @param string $provider The name of the service provider -- youtube || vimeo
	 * @param array $atts The attributes required to fill in an iframe
	 *
	 * @return array A formatted string to display an embeded iframe
	 */
	public function get_iframe($atts) {
		global $content_width;

		$class = ' service-' . $atts['service'] . ' mode-' . $atts['mode'] . ' preset-' . $atts['preset'] . ' ' . $atts['classes']['embed'];

		if ($atts['mode'] == 'embed')
			$class .= ' ' . $atts['class'];
		else
			$class .= ' ' . $atts['mode'];

		$result = '<iframe class="wp-theater-iframe ' . esc_attr($class) . '" width="' . esc_attr($atts['embed_width']) . '" height="' . esc_attr($atts['embed_height']) . '" src="' . esc_url($this->get_request_url($atts)) . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>';
		return $result;
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

		$presets = WP_Theater::$presets;

		// make sure the important things are clean.
		foreach (array('service', 'preset', 'id', 'content', 'mode') as $attr) {
		if (isset($atts['attr']))
			$atts['attr'] = trim($atts['attr']);
		}

		// check for a preset -- atts' preset || shortcode tag as preset || service as preset || die, all empty
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
			else  {
				$atts['id'] = $content;
			}
		}

		$result = apply_filters('wp_theater-format_params', shortcode_atts($presets->get_preset($atts['preset']), $atts, $tag), $content, $tag);

		// make sure a mode is provided
		if(!isset($result['mode']) || empty($result['mode'])) {
			return 'Mode value not found';
		}
		$mode = $result['mode'] == 'theater' ? 'embed' : $result['mode'];
		// make sure the link format is available for the requested mode
		if(!isset($result['modes'][$mode]) || empty($result['modes'][$mode])) {
			return 'Requested mode is undefined';
		}

		return $result;
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
		if(!is_array($atts) || !count($atts)) return $atts;

		// needs -- compile a list of these settings based on available presets -- can't do the show/hide but can do service and mode.
		// case: $services[$value]
		// case: $modes[$value]

		foreach($atts as $key => $value) {
			if(is_int($key)) {

				switch($value) {

					case 'youtube':
					case 'vimeo':
						$atts['service'] = $value;
					 break;

					case 'embed':
					case 'theater':
					case 'preview':
					case 'user':
					case 'playlist':
					case 'channel':
					case 'group':
						$atts['mode'] = $value;
					 break;

					case 'show_title':
					case 'show_video_title':
					case 'show_more_link':
					case 'show_theater':
					case 'show_fullwindow':
					case 'show_lowerlights':
					case 'keep_ratio':
					case 'autoplay_onclick':
						$atts[$value] = TRUE;
					 break;

					case 'hide_title':
					case 'hide_video_title':
					case 'hide_more_link':
					case 'hide_theater':
					case 'hide_fullwindow':
					case 'hide_lowerlights':
						$atts['show' . substr($value, 4)] = FALSE;
					 break;

					case 'dont_cache':
					case 'dont_keep_ratio':
					case 'dont_autoplay_onclick':
						$atts[substr($value, 5)] = FALSE;
					 break;

					case '1cols':
					case '2cols':
					case '3cols':
					case '4cols':
					case '5cols':
					case '6cols':
						$atts['columns'] = (int) substr($value, 0, 1);
					 break;

					default:
					 break;
				}
			}
		}
		return apply_filters('wp_theater-capture_no_value_atts', $atts);
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

		$request_url = $this->get_request_url($atts);

		// let people hook in here to parse their own service
		$result = apply_filters('wp_theater-pre_get_api_data', '', $atts, $request_url);
		if (!empty($result)) return $result;

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
		$service = trim($atts['service']);

		$out = apply_filters("wp_theater-parse_{$service}_response", $out, $response, $atts);

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

		// Allow filter to override the request url
		$result = apply_filters('wp_theater-pre_get_request_url', '', $atts, $request, $output);
		if (!empty($result)) return $result;

		if ($atts['mode'] == 'theater') $atts['mode'] = 'embed';

		$result = str_replace('%id%', $atts['id'], $atts['modes'][$atts['mode']]);

		if(isset($atts['service']) && $atts['service'] == 'vimeo' && $atts['mode'] != 'embed') {
			if($atts['mode'] != 'preview')
				$result .= $request;
			$result .= '.' . $output;
		}

		return $result;
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

		if (isset($feed->data->items)) {
			foreach($feed->data->items as $video) {
				// user feeds don't have a sub listing
				$parsed_video = $this->parse_youtube_video($atts['mode'] == 'user' ? $video : $video->video);
				if ($parsed_video !== FALSE)
					array_push($out->videos, (object) $parsed_video);
			}
		} else {
			$parsed_video = $this->parse_youtube_video($feed->data);
			if ($parsed_video !== FALSE)
				array_push($out->videos, (object) $parsed_video);
		}

		$out->title = $atts['mode'] == 'user' ? (string) 'Uploads by ' . $atts['id'] : (string) $feed->data->title;
		// oddly enough this works even though the video id is not the author of the playlist.... 
		$out->url = $this->get_more_link($atts, isset($out->videos[0]) ? $out->videos[0]->id : '');

		return $out;
	}

	/**
	 * Takes a single youtube video entry and returns a reformatted array
	 * @since WP Theater 1.0.0
	 *
	 * @param object $video The response object for a single video
	 *
	 * @return object A formatted object for a single video
	 */
	public function parse_youtube_video($video) {

		$out = new stdClass();

		if (isset ($video->status->value) && ($video->status->value == 'rejected' || $video->status->value == 'restricted'))
			return FALSE;

		if (isset ($video->accessControl->embed) && $video->accessControl->embed == 'denied')
			return FALSE;

		$out->title = (string) $video->title;
		$out->id = (string) $video->id;
		$out->url = (string) $video->player->default;
		$out->upload_date = (string) $video->uploaded;
		$out->description = (string) $video->description;
		$out->category = (string) $video->category;
		$out->duration = (string) $video->duration;
		$out->rating = (string) $video->rating;
		$out->likeCount = (string) $video->likeCount;
		$out->viewCount = (string) $video->viewCount;

		// dimensions -- not going to bother with their aspect-ratio BS.
		$out->width = (string) '640';
		$out->height = (string) '360';

		// thumbnails
		$out->thumbnails = array();
		$out->thumbnails['small'] = (string) $video->thumbnail->sqDefault;
		// don't they have a mqDefault too? But not in this response... Sweet Youtube! Sweet!
		$out->thumbnails['large'] = (string) $video->thumbnail->hqDefault;

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

		if(!is_array($feed))
			return FALSE;

		foreach($feed as $video) {

			// make sure we can embed the video
			if ($video->embed_privacy  != 'anywhere')
				continue;

			$video->thumbnails = array();
			$video->thumbnails['small']  = $video->thumbnail_small;
			$video->thumbnails['medium'] = $video->thumbnail_medium;
			$video->thumbnails['large']  = $video->thumbnail_large;
			// add the video
			array_push($out->videos, (object) $video);
		}

		// we need to request the info feed as well
		if($atts['mode'] == 'user' || $atts['mode'] == 'channel' || $atts['mode'] == 'group') {

			$request_url = $this->get_request_url($atts, 'info');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_URL, $request_url);

			$response = json_decode(curl_exec($ch));
			curl_close($ch);

			$videos = $out->videos;
			$out = $response;
			$out->videos = $videos;

			// just make sure we have a title named 'title'....
			if ($atts['mode'] == 'user') 
				$out->title = 'Uploads by ' . $out->display_name;
			else
				$out->title = $out->name;
		}

		return $out;
	}

	/**
	 * Get the more link to feeds that don't contain one.
	 * @since WP Theater 1.0.0
	 *
	 * @param array $atts The attributes required to fill in an iframe
	 *
	 * @return string The complete url
	 */
	public function get_more_link($atts, $first_id = '') {

		// Allow filter to override more link
		$result = apply_filters('wp_theater-pre_get_more_link', '', $atts, $first_id);
		if (!empty($result)) return $result;

		$result = $atts['modes']['link'];
		if($atts['service'] == 'youtube') {
			if($atts['mode'] == 'user')
				$result .= 'user/' . $atts['id'];
			else
				$result .= 'watch?';
				if($atts['mode'] == 'embed' || $atts['mode'] == 'preview')
					$result .= 'v=' . $atts['id'];
				elseif($atts['mode'] == 'playlist')
					$result .= 'v=' . $first_id . '&list=' . $atts['id'];
		}
		return apply_filters('wp_theater-get_more_link', $result, $atts, $first_id);
	}

	/**
	 * Get the appropriate video dimensiona based on given data and preferences
	 * @since WP Theater 1.1.3
	 *
	 * @param array $atts The shortcodes attributes
	 * @param array $atts The video to constrain
	 *
	 * @return object The resulting object with width and height properties
	 */
	public function constrain_video_dimensions($atts, $video = FALSE) {

		// if we have height and width settings from the shortcode
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
			$atts['embed_width'] = (int) ($ratio * $dimensions['height']);
			return $atts;
		}

		// if we have a $content_width global then use that;
		if ( isset( $GLOBALS['content_width'] ) ) {
			global $content_width;
			$ratio = $content_width / (int) $dimensions['width'];
			$atts['embed_width'] = $content_width;
			$atts['embed_height'] = (int) ($ratio * $dimensions['height']);
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