<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

require_once(JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'base.php');

class JbblogViewtagsTask extends JbblogBaseController
{

	function __construct()
	{
		$this->toolbar = JB_TOOLBAR_BLOGGER;
	}

	function display()
	{
		global $_JB_CONFIGURATION, $Itemid;

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;
		$post = JInput::get('post');
		$option = 'com_joomblog';
		$trigger = 0;
		$sort = $mainframe->getUserStateFromRequest($option . 'sort', 'sort', 'name', 'word');
		$order = $mainframe->getUserStateFromRequest($option . 'order', 'order', 'asc', 'word');
		$filer_tags = $jinput->get('filter-tags', '', 'string');
		if ($sort == 'weight')
		{
			$sort = 'name';
			$trigger = 1;
		}

		$like = '';
		if (!empty($filer_tags))
		{
			$like = " t.name LIKE '%" . $filer_tags . "%'";
		}
		$query = JFactory::getDbo()->getQuery(true);
		$query->select("t.name, COUNT(DISTINCT a.id) as count");
		$query->from("#__joomblog_content_tags as ct");
		$query->join("LEFT", "#__joomblog_posts as a ON a.id = ct.contentid");
		$query->join("LEFT", "#__joomblog_tags as t ON t.id = ct.tag");
		$query->join('LEFT', '#__joomblog_multicats AS mc ON mc.aid = a.id');
		$query->join('LEFT', '#__categories AS c ON c.id = mc.cid');
		$query->join('LEFT', '#__joomblog_blogs AS b ON b.content_id = a.id');
		$query->join('LEFT', '#__joomblog_list_blogs AS lb ON lb.id = b.blog_id');
		$query->where("t.name <> ''");
		if (!empty($like)) $query->where($like);
		$query->group("ct.tag");
		$query->order("t.$sort $order");

		//acccess check to show correct tags
		if (!in_array('8', JFactory::getUser()->getAuthorisedGroups()))
		{
			$user = JFactory::getUser();
			$user_id = (int) $user->id;
			$groups = implode(',', $user->getAuthorisedViewLevels());
			if (!JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false))
			{
				$query->where('(a.access IN (' . $groups . ') OR a.created_by=' . $user_id . ')');
				$query->where('c.access IN (' . $groups . ')');
				$query->where('(lb.access IN (' . $groups . ') OR lb.user_id=' . $user_id . ')');
			}
			else
			{
				$userJSGroups = JbblogBaseController::getJSGroups($user->id);
				$userJSFriends = JbblogBaseController::getJSFriends($user->id);
				if (count($userJSGroups) > 0)
				{
					$tmpQ1 = ' OR (a.access=-4 AND a.access_gr IN (' . implode(',', $userJSGroups) . ')) ';
					$tmpQ2 = ' OR (lb.access=-4 AND lb.access_gr IN (' . implode(',', $userJSGroups) . ')) ';
				}
				else
				{
					$tmpQ1 = ' ';
					$tmpQ2 = '';
				}
				if (count($userJSFriends) > 0)
				{
					$tmpQ11 = ' OR (a.access=-2 AND a.created_by IN (' . implode(',', $userJSFriends) . ')) ';
					$tmpQ22 = ' OR (lb.access=-2 AND lb.user_id IN (' . implode(',', $userJSFriends) . ')) ';
				}
				else
				{
					$tmpQ11 = ' ';
					$tmpQ22 = '';
				}
				$query->where('(a.access IN (' . $groups . ') OR a.created_by=' . $user_id . ' ' . $tmpQ1 . ' ' . $tmpQ11 . ' )');
				$query->where('c.access IN (' . $groups . ')');
				$query->where('(lb.access IN (' . $groups . ') OR lb.user_id=' . $user_id . ' ' . $tmpQ2 . ' ' . $tmpQ22 . ' )');
			}
		}

