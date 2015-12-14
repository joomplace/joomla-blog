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
 
class JbblogBloggersTask extends JbblogBaseController{
	
	function JbblogBloggersTask(){
		$this->toolbar = JB_TOOLBAR_BLOGGER;
	}
	
	function display(){
		return "List of all current bloggers";
	}
	
}
