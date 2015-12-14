<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.filesystem.file' );

function mb_get_entries(&$searchby, $returnTotal = false)
{
	global $sectionid, $_JB_CONFIGURATION;

	require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'defines.joomblog.php');
    require_once(JB_COM_PATH.DIRECTORY_SEPARATOR.'task'.DIRECTORY_SEPARATOR.'base.php');

	$limit 		= isset($searchby['limit']) 	 ? intval($searchby['limit']): 10;
	$limitstart = isset($searchby['limitstart']) ? intval($searchby['limitstart']): 0;
	$model = JbblogBaseController::getModel('Posts', 'JoomblogModel', true, false);
    $state = $model->getState();

    if(!empty($searchby['setState'])){
		if(!empty($searchby['author_id'])) $model->setState('filter.author_id', $searchby['author_id']);
		if(!empty($searchby['catid'])) $model->setState('filter.category_id', $searchby['catid']);
		if(!empty($searchby['blog_id'])) $model->setState('filter.blogid', $searchby['blog_id']);
		if(!empty($searchby['created_month'])) $model->setState('filter.created_month', $searchby['created_month']);
		if(!empty($searchby['created_year'])) $model->setState('filter.created_year', $searchby['created_year']);

		if(!empty($searchby['ordering'])) {
			$model->setState('list.ordering', $searchby['ordering']);
			$model->setState('list.direction', $searchby['ordering_direction']);
		}

		$model->setState('list.start', $limitstart);
		$model->setState('list.limit', $limit);
    }

    $rows = $model->getItems();
    $totalEntries = $model->getTotal();
	
	
	    $v = 0;
	
	if($rows AND count($rows) > 0)
	{
		$url	= rtrim( JURI::root() , '/' );

		for($i = 0; $i < count($rows); $i++ )
		{
			$rows[$i]->permalink = jbGetPermalinkUrl($rows[$i]->id);
			$rows[$i]->introtext = str_replace('src="images', 'src="'. $url .'/images', $rows[$i]->introtext );
			$rows[$i]->fulltext = str_replace('src="images', 'src="'. $url .'/images',  $rows[$i]->fulltext );
		}
			$rows = array_values($rows);	
	}
	if(!$returnTotal) return $rows;
	else return array($totalEntries, $rows);
}