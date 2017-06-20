<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once( JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'captcha.php' );

class JbblogCaptchaTask
{
	function __construct()
	{
		getCaptcha();
		exit();
	}	
}
