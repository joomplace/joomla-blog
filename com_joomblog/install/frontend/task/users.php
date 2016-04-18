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
require_once(JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'avatar.php');

class JbblogUsersTask extends JbblogBaseController
{
	function JbblogUsersTask()
	{
		$this->toolbar = JB_TOOLBAR_BLOGGER;
	}

	function display()
	{
		global $JBBLOG_LANG, $_JB_CONFIGURATION, $Itemid;

		$option = 'com_joomblog';
		$mainframe = JFactory::getApplication();
		jbAddPathway(JText::_('COM_JOOMBLOG_ALL_BLOGS_TITLE'));
		jbAddPageTitle(JText::_('COM_JOOMBLOG_ALL_BLOGS_TITLE'));
		$limit = $mainframe->getUserStateFromRequest($option . 'limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option . 'limitstart', 'limitstart', 0, 'int');
		$db = JFactory::getDBO();
		$query = JFactory::getDbo()->getQuery(true);
		$query->select("u.username, u.name, ub.*");
		$query->from("#__users as u");
		$query->join("LEFT", "#__joomblog_user AS ub ON ub.user_id=u.id");
		switch ($_JB_CONFIGURATION->get('showAsAuthors', 0))
		{
			case '1':
				$query->join("INNER", "#__joomblog_posts as a ON a.created_by = u.id");
				$query->where('a.state = 1');
				break;
			case '2':
				$query->join('INNER', '#__joomblog_list_blogs AS lb ON lb.user_id = u.id');
				break;
			case '0':
			default:
				break;
		}

		$query->where('u.block = 0');
		$query->group("u.id");
		$db->setQuery($query);
		$users = $db->loadObjectList();
		$total = count($users);
		$pageNav = new JBPagination($total, $limitstart, $limit);

		if (count($users) > $limit)
		{
			$db->setQuery($query, $limitstart, $limit);
			$users = $db->loadObjectList();
		}

		$bloggersHTML = '';
		if (!empty($users))
		{
			foreach ($users as $user)
			{

				$avatar = 'Jb' . ucfirst($_JB_CONFIGURATION->get('avatar')) . 'Avatar';
				$avatar = new $avatar($user->user_id, 0);
				$user->src = $avatar->get();

				if ($_JB_CONFIGURATION->get('integrJoomSoc') && file_exists(JPATH_ROOT . '/components/com_community/libraries/core.php'))
				{
					include_once JPATH_ROOT . '/components/com_community/libraries/core.php';
					// Get CUser object
					$user->link = CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->user_id);
				}
				else
				{
					$user->link = JRoute::_("index.php?option=com_joomblog&task=profile&id={$user->user_id}&Itemid=$Itemid");
				}
				$postsModel = $this->getModel('Posts', 'JoomblogModel', true);
				$postsModel->setState('list.limit', $_JB_CONFIGURATION->get('BloggerRecentPosts'));
				$postsModel->setState('filter.author_id', $user->user_id);

				$bloggers = $postsModel->getItems();
				foreach ($bloggers as $i => $blogger)
				{
					$bloggers[$i]->bid = $bloggers[$i]->blogid;
					$bloggers[$i]->btitle = $bloggers[$i]->blogtitle;
					$bloggers[$i]->multicats = false;
					$cats = jbGetMultiCats($bloggers[$i]->id);

					if (sizeof($cats))
					{
						$jcategories = array();
						foreach ($cats as $cat)
						{
							$catlink = JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $cat . '&Itemid=' . $Itemid);
							$jcategories [] = ' <a class="category" href="' . $catlink . '">' . jbGetJoomlaCategoryName($cat) . '</a> ';
						}
						if (sizeof($jcategories) > 1) $bloggers[$i]->multicats = true;
						if (sizeof($jcategories)) $bloggers[$i]->jcategory = implode(',', $jcategories);

					}
					else $bloggers[$i]->jcategory = '<a class="category" href="' . JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $bloggers[$i]->catid . '&Itemid=' . $Itemid . $tmpl) . '">' . jbGetJoomlaCategoryName($bloggers[$i]->catid) . '</a>';


					$bloggers[$i]->categories = jbCategoriesURLGet($bloggers[$i]->id, true);
				}
				$this->totalEntries = $postsModel->getTotal();


				$template = new JoomblogTemplate();
				$man = array();
				$recent = array();
				$man[0] = $user;
				$recent[0] = $bloggers;

				$template->set('recent', $recent);
				$template->set('Itemid', $Itemid);
				$template->set('man', $man);
				$template->set('totalArticle', $this->totalEntries);
				$template->set('categoryDisplay', $_JB_CONFIGURATION->get('categoryDisplay'));

				$bloggersHTML .= $template->fetch($this->_getTemplateName('users'));

				unset($template);
			}
			$content = '<div id="jblog-section" class="jb-section">' . JText::_('COM_JOOMBLOG_BLOGGERS') . '</div><div id="bloggers">' . $bloggersHTML . '</div>';
			$content .= '<div class="jb-pagenav">' . $pageNav->getPagesLinks() . '</div>';
			return $content;
		}
		else {
				$content = '<div id="jblog-section" class="jb-section">' . JText::_('COM_JOOMBLOG_BLOGGERS_NOT_FOUND') . '</div><div id="bloggers">' . $bloggersHTML . '</div>';

				return $content;
			}
		}
	}

	protected function getRowsByPrivacyFilter($rows = null)
	{
		$user = JFactory::getUser();
		if (sizeof($rows))
		{
			for ($i = 0, $n = sizeof($rows); $i < $n; $i++)
			{

				$post = & $rows[$i];
				$post->posts ? $post->posts : $post->posts = 0;
				switch ($post->posts)
				{
					case 0:
						break;
					case 1:
						if (!$user->id)
						{
							$rows[$i] = null;
							unset($rows[$i]);
							$this->totalEntries--;
						}
						break;
					case 2:
						if (!$user->id)
						{
							unset($rows[$i]);
							$this->totalEntries--;
						}
						else
						{
							if (!$this->isFriends($user->id, $post->created_by) && $user->id != $post->created_by)
							{
								unset($rows[$i]);
								$this->totalEntries--;
							}
						}
						break;
					case 3:
						if (!$user->id)
						{
							unset($rows[$i]);
							$this->totalEntries--;
						}
						else
						{
							if ($user->id != $post->created_by)
							{
								unset($rows[$i]);
								$this->totalEntries--;
							}
						}
						break;
					case 4:
						unset($rows[$i]);
						$this->totalEntries--;
						break;
				}
				if (!isset($post->btitle) && isset($rows[$i]))
				{
					unset($rows[$i]);
					$this->totalEntries--;
				}
			}
			$rows = array_values($rows);
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

		global $Itemid;
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		if (count($rows))
		{
			for ($i = 0; $i < count($rows); $i++)
			{
				$row =& $rows[$i];

				$db->setQuery("SELECT b.blog_id as bid, lb.title as btitle, p.posts, p.jsviewgroup " .
					" FROM #__joomblog_blogs as b, #__joomblog_list_blogs as lb " .
					" LEFT JOIN `#__joomblog_privacy` AS `p` ON `p`.`postid`=`lb`.`id` AND `p`.`isblog`=1 " .
					" WHERE b.content_id=" . $row->id . " AND lb.id = b.blog_id");
				$blogs = $db->loadObjectList();
				if (sizeof($blogs))
				{
					switch ($blogs[0]->posts)
					{
						case 0:
							break;
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
					$row->bid = $blogs[0]->bid;
					$row->btitle = $blogs[0]->btitle;
				}

				$row->multicats = false;
				$cats = jbGetMultiCats($row->id);

				if (sizeof($cats))
				{
					$jcategories = array();
					foreach ($cats as $cat)
					{
						$catlink = JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $cat . '&Itemid=' . $Itemid);
						$jcategories [] = ' <a class="category" href="' . $catlink . '">' . jbGetJoomlaCategoryName($cat) . '</a> ';
					}
					if (sizeof($jcategories) > 1) $row->multicats = true;
					if (sizeof($jcategories)) $row->jcategory = implode(',', $jcategories);

				}
				else $row->jcategory = '<a class="category" href="' . JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $row->catid . '&Itemid=' . $Itemid . $tmpl) . '">' . jbGetJoomlaCategoryName($row->catid) . '</a>';


				$row->categories = jbCategoriesURLGet($row->id, true);
			}
		}
	}

}
