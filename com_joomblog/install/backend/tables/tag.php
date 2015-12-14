<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.database.table');

class JoomBlogTableTag extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__joomblog_tags', 'id', $db);
	}

	public function delete($pk = null)
	{
		$db = $this->getDBO();

		$db->setQuery('DELETE FROM #__joomblog_content_tags WHERE tag='.$pk);
		$db->execute();

		return parent::delete($pk);
	}
	
	public function loadByName($tag = '') {
		return parent::load(array('name'=>$tag));
	}
}
