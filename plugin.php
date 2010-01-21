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
 
$p_path     = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$p_path     = str_replace('\\','/',$p_path);
$p_dir      = substr($p_path,0,strrpos($p_path,'/'));
$p_fullpath = get_bloginfo('siteurl').'/wp-content/plugins/'.$p_dir.'/';
$p_relpath  = str_replace('\\','/',dirname(__FILE__));
define('P_PATH', $p_path);
define('P_FULLPATH', $p_fullpath);
define('P_NAME', 'Strip Ad');
define('P_VERSION', '1.0');
require_once($p_relpath.'/include/functions.php');

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
	    add_action('activate_'.P_PATH, array(&$this, 'pActivate'));
		add_action('init', array(&$this, 'pSetCookie'));	
		add_action('admin_menu', array(&$this, 'pAddMenu'));
		add_filter('get_header', array(&$this, 'pStripAdStart'));
		add_action('wp_head', array(&$this, 'pStripAdStart'));
		add_filter('get_footer', array(&$this, 'pStripAdEnd'));
		add_action('wp_footer', array(&$this, 'pStripAdEnd'));
		
		if( !$this->p_option = get_option('p_stripe_ad') ) {
			$this->p_option  = array();
		}
		if( !$this->p_settings = get_option('p_stripe_ad_settings') ) {
			$this->p_settings  = $this->default_settings;
		}
		if ( empty($this->p_settings['text_style_b']) )
			$this->p_settings['text_style_b'] = 'normal';
		if ( empty($this->p_settings['text_style_i']) )
			$this->p_settings['text_style_i'] = 'normal';
		if ( empty($this->p_settings['text_style_u']) )
			$this->p_settings['text_style_u'] = 'none';
	}
	
	/**
	 * Called when plugin is activated. Adds 'Strip Ad' options to the options table.
	 * @access public
	 */
	function pActivate() {
		add_option('p_stripe_ad', array(), 'Strip Ad Array', 'no');
		add_option('p_stripe_ad_settings', $this->default_settings, 'Strip Ad Settings', 'no');
		return true;
	}
	
	/**
	 * Sets Cookies according to the preferences set in admin
	 * @access public
	 */
	function pSetCookie() {
		if (!is_admin()) {
			$siteurl = get_bloginfo('siteurl');
			$this->__pSetCookie($siteurl);
		}
	}
	
	/**
	 * Start Output Buffer
	 * @access public
	 */
	function pStripAdStart(){
		if ( $this->p_header_executed != 1 ) {
			$this->p_header_executed = 1;
			ob_start();
		}				
	}
	
	/**
	 * Displays Strip Ad. Gets content from output buffer and displays
	 * @access public
	 */
	function pStripAdEnd(){
		if ( $this->p_footer_executed == 1 ) {
			return;
		}
		$this->p_footer_executed = 1;
		
		$p_output = ob_get_contents();
		ob_end_clean();

		ob_start();
		$this->__pStripAdStyle();
		$p_style = ob_get_contents();
		ob_end_clean();
		
		$stripe_ad  = $this->__pShowStripAd();
		$stripe_ad = str_replace("$", "\\$", $stripe_ad); // Escape $ as it has special meaning in regex
		
		$p_output = str_replace("</head>", "\n $p_style \n </head>", $p_output);
		$p_output = preg_replace("/(<body[^>]*>)/i", "\\1 \n $stripe_ad", $p_output);
		echo $p_output;
	}
	
	/**
	 * Adds 'Strip Ad' link to admin's Options menu
	 * @access public 
	 */
	function pAddMenu() {
		add_options_page('Strip Ad', 'Strip Ad', 'manage_options', P_PATH, array(&$this, 'pOptionsPg'));
	}
	
	/**
	 * Displays the page content for 'Strip Ad' Options submenu
	 * Carries out all the operations in Options page.
	 * @access public 
	 */
	function pOptionsPg() {
		$p_msg = '';
		$p_msg = $this->__pOptionsPg();
		
		if ( count($this->p_settings) > 0 ) {
			update_option('p_stripe_ad_settings', $this->p_settings);
		}
		if ( count($this->p_option) > 0 ) {
			update_option('p_stripe_ad', $this->p_option);
		}
		if ( trim($p_msg) != '' ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$p_msg.'</strong></p></div>';
		}
		?>
		<div class="wrap">
		 <?php $this->__pShowOptionsPg(); ?>
		</div>
		<?php
	}
	
} // Eof Class

$StripAdPlugin = new StripAdPlugin();
?>