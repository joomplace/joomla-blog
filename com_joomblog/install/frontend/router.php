<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once('functions.joomblog.php' );

function JoomblogBuildRoute( &$query )
{
	$mainframe	= JFactory::getApplication();
	$segments = array();
	$db	= JFactory::getDBO();
	$admintask = array(
		'adminhome',
		'bloggerpref',
		'bloggerstats',
		'dashboard',
		'showcomments');
		
	$endWithSlash = array();

	if (isset($query['task']) && $query['task'] != 'tag') {
		if($query['task'] == 'tagslist'){
			$query['task'] = 'tagslist';
		}

		if(isset($query['task']) AND ($query['task'] == 'adminhome' OR $query['task'] == 'showcomments' OR $query['task'] == 'bloggerstats' OR $query['task'] == 'editblog') && isset($query['blogid'])){
			$segments[] = $query['task'];
			unset($query['task']);
			$segments[] = $query['blogid'];
			unset($query['blogid']);
		}

		if (isset($query['task']) AND isset($query['id']) AND $query['task']=='preview') {
		$segments[] = 'preview';
		unset($query['task']);
		$segments[] = jbGetPost($query['id'])->alias /*.'/' */;		
		unset($query['id']);
		} 

		if(isset($query['task']) AND $query['task'] == 'categorylist'){
			$query['task'] = 'categorylist';
		}
		
		if( isset($query['task']) AND $query['task'] == 'rss'){
			$query['task'] = 'feed';
		}
		
		if (isset($query['task']) AND $query['task'] == 'profile') {
			$segments[] = 'profile';
			$segments[] = jbUserGetName($query['id']);
			unset($query['id']);
			unset($query['task']);
		}

		if(isset($query['task']) && in_array($query['task'], $admintask)){	
			if (isset($query['amp;Itemid'])) {
				unset($query['amp;Itemid']);
			}
		}
		
		if($mainframe->getCfg('sef') && $mainframe->getCfg('sef_suffix')){
			if(isset($query['task']) && strlen($query['task']) > 5 && (substr($query['task'], -5) == '.html')){
				$query['task'] = substr($query['task'], 0, -5);
			}
		}
		
		if(isset($query['task']) && (in_array($query['task'], $endWithSlash) || 
			in_array($query['task'], $admintask))){
			$query['task'] /*.= '/' */;
		}

		if(isset($query['task']) && $query['task'] == 'write') {
		if ($query['id'] > 0) {
			$db	= JFactory::getDBO();
			$sql = "SELECT `alias` FROM #__joomblog_posts WHERE id = '".(int)$query['id']."' ";
			$db->setQuery( $sql );
			$post_alias=$db->loadResult();
			$segments[] = 'write';
			$segments[] = $post_alias;
			if (isset($query['blogid']))
			{
			$sql = "SELECT `alias` FROM #__joomblog_list_blogs WHERE id = '".(int)$query['blogid']."' ";
			$db->setQuery( $sql );
			$blog_alias=$db->loadResult();
			$segments[] = $blog_alias;
			unset($query['blogid']);
			}
			unset($query['id']);
			unset($query['task']);
			
		}
		else {
			$db	= JFactory::getDBO();
			$segments[] = 'write';
			$segments[] = 'new-post';
			if (isset($query['blogid']))
			{
			$sql = "SELECT `alias` FROM #__joomblog_list_blogs WHERE id = '".(int)$query['blogid']."' ";
			$db->setQuery( $sql );
			$blog_alias=$db->loadResult();
			$segments[] = $blog_alias;
			unset($query['blogid']);
			}
			unset($query['id']);
			unset($query['task']);
		}
		}
	
		
		if(isset($query['task'])){
			$segments[] = $query['task'];
			unset($query['task']);
		}
	} else
	
	if (isset($query['show'])) {
		$segments[] = 'post';
		$segments[] = jbGetPost($query['show'])->alias /*.'/' */;
		unset($query['show']);
	} else
	
	if (isset($query['category'])) {
		$segments[] = 'category';
		$category = jbGetCategory($query['category']);
		if(is_object($category)){
		$segments[] = jbGetCategory($query['category'])->alias /*."/" */;
		    unset($query['category']);
		}

		if(isset($query['task'])) unset($query['task']);
	} else
	
	if (isset($query['tag'])) {
		$segments[] = 'tag';
		$query['tag'] = str_replace(' ', '-', $query['tag']);
		$segments[] = $query['tag'] /*. '/' */;
		unset($query['tag']);
		if(isset($query['task'])) unset($query['task']);
	} else

	if (isset($query['user'])) {
		$segments[] = 'user';
		$segments[] = $query['user'] /*.'/' */;
		unset($query['user']);
	} else
	
	if(in_array('delete', $segments)){
		$segments[] = $query['id'];
		unset($query['id']);
	} else {
	
	}
	
	if (isset($query['view']))
	{
		if ($query['view']=="default") $segments[] = 'mainpage';
		unset($query['view']);
	}

	if (isset($query['blogid']))
	{
		$segments[] = 'blog';
		$db	= JFactory::getDBO();
		$sql = "SELECT `alias` FROM #__joomblog_list_blogs WHERE id = '".(int)$query['blogid']."' ";
		$db->setQuery( $sql );
		$result=$db->loadResult();
		$segments[] = $result;
		unset($query['blogid']);
	}

	return $segments;
}

