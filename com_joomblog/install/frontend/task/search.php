<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once( JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'base.php' );
require_once( JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'plugins.php' );

class JbblogSearchTask extends JbblogBaseController
{
	var $_resultLength	= 250;
	var $_plugins		= null;
	
	function __construct()
	{
		$this->toolbar	= JB_TOOLBAR_SEARCH;
		$this->_plugins	= new JBPlugins();
	}
	
	function display()
	{
		global $Itemid, $JBBLOG_LANG, $_JB_CONFIGURATION;
		
		$mainframe	= JFactory::getApplication();
		$my			= JFactory::getUser();
		$pathway    = $mainframe->getPathway();
		$jinput     = JFactory::getApplication()->input;

		$blogger		= $jinput->get('blogger','','string');
		$keyword		= $jinput->get('keyword','','string');
		$tags			= $jinput->get('tags','','string');
		$limitstart		= $jinput->get( 'limitstart' , 0  );

		$jbGetItemId = jbGetItemId();

		jbAddPageTitle( JText::_( 'COM_JOOMBLOG_SEARCH_BLOG_ENTRY_TITLE') );
		$pathway->addItem(JText::_( 'COM_JOOMBLOG_SEARCH_BLOG_ENTRY_TITLE'),'');

		$cachePrefix = $blogger.'::'.$keyword.'::'.$tags.'::'.$limitstart.'::'.$jbGetItemId;
		$template	= new JoomblogCachedTemplate($cachePrefix . $_JB_CONFIGURATION->get('template'));

		$searchURL	= JRoute::_('index.php?option=com_joomblog&task=search&Itemid='.$jbGetItemId);
		
		$template->set('searchURL', $searchURL);
		$template->set('Itemid', $jbGetItemId);
		$results	= false;
		if((!empty($blogger) && isset($blogger))|| (!empty($keyword) && isset($keyword)) || (!empty($tags) && isset($tags)) )
		{
			$results	= $this->_search(array('blogger' => $blogger, 'keyword' => $keyword, 'tags' => $tags));
		}
		
		$template->set('pagination', $results['pagination']);
		unset($results['pagination']);
		$template->set('categoryDisplay' , $_JB_CONFIGURATION->get('categoryDisplay') );
		$template->set('limitstart' ,$limitstart);
		$template->set('blogger', $blogger);
		$template->set('keyword', $keyword);
		$template->set('tags', $tags);
		$template->set('results', $results);
		$content = $template->fetch($this->_getTemplateName('search'));

		return $content;
	}
	 	 	
	function _search($filter)
	{
		global $_JB_CONFIGURATION;
		$jinput = JFactory::getApplication()->input;
		$limit = $jinput->get( 'limit' , $_JB_CONFIGURATION->get('numEntry') , 'GET');
		$limitstart	= $jinput->get( 'limitstart' , 0  );
		
		$db			= JFactory::getDBO();
		
		$sections = implode(',',jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));
		
		$query = '';
    

		$blogger = isset( $filter['blogger'] ) ? $db->escape( $filter['blogger'] ) : '';
		$keyword = isset( $filter['keyword'] ) ? $db->escape( $filter['keyword'] ) : '';
		$tag = isset( $filter['tags'] ) ? $db->escape( $filter['tags'] ) : '';
		
		$postsModel = $this->getModel('Posts', 'JoomblogModel', true);
		$postsModel->setState('list.limit', $limit);
		$postsModel->setState('list.limitstart', $limitstart);
		
		if(!empty( $tag ))
		{
		    $db->setQuery( "SELECT `id` FROM #__joomblog_tags WHERE `name`=".$db->Quote($db->escape($tag,true)));
			$tagId	=  $db->loadResult();
			if ($tagId) $postsModel->setState('filter.tag_id', $tagId);
		}
		
		if(!empty($blogger))
		{
			$postsModel->setState('filter.author_id', jbGetAuthorId($blogger));
		}

		if(!empty($keyword))
		{
			$postsModel->setState('filter.search', $keyword);
		}

