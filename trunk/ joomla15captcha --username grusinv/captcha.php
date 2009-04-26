<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/* 17:37 16.04.2009 */
/* 4.5.0 */

/**
 * Captcha class and events for Joomla! 1.5
 * 
 * Long description for file see http://code.google.com/p/joomla15captcha/
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; either version 2.1 of the
 * License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category Captcha
 * @package Joomla
 * @author Victor Grusin <joomlacode@kupala.net>
 * @copyright Copyright (C) 2008 Victor Grusin. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL.
 * @version CVS: $Id: captcha.php 30 2008-09-19 00:00:00Z kupala $
 * @link http://code.google.com/p/joomla15captcha/
 * @since File available since Joomla Release 1.5
 * @deprecated
 * @see http://www.joomla.org/
 * @see http://en.wikipedia.org/wiki/CAPTCHA
 */

/**
 * Example usage: Captcha PATCH FOR USER FORM.
 *
 * Used before the Submit button.
 *
 * Where:
 *
 * 'user.contact' is the marker of form.
 * 'onCaptchaRequired' trigger allows enable or disable the Captcha,
 *   depending on the category of user. The default will not show up
 *   for registered users.
 * 'user.contact' marker allows enable or disable the Captcha,
 *  according to the list of markers that are listed in the parameters
 *  of a plug-in.
 * 
 * Attention! Carefully specify the third and fourth options
 * for the handling of events. They include html code.
 * If he is wrong, it hurts your page. (... 'openhtml' => '', 'closehtml' => '<br />' ...)
 *
 * ...

 *There are two options for passing parameters in an event: standard and named.

 * STANDART: 

			<br />
			<?php // capthca patch >>>>>>> ?>
			
			<?php // Captcha Extention patch rev. 4.5.0 Stable
			$dispatcher = &JDispatcher::getInstance();
			$results = $dispatcher->trigger( 'onCaptchaRequired', array( 'user.contact' ) );
			if ($results[0])
				$dispatcher->trigger( 'onCaptchaView', array( 'user.contact', 0, '', '<br />' ) ); ?>
				
			<?php // <<<<<<< capthca patch ?>
			<button class="button validate" type="submit"><?php echo JText::_('Send'); ?></button>

 * NAMED: 

 			<br />
			<?php // capthca patch >>>>>>> ?>
			
			<?php // Captcha Extention Patch rev. 4.5.0 Stable
			$dispatcher = &JDispatcher::getInstance();
			$results = $dispatcher->trigger( 'onCaptchaRequired', array( 'user.contact' ) );
			if ($results[0]) {
				$captchaparambyname = array( 'captchagroup' => 'user.contact', 'closehtml' => '<br />', 'idsuf' => '' );
				$dispatcher->trigger( 'onCaptchaView', array( $captchaparambyname ) ); 
			} ?>
				
			<?php // <<<<<<< capthca patch ?>
			<button class="button validate" type="submit"><?php echo JText::_('Send'); ?></button>


 * Parameters for the events referred to the array. 
 * Parameters in the array must be called.
 * You can identify them in any order, or omit altogether.
 * 
 * Scenarios::
 * ...trigger( 'onCaptchaView', array( 'captchagroup' => 'user.contact', 'hide' => 0, 'openhtml' => '', 'closehtml' => '<br />', 'idsuf' => '' ) )...
 * ...trigger( 'onCaptchaView', array( 'closehtml' => '<br />', 'idsuf' => 'yoologin' ) )...
 * ...trigger( 'onCaptchaView' )...
 * And so on.
 *
 * Attention!
 * If 'captchagroup' => 'user.contact' is empty or ommited then the
 * captcha is not displayed and not verified.
 *
 */

/**
 * Example usage: Captcha PATCH FOR THE CONTROLLER, which processes the data
 * user form.
 * 
 * Attention! The code must be inserted sub first, to avoid unnecessary
 * referrals to the database.
 *
 
		// Captcha Controller Patch rev. 4.5.0 Stable
		$dispatcher	= &JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'onCaptchaRequired', array( 'user.contact' ) );
		if ( $results[0] ) {
			$captchaparams = array( JRequest::getVar( 'captchacode', '', 'post' )
			, JRequest::getVar( 'captchasuffix', '', 'post' )
			, JRequest::getVar( 'captchasessionid', '', 'post' ));
			$results = $dispatcher->trigger( 'onCaptchaVerify', $captchaparams );
			if ( ! $results[0] ) {
				// *** start your code ***
				// JError::raiseWarning( 'CAPTHCA', JText::_( 'CAPTCHACODE_DO_NOT_MATCH' ) );
				// $this->display();
				// *** end your code ***
				return false;
			}
		}
 
 *
 */


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );


