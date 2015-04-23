<?php
/*
Plugin Name: WP Theater
Plugin URI: http://redshiftstudio.com/wp-theater/
Description: Adds shortcodes that can display embeds, previews, user uploads, playlists, channels and groups from Youtube or Vimeo. 
Author: Kenton Farst
Author URI: http://kent.farst.net
Donate URI: http://redshiftstudio.com/wp-theater/
License: GPLv3
Version: 1.2.2
*/

if( defined( 'ABSPATH' ) && defined( 'WPINC' ) && !class_exists( 'WP_Theater' ) ){

class WP_Theater {

	/**
	 * Version constant
	 * @since WP Theater 1.0.0
	 */
	const VERSION = '1.2.2';

	/**
	 * Plugin directory
	 * @since WP Theater 1.0.8
	 */
	public static $dir;

	/**
	 * Plugin URI
	 * @since WP Theater 1.0.8
	 */
	public static $uri;

	/**
	 * Plugin includes directory
	 * @since WP Theater 1.0.8
	 */
	public static $inc;

	/**
	 * Preset's class instance
	 * @since WP Theater 1.0.0
	 */
	public static $presets;

	/**
	 * Shortcode's class instance
	 * @since WP Theater 1.0.0
	 */
	public static $shortcodes;

	/**
	 * Setting's class instance
	 * @since WP Theater 1.0.0
	 */
	public static $settings;

	/**
	 * Constructs .  .  .  .    . 
	 * @since WP Theater 1.0.0
	 */
	function __construct() {
		// nothing to construct as it's all static methods
	}

	/**
	 * Activation handler
	 *
	 * @since WP Theater 1.0.0
	 */
	public static function activation() {

		// Verify PHP requirement
		if ( version_compare( phpversion(), '5.3.0', '<' ) ) {
			echo "<strong>WP Theater</strong> requires <strong>PHP 5.3.0</strong> or higher, and has been deactivated! You're currently running <strong>PHP " . phpversion() . "</strong>." ;
			exit;
		}

		// Verify cURL requirement
		if ( !function_exists( 'curl_init' ) ) {
			echo "<strong>WP Theater</strong> requires the PHP <strong>cURL</strong> extension, and has been deactivated!" ;
			// ehh... needs to deactivate here tard
			exit;
		}

		// establish default settings
		if ( !get_option( 'wp_theater_options' ) ) {

			// Setup default options
			$option = array( 
				'version' => static::VERSION, 
				'load_css' => '1',
				'load_js' => '1',
				'load_genericons' => '1',
				'cache_life' => 14400,
				'show_activate_notice' => '1',
				'yt_v3_sapi_enabled' => '0',
				'yt_v3_sapi_key' => '',
				'enable_default_shortcodes' => '1'
			 );
			add_option( 'wp_theater_options', $option );

		} else {

			// Upgrade
			$option = get_option( 'wp_theater_options' );

			if(version_compare( $option['version'], '1.2', '<' )) {
				$option['yt_v3_sapi_enabled'] = '0';
				$option['yt_v3_sapi_key'] = '';
				$option['enable_default_shortcodes'] = '1';
			}

			$option['show_activate_notice'] = '1';
			$option['version'] = static::VERSION;
			update_option( 'wp_theater_options', $option );
		}

	}

	/**
	 * Deactivation handler
	 *
	 * @since WP Theater 1.0.0
	 */
	public static function deactivation() {}

	/**
	 * Manual deactivation method
	 *
	 * @since WP Theater 1.1.0
	 */
	public static function deactivate() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	/**
	 * Uninstall handler
	 *
	 * @since WP Theater 1.0.0
	 */
	public static function uninstall() {
		delete_option( 'wp_theater_options' );
	}

	/**
	 * Inits
	 * @since WP Theater 1.0.0
	 */
	public static function init () {

		register_activation_hook(   __FILE__, array( __CLASS__, 'activation'   ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivation' ) );
		register_uninstall_hook(    __FILE__, array( __CLASS__, 'uninstall'    ) );

		if ( is_admin() ) {
			// handle new activation
			add_action( 'load-plugins.php', array( __CLASS__, 'activate_check' ) );

			require_once( static::$inc . 'class-settings.php' );
			static::$settings   = new WP_Theater_Settings();
		} else {
			require_once( static::$inc . 'class-presets.php' );
			require_once( static::$inc . 'class-shortcodes.php' );
			require_once( static::$inc . 'filters.php' );
			static::$presets    = new WP_Theater_Presets();
			static::$shortcodes = new WP_Theater_Shortcodes();
		}
	}

	/**
	 * Handles new activations 
	 * @since WP Theater 1.1.0
	 */
	public static function activate_check() {
		$option = get_option( 'wp_theater_options' );
		if ( !current_user_can('activate_plugins') || (isset($option['show_activate_notice']) && $option['show_activate_notice'] != '1')) return;

		add_action( 'admin_notices', array( __CLASS__, 'admin_notice_activation' ) );
	}

	/**
	 * Adds setting link to plugin list
	 * @since WP Theater 1.2
	 */
	public function add_action_links( $links ) {
		return array_merge(
			array('settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'),
			$links
		);
	}

	/**
	 * Outputs activation notice
	 * @since WP Theater 1.1.0
	 */
	//add_action( 'admin_notices', array( __CLASS__, 'admin_notices_donate' ) );
	public static function admin_notice_activation() {
    ?>
    <div class="updated" style="padding:6px;">
			<a style="float:right;padding:6px;" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X3FWTE2FBBTJU" target="_blank" rel="nofollow payment" title="Donate through PayPal"><img src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" alt="PayPal Donate" /></a>
			<p>Thank you for using WP Theater.  For more usage information please visit <a href="http://redshiftstudio.com/wp-theater/" rel="author" title="WP Theater on Redshift Studio's website">redshiftstudio.com/wp-theater/</a></p>
    </div>
    <?php
		$option = get_option( 'wp_theater_options' );
		$option['show_activate_notice'] = '0';
		update_option( 'wp_theater_options', $option );
	}

	/**
	 * Single call for enqueueing(?) assets
	 * @since WP Theater 1.0.7
	 */
	public static function enqueue_assets () {
		static::enqueue_styles();
		static::enqueue_scripts();
	}

	/**
	 * Enqueues our styles
	 * @since WP Theater 1.0.0
	 */
	public static function enqueue_styles() {
		$options = get_option( 'wp_theater_options' );
		$load_css = ( isset( $options['load_css'] ) ) ? $options['load_css'] : '';

		if( (int) $load_css == 1 )
			wp_enqueue_style( 'wp_theater-styles', static::$uri . 'css/style.min.css', array(), '20150404' );

		// Add Genericons font, used in the main stylesheet IF it's not queued up already
		$load_gi = ( isset( $options['load_genericons'] ) ) ? $options['load_genericons'] : '';
		if ( (int) $load_gi == 1 && !wp_style_is( 'genericons', 'registered' ) && !wp_style_is( 'genericons', 'enqueued' ) )
			wp_enqueue_style( 'genericons', static::$uri . 'fonts/genericons.css', array(), '3.02' );
	}

	/**
	 * Enqueues our scripts
	 * @since WP Theater 1.0.0
	 */
	public static function enqueue_scripts() {
		$options = get_option( 'wp_theater_options' );
		$load_js = ( isset( $options['load_js'] ) ) ? $options['load_js'] : '';
		if( (int) $load_js == 1 )
			wp_enqueue_script( 'wp_theater-scripts', static::$uri . 'js/script.min.js', array( 'jquery' ), '20150404', TRUE );
	}
  /**/

} /* END CLASS*/

// define the plugin's location variables
WP_Theater::$dir = trailingslashit( plugin_dir_path( __FILE__ ) );
WP_Theater::$uri = trailingslashit( plugin_dir_url( __FILE__ ) );
WP_Theater::$inc = WP_Theater::$dir  .  trailingslashit( 'inc' );
// start it up
WP_Theater::init();

} /* END EXISTS CHECK */