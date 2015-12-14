<?php
/**
 * JoomBlog component for Joomla 1.6 & 1.7
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * JoomBlog Search plugin
 *
 */
class plgSearchJoomblog extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	function onContentSearchAreas()
	{
		static $areas = array(
			'joomblog' => 'PLG_SEARCH_JOOMBLOG_BLOG'
		);
		return $areas;
	}

	function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$tag = JFactory::getLanguage()->getTag();

		$searchText = $text;
		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		$limit = $this->params->def('search_limit', 50);

		$nullDate = $db->getNullDate();

		//Get current timezone
		$userTz = JFactory::getUser()->getParam('timezone');
		$timeZone = JFactory::getConfig()->get('offset');
		if ($userTz)
		{
			$timeZone = $userTz;
		}

		$date = JFactory::getDate('now', $timeZone);
		$now = $date->toSQL(true);

		$text = trim($text);
		if ($text == '')
		{
			return array();
		}

		$wheres = array();
		switch ($phrase)
		{
			case 'exact':
				$text = $db->Quote('%' . $db->escape($text, true) . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE ' . $text;
				$wheres2[] = 'a.introtext LIKE ' . $text;
				$wheres2[] = 'a.fulltext LIKE ' . $text;
				$wheres2[] = 'a.metakey LIKE ' . $text;
				$wheres2[] = 'a.metadesc LIKE ' . $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();
				foreach ($words as $word)
				{
					$word = $db->Quote('%' . $db->escape($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = 'a.title LIKE ' . $word;
					$wheres2[] = 'a.introtext LIKE ' . $word;
					$wheres2[] = 'a.fulltext LIKE ' . $word;
					$wheres2[] = 'a.metakey LIKE ' . $word;
					$wheres2[] = 'a.metadesc LIKE ' . $word;
					$wheres[] = implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		$morder = '';
		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.title ASC';
				$morder = 'a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
				break;
		}

		$rows = array();
		$query = $db->getQuery(true);

		// search articles
		if ($limit > 0)
		{
			$query->clear();
			$query->select('a.id, a.title AS title, a.metadesc, a.metakey, a.created AS created, p.posts, a.created_by, '
				. 'CONCAT(a.introtext, a.fulltext) AS text, c.title AS section, '
				. 'CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug, '
				. 'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as catslug, '
				. '"2" AS browsernav');
			$query->from('#__joomblog_posts AS a');

			$query->join('INNER', '#__joomblog_blogs AS b ON b.content_id = a.id');
			$query->join('LEFT', '#__joomblog_list_blogs AS lb ON lb.id = b.blog_id');

			$query->join('INNER', '#__joomblog_multicats AS mc ON mc.aid = a.id');
			$query->join('LEFT', '#__categories AS c ON c.id = mc.cid');

			$query->leftJoin(" `#__joomblog_privacy` AS `p` ON `p`.`postid`=`a`.`id` AND `p`.`isblog`=0 ");
			$query->where('(' . $where . ')' . 'AND a.state=1 AND c.published = 1 '
				. 'AND c.extension="com_joomblog" '
				. 'AND (a.publish_up = ' . $db->Quote($nullDate) . ' OR a.publish_up <= ' . $db->Quote($now) . ') '
				. 'AND (a.publish_down = ' . $db->Quote($nullDate) . ' OR a.publish_down >= ' . $db->Quote($now) . ')');
			// Filter by access level.
			if (!in_array('8', JFactory::getUser()->getAuthorisedGroups()))
			{
				if (!defined('JB_COM_PATH')) require_once(JPATH_ROOT . '/components/com_joomblog/defines.joomblog.php');
				require_once(JB_COM_PATH . '/task/base.php');
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
					$query->where('(a.access IN (' . $groups . ')
					    OR a.created_by=' . $user_id . ' ' . $tmpQ1 . ' ' . $tmpQ11 . ' )');
					$query->where('c.access IN (' . $groups . ')');
					$query->where('(lb.access IN (' . $groups . ')
					    OR lb.user_id=' . $user_id . ' ' . $tmpQ2 . ' ' . $tmpQ22 . ' )');
				}
			}
			$query->group('a.id');
			$query->order($order);

			// Filter by language
			if ($app->isSite() && JLanguageMultilang::isEnabled())
			{
				$query->where('a.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')');
				$query->where('c.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')');
			}
			$db->setQuery($query, 0, $limit);
			$list = $db->loadObjectList();
			$limit -= count($list);
			$query = $db->getQuery(true);
			$query->select('`id`');
			$query->from('#__menu');
			$query->where('`link`="index.php?option=com_joomblog&view=blogger"');
			$db->setQuery($query, 0, 1);
			$Itemid = $db->loadResult();
			$itemid = ($Itemid ? $Itemid : JRequest::getInt('Itemid'));
			if (isset($list))
			{
				foreach ($list as $key => $item)
				{
					$list[$key]->href = JRoute::_('index.php?option=com_joomblog&show=' . $item->id . '&Itemid=' . $itemid);
				}
			}
			$rows[] = $list;
		}
		$results = array();

		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$new_row = array();


				foreach ($row AS $key => $article)
				{

					if (isset($article))
						if (searchHelper::checkNoHTML($article, $searchText, array('text', 'title', 'metadesc', 'metakey')))
						{
							$new_row[] = $article;
						}
				}
				$results = array_merge($results, (array) $new_row);
			}
		}
		return $results;
	}

	protected function isFriends($id = 0)
	{
		if (!$id) return false;
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$id1 = $user->id;

		$db->setQuery(" SELECT `user_id`  FROM `#__joomblog_list_blogs` WHERE id=" . $id);
		$id2 = $db->loadResult();
		if ($id1 && $id2)
		{
			$db->setQuery(" SELECT `connection_id` FROM `#__community_connection` " .
				" WHERE connect_from=" . (int) $id1 . " AND connect_to=" . (int) $id2 . " AND `status`=1 ");
			$frindic = $db->loadResult();

			if ($frindic) return true;
			else return false;
		}
		return false;
	}
}
