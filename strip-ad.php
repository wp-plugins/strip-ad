<?php
/* 
 * Plugin Name:   Strip Ad
 * Version:       1.0
 * Plugin URI:    http://www.technozeast.com/strip-ad
 * Description:   Add Strip Ad above the header to your blog.
 * Author:        Shivendu Madhava
 * Author URI:    http://www.technozeast.com 
 *
 */
 
$mta_path     = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$mta_path     = str_replace('\\','/',$mta_path);
$mta_dir      = substr($mta_path,0,strrpos($mta_path,'/'));
$mta_fullpath = get_bloginfo('siteurl').'/wp-content/plugins/'.$mta_dir.'/';
$mta_relpath  = str_replace('\\','/',dirname(__FILE__));
define('MTA_PATH', $mta_path);
define('MTA_FULLPATH', $mta_fullpath);
define('MTA_NAME', 'Strip Ad');
define('MTA_VERSION', '1.0');
require_once($mta_relpath.'/include/strip-ad.cls.php');

/**
 * Strip Ad
 * Holds all the necessary functions and variables
 */
class StripAdPlugin extends StripAd
{
	/**
	 * Constructor. Adds 'Strip Ad' plugin actions/filters and gets the user defined options.
	 * @access public
	 */
	function StripAdPlugin() {
	    add_action('activate_'.MTA_PATH, array(&$this, 'mtaActivate'));
		add_action('init', array(&$this, 'mtaSetCookie'));	
		add_action('admin_menu', array(&$this, 'mtaAddMenu'));
		add_filter('get_header', array(&$this, 'mtaStripAdStart'));
		add_action('wp_head', array(&$this, 'mtaStripAdStart'));
		add_filter('get_footer', array(&$this, 'mtaStripAdEnd'));
		add_action('wp_footer', array(&$this, 'mtaStripAdEnd'));
		
		if( !$this->mta_option = get_option('mta_stripe_ad') ) {
			$this->mta_option  = array();
		}
		if( !$this->mta_settings = get_option('mta_stripe_ad_settings') ) {
			$this->mta_settings  = $this->default_settings;
		}
		if ( empty($this->mta_settings['text_style_b']) )
			$this->mta_settings['text_style_b'] = 'normal';
		if ( empty($this->mta_settings['text_style_i']) )
			$this->mta_settings['text_style_i'] = 'normal';
		if ( empty($this->mta_settings['text_style_u']) )
			$this->mta_settings['text_style_u'] = 'none';
	}
	
	/**
	 * Called when plugin is activated. Adds 'Strip Ad' options to the options table.
	 * @access public
	 */
	function mtaActivate() {
		add_option('mta_stripe_ad', array(), 'Strip Ad Array', 'no');
		add_option('mta_stripe_ad_settings', $this->default_settings, 'Strip Ad Settings', 'no');
		return true;
	}
	
	/**
	 * Sets Cookies according to the preferences set in admin
	 * @access public
	 */
	function mtaSetCookie() {
		if (!is_admin()) {
			$siteurl = get_bloginfo('siteurl');
			$this->__mtaSetCookie($siteurl);
		}
	}
	
	/**
	 * Start Output Buffer
	 * @access public
	 */
	function mtaStripAdStart(){
		if ( $this->mta_header_executed != 1 ) {
			$this->mta_header_executed = 1;
			ob_start();
		}				
	}
	
	/**
	 * Displays Strip Ad. Gets content from output buffer and displays
	 * @access public
	 */
	function mtaStripAdEnd(){
		if ( $this->mta_footer_executed == 1 ) {
			return;
		}
		$this->mta_footer_executed = 1;
		
		$mta_output = ob_get_contents();
		ob_end_clean();

		ob_start();
		$this->__mtaStripAdStyle();
		$mta_style = ob_get_contents();
		ob_end_clean();
		
		$stripe_ad  = $this->__mtaShowStripAd();
		$stripe_ad = str_replace("$", "\\$", $stripe_ad); // Escape $ as it has special meaning in regex
		
		$mta_output = str_replace("</head>", "\n $mta_style \n </head>", $mta_output);
		$mta_output = preg_replace("/(<body[^>]*>)/i", "\\1 \n $stripe_ad", $mta_output);
		echo $mta_output;
	}
	
	/**
	 * Adds 'Strip Ad' link to admin's Options menu
	 * @access public 
	 */
	function mtaAddMenu() {
		add_options_page('Strip Ad', 'Strip Ad', 'manage_options', MTA_PATH, array(&$this, 'mtaOptionsPg'));
	}
	
	/**
	 * Displays the page content for 'Strip Ad' Options submenu
	 * Carries out all the operations in Options page.
	 * @access public 
	 */
	function mtaOptionsPg() {
		$mta_msg = '';
		$mta_msg = $this->__mtaOptionsPg();
		
		if ( count($this->mta_settings) > 0 ) {
			update_option('mta_stripe_ad_settings', $this->mta_settings);
		}
		if ( count($this->mta_option) > 0 ) {
			update_option('mta_stripe_ad', $this->mta_option);
		}
		if ( trim($mta_msg) != '' ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$mta_msg.'</strong></p></div>';
		}
		?>
		<div class="wrap">
		 <?php $this->__mtaShowOptionsPg(); ?>
		</div>
		<?php
	}
	
} // Eof Class

$StripAdPlugin = new StripAdPlugin();
?>