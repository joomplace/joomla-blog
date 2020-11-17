<?php
/**
 * JoomBlog Popular Bloggers Module for Joomla
 * @version    $Id: helper.php 2011-03-16 17:30:15
 * @package    JoomBlog
 * @subpackage helper.php
 * @author     JoomPlace Team
 * @Copyright  Copyright (C) JoomPlace, www.joomplace.com
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class modJbPopularbloggersHelper
{
    public static function getList(&$params)
	{
		global $_JB_CONFIGURATION;

		$limit = $params->get('numPopularBlogs', 5);
		$db = JFactory::getDBO();

		if (!is_numeric($limit))
			$limit = 5;

		$sections = implode(",", jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));

		$strSQL = "SELECT `created_by`, sum(hits) AS `hits` "
			. "FROM #__joomblog_posts WHERE `catid` IN ({$sections}) "
			. "AND `state`='1' GROUP BY `created_by` "
			. "ORDER BY `hits` DESC "
			. "LIMIT 0,{$limit}";

		$db->setQuery($strSQL);
		$rows = $db->loadObjectList();

		return $rows;
	}

    public static function getCountPosts($uid)
	{
		global $_JB_CONFIGURATION;

		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		//Get current timezone
		$userTz = JFactory::getUser()->getParam('timezone');
		$timeZone = JFactory::getConfig()->get('offset');
		if ($userTz)
		{
			$timeZone = $userTz;
		}

		$date = JFactory::getDate('now', $timeZone);

		$sections = implode(",", jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));

		$strSQL = "SELECT a.*, p.posts " .
			" FROM #__joomblog_posts AS `a` " .
			" LEFT JOIN `#__joomblog_privacy` AS `p` ON `p`.`postid`=`a`.`id` AND `p`.`isblog`=0 " .
			" WHERE a.`created_by`='{$uid}' AND a.`catid` IN ({$sections}) AND a.`state` != '0' AND a.`created` <= " . $db->Quote($date->toSql(true));
		$db->setQuery($strSQL);
		$rows = $db->loadObjectList();

		if (!empty($rows))
		{
			for ($i = 0; $i < count($rows); $i++)
			{
				$row = & $rows[$i];
				$db->setQuery(" SELECT b.blog_id as bid, lb.title as btitle, p.posts, p.comments  " .
					" FROM #__joomblog_blogs as b, #__joomblog_list_blogs as lb " .
					" LEFT JOIN `#__joomblog_privacy` AS `p` ON `p`.`postid`=`lb`.`id` AND `p`.`isblog`=1 " .
					" WHERE b.content_id=" . $row->id . " AND lb.id = b.blog_id AND lb.approved=1 AND lb.published=1 ");
				$blogs = $db->loadObjectList();
				if (!empty($blogs))
				{
					switch ($blogs[0]->posts)
					{
						case 0:
							break;
						case 1:
							if (!$user->id)
							{
								unset($rows[$i]);
							}
							break;
						case 2:
							if (!$user->id)
							{
								unset($rows[$i]);
							}
							else
							{
								if (!isFriends($user->id, $row->created_by) && $user->id != $row->created_by)
								{
									unset($rows[$i]);
								}
							}
							break;
						case 3:
							if (!$user->id)
							{
								unset($rows[$i]);
							}
							else
							{
								if ($user->id != $row->created_by)
								{
									unset($rows[$i]);
								}
							}
							break;
					}
				}
				if (isset($rows[$i]))
					switch ($row->posts)
					{
						case 0:
							break;
						case 1:
							if (!$user->id)
							{
								unset($rows[$i]);
							}
							break;
						case 2:
							if (!$user->id)
							{
								unset($rows[$i]);
							}
							else
							{
								if (!isFriends($user->id, $row->created_by) && $user->id != $row->created_by)
								{
									unset($rows[$i]);
								}
							}
							break;
						case 3:
							if (!$user->id)
							{
								unset($rows[$i]);
							}
							else
							{
								if ($user->id != $row->created_by)
								{
									unset($rows[$i]);
								}
							}
							break;
					}
				//$rows = array_values($rows);
			}
		}

		$count = count($rows);

		return $count;
	}
}
