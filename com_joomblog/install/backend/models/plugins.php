<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class JoomBlogModelPlugins extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.name', 'b.published', 'a.folder'
			);
		}
		parent::__construct($config);
	}


	protected function populateState()
	{
		$this->setState('filter.search_plugin', $this->getUserStateFromRequest('com_joomblog.filter.search_plugin', 'filter_search_plugin'));
		$this->setState('filter.search_plugin_type', $this->getUserStateFromRequest('com_joomblog.filter.search_plugin_type', 'filter_plugin_type'));

		parent::populateState();
	}

	protected function getListQuery()
	{
		$this->updatePluginsTable();

		$db = $this->_db;
		$query = $db->getQuery(true)
			->select('a.name, a.folder, a.element, b.id, b.published')
			->from('`#__extensions` AS a, `#__joomblog_plugins` AS b')
			->where('b.id=a.extension_id')
			->where('a.enabled=1');

		$type = $this->getState('filter.search_plugin_type');
		if (!empty($type))
		{
			$query->where('a.folder="' . $type . '"');
		}

		$query->where('a.type="plugin"')
			->where('a.element !="jom_comment_bot"');
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('a.name LIKE ' . $search);
		}

		$query->order($db->escape($this->getState('list.ordering', 'a.ordering ')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Returns an object list
	 *
	 * @param   string The query
	 * @param   int    Offset
	 * @param   int    The number of records
	 * @return  array
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$search = $this->getState('filter.search_plugin');
		$ordering = $this->getState('list.ordering', 'ordering');
		if ($ordering == 'name' || (!empty($search) && stripos($search, 'id:') !== 0))
		{
			$this->_db->setQuery($query);
			$result = $this->_db->loadObjectList();

			$this->translate($result);

			if (!empty($search))
			{
				foreach ($result as $i => $item)
				{
					if (!preg_match("/$search/i", $item->name))
					{
						unset($result[$i]);
					}
				}
			}

			$direction = ($this->getState('list.direction') == 'desc') ? -1 : 1;
			JArrayHelper::sortObjects($result, $ordering, $direction, true, true);

			$total = count($result);
			if ($total < $limitstart)
			{
				$limitstart = 0;
				$this->setState('list.start', 0);
			}
			return array_slice($result, $limitstart, $limit ? $limit : null);
		}
		else
		{
			if ($ordering == 'a.ordering')
			{
				$query->order('a.folder ASC');
				$ordering = 'a.ordering';
			}
			$query->order($this->_db->quoteName($ordering) . ' ' . $this->getState('list.direction'));

			if ($ordering == 'folder')
			{
				$query->order('a.ordering ASC');
			}
			$result = parent::_getList($query, $limitstart, $limit);
			$this->translate($result);

			return $result;
		}
	}

	function updatePluginsTable()
	{
		$query = $this->_db->getQuery(true)
			->select('`a`.`name`, `a`.`folder`, `a`.`extension_id` AS `id`')
			->from('`#__extensions` AS `a`')
			->join('LEFT OUTER', '`#__joomblog_plugins` AS `b` ON (`a`.`extension_id` = `b`.`id`)')
			->where('`b`.`id` IS NULL AND (`a`.`folder` = "content" OR `a`.`folder` = "editors-xtd")')
			->where('`a`.`enabled` = 1 AND `a`.`type` = "plugin" AND `a`.`element` != "jom_comment_bot"');
		$this->_db->setQuery($query);
		$plugins = $this->_db->loadObjectList();

		if ($plugins)
		{
			foreach ($plugins as $plugin)
			{
				$strSQL = "INSERT INTO `#__joomblog_plugins` SET id='{$plugin->id}'";
				$this->_db->setQuery($strSQL);
				$this->_db->execute();
			}
		}
	}

	public function getCurrDate()
	{
		$config = JComponentHelper::getParams('com_joomblog');
		$saved_date = $config->get('curr_date');
		if (strtotime("+2 month", strtotime($saved_date)) <= time())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Translate a list of objects
	 *
	 * @param   array The array of objects
	 * @return  array The array of translated objects
	 */
	protected function translate(&$items)
	{
		$lang = JFactory::getLanguage();

		foreach ($items as &$item)
		{
			$source = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->element;
			$extension = 'plg_' . $item->folder . '_' . $item->element;
			$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load($extension . '.sys', $source, null, false, true);
			$item->name = JText::_($item->name);
		}
	}

}
