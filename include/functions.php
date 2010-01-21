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
	var $p_option = array();
	
    /**
     * Strip Ad options. Holds various settings for strip ad.
     * @var string
     */
	var $p_settings = array();

    /**
     * Holds Post/Get data
     * @var array
     */
	var $p_request = array();
	
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
	function __pSetCookie($siteurl) {
		session_start();
		$url                = parse_url($siteurl);
		$noof_visits        = $this->p_settings['show_visits'];
		$noof_days          = $this->p_settings['show_days'];
		$days_cookie_expire = time() - (3600 * 24) * 365 * 1;  //1 year
		$days_cookie_life   = time() + (3600 * 24) * $noof_days;
		$visits_cookie_life = time() + (3600 * 24) * 365 * 1;
		
		if ( $this->p_settings['show_after'] == 1 && !isset($_COOKIE['pShowAfter']) ) {
			setcookie("pShowAfter", 1, $visits_cookie_life, $url['path'] . '/');
			$halt_show = 1;
		} else if ( $this->p_settings['show_after'] != 1 ) {
			setcookie("pShowAfter", '', $visits_cookie_life, $url['path'] . '/');
		}			
		if ( $this->p_settings['show_after'] == 1 && isset($_COOKIE['pShowAfter']) && $_COOKIE['pShowAfter'] < $this->p_settings['show_after_visits'] ) { 
			$total_visits = intval($_COOKIE['pShowAfter']) + 1;
			setcookie("pShowAfter", $total_visits, $visits_cookie_life, $url['path'] . '/');
		} else {
			$start_show = 1;
		}	
				
		if ( $this->p_settings['show'] == 1 ) {
			setcookie("pDays", '', $days_cookie_expire, $url['path'] . '/');
			setcookie("pVisits", '', $days_cookie_expire, $url['path'] . '/');
		}
		else if ( $this->p_settings['show'] == 2 && !isset($_SESSION['pShown']) && isset($_SESSION['pFirstVisit']) ) { 
			setcookie("pDays", '', $days_cookie_expire, $url['path'] . '/');
			setcookie("pVisits", '', $days_cookie_expire, $url['path'] . '/');
			$_SESSION['pShown'] = 1;
		}
		else if ( $this->p_settings['show'] == 3 && !isset($_COOKIE['pDays']) && $start_show == 1 && $halt_show != 1 ) {
			unset($_SESSION['pShown']);
			setcookie("pDays", $noof_days, $days_cookie_life, $url['path'] . '/');
		}
		else if ( $this->p_settings['show'] == 4 && !isset($_COOKIE['pVisits']) && $start_show == 1 && $halt_show != 1 ) {
			unset($_SESSION['pShown']);
			setcookie("pVisits", 1, $visits_cookie_life, $url['path'] . '/');
		}
		else if ( $this->p_settings['show'] == 4 && isset($_COOKIE['pVisits']) && $_COOKIE['pVisits'] < $noof_visits && $start_show == 1 ) { 
			$upto_visits = intval($_COOKIE['pVisits']) + 1;
			setcookie("pVisits", $upto_visits, $visits_cookie_life, $url['path'] . '/');
		}
		if( !isset($_SESSION['pFirstVisit']) && $start_show == 1 && $halt_show != 1 ) {
			$_SESSION['pFirstVisit'] = 1;
		}
	}
	
	/**
	 * Strip Ad Style and JS
	 * @access public
	 */
	function __pStripAdStyle() {
		if ( $this->p_settings['float'] == 1 && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') === false ) {
			$position = 'position: fixed;';
		} else if ( $this->p_settings['float'] == 1 ) {
			$position = 'position: absolute;';
		}
		$left_width  = '80%';
		$right_width = '19%';
		if ( $this->p_settings['remove_pwd'] == 1 ) {
			$left_width  = '96%';
			$right_width = '3%';
		}
		?>
		<style type="text/css">
		#p_bar { 
			background: <?php echo $this->p_settings['bg_color']?>; 
			border-bottom: 1px solid <?php echo $this->p_settings['border_color']?>; 
			padding: 4px 0; 
			z-index: 100;
			top: 0;
			left: 0;
			width: 100%;
			overflow: auto;
			<?php echo $position?>
		}
		* html #p_bar { /*IE6 hack*/
			position: absolute;
			width: expression(document.compatMode=="CSS1Compat"? document.documentElement.clientWidth+"px" : body.clientWidth+"px");
		}
		.p_left {
			float: left;
			text-align: <?php echo $this->p_settings['text_align']?>;
			font-family: <?php echo $this->p_settings['font_family']?>;
			font-size: <?php echo $this->p_settings['font_size']?>px;
			font-weight: <?php echo $this->p_settings['text_style_b']?>;
			font-style: <?php echo $this->p_settings['text_style_i']?>;
			color: <?php echo $this->p_settings['font_color']?>; 
			width: <?php echo $left_width?>;
		}
		.p_right {
			font-family: Arial, Helvetica, sans-serif;
			float: right;
			text-align: right;
			font-weight: normal;
			font-size: 10px;
			letter-spacing: 0;
			width: <?php echo $right_width?>;
			white-space: nowrap;
		}
		.p_right a {
			font-size: 10px;
			color: <?php echo $this->p_settings['font_color']?>; 
			text-decoration: underline;
		}
		.p_right a:hover {
			font-size: 10px;
			color: <?php echo $this->p_settings['font_color']?>; 
			text-decoration: none;
		}
		#p_left_bar a { 
			text-decoration: <?php echo $this->p_settings['text_style_u']?>; 
			color: <?php echo $this->p_settings['font_color']?>; 
		}
		#p_left_bar a:hover { 
			text-decoration: none; 
			color: <?php echo $this->p_settings['font_color']?>; 
		}
		</style>
		<script type="text/javascript">
		var p_arr   = new Array();
		var p_clear = new Array();
		function __pFloat(p) {
			p_arr[p_arr.length] = this;
			var ppointer = eval(p_arr.length-1);
			this.pagetop       = 0;
			this.cmode         = (document.compatMode && document.compatMode!="BackCompat") ? document.documentElement : document.body;
			this.psrc        = document.all? document.all[p] : document.getElementById(p);
			this.psrc.height = this.psrc.offsetHeight;
			this.pheight     = this.cmode.clientHeight;
			this.poffset     = __pGetOffsetY(p_arr[ppointer]);
			var pbar         = 'p_clear['+ppointer+'] = setInterval("__pFloatInit(p_arr['+ppointer+'])",1);';
			pbar             = pbar;
			eval(pbar);
		}
		function __pGetOffsetY(p) {
			var pTotOffset = parseInt(p.psrc.offsetTop);
			var parentOffset = p.psrc.offsetParent;
			while ( parentOffset != null ) {
				pTotOffset += parentOffset.offsetTop;
				parentOffset  = parentOffset.offsetParent;
			}
			return pTotOffset;
		}
		function __pFloatInit(p) {
			p.pagetop = p.cmode.scrollTop;
			p.psrc.style.top = p.pagetop - p.poffset + "px";
		}
		function __closeTopAds() {
			document.getElementById("p_bar").style.visibility = "hidden";
		}
		</script>
		<?php
	}
	
	/**
	 * Displays Strip Ad
	 * @access public
	 */
	function __pShowStripAd() {
		$stripe_ads = array();
		foreach ( $this->p_option as $key => $ad_arr ) {
			if ( $ad_arr['link_status'] == 1 ) {
				$stripe_ads[] = $ad_arr;
			}
		}
		if ( count($stripe_ads) > 0 ) {
			$count = 0;
			foreach ( $stripe_ads as $key=>$val ) {
				for ( $i=1; $i<=$val['weight']; $i++ ) {
					$p_stripe_ad[$count]['title']        = trim($val['title']);
					$p_stripe_ad[$count]['link']         = trim($val['link']);
					$p_stripe_ad[$count]['disable_link'] = trim($val['disable_link']);
					$p_stripe_ad[$count]['in_new_win']   = trim($val['in_new_win']);
					$count++;
				}
			}
			$random_key    = array_rand($p_stripe_ad);
			$display_title = $p_stripe_ad[$random_key]['title'];
			$display_link  = $p_stripe_ad[$random_key]['link'];
			$disable_link  = $p_stripe_ad[$random_key]['disable_link'];
			$in_new_win    = $p_stripe_ad[$random_key]['in_new_win'];
		}
		$p_cbid = $this->p_settings['cb_id'];

		if ( count($p_stripe_ad) > 0 && $this->p_settings['disable'] != 1 ) {
			if ( 
			($this->p_settings['show_after'] != 1 || $_COOKIE['pShowAfter'] >= $this->p_settings['show_after_visits']) && 
			!isset($_COOKIE['pDays']) && 
			!isset($_SESSION['pShown']) && 
			($_COOKIE['pVisits'] < $this->p_settings['show_visits']) && !isset($_SESSION['RemainClosed'])
			) {
				$pwd_style = 'font-size:10px;color:'.$this->p_settings['font_color'];
				if ( $plain_txt == 1 ) {
					$ad = $display_title;
				} else {
					if ( $in_new_win == 1 ) $_target = '_blank';
					else $_target = '_self';
					$ad = '<a href="'.$display_link.'" target="'.$_target.'" rel="nofollow">'.$display_title.'</a>';
				}
				if ( $this->p_settings['remove_pwd'] != 1 ) {
					if ( trim($this->p_settings['cb_id']) != '' ) {
						$p_pwd = '<a href="http://www.technoeast.com/" target="_blank" title="TechnoZeast.com" style="'.$pwdby_style.'">Powered by TechnoZeast</a>';
					} else {
						$p_pwd = '<a href="http://www.technozeast.com/strip-ad" target="_blank" title="TechnoZeast.com" style="'.$pwdby_style.'">Powered by TechnoZeast</a>';
					}
				} else {
					$p_pwd = '';
				}
				$p_stripe_ad = '<div id="p_bar">
				<div id="p_left_bar"><span class="p_left">'.$ad.'</span></div>
				<span class="p_right" onmouseover="self.status=\'TechnoZeast.com\';return true;" onmouseout="self.status=\'\'">'.$p_pwd;
				if ( $this->p_settings['hide_close'] != 1 ) {
					$p_stripe_ad .= ' <img src="'.P_FULLPATH.'images/close.gif" onClick="__closeTopAds();return false;" style="cursor:hand;cursor:pointer;" align="absmiddle" border="0">';
				}
				$p_stripe_ad .= '&nbsp;</span></div>';
				$stripe_ad = $p_stripe_ad;
				if ( strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') !== false ) $stripe_ad .= '<br><br>';
				
				if ( $this->p_settings['float'] == 1 ) {
				  $stripe_ad .= '<br />';
				  if ( strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') !== false ) {
				     $stripe_ad .= '<br />';
					 ?>
					 <script type="text/javascript">new __pFloat("p_bar");</script>
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
	function __pOptionsPg() {
		$p_msg = '';
		$this->p_request = $_REQUEST['p'];
		if ( isset($this->p_request['add']) ) {
			$next_key = count($this->p_option);
			$p_option_new[$next_key]['title']        = stripslashes(htmlspecialchars($this->p_request['title']));
			$p_option_new[$next_key]['plain_txt']    = $this->p_request['plain_txt'];
			if ( strpos($this->p_request['link'],'http://') === false && $this->p_request['plain_txt'] != 1 ) { 
				$p_option_new[$next_key]['link']     = 'http://'.trim($this->p_request['link']);
			} else if ( $this->p_request['plain_txt'] == 1 ) { 
				$p_option_new[$next_key]['link']     = '';
			} else {
				$p_option_new[$next_key]['link']     = trim($this->p_request['link']);
			}
			$p_option_new[$next_key]['in_new_win']   = $this->p_request['in_new_win'];
			$p_option_new[$next_key]['weight']       = $this->p_request['weight'];
			$p_option_new[$next_key]['link_status']  = 1;
			$this->p_option = array_merge($this->p_option, $p_option_new);
			$p_msg = 'Added Successfully';
		} else if ( isset($this->p_request['save']) || isset($this->p_request['savesettings']) || isset($this->p_request['saveall']) ) {
			if ( count($this->p_request) ) {
				foreach ( $this->p_request as $key=>$val ) {
					if ( is_numeric($key) && is_array($val) ) {
						$p_option_new[$key]['title']        = stripslashes(htmlspecialchars($val['title']));
						if ( trim($val['link']) != '' && strpos($val['link'],'http://') === false ) { 
							$p_option_new[$key]['link']     = 'http://'.trim($val['link']);
						} else {
							$p_option_new[$key]['link']     = trim($val['link']);
						}
						$p_option_new[$key]['plain_txt']    = $val['plain_txt'];
						$p_option_new[$key]['link_status']  = $val['link_status'];
						$p_option_new[$key]['in_new_win']   = $val['in_new_win'];
						$p_option_new[$key]['weight']       = $val['weight'];
					}
				}
				$this->p_option = $p_option_new;
			}
			$this->p_settings['font_family']   = $this->p_request['font_family'];
			$this->p_settings['font_size']     = $this->p_request['font_size'];
			$this->p_settings['font_color']    = $this->p_request['font_color'];
			$this->p_settings['bg_color']      = $this->p_request['bg_color'];
			$this->p_settings['border_color']  = $this->p_request['border_color'];
			$this->p_settings['text_align']    = $this->p_request['text_align'];
			$this->p_settings['text_style_b']  = $this->p_request['text_style_b'];
			$this->p_settings['text_style_i']  = $this->p_request['text_style_i'];
			$this->p_settings['text_style_u']  = $this->p_request['text_style_u'];
			$this->p_settings['show_after']    = $this->p_request['show_after'];
			$this->p_settings['show_after_visits'] = $this->p_request['show_after_visits'];
			$this->p_settings['show']          = $this->p_request['show'];
			$this->p_settings['show_days']     = $this->p_request['show_days'];
			$this->p_settings['show_visits']   = $this->p_request['show_visits'];
			$this->p_settings['float']         = $this->p_request['float'];
			$this->p_settings['disable']       = $this->p_request['disable'];
			$this->p_settings['hide_close']    = $this->p_request['hide_close'];
			$this->p_settings['cb_id']         = $this->p_request['cb_id'];
			$this->p_settings['remove_pwd']    = $this->p_request['remove_pwd'];
			$p_msg = 'Saved Successfully';
		} else if ( isset($this->p_request['delete_checked']) ) {
			if ( count($this->p_request['delete']) ) {
				foreach ( $this->p_request['delete'] as $key ) {
					unset($this->p_option[$key]);
				}
			}
			$p_msg = 'Deleted Successfully';
		}
		return $p_msg;
	}
	
	/**
	 * Displays the various options available
	 * @access public 
	 */
	function __pShowOptionsPg() {
		if ( $this->p_settings['show'] == 4 )       $p_show_4_chk = 'checked';
		else if ( $this->p_settings['show'] == 3 )  $p_show_3_chk = 'checked';
		else if ( $this->p_settings['show'] == 2 )  $p_show_2_chk = 'checked';
		else $p_show_1_chk = 'checked';
		if ( $this->p_settings['show_after'] == 1 ) $p_show_after_chk = 'checked';
		if ( $this->p_settings['float'] == 1 )      $p_float_chk      = 'checked';
		if ( $this->p_settings['disable'] == 1 )    $p_disable_chk    = 'checked';
		if ( $this->p_settings['hide_close'] == 1 ) $p_close_chk      = 'checked';
		if ( $this->p_settings['remove_pwd'] == 1 ) $p_remove_pwd_chk = 'checked';
		if ( $this->p_settings['text_style_b'] == 'bold' )      $p_style_b_chk = 'checked';
		if ( $this->p_settings['text_style_i'] == 'italic' )    $p_style_i_chk = 'checked';
		if ( $this->p_settings['text_style_u'] == 'underline' ) $p_style_u_chk = 'checked';
		
		$plain_txt_tooltip = "Check it if you just want to display a notice instead of a link";
		$edit_here_tooltip = "Below, you can directly edit the title, link and other properties for the strip ad. After making the changes click on the &quot;Save&quot; button to update your settings.";
		?>
		<script><!--//
		function __validatepForm1() {
			var p_title = document.getElementById('p_title');
			var p_link  = document.getElementById('p_link');
			var p_plain_txt = document.getElementById('p_plain_txt');
			if ( p_title.value == '' ) {
				alert('Title required');
				p_title.focus();
				return false;
			}
			if ( p_link.value == '' && p_plain_txt.checked == false ) {
				alert('Link required');
				p_link.focus();
				return false;
			}
			return true;
		}
		function __pToggleAll(parent) {
			var now = parent.checked;
			var frm = document.pform2;
			var len = frm.elements.length;
			for ( i=0; i<len; i++ ) {
				if ( frm.elements[i].name=='p[delete][]' ) {
					frm.elements[i].checked = now;
				}
			}
		}
		function __pShowHide(curr, img) {
			var curr = document.getElementById(curr);
			var img  = document.getElementById(img);
			if ( curr.style=="" || curr.style.display=="none" ) {
				curr.style.display = "block";
				img.src = '<?php echo P_FULLPATH?>images/minus.gif';
			} else if( curr.style!="" || curr.style.display=="block" ) {
				curr.style.display = "none";
				img.src = '<?php echo P_FULLPATH?>images/plus.gif';
			}
		}
		function __pLivePreview(src) {
			var preview_txt  = document.getElementById('preview_txt');
			var text_style_b = document.getElementById('p_text_style_b');
			var text_style_i = document.getElementById('p_text_style_i');
			var text_style_u = document.getElementById('p_text_style_u');
			
			if(src.id=='p_font_family')
				preview_txt.style.fontFamily = src.value;
			if(src.id=='p_font_size')
				preview_txt.style.fontSize = src.value+'px';
			if(src.id=='p_text_align')
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
		function __pShowPlainText() {
			var p_plain_txt  = document.getElementById('p_plain_txt');
			var p_link_row   = document.getElementById('p_link_row');
			var p_weight_row = document.getElementById('p_weight_row');
			var p_addbtn_row = document.getElementById('p_addbtn_row');
			var showRow = 'block'
			if ( navigator.appName.indexOf('Microsoft') == -1 ) {
				var showRow = 'table-row';
			}
			if ( p_plain_txt.checked == true ) {
				p_link_row.style.display = 'none';
				p_weight_row.style.backgroundColor = '#ffffff';
				p_addbtn_row.style.backgroundColor = '#f1f1f1';
			} else {
				p_link_row.style.display = showRow;
				p_weight_row.style.backgroundColor = '#f1f1f1';
				p_addbtn_row.style.backgroundColor = '#ffffff';
			}
		}
		//--></script>
		<script type="text/javascript" src="<?php echo P_FULLPATH;?>include/tooltip.js"></script>
		<link href="<?php echo P_FULLPATH;?>include/tooltip.css" rel="stylesheet" type="text/css">
		<style type="text/css">
		.preview_class {
			padding: 4px 0; 
			background: <?php echo $this->p_settings['bg_color']?>; 
			border-bottom: 1px solid <?php echo $this->p_settings['border_color']?>; 
			text-align: <?php echo $this->p_settings['text_align']?>;
			font-family: <?php echo $this->p_settings['font_family']?>;
			font-size: <?php echo $this->p_settings['font_size']?>px;
			font-weight: <?php echo $this->p_settings['text_style_b']?>;
			font-style: <?php echo $this->p_settings['text_style_i']?>;
			text-decoration: <?php echo $this->p_settings['text_style_u']?>; 
			color: <?php echo $this->p_settings['font_color']?>; 
		}
		</style>
		<h2> <?php echo P_NAME.' '.P_VERSION; ?> Settings</h2><br />
<strong>Make A Donation</strong>
If you liked this plugin, please consider making a kind donation towards our efforts to adding new features to this plugin and developing other new useful resources.If you want to donate in my account directly please contact me <a href="http://www.technozeast.com/contact" target="_blank">here</a>.

<form style="text-align: center;" action="https://www.paypal.com/cgi-bin/webscr" method="post"> <input name="cmd" type="hidden" value="_s-xclick" /> <input name="hosted_button_id" type="hidden" value="10326093" /> <input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" type="image" /> <img src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" />
</form>
		<strong>
		 <h3>Add new "Strip Ad"</h3>
		 <table cellspacing="0" cellpadding="3" width="100%" style="border:1px solid #f1f1f1">
		  <form name="pform1" method="post" onsubmit="return __validatepForm1();">
		  <tr bgcolor="#f1f1f1">
		   <td width="65"><strong>Title: </strong></td>
		   <td><input type="text" name="p[title]" id="p_title" value="" size="50" maxlength="200" /> &nbsp;&nbsp;
		   <input type="checkbox" name="p[plain_txt]" id="p_plain_txt" value="1" onclick="__pShowPlainText()" /> Show As Plain Text? 
		   <a href="#" onMouseover="tooltip('<?php echo $plain_txt_tooltip;?>',282)" onMouseout="hidetooltip()" style="border-bottom:none;"><img src="<?php echo P_FULLPATH;?>images/help.gif" border="0" align="absmiddle" /></a>
		   </td>
		  </tr>
		  <tr id="p_link_row">
		   <td><strong>Link: </strong></td>
		   <td><input type="text" name="p[link]" id="p_link" value="http://" size="50" maxlength="200" /> &nbsp;&nbsp;
		   <input type="checkbox" name="p[in_new_win]" id="p_in_new_win" value="1" /> Open in new window
		   </td>
		  </tr>
		  <tr id="p_weight_row" bgcolor="#f1f1f1">
		   <td><strong>Weight: </strong></td>
		   <td>
			<select name="p[weight]" id="p_weight">
			 <?php for( $i=1; $i<=10; $i++ ) { ?>
			 <option value="<?php echo $i;?>"><?php echo $i;?></option>
			 <?php } ?>
			</select></td>
		  </tr>
		  <tr id="p_addbtn_row">
		   <td>&nbsp;</td>
		   <td><input type="submit" name="p[add]" class="button" value=" Add &raquo; " /></td>
		  </tr>
		  </form>
		 </table>
		 <br /><br />
	 
		 <form name="pform2" method="post">
		 <h3>"Strip Ad" Being Rotated
		 <a href="#" onMouseover="tooltip('<?php echo $edit_here_tooltip;?>',350)" onMouseout="hidetooltip()" style="border-bottom:none;"><img src="<?php echo P_FULLPATH;?>images/help.gif" border="0" align="absmiddle" /></a>
		 </h3>
		 <table cellspacing="1" cellpadding="3" width="100%" style="border:1px solid #f1f1f1">
		  <tr bgcolor="#f1f1f1">
		   <td width="2%"><input type="checkbox" name="checkall" onclick="__pToggleAll(this)"/></td>
		   <td width="25%"><strong>Title</strong></td>
		   <td width="30%"><strong>Link</strong></td>
		   <td width="12%"><div align="center"><strong>Show As Plain Text</strong></div></td>
		   <td width="12%"><div align="center"><strong>Open In New Window</strong></div></td>
		   <td width="12%"><div align="center"><strong>Status</strong></div></td>
		   <td width="5%"><div align="center"><strong>Weight</strong></div></td>
		  </tr>
		  <?php
		  if ( count($this->p_option) > 0 ) {
		   foreach ($this->p_option as $key => $val) {
			 $page_name = $this->p_pages[$thepage];
			 if ( $key % 2 != 0 ) $bgcol = '#f1f1f1';
			 else $bgcol = '#ffffff';
			 ?>
			 <tr valign="top" bgcolor="<?php echo $bgcol; ?>">
			  <td><input type="checkbox" name="p[delete][]" value="<?php echo $key; ?>" /></td>
			  <td><input type="text" name="p[<?php echo $key; ?>][title]" value="<?php echo $val['title']; ?>" size="20" maxlength="200" /></td>
			  <td><input type="text" name="p[<?php echo $key; ?>][link]" value="<?php echo $val['link']; ?>" size="30" maxlength="200" /></td>
			  <td><div align="center">
			  <select name="p[<?php echo $key; ?>][plain_txt]">
				 <option value=1 <?php if($val['plain_txt']==1){print'selected';}?>>Yes</option>
				 <option value=0 <?php if($val['plain_txt']==0){print'selected';}?>>No</option>
			  </select></div>
			  </td>
			  <td><div align="center">
			  <select name="p[<?php echo $key; ?>][in_new_win]">
				 <option value=1 <?php if($val['in_new_win']==1){print'selected';}?>>Yes</option>
				 <option value=0 <?php if($val['in_new_win']==0){print'selected';}?>>No</option>
			  </select></div>
			  </td>
			  <td><div align="center">
			  <select name="p[<?php echo $key; ?>][link_status]">
				 <option value=1 <?php if($val['link_status']==1){print'selected';}?>>Active</option>
				 <option value=0 <?php if($val['link_status']==0){print'selected';}?>>Inactive</option>
			  </select></div>
			  </td>
			  <td><div align="center">
			  <select name="p[<?php echo $key; ?>][weight]">
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
		   <input type="submit" name="p[save]" class="button" value="Save" />
		   <input type="submit" name="p[delete_checked]" class="button" value="Delete Checked" onclick="return confirm('The selected rows will be deleted. Are you sure?');" />
		   </td>
		  </tr>
		 </table><br /><br />
	 
		 <h3>Settings</h3>
		 <table cellspacing="0" cellpadding="3" width="100%" style="border:1px solid #f1f1f1">
		  <tr bgcolor="#f1f1f1">
		   <td><input type="checkbox" name="p[float]" value="1" <?php echo $p_float_chk;?> /> Stick on the top of the page</td>
		  </tr>
		  <tr>
		   <td><input type="checkbox" name="p[hide_close]" value="1" <?php echo $p_close_chk;?> /> Hide close button</td>
		  </tr>
		  <tr bgcolor="#f1f1f1">
		   <td><input type="checkbox" name="p[disable]" value="1" <?php echo $p_disable_chk;?> /> Disable Strip Ad</td>
		  </tr>
		  <tr>
		   <td><input type="checkbox" name="p[remove_pwd]" value="1" <?php echo $p_remove_pwd_chk;?> /> Remove Powered by TechnoZeast Link</td>
		  </tr>		  
		  <tr bgcolor="#f1f1f1">
		   <td><input type="submit" name="p[savesettings]" class="button" value="Save Settings" /></td>
		  </tr>
		 </table><br /><br />
		
		 <h3><a name="pdv" href="#pdv" onclick="__pShowHide('adv_option','adv_img');"><img src="<?php echo P_FULLPATH?>images/plus.gif" id="adv_img" border="0" /><strong>Advance Settings</strong></a></h3>
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
			   <select name="p[font_family]" id="p_font_family" style="width:105px;" onchange="__pLivePreview(this)">
				<option value="Arial" <?php if($this->p_settings['font_family']=='Arial'){print'selected';}?> style="font-family:Arial">Arial</option>
				<option value="Comic Sans MS" <?php if($this->p_settings['font_family']=='Comic Sans MS'){print'selected';}?> style="font-family:Comic Sans MS">Comic Sans MS</option>
				<option value="Courier New" <?php if($this->p_settings['font_family']=='Courier New'){print'selected';}?> style="font-family:Courier New">Courier New</option>
				<option value="Georgia" <?php if($this->p_settings['font_family']=='Georgia'){print'selected';}?> style="font-family:Georgia">Georgia</option>
				<option value="Impact" <?php if($this->p_settings['font_family']=='Impact'){print'selected';}?> style="font-family:Impact">Impact</option>
				<option value="Sans Serif" <?php if($this->p_settings['font_family']=='Sans Serif'){print'selected';}?> style="font-family:Sans Serif">Sans Serif</option>
				<option value="Tahoma" <?php if($this->p_settings['font_family']=='Tahoma'){print'selected';}?> style="font-family:Tahoma">Tahoma</option>
				<option value="Times New Roman" <?php if($this->p_settings['font_family']=='Times New Roman'){print'selected';}?> style="font-family:Times New Roman">Times New Roman</option>
				<option value="Verdana" <?php if($this->p_settings['font_family']=='Verdana'){print'selected';}?> style="font-family:Verdana">Verdana</option>
			   </select>
			   </td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td>Size: </td>
			   <td>
			   <select name="p[font_size]" id="p_font_size" style="width:105px;" onchange="__pLivePreview(this)">
				<option value="10" <?php if($this->p_settings['font_size']=='10'){print'selected';}?> style="font-size:10px">Size 1</option>
				<option value="11" <?php if($this->p_settings['font_size']=='11'){print'selected';}?> style="font-size:11px">Size 2</option>
				<option value="12" <?php if($this->p_settings['font_size']=='12'){print'selected';}?> style="font-size:12px">Size 3</option>
				<option value="13" <?php if($this->p_settings['font_size']=='13'){print'selected';}?> style="font-size:13px">Size 4</option>
				<option value="14" <?php if($this->p_settings['font_size']=='14'){print'selected';}?> style="font-size:14px">Size 5</option>
				<option value="15" <?php if($this->p_settings['font_size']=='15'){print'selected';}?> style="font-size:15px">Size 6</option>
				<option value="16" <?php if($this->p_settings['font_size']=='16'){print'selected';}?> style="font-size:16px">Size 7</option>
			   </select>
			   </td>
			  </tr>
			  <tr>
			   <td>Text Color: </td>
			   <td>
			   <input type="text" name="p[font_color]" id="p_font_color" value="<?php echo $this->p_settings['font_color'];?>" style="width:70px;" readonly />
			   <input type="button" name="p_font_color_btn" id="p_font_color_btn" title="Select Font Color" style="line-height:8px;width:20px;cursor:pointer;cursor:hand;background-color:<?php echo $this->p_settings['font_color'];?>" onclick='window.open("<?php echo P_FULLPATH;?>include/pickcolor.html?pid=font_color","colorpicker","left=300,top=200,width=240,height=170,resizable=0");' />
			   </td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td>Background Color: </td>
			   <td>
			   <input type="text" name="p[bg_color]" id="p_bg_color" value="<?php echo $this->p_settings['bg_color'];?>" style="width:70px;" readonly />
			   <input type="button" name="p_bg_color_btn" id="p_bg_color_btn" title="Select Background Color" style="line-height:8px;width:20px;cursor:pointer;cursor:hand;background-color:<?php echo $this->p_settings['bg_color'];?>" onclick='window.open("<?php echo P_FULLPATH;?>include/pickcolor.html?pid=bg_color","colorpicker","left=300,top=200,width=240,height=170,resizable=0");' />
			   </td>
			  </tr>
			  <tr>
			   <td>Border Color: </td>
			   <td>
			   <input type="text" name="p[border_color]" id="p_border_color" value="<?php echo $this->p_settings['border_color'];?>" style="width:70px;" readonly />
			   <input type="button" name="p_border_color_btn" id="p_border_color_btn" title="Select Border Color" style="line-height:8px;width:20px;cursor:pointer;cursor:hand;background-color:<?php echo $this->p_settings['border_color'];?>" onclick='window.open("<?php echo P_FULLPATH;?>include/pickcolor.html?pid=border_color","colorpicker","left=300,top=200,width=240,height=170,resizable=0");' />
			   </td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td>Text Align: </td>
			   <td>
			   <select name="p[text_align]" id="p_text_align" style="width:105px;" onchange="__pLivePreview(this)">
				<option value="center" <?php if($this->p_settings['text_align']=='center'){print'selected';}?>>Center</option>
				<option value="left" <?php if($this->p_settings['text_align']=='left'){print'selected';}?>>Left</option>
				<option value="right" <?php if($this->p_settings['text_align']=='right'){print'selected';}?>>Right</option>
			   </select>
			   </td>
			  </tr>
			  <tr>
			   <td>Style: </td>
			   <td>
			   <input type="checkbox" name="p[text_style_b]" id="p_text_style_b" value="bold" <?php echo $p_style_b_chk;?> onclick="__pLivePreview(this)">Bold &nbsp; 
			   <input type="checkbox" name="p[text_style_i]" id="p_text_style_i" value="italic" <?php echo $p_style_i_chk;?> onclick="__pLivePreview(this)">Italic &nbsp; 
			   <input type="checkbox" name="p[text_style_u]" id="p_text_style_u" value="underline" <?php echo $p_style_u_chk;?> onclick="__pLivePreview(this)">Underline &nbsp; 
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
			   <td><input type="checkbox" name="p[show_after]" value="1" <?php echo $p_show_after_chk;?> /> After <input type="text" name="p[show_after_visits]" id="p_show_after_visits" value="<?php echo $this->p_settings['show_after_visits']?>" size="3" maxlength="5" /> visits</td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td><input type="radio" name="p[show]" value="1" <?php echo $p_show_1_chk;?> /> All the time</td>
			  </tr>
			  <tr>
			   <td><input type="radio" name="p[show]" value="2" <?php echo $p_show_2_chk;?> /> Once until browser is closed</td>
			  </tr>
			  <tr bgcolor="#f1f1f1">
			   <td><input type="radio" name="p[show]" value="3" <?php echo $p_show_3_chk;?> /> Every <input type="text" name="p[show_days]" id="p_show_days" value="<?php echo $this->p_settings['show_days']?>" size="3" maxlength="5" /> days</td>
			  </tr>
			  <tr>
			   <td><input type="radio" name="p[show]" value="4" <?php echo $p_show_4_chk;?> /> For first <input type="text" name="p[show_visits]" id="p_show_visits" value="<?php echo $this->p_settings['show_visits']?>" size="3" maxlength="5" /> visits</td>
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
		 <p><input type="submit" name="p[saveall]" class="button" value="Save All" /></p>
		 </div>
		 </form>
		<p style="text-align:center;margin-top:3em;"><strong><?php echo P_NAME.' '.P_VERSION; ?> by <a href="http://www.technozeast.com/" target="_blank" >TechnoZeast</a></strong> Visit <a href="http://www.technozeast.com/strip-ad" target="_blank">Plugins Page</a></p>
		<?php
	}
	
} // Eof Class

$StripAd = new StripAd();
?>