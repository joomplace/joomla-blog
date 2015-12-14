<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class JoomBlogModelDrafts extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'c.id', 'c.state', 'c.ordering', 'c.title', 'c.hits', 'cc.title', 'u.name', 'c.created'
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState()
	{
		$this->setState('filter.search', $this->getUserStateFromRequest('com_joomblog.filter.search', 'filter_search'));
		$this->setState('filter.category_id', $this->getUserStateFromRequest('com_joomblog.filter.category_id', 'filter_category_id'));
		$this->setState('filter.blog_id', $this->getUserStateFromRequest('com_joomblog.filter.blog_id', 'filter_blog_id'));
		$this->setState('filter.author_id', $this->getUserStateFromRequest('com_joomblog.filter.author_id', 'filter_author_id'));

		parent::populateState();
	}

	protected function getListQuery() 
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->from('#__joomblog_blogs AS i');
		
		$query->select('c.id, c.title, c.alias, c.state, c.ordering, c.hits, c.catid, c.created_by, c.created, c.publish_up, c.publish_down' );
		$query->join('INNER', '#__joomblog_posts AS c ON c.id=i.content_id');
        $query->where('c.state=-2');
		
		$query->select('cc.title AS category_title');
		$query->join('LEFT', '#__categories AS cc ON cc.id=c.catid');
		
		
		$query->select('u.name AS author');
		$query->join('LEFT', '#__users AS u ON u.id=c.created_by');
		
		$query->join('LEFT', '#__joomblog_multicats AS mc ON mc.aid=c.id');

		$query->join('LEFT', '#__joomblog_list_blogs AS l ON l.id=i.blog_id');

		$query->order($db->getEscaped($this->getState('list.ordering', 'c.title ')).' '.$db->getEscaped($this->getState('list.direction', 'ASC')));

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
			$query->where('c.title LIKE '.$search.' OR c.introtext LIKE '.$search.' OR c.fulltext LIKE '.$search);
		}

		$search = $this->getState('filter.category_id');
		if (!empty($search)) {
			$query->where('mc.cid='.(int)$search);
		}

		$search = $this->getState('filter.blog_id');
		if (!empty($search)) {
			$query->where('i.blog_id='.(int)$search);
		}

		$search = $this->getState('filter.author_id');
		if (!empty($search)) {
			$query->where('c.created_by='.(int)$search);
		}
		
		$query->group('i.id');
		return $query;
	}
	
	public function getMulticats($id=0)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('mc.cid, c.title ');
		$query->from('#__joomblog_multicats AS mc');
		$query->join('LEFT', '#__categories AS c ON c.id=mc.cid');
		$query->where('aid='.$id);
		$db->setQuery($query);
		$items = $db->loadObjectList();
		
		return $items;
	}
	
	public function getTags($id=0)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('GROUP_CONCAT(ct.name) AS tags');
		$query->join('LEFT', '#__joomblog_content_tags AS ctx ON ctx.contentid='.$id);
		$query->from('#__joomblog_tags AS ct');
		$query->where('ct.id=ctx.tag');
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	public function getCategories()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('cc.id, cc.title');
		$query->from('#__categories AS cc');

		$query->where('cc.extension="com_joomblog"');
		$query->where('cc.published=1');
		$query->where('cc.access=1');

		$db->setQuery($query);
		$items = $db->loadObjectList('id');
		
		return $items;
	}
	
	public function getBlogs()
	{
		$model = JModelLegacy::getInstance('Blogs', 'JoomBlogModel', array('ignore_request'=>true));
		$items = $model->getItems();
		
		return $items;
	}
	
	public function getUsers()
	{
		$model = JModelLegacy::getInstance('Users', 'JoomBlogModel', array('ignore_request'=>true));
		$items = $model->getItems();
		
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
