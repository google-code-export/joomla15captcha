<?php
/**
 * @version		$Id: admin.login.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @subpackage	Joomla.Extensions
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

switch ( JRequest::getCmd('task'))
{
	case 'login' :
		LoginController::login();
		break;

	case 'logout' :
		LoginController::logout();
		break;

	default :
		LoginController::display();
		break;
}


/**
 * Static class to hold controller functions for the Login component
 *
 * @static
 * @package		Joomla
 * @subpackage	Login
 * @since		1.5
 */
class LoginController
{
	function display()
	{
		jimport('joomla.application.module.helper');
		$module = & JModuleHelper::getModule('mod_login');
		$module = JModuleHelper::renderModule($module, array('style' => 'rounded', 'id' => 'section-box'));
		echo $module;
	}

	function login()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken('request') or jexit( 'Invalid Token' );

		// Captcha Controller Patch rev. 4.5.0 Stable
		$dispatcher	= &JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'onCaptchaRequired', array( 'administrator.login' ) );
		if ( $results[0] ) {
			$captchaparams = array( JRequest::getVar( 'captchacode', '', 'post' )
			, JRequest::getVar( 'captchasuffix', '', 'post' )
			, JRequest::getVar( 'captchasessionid', '', 'post' ));
			$results = $dispatcher->trigger( 'onCaptchaVerify', $captchaparams );
			if ( ! $results[0] ) {
				JError::raiseWarning( 'CAPTHCA', JText::_( 'CAPTCHACODE_DO_NOT_MATCH' ));
				LoginController::display();
				return false;
			}
		}

		$credentials = array();

		$credentials['username'] = JRequest::getVar('username', '', 'method', 'username');
		$credentials['password'] = JRequest::getVar('passwd', '', 'post', 'string', JREQUEST_ALLOWRAW);

		$result = $mainframe->login($credentials);

		if (!JError::isError($result)) {
			$mainframe->redirect('index.php');
		}

		LoginController::display();
	}

	function logout()
	{
		global $mainframe;

		$result = $mainframe->logout();

		if (!JError::isError($result)) {
			$mainframe->redirect('index.php?option=com_login');
		}

		LoginController::display();
	}
}