<?php
 
/**
 * Strip Ad
 * Holds all the necessary functions and variables
 */
class StripAd
{
    /////////////////////////////////////////////////
    // PUBLIC VARIABLES
    /////////////////////////////////////////////////

    /**
     * Strip Ad options. Holds all the Ads.
     * @var string
     */
	var $mta_option = array();
	
    /**
     * Strip Ad options. Holds various settings for strip ad.
     * @var string
     */
	var $mta_settings = array();

    /**
     * Holds Post/Get data
     * @var array
     */
	var $mta_request = array();
	
	/**
     * Holds the default settings values
     * @var array
     */
	var	$default_settings = array(
					'font_family' => 'Arial', 'font_size' => 11, 'font_color' => '#0000FF', 
					'bg_color' => '#FFFFE1', 'border_color' => '#808080', 'text_align' => 'center', 
					'show_after' => 0, 'show_after_visits' => 5, 'show' => 1, 'show_days' => 3, 'show_visits' => 3,
					'float' => 0, 'disable' => 0
					);
	/**
	 * Constructor.
	 */
	function StripAd() {}
	
	/**
	 * Sets Cookies according to the preferences set in admin
	 * @access public
	 */
	function __mtaSetCookie($siteurl) {
		session_start();
		$url                = parse_url($siteurl);
		$noof_visits        = $this->mta_settings['show_visits'];
		$noof_days          = $this->mta_settings['show_days'];
		$days_cookie_expire = time() - (3600 * 24) * 365 * 1;  //1 year
		$days_cookie_life   = time() + (3600 * 24) * $noof_days;
		$visits_cookie_life = time() + (3600 * 24) * 365 * 1;
		
		if ( $this->mta_settings['show_after'] == 1 && !isset($_COOKIE['mtaShowAfter']) ) {
			setcookie("mtaShowAfter", 1, $visits_cookie_life, $url['path'] . '/');
			$halt_show = 1;
		} else if ( $this->mta_settings['show_after'] != 1 ) {
			setcookie("mtaShowAfter", '', $visits_cookie_life, $url['path'] . '/');
		}			
		if ( $this->mta_settings['show_after'] == 1 && isset($_COOKIE['mtaShowAfter']) && $_COOKIE['mtaShowAfter'] < $this->mta_settings['show_after_visits'] ) { 
			$total_visits = intval($_COOKIE['mtaShowAfter']) + 1;
			setcookie("mtaShowAfter", $total_visits, $visits_cookie_life, $url['path'] . '/');
		} else {
			$start_show = 1;
		}	
				
		if ( $this->mta_settings['show'] == 1 ) {
			setcookie("mtaDays", '', $days_cookie_expire, $url['path'] . '/');
			setcookie("mtaVisits", '', $days_cookie_expire, $url['path'] . '/');
		}
		else if ( $this->mta_settings['show'] == 2 && !isset($_SESSION['mtaShown']) && isset($_SESSION['mtaFirstVisit']) ) { 
			setcookie("mtaDays", '', $days_cookie_expire, $url['path'] . '/');
			setcookie("mtaVisits", '', $days_cookie_expire, $url['path'] . '/');
			$_SESSION['mtaShown'] = 1;
		}
		else if ( $this->mta_settings['show'] == 3 && !isset($_COOKIE['mtaDays']) && $start_show == 1 && $halt_show != 1 ) {
			unset($_SESSION['mtaShown']);
			setcookie("mtaDays", $noof_days, $days_cookie_life, $url['path'] . '/');
		}
		else if ( $this->mta_settings['show'] == 4 && !isset($_COOKIE['mtaVisits']) && $start_show == 1 && $halt_show != 1 ) {
			unset($_SESSION['mtaShown']);
			setcookie("mtaVisits", 1, $visits_cookie_life, $url['path'] . '/');
		}
		else if ( $this->mta_settings['show'] == 4 && isset($_COOKIE['mtaVisits']) && $_COOKIE['mtaVisits'] < $noof_visits && $start_show == 1 ) { 
			$upto_visits = intval($_COOKIE['mtaVisits']) + 1;
			setcookie("mtaVisits", $upto_visits, $visits_cookie_life, $url['path'] . '/');
		}
		if( !isset($_SESSION['mtaFirstVisit']) && $start_show == 1 && $halt_show != 1 ) {
			$_SESSION['mtaFirstVisit'] = 1;
		}
	}
	