function JoomblogParseRoute( $segments )
{
	$db	= JFactory::getDBO();
	$vars = array();
	$admintask = array(
		'adminhome',
		'bloggerpref',
		'dashboard',
		'bloggerstats',
		'showcomments'
	);
	
	$actions = array(
		'cpage',
		'tagslist',
		'categorylist',
		'archive',
		'feed',
		'search',
		'blogs'
	);
  
	if(isset($segments[0])){		

		for($i = 0; $i < count($segments); $i++){
			if(strlen($segments[$i]) > 5 && substr($segments[$i], -5) == '.html'){
				$segments[$i] = substr($segments[$i], 0, -5);
			}
		}
		
		if(($segments[0] == 'adminhome' OR $segments[0] == 'showcomments' OR $segments[0] == 'bloggerstats' OR $segments[0] == 'editblog') && isset($segments[1])){
			$vars['task']	= $segments[0];
			$vars['blogid'] = $segments[1];

		} 

		if($segments[0] == 'preview' && isset($segments[1])){
			$vars['task']	= $segments[0];
			$segments[1] = str_replace(':', '-', $segments[1]); 
			$query = "SELECT a.id FROM #__joomblog_posts AS a WHERE a.alias = '{$segments[1]}' ";
			$db->setQuery( $query );
			$vars['id'] = $db->loadResult();
		} 
		
		if($segments[0] == 'post' && isset($segments[1])){
			$vars['task']	= 'show';
			$segments[1] = str_replace(':', '-', $segments[1]); 
			$query = "SELECT a.id FROM #__joomblog_posts AS a, #__categories AS c WHERE c.extension = 'com_joomblog' AND c.id = a.catid AND a.alias = '{$segments[1]}' ";
			$db->setQuery( $query );
			$vars['show'] = $db->loadResult();
		} else		
		
		if($segments[0] == 'tagslist' && !isset($segments[1])){
			$vars['task'] = 'tagslist';
		} else
		
		if($segments[0] == 'categorylist' && !isset($segments[1])){
			$vars['task'] = 'categorylist';
		} else
		
		if($segments[0] == 'category' && isset($segments[1])){
			$vars['task']	= 'tag';
			$vars['view'] = 'jjcategory';
			$segments[1] = str_replace(':', '-', $segments[1]);
			$db	= JFactory::getDBO();
			$db->setQuery("SELECT `id` FROM #__categories WHERE extension = 'com_joomblog' AND `alias`=".$db->quote($segments[1]));
			$vars['category'] = $db->loadResult();
		} else
		
		if($segments[0] == 'tag' && isset($segments[1])){
			$vars['task']	= 'tag';
			$segments[1] = str_replace(':', '-', $segments[1]); 
			$segments[1] = str_replace('-', ' ', $segments[1]);
			$vars['tag'] = $segments[1];
		} else
		
		if($segments[0] == 'archive' && isset($segments[1])){
			$vars['archive'] = $segments[1];
		} else 

		if($segments[0] == 'delete'){
			$vars['task'] = 'delete';
		} else 
		
		if($segments[0] == 'user' && isset($segments[1])){
			$vars['user'] = $segments[1];
		} else
		
		if($segments[0] == 'profile' && isset($segments[1])){
			$vars['task'] = 'profile';
			$userid = jbGetAuthorId($segments[1]);
			$vars['id'] = $userid;
		} else
				
		if($segments[0] == 'feed'){
			$vars['task'] = 'rss';
			if(isset($segments[1])){
			    switch($segments[1]){
				case 'blog':
				    if(!empty($segments[2])){
					$segments[2] = str_replace(':', '-', $segments[2]); 
					$db	= JFactory::getDBO();
					$db->setQuery("SELECT `id` FROM #__joomblog_list_blogs WHERE `alias`=".$db->quote($segments[2]));
					$vars['blogid'] = $db->loadResult();
				    }
				    break;
				default:
				    break;
			    }
			}

		}else
		
		if($segments[0] == 'search'){
			$vars['task'] = 'search';
		}
		
		if(in_array($segments[0], $admintask) || empty($vars)){
			$vars['task'] = $segments[0];
		} 
		
		
		if($segments[0] == 'blog' && isset($segments[1])){
			
			$vars['task'] = '';
			$vars['view'] = 'blogger';
			$segments[1] = str_replace(':', '-', $segments[1]); 
			$db	= JFactory::getDBO();
			$db->setQuery("SELECT `id` FROM #__joomblog_list_blogs WHERE `alias`='{$segments[1]}'"); 
			$vars['blogid'] = $db->loadResult();
		} else
		
		if($segments[0] == 'mainpage')
		{
			$vars['task'] = '';
			$segments[0]='';
			$vars['view'] = 'default';
		}
		else
		
		if($segments[0] == 'write')
		{
			$vars['task'] = 'write';
			$db	= JFactory::getDBO();
			if ($segments[1] == 'new-post')
			{
				$vars['id'] = 0;
			}
			else
			{
				$segments[1] = str_replace(':', '-', $segments[1]); 
				$db->setQuery("SELECT `id` FROM #__joomblog_posts WHERE `alias`=".$db->quote($segments[1])); 
				$vars['id'] = $db->loadResult();
			}
				
			if(!empty($segments[2]))
			{
				$segments[2] = str_replace(':', '-', $segments[2]); 
				$db	= JFactory::getDBO();
				$db->setQuery("SELECT `id` FROM #__joomblog_list_blogs WHERE `alias`=".$db->quote($segments[2]));
				$vars['blogid'] = $db->loadResult();
			}
		}
		
	}

	return $vars;
}

