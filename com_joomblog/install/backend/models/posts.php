<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

class JoomBlogModelPosts extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'c.id', 'c.state', 'c.ordering', 'c.title', 'c.hits', 'cc.title', 'u.name', 'c.created', 'c.publish_up', 'c.publish_down'
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState()
	{
		
		$this->setState('filter.search', $this->getUserStateFromRequest('com_joomblog.filter.search', 'filter_search'));
		$this->setState('filter.status', $this->getUserStateFromRequest('com_joomblog.filter.status', 'filter_status'));
		$this->setState('filter.category_id', $this->getUserStateFromRequest('com_joomblog.filter.category_id', 'filter_category_id'));
		$this->setState('filter.blog_id', $this->getUserStateFromRequest('com_joomblog.filter.blog_id', 'filter_blog_id'));
		$this->setState('filter.author_id', $this->getUserStateFromRequest('com_joomblog.filter.author_id', 'filter_author_id'));
        $this->setState('filter.tags_id', $this->getUserStateFromRequest('com_joomblog.filter.tags_id', 'filter_tags_id'));

		parent::populateState();
	}

	protected function getListQuery() 
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->from('#__joomblog_blogs AS i');

		$query->select('c.id, c.title, c.alias, c.state, c.ordering, c.hits, c.catid, c.created_by, c.created, c.publish_up, c.publish_down' );
		$query->join('INNER', '#__joomblog_posts AS c ON c.id=i.content_id');
        

		$query->select('cc.title AS category_title');
		$query->join('LEFT', '#__categories AS cc ON cc.id=c.catid');

		$query->select('u.name AS author');
		$query->join('LEFT', '#__users AS u ON u.id=c.created_by');

		$query->join('LEFT', '#__joomblog_multicats AS mc ON mc.aid=c.id');
		$query->join('LEFT', '#__joomblog_list_blogs AS l ON l.id=i.blog_id');

        $query->join('LEFT', '#__joomblog_content_tags AS ct ON ct.contentid=i.content_id');
        $query->join('LEFT', '#__joomblog_tags AS tt ON tt.id=ct.tag');
		$query->order($db->escape($this->getState('list.ordering', 'c.created')).' '.$db->escape($this->getState('list.direction', 'DESC')));

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('c.title LIKE '.$search.' OR c.introtext LIKE '.$search.' OR c.fulltext LIKE '.$search);
		}
		$search = $this->getState('filter.category_id');
		if (!empty($search)) {
			$query->where('mc.cid='.(int)$search);
		}
		$search = $this->getState('filter.status');
		if ($search == '0' OR $search == '1' OR $search == '-2') {
			$query->where('c.state='.(int)$search);
		}
		if ($search == '2') {
			$query->where('c.state IN (0,1,-2)');
		}

		$search = $this->getState('filter.blog_id');
		if (!empty($search)) {
			$query->where('i.blog_id='.(int)$search);
		}

		$search = $this->getState('filter.author_id');
		if (!empty($search)) {
			$query->where('c.created_by='.(int)$search);
		}

        $search = $this->getState('filter.tags_id');
        if (!empty($search)) {
            $query->where('tt.id=' . (int)$search);
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
		$query->where('c.published=1');
		$query->where('aid='.$id);
		$db->setQuery($query);
		$items = $db->loadObjectList();
		//send post to draft if it is not in any category
		if (empty($items))
		{
			$query = "SELECT `state` FROM `#__joomblog_posts` WHERE `id`=".$id;
			$db->setQuery( $query );
			$post_state = $db->loadResult();
			if ($post_state !== '-2')
			{
				$query	= "UPDATE `#__joomblog_posts` SET `state`='-2' WHERE `id`=".$id;
				$db->setQuery( $query );
				$db->execute();
				JController::setRedirect(JRoute::_('index.php?option=com_joomblog&view=posts', false), sprintf(Jtext::_('COM_JOOMBLOG_POSTS_WITH_IDS_MOVED_TO_DRAFTS'),$id).Jtext::_('COM_JOOMBLOG_DUE_TO').Jtext::_('COM_JOOMBLOG_CATEGORY_DELETION') );
				JController::redirect();
			}			
		}	

		
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

	public function getFilteredTags()
	{
		$db = $this->_db;
		$query = $db->getQuery(true)
			->select('DISTINCT `id`, `name`')
			->from('`#__joomblog_tags`')
			->order('`name` ASC');
		$db->setQuery($query);

		return $db->loadObjectList();
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