	/**
	 * Strip Ad Style and JS
	 * @access public
	 */
	function __mtaStripAdStyle() {
		if ( $this->mta_settings['float'] == 1 && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') === false ) {
			$position = 'position: fixed;';
		} else if ( $this->mta_settings['float'] == 1 ) {
			$position = 'position: absolute;';
		}
		$left_width  = '80%';
		$right_width = '19%';
		if ( $this->mta_settings['remove_pwd'] == 1 ) {
			$left_width  = '96%';
			$right_width = '3%';
		}
		?>
		<style type="text/css">
		#mta_bar { 
			background: <?php echo $this->mta_settings['bg_color']?>; 
			border-bottom: 1px solid <?php echo $this->mta_settings['border_color']?>; 
			padding: 4px 0; 
			z-index: 100;
			top: 0;
			left: 0;
			width: 100%;
			overflow: auto;
			<?php echo $position?>
		}
		* html #mta_bar { /*IE6 hack*/
			position: absolute;
			width: expression(document.compatMode=="CSS1Compat"? document.documentElement.clientWidth+"px" : body.clientWidth+"px");
		}
		.mta_left {
			float: left;
			text-align: <?php echo $this->mta_settings['text_align']?>;
			font-family: <?php echo $this->mta_settings['font_family']?>;
			font-size: <?php echo $this->mta_settings['font_size']?>px;
			font-weight: <?php echo $this->mta_settings['text_style_b']?>;
			font-style: <?php echo $this->mta_settings['text_style_i']?>;
			color: <?php echo $this->mta_settings['font_color']?>; 
			width: <?php echo $left_width?>;
		}
		.mta_right {
			font-family: Arial, Helvetica, sans-serif;
			float: right;
			text-align: right;
			font-weight: normal;
			font-size: 10px;
			letter-spacing: 0;
			width: <?php echo $right_width?>;
			white-space: nowrap;
		}
		.mta_right a {
			font-size: 10px;
			color: <?php echo $this->mta_settings['font_color']?>; 
			text-decoration: underline;
		}
		.mta_right a:hover {
			font-size: 10px;
			color: <?php echo $this->mta_settings['font_color']?>; 
			text-decoration: none;
		}
		#mta_left_bar a { 
			text-decoration: <?php echo $this->mta_settings['text_style_u']?>; 
			color: <?php echo $this->mta_settings['font_color']?>; 
		}
		#mta_left_bar a:hover { 
			text-decoration: none; 
			color: <?php echo $this->mta_settings['font_color']?>; 
		}
		</style>
		<script type="text/javascript">
		var mta_arr   = new Array();
		var mta_clear = new Array();
		function __mtaFloat(mta) {
			mta_arr[mta_arr.length] = this;
			var mtapointer = eval(mta_arr.length-1);
			this.pagetop       = 0;
			this.cmode         = (document.compatMode && document.compatMode!="BackCompat") ? document.documentElement : document.body;
			this.mtasrc        = document.all? document.all[mta] : document.getElementById(mta);
			this.mtasrc.height = this.mtasrc.offsetHeight;
			this.mtaheight     = this.cmode.clientHeight;
			this.mtaoffset     = __mtaGetOffsetY(mta_arr[mtapointer]);
			var mtabar         = 'mta_clear['+mtapointer+'] = setInterval("__mtaFloatInit(mta_arr['+mtapointer+'])",1);';
			mtabar             = mtabar;
			eval(mtabar);
		}
		function __mtaGetOffsetY(mta) {
			var mtaTotOffset = parseInt(mta.mtasrc.offsetTop);
			var parentOffset = mta.mtasrc.offsetParent;
			while ( parentOffset != null ) {
				mtaTotOffset += parentOffset.offsetTop;
				parentOffset  = parentOffset.offsetParent;
			}
			return mtaTotOffset;
		}
		function __mtaFloatInit(mta) {
			mta.pagetop = mta.cmode.scrollTop;
			mta.mtasrc.style.top = mta.pagetop - mta.mtaoffset + "px";
		}
		function __closeTopAds() {
			document.getElementById("mta_bar").style.visibility = "hidden";
		}
		</script>
		<?php
	}
	
	/**
	 * Displays Strip Ad
	 * @access public
	 */
	function __mtaShowStripAd() {
		$stripe_ads = array();
		foreach ( $this->mta_option as $key => $ad_arr ) {
			if ( $ad_arr['link_status'] == 1 ) {
				$stripe_ads[] = $ad_arr;
			}
		}
		if ( count($stripe_ads) > 0 ) {
			$count = 0;
			foreach ( $stripe_ads as $key=>$val ) {
				for ( $i=1; $i<=$val['weight']; $i++ ) {
					$mta_stripe_ad[$count]['title']        = trim($val['title']);
					$mta_stripe_ad[$count]['link']         = trim($val['link']);
					$mta_stripe_ad[$count]['disable_link'] = trim($val['disable_link']);
					$mta_stripe_ad[$count]['in_new_win']   = trim($val['in_new_win']);
					$count++;
				}
			}
			$random_key    = array_rand($mta_stripe_ad);
			$display_title = $mta_stripe_ad[$random_key]['title'];
			$display_link  = $mta_stripe_ad[$random_key]['link'];
			$disable_link  = $mta_stripe_ad[$random_key]['disable_link'];
			$in_new_win    = $mta_stripe_ad[$random_key]['in_new_win'];
		}
		$mta_cbid = $this->mta_settings['cb_id'];

		if ( count($mta_stripe_ad) > 0 && $this->mta_settings['disable'] != 1 ) {
			if ( 
			($this->mta_settings['show_after'] != 1 || $_COOKIE['mtaShowAfter'] >= $this->mta_settings['show_after_visits']) && 
			!isset($_COOKIE['mtaDays']) && 
			!isset($_SESSION['mtaShown']) && 
			($_COOKIE['mtaVisits'] < $this->mta_settings['show_visits']) && !isset($_SESSION['RemainClosed'])
			) {
				$pwd_style = 'font-size:10px;color:'.$this->mta_settings['font_color'];
				if ( $plain_txt == 1 ) {
					$ad = $display_title;
				} else {
					if ( $in_new_win == 1 ) $_target = '_blank';
					else $_target = '_self';
					$ad = '<a href="'.$display_link.'" target="'.$_target.'" rel="nofollow">'.$display_title.'</a>';
				}
				if ( $this->mta_settings['remove_pwd'] != 1 ) {
					if ( trim($this->mta_settings['cb_id']) != '' ) {
						$mta_pwd = '<a href="http://www.technoeast.com/" target="_blank" title="TechnoZeast.com" style="'.$pwdby_style.'">Powered by TechnoZeast</a>';
					} else {
						$mta_pwd = '<a href="http://www.technozeast.com/strip-ad" target="_blank" title="TechnoZeast.com" style="'.$pwdby_style.'">Powered by TechnoZeast</a>';
					}
				} else {
					$mta_pwd = '';
				}
				$mta_stripe_ad = '<div id="mta_bar">
				<div id="mta_left_bar"><span class="mta_left">'.$ad.'</span></div>
				<span class="mta_right" onmouseover="self.status=\'TechnoZeast.com\';return true;" onmouseout="self.status=\'\'">'.$mta_pwd;
				if ( $this->mta_settings['hide_close'] != 1 ) {
					$mta_stripe_ad .= ' <img src="'.MTA_FULLPATH.'images/close.gif" onClick="__closeTopAds();return false;" style="cursor:hand;cursor:pointer;" align="absmiddle" border="0">';
				}
				$mta_stripe_ad .= '&nbsp;</span></div>';
				$stripe_ad = $mta_stripe_ad;
				if ( strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') !== false ) $stripe_ad .= '<br><br>';
				
				if ( $this->mta_settings['float'] == 1 ) {
				  $stripe_ad .= '<br />';
				  if ( strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') !== false ) {
				     $stripe_ad .= '<br />';
					 ?>
					 <script type="text/javascript">new __mtaFloat("mta_bar");</script>
					 <?php
				  }
				} 
			}
		}
		return $stripe_ad;
	}
	
	/**
	 * Carries out all the operations
	 * @access public 
	 */
	function __mtaOptionsPg() {
		$mta_msg = '';
		$this->mta_request = $_REQUEST['mta'];
		if ( isset($this->mta_request['add']) ) {
			$next_key = count($this->mta_option);
			$mta_option_new[$next_key]['title']        = stripslashes(htmlspecialchars($this->mta_request['title']));
			$mta_option_new[$next_key]['plain_txt']    = $this->mta_request['plain_txt'];
			if ( strpos($this->mta_request['link'],'http://') === false && $this->mta_request['plain_txt'] != 1 ) { 
				$mta_option_new[$next_key]['link']     = 'http://'.trim($this->mta_request['link']);
			} else if ( $this->mta_request['plain_txt'] == 1 ) { 
				$mta_option_new[$next_key]['link']     = '';
			} else {
				$mta_option_new[$next_key]['link']     = trim($this->mta_request['link']);
			}
			$mta_option_new[$next_key]['in_new_win']   = $this->mta_request['in_new_win'];
			$mta_option_new[$next_key]['weight']       = $this->mta_request['weight'];
			$mta_option_new[$next_key]['link_status']  = 1;
			$this->mta_option = array_merge($this->mta_option, $mta_option_new);
			$mta_msg = 'Added Successfully';
		} else if ( isset($this->mta_request['save']) || isset($this->mta_request['savesettings']) || isset($this->mta_request['saveall']) ) {
			if ( count($this->mta_request) ) {
				foreach ( $this->mta_request as $key=>$val ) {
					if ( is_numeric($key) && is_array($val) ) {
						$mta_option_new[$key]['title']        = stripslashes(htmlspecialchars($val['title']));
						if ( trim($val['link']) != '' && strpos($val['link'],'http://') === false ) { 
							$mta_option_new[$key]['link']     = 'http://'.trim($val['link']);
						} else {
							$mta_option_new[$key]['link']     = trim($val['link']);
						}
						$mta_option_new[$key]['plain_txt']    = $val['plain_txt'];
						$mta_option_new[$key]['link_status']  = $val['link_status'];
						$mta_option_new[$key]['in_new_win']   = $val['in_new_win'];
						$mta_option_new[$key]['weight']       = $val['weight'];
					}
				}
				$this->mta_option = $mta_option_new;
			}
			$this->mta_settings['font_family']   = $this->mta_request['font_family'];
			$this->mta_settings['font_size']     = $this->mta_request['font_size'];
			$this->mta_settings['font_color']    = $this->mta_request['font_color'];
			$this->mta_settings['bg_color']      = $this->mta_request['bg_color'];
			$this->mta_settings['border_color']  = $this->mta_request['border_color'];
			$this->mta_settings['text_align']    = $this->mta_request['text_align'];
			$this->mta_settings['text_style_b']  = $this->mta_request['text_style_b'];
			$this->mta_settings['text_style_i']  = $this->mta_request['text_style_i'];
			$this->mta_settings['text_style_u']  = $this->mta_request['text_style_u'];
			$this->mta_settings['show_after']    = $this->mta_request['show_after'];
			$this->mta_settings['show_after_visits'] = $this->mta_request['show_after_visits'];
			$this->mta_settings['show']          = $this->mta_request['show'];
			$this->mta_settings['show_days']     = $this->mta_request['show_days'];
			$this->mta_settings['show_visits']   = $this->mta_request['show_visits'];
			$this->mta_settings['float']         = $this->mta_request['float'];
			$this->mta_settings['disable']       = $this->mta_request['disable'];
			$this->mta_settings['hide_close']    = $this->mta_request['hide_close'];
			$this->mta_settings['cb_id']         = $this->mta_request['cb_id'];
			$this->mta_settings['remove_pwd']    = $this->mta_request['remove_pwd'];
			$mta_msg = 'Saved Successfully';
		} else if ( isset($this->mta_request['delete_checked']) ) {
			if ( count($this->mta_request['delete']) ) {
				foreach ( $this->mta_request['delete'] as $key ) {
					unset($this->mta_option[$key]);
				}
			}
			$mta_msg = 'Deleted Successfully';
		}
		return $mta_msg;
	}
	
	/**
	 * Displays the various options available
	 * @access public 
	 */
	function __mtaShowOptionsPg() {
		if ( $this->mta_settings['show'] == 4 )       $mta_show_4_chk = 'checked';
		else if ( $this->mta_settings['show'] == 3 )  $mta_show_3_chk = 'checked';
		else if ( $this->mta_settings['show'] == 2 )  $mta_show_2_chk = 'checked';
		else $mta_show_1_chk = 'checked';
		if ( $this->mta_settings['show_after'] == 1 ) $mta_show_after_chk = 'checked';
		if ( $this->mta_settings['float'] == 1 )      $mta_float_chk      = 'checked';
		if ( $this->mta_settings['disable'] == 1 )    $mta_disable_chk    = 'checked';
		if ( $this->mta_settings['hide_close'] == 1 ) $mta_close_chk      = 'checked';
		if ( $this->mta_settings['remove_pwd'] == 1 ) $mta_remove_pwd_chk = 'checked';
		if ( $this->mta_settings['text_style_b'] == 'bold' )      $mta_style_b_chk = 'checked';
		if ( $this->mta_settings['text_style_i'] == 'italic' )    $mta_style_i_chk = 'checked';
		if ( $this->mta_settings['text_style_u'] == 'underline' ) $mta_style_u_chk = 'checked';
		
		$plain_txt_tooltip = "Check it if you just want to display a notice instead of a link";
		$edit_here_tooltip = "Below, you can directly edit the title, link and other properties for the strip ad. After making the changes click on the &quot;Save&quot; button to update your settings.";
		?>
		<script><!--//
		function __validateMTAForm1() {
			var mta_title = document.getElementById('mta_title');
			var mta_link  = document.getElementById('mta_link');
			var mta_plain_txt = document.getElementById('mta_plain_txt');
			if ( mta_title.value == '' ) {
				alert('Title required');
				mta_title.focus();
				return false;
			}
			if ( mta_link.value == '' && mta_plain_txt.checked == false ) {
				alert('Link required');
				mta_link.focus();
				return false;
			}
			return true;
		}
		function __mtaToggleAll(parent) {
			var now = parent.checked;
			var frm = document.mtaform2;
			var len = frm.elements.length;
			for ( i=0; i<len; i++ ) {
				if ( frm.elements[i].name=='mta[delete][]' ) {
					frm.elements[i].checked = now;
				}
			}
		}
		function __mtaShowHide(curr, img) {
			var curr = document.getElementById(curr);
			var img  = document.getElementById(img);
			if ( curr.style=="" || curr.style.display=="none" ) {
				curr.style.display = "block";
				img.src = '<?php echo MTA_FULLPATH?>images/minus.gif';
			} else if( curr.style!="" || curr.style.display=="block" ) {
				curr.style.display = "none";
				img.src = '<?php echo MTA_FULLPATH?>images/plus.gif';
			}
		}
		function __mtaLivePreview(src) {
			var preview_txt  = document.getElementById('preview_txt');
			var text_style_b = document.getElementById('mta_text_style_b');
			var text_style_i = document.getElementById('mta_text_style_i');
			var text_style_u = document.getElementById('mta_text_style_u');
			
			if(src.id=='mta_font_family')
				preview_txt.style.fontFamily = src.value;
			if(src.id=='mta_font_size')
				preview_txt.style.fontSize = src.value+'px';
			if(src.id=='mta_text_align')
				preview_txt.style.textAlign = src.value;
			if(text_style_b.checked==true)
				preview_txt.style.fontWeight = 'bold';
			else
				preview_txt.style.fontWeight = 'normal';
			if(text_style_i.checked==true)
				preview_txt.style.fontStyle = 'italic';
			else
				preview_txt.style.fontStyle = 'normal';
			if(text_style_u.checked==true)
				preview_txt.style.textDecoration = 'underline';
			else
				preview_txt.style.textDecoration = 'none';
		}
		function __mtaShowPlainText() {
			var mta_plain_txt  = document.getElementById('mta_plain_txt');
			var mta_link_row   = document.getElementById('mta_link_row');
			var mta_weight_row = document.getElementById('mta_weight_row');
			var mta_addbtn_row = document.getElementById('mta_addbtn_row');
			var showRow = 'block'
			if ( navigator.appName.indexOf('Microsoft') == -1 ) {
				var showRow = 'table-row';
			}
			if ( mta_plain_txt.checked == true ) {
				mta_link_row.style.display = 'none';
				mta_weight_row.style.backgroundColor = '#ffffff';
				mta_addbtn_row.style.backgroundColor = '#f1f1f1';
			} else {
				mta_link_row.style.display = showRow;
				mta_weight_row.style.backgroundColor = '#f1f1f1';
				mta_addbtn_row.style.backgroundColor = '#ffffff';
			}
		}
		//--></script>
		<script type="text/javascript" src="<?php echo MTA_FULLPATH;?>include/tooltip.js"></script>
		<link href="<?php echo MTA_FULLPATH;?>include/tooltip.css" rel="stylesheet" type="text/css">
		<style type="text/css">
		.preview_class {
			padding: 4px 0; 
			background: <?php echo $this->mta_settings['bg_color']?>; 
			border-bottom: 1px solid <?php echo $this->mta_settings['border_color']?>; 
			text-align: <?php echo $this->mta_settings['text_align']?>;
			font-family: <?php echo $this->mta_settings['font_family']?>;
			font-size: <?php echo $this->mta_settings['font_size']?>px;
			font-weight: <?php echo $this->mta_settings['text_style_b']?>;
			font-style: <?php echo $this->mta_settings['text_style_i']?>;
			text-decoration: <?php echo $this->mta_settings['text_style_u']?>; 
			color: <?php echo $this->mta_settings['font_color']?>; 
		}
		</style>
		<h2> <?php echo MTA_NAME.' '.MTA_VERSION; ?> Settings</h2><br />
<strong>Make A Donation</strong>
If you liked this plugin, please consider making a kind donation towards our efforts to adding new features to this plugin and developing other new useful resources.If you want to donate in my account directly please contact me <a href="http://www.technozeast.com/contact" target="_blank">here</a>.

<form style="text-align: center;" action="https://www.paypal.com/cgi-bin/webscr" method="post"> <input name="cmd" type="hidden" value="_s-xclick" /> <input name="hosted_button_id" type="hidden" value="10326093" /> <input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" type="image" /> <img src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" />
</form>
		<strong>
		 <h3>Add new "Strip Ad"</h3>
		 <table cellspacing="0" cellpadding="3" width="100%" style="border:1px solid #f1f1f1">
		  <form name="mtaform1" method="post" onsubmit="return __validateMTAForm1();">
		  <tr bgcolor="#f1f1f1">
		   <td width="65"><strong>Title: </strong></td>
		   <td><input type="text" name="mta[title]" id="mta_title" value="" size="50" maxlength="200" /> &nbsp;&nbsp;
		   <input type="checkbox" name="mta[plain_txt]" id="mta_plain_txt" value="1" onclick="__mtaShowPlainText()" /> Show As Plain Text? 
		   <a href="#" onMouseover="tooltip('<?php echo $plain_txt_tooltip;?>',282)" onMouseout="hidetooltip()" style="border-bottom:none;"><img src="<?php echo MTA_FULLPATH;?>images/help.gif" border="0" align="absmiddle" /></a>
		   </td>
		  </tr>
		  <tr id="mta_link_row">
		   <td><strong>Link: </strong></td>
		   <td><input type="text" name="mta[link]" id="mta_link" value="http://" size="50" maxlength="200" /> &nbsp;&nbsp;
		   <input type="checkbox" name="mta[in_new_win]" id="mta_in_new_win" value="1" /> Open in new window
		   </td>
		  </tr>
		  <tr id="mta_weight_row" bgcolor="#f1f1f1">
		   <td><strong>Weight: </strong></td>
		   <td>
			<select name="mta[weight]" id="mta_weight">
			 <?php for( $i=1; $i<=10; $i++ ) { ?>
			 <option value="<?php echo $i;?>"><?php echo $i;?></option>
			 <?php } ?>
			</select></td>
		  </tr>
		  <tr id="mta_addbtn_row">
		   <td>&nbsp;</td>
		   <td><input type="submit" name="mta[add]" class="button" value=" Add &raquo; " /></td>
		  </tr>
		  </form>
		 </table>
		 <br /><br />
	 
		 <form name="mtaform2" method="post">
		 <h3>"Strip Ad" Being Rotated
		 <a href="#" onMouseover="tooltip('<?php echo $edit_here_tooltip;?>',350)" onMouseout="hidetooltip()" style="border-bottom:none;"><img src="<?php echo MTA_FULLPATH;?>images/help.gif" border="0" align="absmiddle" /></a>
		 </h3>
		 <table cellspacing="1" cellpadding="3" width="100%" style="border:1px solid #f1f1f1">
		  <tr bgcolor="#f1f1f1">
		   <td width="2%"><input type="checkbox" name="checkall" onclick="__mtaToggleAll(this)"/></td>
		   <td width="25%"><strong>Title</strong></td>
		   <td width="30%"><strong>Link</strong></td>
		   <td width="12%"><div align="center"><strong>Show As Plain Text</strong></div></td>
		   <td width="12%"><div align="center"><strong>Open In New Window</strong></div></td>
		   <td width="12%"><div align="center"><strong>Status</strong></div></td>
		   <td width="5%"><div align="center"><strong>Weight</strong></div></td>
		  </tr>
		  <?php
		  if ( count($this->mta_option) > 0 ) {
		   foreach ($this->mta_option as $key => $val) {
			 $page_name = $this->mta_pages[$thepage];
			 if ( $key % 2 != 0 ) $bgcol = '#f1f1f1';
			 else $bgcol = '#ffffff';
			 ?>
			 <tr valign="top" bgcolor="<?php echo $bgcol; ?>">
			  <td><input type="checkbox" name="mta[delete][]" value="<?php echo $key; ?>" /></td>
			  <td><input type="text" name="mta[<?php echo $key; ?>][title]" value="<?php echo $val['title']; ?>" size="20" maxlength="200" /></td>
			  <td><input type="text" name="mta[<?php echo $key; ?>][link]" value="<?php echo $val['link']; ?>" size="30" maxlength="200" /></td>
			  <td><div align="center">
			  <select name="mta[<?php echo $key; ?>][plain_txt]">
				 <option value=1 <?php if($val['plain_txt']==1){print'selected';}?>>Yes</option>
				 <option value=0 <?php if($val['plain_txt']==0){print'selected';}?>>No</option>
			  </select></div>
			  </td>
			  <td><div align="center">
			  <select name="mta[<?php echo $key; ?>][in_new_win]">
				 <option value=1 <?php if($val['in_new_win']==1){print'selected';}?>>Yes</option>
				 <option value=0 <?php if($val['in_new_win']==0){print'selected';}?>>No</option>
			  </select></div>
			  </td>
			  <td><div align="center">
			  <select name="mta[<?php echo $key; ?>][link_status]">
				 <option value=1 <?php if($val['link_status']==1){print'selected';}?>>Active</option>
				 <option value=0 <?php if($val['link_status']==0){print'selected';}?>>Inactive</option>
			  </select></div>
			  </td>
			  <td><div align="center">
			  <select name="mta[<?php echo $key; ?>][weight]">
				 <?php for( $i=1; $i<=10; $i++ ) { ?>
				 <option value="<?php echo $i;?>" <?php if($val['weight']==$i){print'selected';}?>><?php echo $i;?></option>
				 <?php } ?>
			  </select></div>
			  </td>
			 </tr>
			<?php
			$i++;
		   } // Eof foreach
		  }
		  ?>
		  <tr>
		   <td colspan="7">
		   <input type="submit" name="mta[save]" class="button" value="Save" />
		   <input type="submit" name="mta[delete_checked]" class="button" value="Delete Checked" onclick="return confirm('The selected rows will be deleted. Are you sure?');" />
		   </td>
		  </tr>
		 </table><br /><br />
	 
		 <h3>Settings</h3>
		 <table cellspacing="0" cellpadding="3" width="100%" style="border:1px solid #f1f1f1">
		  <tr bgcolor="#f1f1f1">
		   <td><input type="checkbox" name="mta[float]" value="1" <?php echo $mta_float_chk;?> /> Stick on the top of the page</td>
		  </tr>
		  <tr>
		   <td><input type="checkbox" name="mta[hide_close]" value="1" <?php echo $mta_close_chk;?> /> Hide close button</td>
		  </tr>
		  <tr bgcolor="#f1f1f1">
		   <td><input type="checkbox" name="mta[disable]" value="1" <?php echo $mta_disable_chk;?> /> Disable Strip Ad</td>
		  </tr>
		  <tr>
		   <td><input type="checkbox" name="mta[remove_pwd]" value="1" <?php echo $mta_remove_pwd_chk;?> /> Remove Powered by TechnoZeast Link</td>
		  </tr>		  
		  <tr bgcolor="#f1f1f1">
		   <td><input type="submit" name="mta[savesettings]" class="button" value="Save Settings" /></td>
		  </tr>
		 </table><br /><br />
		
		 <h3><a name="mtadv" href="#mtadv" onclick="__mtaShowHide('adv_option','adv_img');"><img src="<?php echo MTA_FULLPATH?>images/plus.gif" id="adv_img" border="0" /><strong>Advance Settings</strong></a></h3>
		 <div id="adv_option" style="display:none">
		 <table border="0" width="100%">
		  <tr>
		   <td width="48%">
			 <table width="90%" cellspacing="0" cellpadding="3" style="border:1px solid #f1f1f1">
			  <tr bgcolor="#f1f1f1">
			   <td colspan="2"><strong>"Strip Ad" Formatting:</strong></td>
			  </tr>
			  <tr>
			   <td width="31%">Font: </td>
			   <td>
			   <select name="mta[font_family]" id="mta_font_family" style="width:105px;" onchange="__mtaLivePreview(this)">
				<option value="Arial" <?php if($this->mta_settings['font_family']=='Arial'){print'selected';}?> style="font-family:Arial">Arial</option>
				<option value="Comic Sans MS" <?php if($this->mta_settings['font_family']=='Comic Sans MS'){print'selected';}?> style="font-family:Comic Sans MS">Comic Sans MS</option>
				<option value="Courier New" <?php if($this->mta_settings['font_family']=='Courier New'){print'selected';}?> style="font-family:Courier New">Courier New</option>
				<option value="Georgia" <?php if($this->mta_settings['font_family']=='Georgia'){print'selected';}?> style="font-family:Georgia">Georgia</option>
				<option value="Impact" <?php if($this->mta_settings['font_family']=='Impact'){print'selected';}?> style="font-family:Impact">Impact</option>
				<option value="Sans Serif" <?php if($this->mta_settings['font_family']=='Sans Serif'){print'selected';}?> style="font-family:Sans Serif">Sans Serif</option>
				<option value="Tahoma" <?php if($this->mta_settings['font_family']=='Tahoma'){print'selected';}?> style="font-family:Tahoma">Tahoma</option>
				<option value="Times New Roman" <?php if($this->mta_settings['font_family']=='Times New Roman'){print'selected';}?> style="font-family:Times New Roman">Times New Roman</option>
				<option value="Verdana" <?php if($this->mta_settings['font_family']=='Verdana'){print'selected';}?> style="font-family:Verdana">Verdana</option>
			   </select>
			   </td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td>Size: </td>
			   <td>
			   <select name="mta[font_size]" id="mta_font_size" style="width:105px;" onchange="__mtaLivePreview(this)">
				<option value="10" <?php if($this->mta_settings['font_size']=='10'){print'selected';}?> style="font-size:10px">Size 1</option>
				<option value="11" <?php if($this->mta_settings['font_size']=='11'){print'selected';}?> style="font-size:11px">Size 2</option>
				<option value="12" <?php if($this->mta_settings['font_size']=='12'){print'selected';}?> style="font-size:12px">Size 3</option>
				<option value="13" <?php if($this->mta_settings['font_size']=='13'){print'selected';}?> style="font-size:13px">Size 4</option>
				<option value="14" <?php if($this->mta_settings['font_size']=='14'){print'selected';}?> style="font-size:14px">Size 5</option>
				<option value="15" <?php if($this->mta_settings['font_size']=='15'){print'selected';}?> style="font-size:15px">Size 6</option>
				<option value="16" <?php if($this->mta_settings['font_size']=='16'){print'selected';}?> style="font-size:16px">Size 7</option>
			   </select>
			   </td>
			  </tr>
			  <tr>
			   <td>Text Color: </td>
			   <td>
			   <input type="text" name="mta[font_color]" id="mta_font_color" value="<?php echo $this->mta_settings['font_color'];?>" style="width:70px;" readonly />
			   <input type="button" name="mta_font_color_btn" id="mta_font_color_btn" title="Select Font Color" style="line-height:8px;width:20px;cursor:pointer;cursor:hand;background-color:<?php echo $this->mta_settings['font_color'];?>" onclick='window.open("<?php echo MTA_FULLPATH;?>include/pickcolor.html?pid=font_color","colorpicker","left=300,top=200,width=240,height=170,resizable=0");' />
			   </td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td>Background Color: </td>
			   <td>
			   <input type="text" name="mta[bg_color]" id="mta_bg_color" value="<?php echo $this->mta_settings['bg_color'];?>" style="width:70px;" readonly />
			   <input type="button" name="mta_bg_color_btn" id="mta_bg_color_btn" title="Select Background Color" style="line-height:8px;width:20px;cursor:pointer;cursor:hand;background-color:<?php echo $this->mta_settings['bg_color'];?>" onclick='window.open("<?php echo MTA_FULLPATH;?>include/pickcolor.html?pid=bg_color","colorpicker","left=300,top=200,width=240,height=170,resizable=0");' />
			   </td>
			  </tr>
			  <tr>
			   <td>Border Color: </td>
			   <td>
			   <input type="text" name="mta[border_color]" id="mta_border_color" value="<?php echo $this->mta_settings['border_color'];?>" style="width:70px;" readonly />
			   <input type="button" name="mta_border_color_btn" id="mta_border_color_btn" title="Select Border Color" style="line-height:8px;width:20px;cursor:pointer;cursor:hand;background-color:<?php echo $this->mta_settings['border_color'];?>" onclick='window.open("<?php echo MTA_FULLPATH;?>include/pickcolor.html?pid=border_color","colorpicker","left=300,top=200,width=240,height=170,resizable=0");' />
			   </td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td>Text Align: </td>
			   <td>
			   <select name="mta[text_align]" id="mta_text_align" style="width:105px;" onchange="__mtaLivePreview(this)">
				<option value="center" <?php if($this->mta_settings['text_align']=='center'){print'selected';}?>>Center</option>
				<option value="left" <?php if($this->mta_settings['text_align']=='left'){print'selected';}?>>Left</option>
				<option value="right" <?php if($this->mta_settings['text_align']=='right'){print'selected';}?>>Right</option>
			   </select>
			   </td>
			  </tr>
			  <tr>
			   <td>Style: </td>
			   <td>
			   <input type="checkbox" name="mta[text_style_b]" id="mta_text_style_b" value="bold" <?php echo $mta_style_b_chk;?> onclick="__mtaLivePreview(this)">Bold &nbsp; 
			   <input type="checkbox" name="mta[text_style_i]" id="mta_text_style_i" value="italic" <?php echo $mta_style_i_chk;?> onclick="__mtaLivePreview(this)">Italic &nbsp; 
			   <input type="checkbox" name="mta[text_style_u]" id="mta_text_style_u" value="underline" <?php echo $mta_style_u_chk;?> onclick="__mtaLivePreview(this)">Underline &nbsp; 
			   </td>
			  </tr>
			 </table>
		   </td>
		   <td width="50%" valign="top">
			 <table width="100%" cellspacing="0" cellpadding="3" style="border:1px solid #f1f1f1">
			  <tr bgcolor="#f1f1f1">
			   <td><strong>"Strip Ad" Preview</strong></td>
			  </tr>
			  <tr>
			   <td height="100">
				<table width="98%" align="center">
				 <tr>
				   <td class="preview_class" id="preview_txt">Strip Ad Live Preview</td>
				 </tr>
				</table>
			   </td>
			  </tr>
			 </table>
		   </td>
		  </tr>
		  <tr><td>&nbsp;</td></tr>
		  <tr>
		   <td>
			 <table width="90%" cellspacing="0" cellpadding="3" style="border:1px solid #f1f1f1">
			  <tr bgcolor="#f1f1f1">
			   <td><strong>Show "Strip Ad":</strong></td>
			  </tr>
			  <tr>
			   <td><input type="checkbox" name="mta[show_after]" value="1" <?php echo $mta_show_after_chk;?> /> After <input type="text" name="mta[show_after_visits]" id="mta_show_after_visits" value="<?php echo $this->mta_settings['show_after_visits']?>" size="3" maxlength="5" /> visits</td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td><input type="radio" name="mta[show]" value="1" <?php echo $mta_show_1_chk;?> /> All the time</td>
			  </tr>
			  <tr>
			   <td><input type="radio" name="mta[show]" value="2" <?php echo $mta_show_2_chk;?> /> Once until browser is closed</td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td><input type="radio" name="mta[show]" value="3" <?php echo $mta_show_3_chk;?> /> Every <input type="text" name="mta[show_days]" id="mta_show_days" value="<?php echo $this->mta_settings['show_days']?>" size="3" maxlength="5" /> days</td>
			  </tr>
			  <tr>
			   <td><input type="radio" name="mta[show]" value="4" <?php echo $mta_show_4_chk;?> /> For first <input type="text" name="mta[show_visits]" id="mta_show_visits" value="<?php echo $this->mta_settings['show_visits']?>" size="3" maxlength="5" /> visits</td>
			  </tr>
			 </table>
		   </td>
		   <td>&nbsp;</td>
		  </tr>
		  
		  <tr><td>&nbsp;</td></tr>
		  <tr>
		   <td>
		   </td>
		   <td>&nbsp;</td>
		  </tr>
		  
		 </table>
		 <p><input type="submit" name="mta[saveall]" class="button" value="Save All" /></p>
		 </div>
		 </form>
		<p style="text-align:center;margin-top:3em;"><strong><?php echo MTA_NAME.' '.MTA_VERSION; ?> by <a href="http://www.technozeast.com/" target="_blank" >TechnoZeast</a></strong> Visit <a href="http://www.technozeast.com/strip-ad" target="_blank">Plugins Page</a></p>
		<?php
	}
	
} // Eof Class

$StripAd = new StripAd();
?>