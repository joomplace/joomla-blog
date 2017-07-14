<?php
/**
* JoomBlog Latest Post Module for Joomla
* @version $Id: helper.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage helper.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class modJbLatestPostsHelper
{
	public static function getList(&$params)
	{
		$limit = $params->get('numLatestEntries', 5);
    	$titleMaxLength = $params->get('titleMaxLength', 20);
        $app = JFactory::getApplication();
        $user = $app->input->get('user', '');

    	$authorid	= jbGetAuthorId( $user );
    
		if($authorid == '0')
		  $authorid	= '';

		if (!is_numeric($limit))
		  $limit = 5;

    	if( function_exists('mb_get_entries'))
		{
      		$filter = array(
                'limit'=> $limit,
                'limitstart' => 0,
                'authorid' => $authorid,
				'setState' => 1
              );

			switch ( (int)$params->get('orderByPost', 0) )
			{
				case 1:
					$filter['ordering'] = 'a.created';
					$filter['ordering_direction'] = 'ASC';
				break;
				case 2:
					$filter['ordering'] = 'a.hits';
					$filter['ordering_direction'] = 'DESC';
				break;
				case 3:
					$filter['ordering'] = 'a.hits';
					$filter['ordering_direction'] = 'ASC';
				break;
			}

			if ($params->get('displayMode') == 2)
			{
				$filter['setState'] = 1;
				$filter['catid'] = $params->get('jbCat');
			}

      		$entries = mb_get_entries($filter);
   		}
	    else
	    {
      		$objDataMngr = new JB_DataManager();
      		$entries = $objDataMngr->getEntries($total,$limit,0,$authorid);
    	}
	
		return $entries;
	}
}
