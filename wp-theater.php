<?php
/*
Plugin Name: WP Theater
Plugin URI: http://redshiftstudio.com/wp-theater/
Description: Adds shortcodes that can display embeds, previews, user uploads, playlists, channels and groups from Youtube or Vimeo.
Author: Kenton Farst
Author URI: http://kent.farst.net
Donate URI: http://redshiftstudio.com/wp-theater/
License: GPLv3
Version: 1.0.6
*/

if(defined('ABSPATH') && defined('WPINC') && !class_exists('WP_Theater')){

class WP_Theater {

	/**
	 * Version constant
	 * @since WP Theater 1.0.0
	 */
	const VERSION = '1.0.6';

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
	 * Boolean for knowing when a shortcode is going to be applied. // NOT USED YET
	 * @since WP Theater 1.0.0
	 */
	public static $shortcode_in_use = FALSE;

	/**
	 * Constructor
	 * @since WP Theater 1.0.0
	 */
	public function WP_Theater() {__construct();}

	/**
	 * Constructs....  .
	 * @since WP Theater 1.0.0
	 */
	function __construct() {
		// nothing to construct as it's all static methods
	}

	/**
	 * Activation deactivation and uninstall methods
	 *
	 * @since WP Theater 1.0.0
	 */
	public static function activation() {

		// establish default settings
		if (!get_option('wp_theater_options')) {

			$val = array(
				'load_css' => '1',
				'load_js' => '1',
				'load_genericons' => '1',
				'cache_life' => 14400,
				'show_activate_notice' => '1',
			);
			add_option('wp_theater_options', $val);
		}
	}
	public static function deactivation() {}
	public static function uninstall() {
		delete_option('wp_theater_options');
	}

	/**
	 * Defines constants used by this plugin.
	 *
	 * @since WP Theater 1.0.0
	 */
	protected static function constants() {
		define ('WP_THEATER_DIR',      trailingslashit(plugin_dir_path(__FILE__)));
		define ('WP_THEATER_URI',      trailingslashit(plugin_dir_url(__FILE__)));
		define ('WP_THEATER_INCLUDES', WP_THEATER_DIR.trailingslashit('inc'));
		define ('WP_THEATER_ADMIN',    WP_THEATER_DIR.trailingslashit('admin'));
	}

	/**
	 * Load the files required by this plugin.
	 *
	 * @since WP Theater 1.0.0
	 */
	protected static function includes() {
		require_once(WP_THEATER_INCLUDES.'class-settings.php');
		require_once(WP_THEATER_INCLUDES.'class-presets.php');
		require_once(WP_THEATER_INCLUDES.'class-shortcodes.php');
		require_once(WP_THEATER_INCLUDES.'filters.php');
	}

	/**
	 * Inits
	 * @since WP Theater 1.0.0
	 */
	public static function init () {

		static::constants();
		static::includes();

		register_activation_hook(  __FILE__,array(__CLASS__,'activation'  ));
		register_deactivation_hook(__FILE__,array(__CLASS__,'deactivation'));
		register_uninstall_hook(   __FILE__,array(__CLASS__,'uninstall'   ));

		static::$settings = new WP_Theater_Settings();
		static::$presets = new WP_Theater_Presets();
		static::$shortcodes = new WP_Theater_Shortcodes();

		add_action('wp_enqueue_scripts',array(__CLASS__,'enqueue_styles'));
		add_action('wp_enqueue_scripts',array(__CLASS__,'enqueue_scripts'));
	}

	/**
	 * Enqueues our styles
	 * @since WP Theater 1.0.0
	 */
	public static function enqueue_styles() {
		$options = get_option('wp_theater_options');
		$load_css = (isset($options['load_css'])) ? $options['load_css'] : '';

		if((int)$load_css == 1)
			wp_enqueue_style('wp_theater-styles',trailingslashit(WP_THEATER_URI).'css/style-min.css',array(),'20130824');

		// Add Genericons font,used in the main stylesheet IF it's not queued up already
		$load_gi = (isset($options['load_genericons'])) ? $options['load_genericons'] : '';
		if ((int)$load_gi == 1 && !wp_style_is('genericons','registered') && !wp_style_is('genericons','enqueued'))
			wp_enqueue_style('genericons',trailingslashit(WP_THEATER_URI).'/fonts/genericons.css',array(),'2.09');
	}
	public static function enqueue_scripts() {
		$options = get_option('wp_theater_options');
		$load_js = (isset($options['load_js'])) ? $options['load_js'] : '';
		if((int)$load_js == 1)
			wp_enqueue_script('wp_theater-scripts',trailingslashit(WP_THEATER_URI).'js/script-min.js',array('jquery'),'20130823',TRUE);
	}

	/**
	 * Adds notice for admins to concider donating
	 * @since WP Theater 1.0.0
	 */
	//add_action('admin_notices', array(__CLASS__,'admin_notices_donate'));
	public function admin_notices_donate() {
    ?>
    <div class="updated">
			<p>Thank you for using WP Theater. For more detail visit <a href="http://redshiftstudio.com/wp-theater/" rel="author" title="WP Theater on Redshift Studio's website">redshiftstudio.com/wp-theater/</a></p>
			<p>We put a lot of time into this plugin and hope that you'd concider donating to support continued development.<br />
			<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X3FWTE2FBBTJU" target="_blank" rel="nofollow payment" title="Donate through PayPal"><img src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" alt="PayPal Donate" /></a>
			</p>
    </div>
    <?php
	}
  /**/

} /* END CLASS*/

} /* END EXISTS CHECK */

// start it up
WP_Theater::init();