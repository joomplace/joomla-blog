<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class JoomBlogModelUsers extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'i.id', 'i.name', 'i.username', 'i.email', 'i.block', 'i.registerDate', 'i.lastvisitDate', 'g.title', 'posts_count', 'comments_count'
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState()
	{
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$this->setState('filter.group_id', $this->getUserStateFromRequest('com_joomblog.filter.group_id', 'filter_group_id'));

		parent::populateState();
	}

	protected function getListQuery() 
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('i.*');
		$query->from('#__users AS i');

		$query->select('g.title AS usergroup');
		$query->join('LEFT', '#__user_usergroup_map AS u ON u.user_id=i.id');
		$query->join('LEFT', '#__usergroups AS g ON g.id=u.group_id');
		
		$query->select('(SELECT COUNT(c.id) FROM #__joomblog_posts AS c WHERE c.created_by=i.id) AS posts_count');
		$query->select('(SELECT COUNT(cc.id) FROM #__joomblog_comment AS cc WHERE cc.user_id=i.id) AS comments_count');

		$query->order($db->escape($this->getState('list.ordering', 'i.name')).' '.$db->escape($this->getState('list.direction', 'ASC')));

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('i.name LIKE '.$search.' OR i.username LIKE '.$search);
		}
		$search = $this->getState('filter.group_id');
		if (!empty($search)) {
			$query->where('u.group_id='.(int)$search);
		}


		$query->group('i.id');

		return $query;
	}
	
	public function getGroups()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('u.id, u.title');
		$query->from('#__usergroups AS u');

		$db->setQuery($query);
		$items = $db->loadObjectList('id');
		
		return $items;
	}

	public function getCurrDate()
		{
			
			$config		= JComponentHelper::getParams('com_joomblog');
            $saved_date = $config->get('curr_date');
			if (strtotime("+2 month",strtotime($saved_date))<=time()) {
				return true;
			} else {
				return false;
			}
		}
}