/**
 * Captcha system plugin
 */
class plgSystemCaptcha extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemCaptcha( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		
		JPlugin::loadLanguage( 'plg_system_captcha', JPATH_ADMINISTRATOR ); 
		
	}


	/**
	* Allows you to selectively control the output and processing
	* By default, does not show a captcha and does not handle it
	* for registered users.
	*
	* The distinction for the withdrawal of the captcha at the user level. 
	*/
	function onCaptchaRequired( $captchagroup = '' ) {

		$captchaplugin = &JPluginHelper::getPlugin( 'system', 'captcha' );
		$captchaparameters = new JParameter( $captchaplugin->params );
		$enabledcaptchas   = $captchaparameters->def( 'enabledcaptchas', '' );
		// do not required if disabled group and
		// do not required if captchas distroy (Disabled captchas) and
		// do not required if parameters of plugin is not saved
		$enabledcaptchas = str_replace( ' ', '', $enabledcaptchas );
		if (!(($captchagroup) && (substr_count( (',' . $enabledcaptchas . ','), (',' . $captchagroup . ',') )))) return false;

		$user = &JFactory::getUser();
		if ($user->guest) {
			return true;
		} else {
			return false;
		}
	}

	
	/**
	* Verify a captcha code, which was entered by user.
	* @param $usersecurecode - 
	* @param $idsuf - If on page some forms with the captcha.
	* @param $captchasessionid - If there was a simultaneous plurality of pages
	*   or there was a generation of pages after form display.
	*
	* Attention! If a form not have a captcha, we must return FALSE.
	*/
	function onCaptchaVerify( $usersecurecode='', $idsuf = '', $captchasessionid = '' ) {
	
		$usersecurecode = strtolower( $usersecurecode );
		$hiddencaptchas = '';
		$captchagroup = '';
		$acaptcachar = '';
		$ncaptcachar = '';
		$captchasessionid = '';
		$captchasessionidfromcurrentsession = '';
		
		// Sessions >>>>>
		
		// close and save previous session
		if (session_id()) {
		
			// for backward compatibility
			// will be deprecated in 5.0.0 or wil be used for hi security
			@$captchasessionidfromcurrentsession .= $_SESSION['captchasessionid'];
			$lastsession = $_SESSION;
			$lastsessionid = session_id();
		}
		session_write_close();
		
		// $captchasessionidfromcurrentsession will be deprecated in 5.0.0 or other used
		if ($captchasessionid == '') $captchasessionid = $captchasessionidfromcurrentsession;

		// captcha session
		if ( $captchasessionid ) {
			ini_set( 'session.save_handler', 'files' );
			@session_id( $captchasessionid );
			session_start();
			@$hiddencaptchas = $_SESSION [ 'hiddencaptchas' . $idsuf ] ;
			@$captchagroup = $_SESSION [ 'captchagroup' . $idsuf ] ;
			@$acaptcachar = $_SESSION [ 'acaptcha' . $idsuf ] ;
			@$ncaptcachar = $_SESSION [ 'ncaptcha' . $idsuf ] ;
			session_write_close();
		}
		
		// restore previous session
		$conf = &JFactory::getConfig();
		$handler =  $conf->getValue('config.session_handler', 'none');
		if ($handler == 'none') {
			ini_set( 'session.save_handler', 'files' );
		} else {
			ini_set( 'session.save_handler', 'user' );
			$sessionstorage = new JSessionStorageDatabase();
		}
		session_id($lastsessionid);
		session_start();
		
		// <<<<< Sessions
		
		// do not verify if hidden group and do not verify if captchas session distroy
		if (($captchagroup) && (substr_count( (',' . $hiddencaptchas . ','), (',' . $captchagroup . ',') ))) return true;
		
		$atruesecurecode = '';
		$ntruesecurecode = '';
		
		$atruesecurecode .= $acaptcachar;
		$ntruesecurecode .= $ncaptcachar;
		
		if ((($atruesecurecode == $usersecurecode) || ($ntruesecurecode == $usersecurecode)) && ( $usersecurecode != '' )) {
			return true;
		}
		return false;	
	}

	
	/**
	* Show details a captcha in the form of user.
	*
	* @param $captchagroup - Group Id. Specifying an ID plug-in options
	*     on the admin panel allows the administrator to hide captchas
	*     belonging to the group.
	* @param $hide - Length of captcha code.
	* @param $openhtml - A fragment of HTML code that is inserted before
	*     the captcha itself. (If necessary)
	* @param $closehtml - A fragment of code that is inserted after
	*     the display captcha itself. (If necessary)
	* @param $idsuf - suffix ID div tags for use in scripts and styles.
	*/
	function onCaptchaView( $captchagroup = '', $hide = 0, $openhtml = '', $closehtml = '', $idsuf = '' ) {
		global $mainframe;
		// id safety for <div> tags of captcha
		static $statcountshows = 0; // for control of javascript code
		static $statcount = 1;
		//static $statidsufused = ',,'; // because ie7 have problem TODO
		static $statidsufused = ','; // because ie7 have problem with search module in top
		static $captchasessionid = '';
		
		//if ($captchagroup = 'none') return;
		
		// parameters by name - Alternate call
		if (is_array( $captchagroup )) {
			$captchagroup = isset( $captchagroup['captchagroup'] ) ? $captchagroup['captchagroup'] : '' ;
			$hide = isset( $captchagroup['hide'] ) ? $captchagroup['hide'] : 0 ;
			$openhtml = isset( $captchagroup['openhtml'] ) ? $captchagroup['openhtml'] : '' ;
			$closehtml = isset( $captchagroup['closehtml'] ) ? $captchagroup['closehtml'] : '' ;
			$idsuf = isset( $captchagroup['idsuf'] ) ? $captchagroup['idsuf'] : '' ;
		}
		
		$idsuffix = (string) $statcount;
		if (!$idsuffix) return;
		
		$captchaplugin = &JPluginHelper::getPlugin( 'system', 'captcha' );
		$captchaparameters = new JParameter( $captchaplugin->params );
		
		$doc = &JFactory::getDocument();
		$captchalang = $doc->getLanguage();
		
		$captchalayout    = $captchaparameters->def( 'layout', 'image' );
		$captchacrypttype = $captchaparameters->def( 'mode', '0' ); // Not used now. But in code.
		$captchareloads   = $captchaparameters->def( 'reloads', '5' );
		$captchahelpon    = $captchaparameters->def( 'help', '1' );
		$captchahelpURL   = $captchaparameters->def( 'helpurl', '' );
		$captchadonate    = $captchaparameters->def( 'donate', '0' );
		$captchatextcolor = $captchaparameters->def( 'textcolor', '0' );
		$captchabackcolor = $captchaparameters->def( 'backcolor', '196,196,196' );
		$hiddencaptchas   = $captchaparameters->def( 'hiddencaptchas', 'none' );
		if ($hide) $hiddencaptchas .= ',' . $captchagroup . ',';
		
		$xsize            = $captchaparameters->def( 'xsize', '' );
		$ysize            = $captchaparameters->def( 'ysize', '' );
		$usetemplatettf   = $captchaparameters->def( 'usetemplatettf', '0' );
		
		$min_font_size    = $captchaparameters->def( 'min_font_size', '18' ); // 18 - 24
		$max_font_size    = $captchaparameters->def( 'max_font_size', '24' ); // 18 - 24
		$max_angle        = $captchaparameters->def( 'max_angle', '0' ); // 0 - 30
		$im_padding       = $captchaparameters->def( 'im_padding', '5' ); // 1 - 10
		$char_padding     = $captchaparameters->def( 'char_padding', '5' ); // 1 - 10
		$char_filling     = $captchaparameters->def( 'char_filling', '/' ); // '/'

		$length = 0; // TODO throus function parameter
		if ($length == 0) {
			$length = $captchaparameters->def( 'lenght', 4 );
		}

		$captchaalphanumeric  = '23456789abcdefghijkmnpqrstuvwxyz'; // no 'l', '1', 'o', '0' - trouble for distinct
		$captchanumeric  = '0123456789'; // sound captcha
		$captchalen = strlen( $captchaalphanumeric ) - 1;
		$captchaslist = array();
		$captchareloads = min( $captchareloads, 20 );
		for ( $j = 0; $j < $captchareloads; $j++ ) {
			$char = '';
			$number = '';
			for ( $i = 0; $i < $length; $i++ ) {
				$char .= substr( $captchaalphanumeric, mt_rand( 0, $captchalen ), 1 );        
				$number .= substr( $captchanumeric, mt_rand( 0, 9 ), 1 );        
			}
			$captchaslist[] = array( 'imagecode' => $char, 'soundcode' => $number );
		}
		
		// Session >>>
		
		$lastsession = $_SESSION;
		$lastsessionid = session_id();
		session_write_close();
		
		// captcha session
		ini_set( 'session.save_handler', 'files' );
		if (!$captchasessionid == '') {
			@session_id( $captchasessionid );
			session_start();
		} else {
			session_start();
			session_regenerate_id();
			$captchasessionid = session_id();
			$_SESSION = array();
		}
		$hiddencaptchas = str_replace( ' ', '', $hiddencaptchas );
		$_SESSION [ 'hiddencaptchas' . $idsuffix ] = $hiddencaptchas;
		$_SESSION [ 'captchagroup' . $idsuffix ] = $captchagroup;
		if ($usetemplatettf == '1')
			$_SESSION [ 'attffile' . $idsuffix ] = (($mainframe->isSite()) ? '../../../templates/' : '../../../administrator/templates/')
			. $mainframe->getTemplate() . '/images/accessibility.ttf';
		$_SESSION [ 'captchaslist' . $idsuffix ] = $captchaslist;
		
		// extend parameters
		$_SESSION [ 'min_font_size' ] = $min_font_size;
		$_SESSION [ 'max_font_size' ] = $max_font_size;
		$_SESSION [ 'max_angle' ] = $max_angle;
		$_SESSION [ 'im_padding' ] = $im_padding;
		$_SESSION [ 'char_padding' ] = $char_padding;
		$_SESSION [ 'char_filling' ] = $char_filling;

		$_SESSION [ 'layout' ] = $captchalayout;
		
		session_write_close();
		
		// restore previous session
		$conf = &JFactory::getConfig();
		$handler =  $conf->getValue('config.session_handler', 'none');
		if ($handler == 'none') {
			ini_set( 'session.save_handler', 'files' );
		} else {
			ini_set( 'session.save_handler', 'user' );
			$sessionstorage = new JSessionStorageDatabase();
		}
		session_id($lastsessionid);
		session_start();
		
		$_SESSION = $lastsession;
		$_SESSION [ 'captchasessionid' ] = $captchasessionid;
		setcookie( 'jsid', $captchasessionid, time() + 3600, '/' ); // TODO

		// <<< Session 
			
		if (($captchagroup) && (substr_count( (',' . $hiddencaptchas . ','), (',' . $captchagroup . ',') ))) {
			?>
			<?php echo $openhtml ?>
			<input type="hidden" name="captchasuffix" value="<?php echo $idsuffix ?>" />
			<input type="hidden" name="captchasessionid" value="<?php echo $captchasessionid ?>" />
			<?php echo $closehtml ?>
			<?php
			$statcount += 1;
			return;
		}
		
		// URLs
		
		$captcha_URI = JURI::root() . 'plugins/system/captcha/';
		$image_URI   = $captcha_URI . 'showcode.php?';
		$sound_URI   = $captcha_URI . 'playcode.php?';
		$acces_URI   = $captcha_URI . 'files/accessibility.gif';
		$accessibilitytemplatepath = 'templates/'
		. $mainframe->getTemplate() . '/images/accessibility.gif';
		if (file_exists($accessibilitytemplatepath)) {
			$accessibilitytemplatepath = (($mainframe->isSite()) ? '' : 'administrator/') . $accessibilitytemplatepath;
			$acces_URI   = JURI::root().$accessibilitytemplatepath;
		}
		$url_imagecore   = JRoute::_( $image_URI );
		$url_soundcore   = JRoute::_( $sound_URI );
		$url_acces   = JRoute::_( $acces_URI );
		$image_params   = '&amp;sid='.$captchasessionid.'&amp;crt='.$captchacrypttype.'&amp;clr='.$captchatextcolor.'&amp;bgr='.$captchabackcolor.'&amp;xsize='.$xsize.'&amp;ysize='.$ysize.'&amp;suf='; // .$idsuffix
		$sound_params   = '&amp;sid='.$captchasessionid.'&amp;crt='.$captchacrypttype.'&amp;lng='.$captchalang.'&amp;typ=.mp3'.'&amp;suf='; // .$idsuffix
		$url_image   = $url_imagecore.time().$image_params;
		$url_sound   = $url_soundcore.time().$sound_params;
		
		// Title
		// TODO if reload without image??? перезагркзка по каждому прослушиванию
		
		if (!$statcountshows) {
			?>
			<script type="text/javascript"><!--
			function JGetElementById( s ) {
				var o = (document.getElementById ? document.getElementById(s)
				: document.all[s]);
				return ((o == null) ? false : o);
			}
			function reloadCaptcha( suf ) {
				var ocap = JGetElementById( 'captchaimage' + suf );
				if (ocap) {
					var today = new Date(); 
					ocap.setAttribute( 'src', '<?php echo $url_imagecore ?>' + today.getTime() + '<?php echo str_replace( '&amp;', '&', $image_params ) ?>' + suf );
				}
				var ocapc = JGetElementById( 'captchacode' + suf );
				ocapc.value="";
				ocapc.focus();
			}
			--></script>
			<?php
		}
		$captchalegend = JText::_( 'CAPTCHACODE_FORM_TEXT' );
		if (($captchalayout == 'sound') || ($captchalayout == 'imagesound')) {
			if ($captchalayout == 'sound') {
				$captchalegend = JText::_( 'CAPTCHACODE_FORM_SOUND' );
			} else {
				$captchalegend .= ' ' . JText::_( 'CAPTCHACODE_OR' ) . ' ' . JText::_( 'CAPTCHACODE_FORM_SOUND' );
			}
			if (!$statcountshows) {
				?>
				<script type="text/javascript"><!--
				function JGetPlayCapthca( s ) {
					var ocap = JGetElementById( s );
					if (!ocap) {
						newElement = document.createElement( 'div' );
						newElement.setAttribute( 'id', 'playcapthca' );
						ocap = document.body.appendChild( newElement );
					}
					return ((ocap == null) ? false : ocap);
				}
				function playCaptcha( suf ) {
					var today = new Date(); // kill noise sessions
					JGetPlayCapthca('playcapthca').innerHTML = ''; 
					JGetPlayCapthca('playcapthca').innerHTML = '<embed src="<?php echo $url_sound ?>' + suf + '&amp;stamp=' + today.getTime() + '" hidden="true" autostart="true" />';
					var ocapc = JGetElementById( 'captchacode' + suf );
					ocapc.value="";
					ocapc.focus();
				}
				--></script>
				<?php
			}
		}
		if ($captchalayout != 'sound')
			$captchalegend .= ' ' . JText::_( 'CAPTCHACODE_HOW_TO_RELOAD' );
		// Form
		?>
		<?php echo $openhtml ?>
		<span id="captchatitle<?php echo $idsuffix ?>"><?php echo JText::_( 'CAPTCHACODE_FORM_TITLE' ) ?></span>		
		<span id="captchalegend<?php echo $idsuffix ?>"><?php echo $captchalegend ?></span>
		<input type="hidden" name="captchasuffix" value="<?php echo $idsuffix ?>" />
		<input type="hidden" name="captchasessionid" value="<?php echo $captchasessionid ?>" />
		<?php if (($captchalayout == 'image') || ($captchalayout == 'imagesound')) : ?>
			<br />
			<img id="captchaimage<?php echo $idsuffix ?>" src="<?php echo $url_image . $idsuffix ?>" title="<?php echo JText::_( 'CAPTCHACODE_FORM_TEXT' ) ?>" alt="<?php echo JText::_( 'CAPTCHACODE_FORM_TEXT' ) ?>" onclick="reloadCaptcha('<?php echo $idsuffix ?>')" style="cursor: pointer;" />
		<?php endif ?>
		<br />
		<input id="captchacode<?php echo $idsuffix ?>" type="text" name="captchacode" class="captchainputbox required" size="10" value="" />&nbsp;*&nbsp;
		<?php if (($captchalayout == 'sound') || ($captchalayout == 'imagesound')) : ?>
			<script type="text/javascript"><!--
			document.write('<a href="javascript:playCaptcha('+"'<?php echo $idsuffix ?>'"+')">');
			document.write('<img src="'+'<?php echo $url_acces ?>'+'" border="0" alt="'+'<?php echo JText::_( 'CAPTCHACODE_SOUND' ) ?>'+'"></a>');
			--></script>
		<?php endif ?>      
		<?php if ( $captchahelpon == '1' || $captchadonate == '0' ) : ?>  	
			<br />
			<span id="captchahelplink<?php echo $idsuffix ?>" style="font-size: xx-small"><?php echo JText::_( 'CAPTCHACODE_HELP' ) ?>
			<?php if ( $captchahelpURL !== '' && $captchadonate == '1' ) { ?>  	
				<a target="_blank" href="<?php echo $captchahelpURL ?>"><?php echo JText::_( 'CAPTCHACODE_HELP_LINKNAME' ) ?></a>
			<?php } else { ?>
				<a target="_blank" href="http://code.google.com/p/joomla15captcha/"><?php echo JText::_( 'PROJECT_PAGE' ) ?></a>&nbsp;::&nbsp;
				<a target="_blank" href="http://kupala.net/">Kupala.Net</a>
			<?php } ?>
			</span>
		<?php endif ?>
		<?php echo $closehtml ?>
		<?php
		$statcountshows += 1;
		$statcount += 1;
	}
}		