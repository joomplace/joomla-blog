<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgContentJoomblogcontent extends JPlugin
{
	public function onContentBeforeDelete($context, $data)
	{
		if ($context != 'com_categories.category' and $context != 'com_joomblog.blog')
		{
			return true;
		}

		$result = true;

		if ($context == 'com_categories.category')
		{
			$extension = JRequest::getString('extension');
			if ($extension == 'com_joomblog')
			{
				$table = '#__joomblog_posts';
				$count = $this->_countItemsInCategory($table, $data->get('id'));
				if ($count === false)
				{
					$result = false;
				}
				else
				{
					if ($count > 0)
					{
						$msg = JText::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title')) .
							JText::plural('COM_CATEGORIES_N_ITEMS_ASSIGNED', $count);
						JError::raiseWarning(403, $msg);
						$result = false;
					}
					if (!$data->isLeaf())
					{
						$count = $this->_countItemsInChildren($table, $data->get('id'), $data);
						if ($count === false)
						{
							$result = false;
						}
						elseif ($count > 0)
						{
							$msg = JText::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title')) .
								JText::plural('COM_CATEGORIES_HAS_SUBCATEGORY_ITEMS', $count);
							JError::raiseWarning(403, $msg);
							$result = false;
						}
					}
				}
			}
		}

		if ($context == 'com_joomblog.blog')
		{
			$table = '#__joomblog_blogs';
			$count = $this->_countItemsInBlog($table, $data->get('id'));
			if ($count > 0)
			{
				$result = false;
			}
			else
			{
				$result = true;
			}
		}

		return $result;
	}

	private function _countItemsInCategory($table, $catid)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)');
		$query->from($table);
		$query->where('catid = ' . $catid);
		$db->setQuery($query);
		$count = $db->loadResult();

		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return false;
		}
		else
		{
			return $count;
		}
	}

	private function _countItemsInChildren($table, $catid, $data)
	{
		$db = JFactory::getDbo();
		$childCategoryTree = $data->getTree();
		unset($childCategoryTree[0]);
		$childCategoryIds = array();
		foreach ($childCategoryTree as $node)
		{
			$childCategoryIds[] = $node->id;
		}

		if (count($childCategoryIds))
		{
			$query = $db->getQuery(true);
			$query->select('COUNT(id)');
			$query->from($table);
			$query->where('catid IN (' . implode(',', $childCategoryIds) . ')');
			$db->setQuery($query);
			$count = $db->loadResult();

			if ($error = $db->getErrorMsg())
			{
				JError::raiseWarning(500, $error);
				return false;
			}
			else
			{
				return $count;
			}
		}
		else
		{
			return 0;
		}
	}

	protected function _countItemsInBlog($table, $blogid)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)');
		$query->from($table);
		$query->where('blog_id = ' . $blogid);
		$db->setQuery($query);
		$count = $db->loadResult();

		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return false;
		}
		else
		{
			return $count;
		}
	}
}
