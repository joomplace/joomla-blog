<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class TableTags extends JTable
{
	var $id = 0;
	var $name = null;
	var $default = null;
	
	function __construct( $db )
	{
		parent::__construct('#__joomblog_tags','id', $db);
	}
}
