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
		add_filter( 'plugin_action_links_' . WP_Theater::$basename, array( $this, 'add_action_links' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}

	/* Register menu item */
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

	/**
	 * Adds a Setting link to the plugin list
	 * @since WP Theater 1.2.3
	 */
	public static function add_action_links( $links ) {
		return array_merge(
			$links,
			array('settings' => '<a href="' . admin_url( 'options-general.php?page=wp_theater' ) . '">' . __( 'Settings', 'wptheater' ) . '</a>')
		);
	}

	/* register settings */
	public function settings_init(){

		register_setting(
			'wp_theater_options_group',
			'wp_theater_options',
			array( $this, 'options_sanitize' )
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
			esc_html__('v3 Public API', 'wptheater'),
			array( $this, 'youtube_v3_sapi_fields' ),
			'wp_theater',
			'wpts_youtube'
		);
	}

	/* sanitize input */
	public function options_sanitize($input){

		foreach ($input as $key => $value) {
			switch ($key){
				case 'load_css':
				case 'load_js':
				case 'enable_default_shortcodes':
				case 'yt_v3_sapi_enabled':
					$input[$key] = (int) $value;
					$input[$key] = (string) $value;
				 break;
				case 'yt_v3_sapi_key':
				case 'version':
					$input[$key] = apply_filters('pre_wp_theater-text', $value);
				 break;
				default:
					$input[$key] = apply_filters('pre_wp_theater-text', $value);
				 break;
			}
		}

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
		$load_css = (isset($options['load_css'])) ? $options['load_css'] : '0';
		$load_css = (int) $load_css;
		$load_js = (isset($options['load_js'])) ? $options['load_js'] : '0';
		$load_js = (int) $load_js;
		$version = (isset($options['version'])) ? $options['version'] : WP_Theater::VERSION;
		echo '<input type="hidden" id="version" name="wp_theater_options[version]" value="' . esc_attr($version) . '" ' . checked( '1', $load_css, false ) . '> Use plugin\'s CSS file<br/><br/>';
		
		echo '<input type="checkbox" id="load_css" name="wp_theater_options[load_css]" value="1" ' . checked( '1', $load_css, false ) . '> Use plugin\'s CSS file<br/><br/>';
		echo '<input type="checkbox" id="load_js" name="wp_theater_options[load_js]" value="1" ' . checked( '1', $load_js, false ) . '> Use plugin\'s JavaScript file';
	}

	public function default_shortcode_fields() {
		$options = get_option('wp_theater_options');
		$endef_shortcodes = (isset($options['enable_default_shortcodes'])) ? $options['enable_default_shortcodes'] : '0';
		$endef_shortcodes = (int) $endef_shortcodes;
		echo '<input type="checkbox" id="enable_default_shortcodes" name="wp_theater_options[enable_default_shortcodes]" value="1" ' . checked( 1, $endef_shortcodes, false ) . '> Register [vimeo] and [youtube] shotcodes.<br/><em>Disable to use [wptheater vimeo] and [wptheater youtube] instead.</em>';
	}

	public function default_cache_fields() {
		$options = get_option('wp_theater_options');
		$cache_life = (isset($options['cache_life'])) ? $options['cache_life'] : '1440';
		$cache_life = (int) $cache_life;
		echo '<input type="number" id="cache_life" name="wp_theater_options[cache_life]" value="' . esc_attr($cache_life) . '" size="5"> seconds<br/><em>' . esc_html__('0 (zero) to bypass.', 'wptheater') . '</em>';
	}

	/*
	 * YouTube Settings Fields
	 */

	public function wpts_youtube_desc(){
		echo '<p>Youtube\'s v2 API was depreciated in March \'15 and is unstable.  Switch to the v3 API to remove the DeviceSupport error video.</p>';
		echo '<p>If you don\'t use YouTube then just check the <em>Enable</em> option and the annoying notice will go away.</p>';
	}

	public function youtube_v3_sapi_fields() {
		$options = get_option('wp_theater_options');
		$yt_v3_sapi_enabled = (isset($options['yt_v3_sapi_enabled'])) ? $options['yt_v3_sapi_enabled'] : '0';
		$yt_v3_sapi_enabled = (int) $yt_v3_sapi_enabled;
		echo '<input type="checkbox" id="yt_v3_sapi_enabled" name="wp_theater_options[yt_v3_sapi_enabled]" value="1" ' . checked( 1, $yt_v3_sapi_enabled, false ) . '>Enable<br/><br/>';

		$options = get_option('wp_theater_options');
		$yt_v3_sapi_key = (isset($options['yt_v3_sapi_key'])) ? $options['yt_v3_sapi_key'] : '';
		echo 'API Key<br/>';
		echo '<input type="text" id="yt_v3_sapi_key" name="wp_theater_options[yt_v3_sapi_key]" class="long-text" value="' . esc_attr($yt_v3_sapi_key) . '" autocomplete="off" size="40" ><br/>';
		echo 'Need help? <a href="https://wordpress.org/support/topic/how-to-setup-v3-api" target="_blank">Follow these instructions</a>.';
	}
	/**/

} // END CLASS
} // END EXISTS CHECK