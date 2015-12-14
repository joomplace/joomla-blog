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

class JoomblogTableDashboard_item extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__joomblog_dashboard_items', 'id', $db);
	}

    public function store() {
        return parent::store();
    }

}