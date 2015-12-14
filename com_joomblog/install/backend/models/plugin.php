<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');


class JoomBlogModelPlugin extends JModelAdmin
{
	protected $context = 'com_joomblog';

	public function getTable($type = 'Plugin', $prefix = 'JoomBlogTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		return false;
	}	
}