		$lang = JFactory::getLanguage();
		// $query	.= " AND a.language IN ('*','".$lang->get('tag')."') ";
		$db->setQuery( "SELECT COUNT(DISTINCT a.id) FROM #__joomblog_posts AS a LEFT JOIN #__joomblog_content_tags AS b ON a.id = b.contentid ".$query );
		$total = $db->loadResult();
		$this->totalEntries=$total;
		$pageNav	= new JBPagination( $total , $limitstart , $limit  );
		$db->setQuery( "SELECT DISTINCT a.*, p.posts, p.comments FROM #__joomblog_posts AS a " .
				" LEFT JOIN `#__joomblog_privacy` AS `p` ON `p`.`postid`=`a`.`id`  AND `p`.`isblog`=0".
				" LEFT JOIN #__joomblog_content_tags AS b ON a.id = b.contentid ".$query, $pageNav->limitstart, $pageNav->limit );
		$results	= $postsModel->getItems();
		$this->_format($results);
		$total = $postsModel->getTotal();
		$this->totalEntries=$total;
		JFactory::getApplication()->input->set('searchPagination', true);
		$pageNav	= new JBPagination( $total , $limitstart , $limit  );
		$entrySession = array(); $_SESSION['entrySession'] = array();
		foreach($results as $entry){
				array_push($entrySession, $entry->id);
		}
		$_SESSION['entrySession'] = (!empty($entrySession)) ? $entrySession : array();		
		
		$results['pagination'] = '<div class="jb-pagenav">' . preg_replace('#(href)="([^:"]*)(?:")#','$1="$2'.($tag?'&tags='.$tag:'').($blogger?'&blogger='.$blogger:'').($keyword?'&keyword='.$keyword:'').'"',$pageNav->getPagesLinks()) . '</div>';
		return $results;
	}
	
	protected function getRowsByPrivacyFilter($rows=null)
	{
		$user	= JFactory::getUser();
		if (sizeof($rows))
		{
			for ( $i = 0, $n = sizeof( $rows ); $i < $n; $i++ ) 
			{
				$post = &$rows[$i];
				$post->posts?$post->posts:$post->posts=0;
				switch ( $post->posts ) 
				{
					case 0:	break;
					case 1:	
						if (!$user->id) 
						{
							$rows[$i]=null;
							unset($rows[$i]);
							$this->totalEntries--;
						}
					break;
					case 2:	
						if (!$user->id) 
						{
							unset($rows[$i]);
							$this->totalEntries--;
						}else
						{
							if (!$this->isFriends($user->id, $post->created_by) && $user->id!=$post->created_by)
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
						}else
						{
							if ($user->id!=$post->created_by)
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
				if (!isset($post->blogtitle)) {unset($rows[$i]); $this->totalEntries--;}
			}
			
			$rows = array_values($rows);
		}
		return $rows;
	}
	
	protected function isFriends($id1=0,$id2=0)
	{
		$db	= JFactory::getDBO();
		$db->setQuery(	" SELECT `connection_id` FROM `#__community_connection` " .
						" WHERE connect_from=".(int)$id1." AND connect_to=".(int)$id2." AND `status`=1 ");
		$frindic = $db->loadResult();
		if ($frindic) return true; else return false;				
	}
	
		
	function _format(&$rows)
	{
		global $_JB_CONFIGURATION;
		$db			= JFactory::getDBO();
		$user	= JFactory::getUser();
		$this->_plugins->load();
		
		for($i =0; $i < count($rows); $i++){
			$row    =& $rows[$i];
			
			$row->text		= $row->introtext . $row->fulltext;
			$row->text		= JString::substr($row->text, 0, $this->_resultLength) . '...';
			$row->text		= strip_tags($row->text);
			$row->text		= str_replace(array('{jomcomment lock}','{!jomcomment}','{jomcomment}'),'',$row->text);
			$row->text		= preg_replace('#\s*<[^>]+>?\s*$#','',$row->text);
			$row->user		= jbGetAuthorName($row->created_by, $_JB_CONFIGURATION->get('useFullName'));
			$row->user		= $row->user;
			$row->link		= jbGetPermalinkURL($row->id);
			$row->jcategory		= '<a class="category" href="' . rtrim( JURI::base() , '/' ).JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $row->catid ) . '">' . jbGetJoomlaCategoryName( $row->catid ) . '</a>';
			$row->userlink	= JRoute::_('index.php?option=com_joomblog&user=' . jbGetAuthorName($row->created_by));
			
			$date			= JFactory::getDate( $row->created );
			$row->date		= $date->format('Y-m-d H:i:s');
		}
	}
}
