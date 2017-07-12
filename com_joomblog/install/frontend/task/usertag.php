<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

include_once(JB_COM_PATH . '/task/base.php');
 
class JbblogUsertagTask extends JbblogBaseController{
	function __construct(){
	}
	
	function display(){
		return "<h1>List all entry with the given tag from the given user</h1>";
	}
}
