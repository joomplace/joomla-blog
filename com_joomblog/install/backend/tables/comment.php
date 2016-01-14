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

class JoomBlogTableComment extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__joomblog_comment', 'id', $db);
	}

	public function delete($pk = null)
	{
		$db = $this->getDBO();

		$db->setQuery('DELETE FROM #__joomblog_comment_votes WHERE commentid='.$pk);
		$db->execute();

		return parent::delete($pk);
	}
	public function store($updateNulls = false){
	
	if (!$this->created) $this->created = date('Y-m-d H:i:s');
	if (!$this->created=='0000-00-00 00:00:00') $this->created = date('Y-m-d H:i:s');
	
	return parent::store($updateNulls);
	}
	
}
