<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.database.table');

class TableBlog extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__joomblog_list_blogs', 'id', $db);
	}

	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		return 'com_joomblog.blog.' . (int) $this->$k;
	}

	protected function _getAssetTitle()
	{
		return $this->title;
	}

	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$assetId = null;
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_joomblog');
		return $asset->id;
	}

	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}
		return parent::bind($array, $ignore);
	}

	public function delete($pk = null)
	{
		$db = $this->getDBO();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__joomblog_blogs');
		$query->where('blog_id=' . $pk);

		$db->setQuery($query);
		$ids = $db->loadObjectList();

		if ($ids)
		{
			$content = JTable::getInstance('Posts', 'Table', array());
			foreach ($ids as $id)
			{
				$content->delete($id);
			}
		}

		return parent::delete($pk);
	}

	public function store($updateNulls = false)
	{
		if (parent::store($updateNulls))
		{
			if ($this->id)
			{
				$db = JFactory::getDBO();
				$jform = JRequest::getVar('jform');
				if ($jform['access'] == -4)
				{
					$js_group = (int) $jform['access_gr'];
					$query = "UPDATE `#__joomblog_list_blogs` SET access_gr=$js_group WHERE `id`='" . $this->id . "' ";
					$db->setQuery($query);
					$db->execute();
				}
				if ($jform['waccess'] == -4)
				{
					$js_group = (int) $jform['waccess_gr'];
					$query = "UPDATE `#__joomblog_list_blogs` SET waccess_gr=$js_group WHERE `id`='" . $this->id . "' ";
					$db->setQuery($query);
					$db->execute();

				}
			}
			return true;
		}
		return false;
	}

	public function checkAlias()
	{
		$alias = $this->alias;
		if (empty($alias)) $alias = trim(jbTitleToLink($this->title));

		if (trim(str_replace('-', '', $alias)) == '')
		{
			$alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}
		$table = JTable::getInstance('Blog', 'Table');
		if ($table->load(array('alias' => $alias)) && ($table->id != $this->id || $this->id == 0))
		{
			return false;
		}
		$this->alias = $alias;
		return true;
	}
}