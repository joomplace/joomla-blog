<?php
/**
* JoomBlog Archive Module for Joomla
* @version $Id: helper.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage helper.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

if(!defined('JB_COM_PATH'))
	require_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'defines.joomblog.php');

require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'config.joomblog.php');
require_once(JB_COM_PATH.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'datamanager.php');
require_once(JB_COM_PATH.DIRECTORY_SEPARATOR.'functions.joomblog.php');
require_once (JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'router.php');


class modJbArchivePostsHelper
{
	function getList(&$params)
	{
    global $_JB_CONFIGURATION;
	
		$db = &JFactory::getDBO();
		
    $managedSections = $_JB_CONFIGURATION->managedSections;

    
    $posts_type = $params->get('posts_type');
    if (!isset($posts_type)) $posts_type = 1;

	
    if(!isset($managedSections)){
      $query = "SELECT value FROM #__joomblog_config WHERE name='all' LIMIT 1 ";
      $db->setQuery($query);
      $config = $db->loadResult();
      eval($config);
    }
    
     $sections = implode(",",jbGetCategoryArray($managedSections));
    
		$where = " AND ( c.catid IN ( ".$sections." ) ) ";
			$sql = " 
     		SELECT  
        	DATE_FORMAT( c.created,'%M') AS monthname,
        	MONTH( c.created ) AS created_month, 
        	YEAR( c.created ) AS created_year
      		FROM #__joomblog_posts AS c
        	WHERE c.state = 1 AND c.created < NOW() $where
      		GROUP BY created_year DESC, created_month DESC ";
		//,  count( c.created ) AS count_month
		$db->setQuery($sql);
		$posts = $db->loadObjectList();
		
		$list = array();
		if($posts){
			foreach($posts as $value){
			    list($value->count_month, $value->posts) = modJbArchivePostsHelper::getCountByYearAndMonth($value->created_year, $value->created_month);
			    $list[$value->created_year]['month'][] = $value;
			    if(!empty($list[$value->created_year]['count'])) $list[$value->created_year]['count'] += $value->count_month;
			    else $list[$value->created_year]['count'] = $value->count_month;	
			}
		}
		return $list;
	}
	
	function getCountByYearAndMonth($created_year=0, $created_month=0)
	{
	    $filter = array(
                'limit'=> 5,
                'limitstart' => 0,
                'created_month' => $created_month,
                'created_year' => $created_year,
		'setState' => true,
              );
	    $entries = mb_get_entries($filter, true);
	    return $entries;
	}
	
	function getItemid(){
		$Itemid = JRequest::getInt('Itemid');
	
        $menu = JFactory::getApplication()->getMenu();
		$items	= $menu->getItems('link', 'index.php?option=com_joomblog&view=default');
		
		return $items?$items[0]->id:$Itemid;
	}
}