		$db->setQuery($query);
		$tags = $db->loadObjectList();
		if (count($tags) && $trigger)
		{
			usort($tags, array("JbblogViewtagsTask", "cmpSort"));
			if ($order == 'asc')
			{
				ksort($tags);
			}
			else if ($order == 'desc')
			{
				krsort($tags);
			}
		}
		$tag = array();
		$tag[0] = $tags;
		$template = new JoomblogTemplate();
		$template->set('Itemid', $Itemid);
		$template->set('tags', $tag);
		$template->set('filtertags', !empty($filer_tags) ? $filer_tags : '');
		$content = $template->fetch($this->_getTemplateName('viewtags'));

		return $content;

	}

	protected function getRowsByPrivacyFilter($rows = null)
	{
		$user = JFactory::getUser();
		if (sizeof($rows))
		{
			for ($i = 0, $n = sizeof($rows); $i < $n; $i++)
			{
				$post = $rows[$i];
				$post->posts ? $post->posts : $post->posts = 0;
				if (!isset($post->blogtitle))
				{
					$rows[$i]->count--;
					continue;
				}
				switch ($post->posts)
				{
					case 0:
						break;
					case 1:
						if (!$user->id)
						{
							$rows[$i]->count--;
						}
						break;
					case 2:
						if (!$user->id)
						{
							unset($rows[$i]);
						}
						else
						{
							if (!$this->isFriends($user->id, $post->created_by) && $user->id != $post->created_by)
							{
								$rows[$i]->count--;
							}
						}
						break;
					case 3:
						if (!$user->id)
						{
							$rows[$i]->count--;
						}
						else
						{
							if ($user->id != $post->created_by)
							{
								$rows[$i]->count--;
							}
						}
						break;
					case 4:
						$rows[$i]->count--;
						break;
				}
			}
		}
		return $rows;
	}

	protected function isFriends($id1 = 0, $id2 = 0)
	{
		$db = JFactory::getDBO();
		$db->setQuery(" SELECT `connection_id` FROM `#__community_connection` " .
			" WHERE connect_from=" . (int) $id1 . " AND connect_to=" . (int) $id2 . " AND `status`=1 ");
		$frindic = $db->loadResult();
		if ($frindic) return true;
		else return false;
	}

	function _getBlogs(&$rows)
	{

		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		if (count($rows))
		{
			for ($i = 0; $i < count($rows); $i++)
			{
				$row =& $rows[$i];

				$db->setQuery(" SELECT b.blog_id, lb.title,p.posts, p.comments, p.jsviewgroup   " .
					" FROM #__joomblog_blogs as b, #__joomblog_list_blogs as lb " .
					" LEFT JOIN `#__joomblog_privacy` AS `p` ON `p`.`postid`=`lb`.`id` AND `p`.`isblog`=1 " .
					" WHERE b.content_id=" . $row->id . " AND lb.id = b.blog_id AND lb.approved=1 AND lb.published=1 ");
				$blogs = $db->loadObjectList();
				if (sizeof($blogs))
				{
					switch ($blogs[0]->posts)
					{
						case 0:	break;
						case 1:
							if (!$user->id)
							{
								$row->posts = $blogs[0]->posts;
							}
							break;
						case 2:
							if (!$user->id)
							{
								$row->posts = $blogs[0]->posts;
							}
							else
							{
								if (!$this->isFriends($user->id, $row->created_by) && $user->id != $row->created_by)
								{
									$row->posts = $blogs[0]->posts;
								}
							}
							break;
						case 3:
							if (!$user->id)
							{
								$row->posts = $blogs[0]->posts;
							}
							else
							{
								if ($user->id != $row->created_by)
								{
									$row->posts = $blogs[0]->posts;
								}
							}
							break;
						case 4:
							if (!$user->id)
							{
								$row->posts = $blogs[0]->posts;
							}
							else
							{
								if (!jbInJSgroup($user->id, $blogs[0]->jsviewgroup))
								{
									$row->posts = $blogs[0]->posts;
								}
								else $row->posts = 0;
							}
							break;
					}
					$row->blogid = $blogs[0]->blog_id;
					$row->blogtitle = $blogs[0]->title;
				}
			}
		}
	}

	function cmpSort($a, $b)
	{

		if ($a->count == $b->count)
		{
			return 0;
		}

		return ($a->count < $b->count) ? -1 : +1;
	}
}

