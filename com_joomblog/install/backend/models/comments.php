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

class JoomBlogModelComments extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'i.id', 'i.comment', 'i.published', 'c.title', 'i.name', 'i.created'
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState()
	{
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		parent::populateState();
	}

	protected function getListQuery() 
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('i.*');
		$query->from('#__joomblog_comment AS i');

		$query->select('c.title AS post_title');
		$query->join('LEFT', '#__joomblog_posts AS c ON c.id=i.contentid');

		$query->select('u.name AS author');
		$query->join('LEFT', '#__users AS u ON u.id=i.user_id');

		$query->order($db->escape($this->getState('list.ordering', 'i.id')).' '.$db->escape($this->getState('list.direction', 'ASC')));

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('i.comment LIKE '.$search);
		}
		
		$query->group('i.id');

		return $query;
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
