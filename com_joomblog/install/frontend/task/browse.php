<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

include_once(JB_COM_PATH . '/task/browse.base.php');

class JbblogBrowseTask extends JbblogBrowseBase
{
	
	function __construct()
	{
		parent::__construct();
		$this->toolbar = JB_TOOLBAR_HOME;
	}
	
}
