<?php 
if(class_exists('WP_Theater') && !class_exists('WP_Theater_Settings')){

class WP_Theater_Settings {

	/**
	 * Version constant
	 * @since WP Theater 1.0.0
	 */
	protected $page = '';

	/**
	 * Constructs
	 * @since WP Theater 1.0.0
	 */
	public function __construct () {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) ); //menu setup
		add_filter( 'plugin_action_links_wp-theater', array( $this, 'plugin_action_links' ), 2, 2 );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}

	/* register menu item */
	public function admin_menu(){
		$this->page = add_options_page(
			'WP Theater Settings',
			'WP Theater',
			'manage_options',
			'wp_theater',
			array($this, 'options_screen')
		);
	}

	/* display page content */
	public function options_screen() {
		global $submenu;
		// access page settings 

		// output 
		?>
		<div class="wrap">
			<?php screen_icon();?>
			<h2>WP Theater Settings</h2>
			<form id="wpmsc_options" action="options.php" method="post">
				<?php
				settings_fields('wp_theater_options_group');
				do_settings_sections('wp_theater'); 
				submit_button('Save', 'primary', 'wp_theater_options_submit');
				?>
			</form>
		</div>
		<?php
	}

	/* settings link in plugin management screen */
	public function plugin_action_links($actions, $file) {
		if(false !== strpos($file, 'wp_theater'))
			$actions['settings'] = '<a href="options-general.php?page=wp_theater">' . esc_html__('Settings', 'wptheater') . '</a>';

		return $actions; 
	}

	/* register settings */
	public function settings_init(){

		register_setting(
			'wp_theater_options_group',
			'wp_theater_options',
			array( $this, 'options_validate' )
		);

		/*
		 * General Settings
		 */
		add_settings_section(
			'wpts_genset',
			esc_html__('General', 'wptheater'),
			array( $this, 'wpts_genset_desc' ),
			'wp_theater'
		);

		add_settings_field(
			'wpts_genset_load_assets',
			esc_html__('Assets', 'wptheater'),
			array( $this, 'default_asset_fields' ),
			'wp_theater',
			'wpts_genset'
		);

		add_settings_field(
			'wpts_genset_enable_default_shortcodes',
			esc_html__('Shortcodes', 'wptheater'),
			array( $this, 'default_shortcode_fields' ),
			'wp_theater',
			'wpts_genset'
		);

		add_settings_field(
			'wpts_genset_cache_life',
			esc_html__('Cache Expiration', 'wptheater'),
			array( $this, 'default_cache_fields' ),
			'wp_theater',
			'wpts_genset'
		);

		/*
		 * YouTube Settings
		 */
		add_settings_section(
			'wpts_youtube',
			esc_html__('YouTube', 'wptheater'),
			array( $this, 'wpts_youtube_desc' ),
			'wp_theater'
		);

		add_settings_field(
			'wpts_youtube_v3_sapi',
			esc_html__('v3 Simple API', 'wptheater'),
			array( $this, 'youtube_v3_sapi_fields' ),
			'wp_theater',
			'wpts_youtube'
		);
	}

	/* validate input */
	public function options_validate($input){

		if(isset($input['load_css']))
			$input['load_css'] = (int) $input['load_css'];
		if(isset($input['load_js']))
			$input['load_js'] = (int) $input['load_js'];
		if(isset($input['load_genericons']))
			$input['load_genericons'] = (int) $input['load_genericons'];

		if(isset($input['cache_life']))
			$input['cache_life'] = (int) $input['cache_life'];

		if(isset($input['enable_default_shortcodes']))
			$input['enable_default_shortcodes'] = (int) $input['enable_default_shortcodes'];

		if(isset($input['yt_v3_sapi_enabled']))
			$input['yt_v3_sapi_enabled'] = (int) $input['yt_v3_sapi_enabled'];
		if(isset($input['yt_v3_sapi_key']))
			$input['yt_v3_sapi_key'] = apply_filters('pre_wp_theater-text', $input['yt_v3_sapi_key'] );

		return $input;
	}

	/*
	 * General Settings Fields
	 */

	/* description text */
	public function wpts_genset_desc(){
		echo '<p></p>';
	}

	public function default_asset_fields() {
		$options = get_option('wp_theater_options');
		$load_css = (isset($options['load_css'])) ? $options['load_css'] : '';
		$load_css = (int) $load_css; //sanitise output
		$load_gi = (isset($options['load_genericons'])) ? $options['load_genericons'] : '';
		$load_gi = (int) $load_gi; //sanitise output
		$load_js = (isset($options['load_js'])) ? $options['load_js'] : '';
		$load_js = (int) $load_js; //sanitise output
		echo '<input type="checkbox" id="load_css" name="wp_theater_options[load_css]" value="1" ' . checked( 1, $load_css, false ) . '> Use plugin\'s CSS file<br/><br/>';
		echo '<input type="checkbox" id="load_genericons" name="wp_theater_options[load_genericons]" value="1" ' . checked( 1, $load_gi, false ) . '> Use plugin\'s Genericons font file<br/><em>if not already loaded</em><br/><br/>';
		echo '<input type="checkbox" id="load_js" name="wp_theater_options[load_js]" value="1" ' . checked( 1, $load_js, false ) . '> Use plugin\'s JavaScript file';
	}

	public function default_cache_fields() {
		$options = get_option('wp_theater_options');
		$cache_life = (isset($options['cache_life'])) ? $options['cache_life'] : '1440';
		$cache_life = (int) $cache_life; //sanitise output
		echo '<input type="text" id="cache_life" name="wp_theater_options[cache_life]" value="' . $cache_life . '" size="5"> seconds<br/><em>' . esc_html__('0 (zero) to bypass.', 'wptheater') . '</em>';
	}

	public function default_shortcode_fields() {
		$options = get_option('wp_theater_options');
		$endef_shortcodes = (isset($options['enable_default_shortcodes'])) ? $options['enable_default_shortcodes'] : '1440';
		$endef_shortcodes = (int) $endef_shortcodes; //sanitise output
		echo '<input type="checkbox" id="enable_default_shortcodes" name="wp_theater_options[enable_default_shortcodes]" value="1" ' . checked( 1, $endef_shortcodes, false ) . '> Register [vimeo] and [youtube] shotcodes.<br/><em>Use [wptheater vimeo] and [wptheater youtube] instead.</em>';
	}

	/*
	 * YouTube Settings Fields
	 */

	public function wpts_youtube_desc(){
		echo '<p>Youtube\'s v2 API was depreciated in March \'15 and is unstable.  Please switch to v3 ASAP.</p>';
	}

	public function youtube_v3_sapi_fields() {
		$options = get_option('wp_theater_options');
		$yt_v3_sapi_enabled = (isset($options['yt_v3_sapi_enabled'])) ? (int) $options['yt_v3_sapi_enabled'] : '0';
		echo '<input type="checkbox" id="yt_v3_sapi_enabled" name="wp_theater_options[yt_v3_sapi_enabled]" value="1" ' . checked( 1, $yt_v3_sapi_enabled, false ) . '>Enable<br/><br/>';
		
		$options = get_option('wp_theater_options');
		$yt_v3_sapi_key = (isset($options['yt_v3_sapi_key'])) ? esc_attr($options['yt_v3_sapi_key']) : '';
		echo 'API Key<br/>';
		echo '<input type="text" id="yt_v3_sapi_key" name="wp_theater_options[yt_v3_sapi_key]" class="long-text" value="' . $yt_v3_sapi_key . '" autocomplete="off" size="40" ><br/>';
		echo 'Need help? <a href="http://youtu.be/JbWnRhHfTDA" targe="_blank">Watch this video from Google</a> and visit <a href="https://code.google.com/apis/console" targe="_blank">code.google.com/apis/console</a>';
	}
	/**/

} // END CLASS
} // END EXISTS CHECK