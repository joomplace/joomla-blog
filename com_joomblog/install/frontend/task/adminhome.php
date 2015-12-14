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

class JbblogAdminhomeTask extends JbblogBaseController
{
	function JbblogAdminhomeTask()
	{
		$this->toolbar	= JB_TOOLBAR_ACCOUNT;
	}

	function display()
	{
		global $_JB_CONFIGURATION;
		$mainframe	= JFactory::getApplication();
		$document = JFactory::getDocument();
		$my	= JFactory::getUser();
		$db	= JFactory::getDBO();
		$blogid = JFactory::getApplication()->input->get('blogid');
		//Get blog title
		$query = "SELECT `title` FROM `#__joomblog_list_blogs` WHERE `id`=".$blogid;
		$db->setQuery($query);
		$blog_title = $db->loadResult();
		$itemid = JFactory::getApplication()->input->get('itemid');
		if (empty($itemid)) $itemid = jbGetItemId();

		//Get categories
		$query = "SELECT `id`, `title` FROM `#__categories` WHERE `extension`='com_joomblog' AND `published`=1";
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		//Get tags
		$query = "SELECT `id`, `name` FROM `#__joomblog_tags`";
		$db->setQuery($query);
		$tags = $db->loadObjectList();

		//Get posts
		$pathway = $mainframe->getPathway();
		$jinput = JFactory::getApplication()->input;		
 		$filter_search = $mainframe->getUserStateFromRequest("search.com_joomblog.filter_search", 'filter_search', '');
		$filter_search = $db->escape(JString::trim(JString::strtolower($filter_search)));
		$search_cat = $mainframe->getUserStateFromRequest("search.com_joomblog.cat_search", 'cat_search', '');
		$search_tag = $mainframe->getUserStateFromRequest("search.com_joomblog.tag_search", 'tag_search', '');
		$filter_state = $mainframe->getUserStateFromRequest("search.com_joomblog.filter_state", 'filter_state', '');

 		$search_query = "";
 		if ($filter_search){
			$search_query = " AND ( 
				c.title LIKE '%$filter_search%' 
				OR c.introtext LIKE '%$filter_search%'
				OR c.fulltext LIKE '%$filter_search%' 
				) ";
		}
		$search_query = "";
 		if ($filter_search){
			$search_query = " AND ( 
				c.title LIKE '%$filter_search%' 
				OR c.introtext LIKE '%$filter_search%'
				OR c.fulltext LIKE '%$filter_search%' 
				) ";
		}
		$secid = implode(',',jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));
		if (!empty($search_cat) AND $search_cat !=='All categories')  
		{
			$search_query .= " AND (c.id = '$search_cat' OR mc.cid = '$search_cat') ";
		}
		else
		{
			$search_query .= " AND c.catid IN ({$secid}) ";
		}
		if (!empty($search_tag) AND $search_tag !=='All tags') 
		{
			$search_query .= " AND jt.tag = '$search_tag' ";
		}
		if (!empty($filter_state) AND $filter_state !=='All') 
		{
			switch ($filter_state)
			{
			    case 'Published':
			        $search_query .= " AND c.state = '1' ";
			        break;
			    case 'Unpublished':
			         $search_query .= " AND c.state = '0' ";
			        break;
			    case 'Drafts':
			         $search_query .= " AND c.state = '-2' ";
			        break;
			}
		}
  		$deflimit = $_JB_CONFIGURATION->get('numEntry');
 		$limit = $jinput->get( 'limit' , $deflimit , 'REQUEST' );
		$limitstart	= $jinput->get( 'limitstart' , 0 , 'REQUEST' );
 		$limitstart = intval($limitstart);
		$limit = $limitstart ? "LIMIT $limitstart, ".$deflimit : 'LIMIT '.$deflimit;
		$pathway->addItem(JText::_( 'COM_JOOMBLOG_ADMIN_MY_ENTRIES'),'');
		$query		= "SELECT c.*, u.name, u.username FROM #__joomblog_posts AS c LEFT JOIN #__joomblog_multicats as mc ON c.id = mc.aid LEFT JOIN #__joomblog_blogs as jb ON c.id = jb.content_id LEFT JOIN #__joomblog_content_tags as jt ON c.id = jt.contentid LEFT JOIN #__users as u ON c.created_by = u.id WHERE jb.blog_id = ".$blogid
					. $search_query
					. "GROUP BY c.id ORDER BY c.created DESC "
					. $limit;
		$db->setQuery($query);
		$entries	= $db->loadObjectList();

		//All count
		$query		= "SELECT c.*, u.name, u.username FROM #__joomblog_posts AS c LEFT JOIN #__joomblog_multicats as mc ON c.id = mc.aid LEFT JOIN #__joomblog_blogs as jb ON c.id = jb.content_id LEFT JOIN #__joomblog_content_tags as jt ON c.id = jt.contentid LEFT JOIN #__users as u ON c.created_by = u.id WHERE jb.blog_id = ".$blogid
					. $search_query
					. "GROUP BY c.id ORDER BY c.created DESC ";
		$db->setQuery($query);
		$entries_array = $db->loadAssocList();
		$entries_count = count($entries_array);

		//Published count
		$query		= "SELECT c.*, u.name, u.username FROM #__joomblog_posts AS c LEFT JOIN #__joomblog_multicats as mc ON c.id = mc.aid LEFT JOIN #__joomblog_blogs as jb ON c.id = jb.content_id LEFT JOIN #__joomblog_content_tags as jt ON c.id = jt.contentid LEFT JOIN #__users as u ON c.created_by = u.id WHERE jb.blog_id = ".$blogid
					. $search_query . " AND c.`state`=1 "
					. "GROUP BY c.id ORDER BY c.created DESC ";
		$db->setQuery($query);
		$entries_array = $db->loadAssocList();
		$published_count = count($entries_array);

		//Unpublished count
		$query		= "SELECT c.*, u.name, u.username FROM #__joomblog_posts AS c LEFT JOIN #__joomblog_multicats as mc ON c.id = mc.aid LEFT JOIN #__joomblog_blogs as jb ON c.id = jb.content_id LEFT JOIN #__joomblog_content_tags as jt ON c.id = jt.contentid LEFT JOIN #__users as u ON c.created_by = u.id WHERE jb.blog_id = ".$blogid
					. $search_query . " AND c.`state`=0 "
					. "GROUP BY c.id ORDER BY c.created DESC ";
		$db->setQuery($query);
		$entries_array = $db->loadAssocList();
		$unpublished_count = count($entries_array);

		//Drafts count
		$query		= "SELECT c.*, u.name, u.username FROM #__joomblog_posts AS c LEFT JOIN #__joomblog_multicats as mc ON c.id = mc.aid LEFT JOIN #__joomblog_blogs as jb ON c.id = jb.content_id LEFT JOIN #__joomblog_content_tags as jt ON c.id = jt.contentid LEFT JOIN #__users as u ON c.created_by = u.id WHERE jb.blog_id = ".$blogid
					. $search_query . " AND c.`state`=-2 "
					. "GROUP BY c.id ORDER BY c.created DESC ";
		$db->setQuery($query);
		$entries_array = $db->loadAssocList();
		$drafts_count = count($entries_array);

		jimport( 'joomla.filesystem.file' );
		$jomcommentExists	= JFile::exists( JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jomcomment' . DIRECTORY_SEPARATOR . 'config.jomcomment.php' );
		
		for($i = 0; $i < count($entries); $i++)
		{
			$entries[$i]->canEditState = $my->authorise('core.edit.state', 'com_joomblog') && $my->authorise('core.edit.state', 'com_joomblog.article.'.$entries[$i]->id);
			$entries[$i]->canDelete = $my->authorise('core.delete', 'com_joomblog') && $my->authorise('core.delete', 'com_joomblog.article.'.$entries[$i]->id);
			$entries[$i]->canEdit   = $my->authorise('core.edit', 'com_joomblog') && $my->authorise('core.edit', 'com_joomblog.article.'.$entries[$i]->id);
			$entries[$i]->title    = htmlspecialchars($entries[$i]->title);
			$entries[$i]->action = '[ edit | delete ]';
			if( $jomcommentExists && $_JB_CONFIGURATION->get('useComment')  && $_JB_CONFIGURATION->get('useJomComment'))
			{
				$query	= "SELECT COUNT(*) FROM #__jomcomment AS a WHERE a.contentid='" .$entries[$i]->id . "' AND a.option='com_joomblog'";
				$db->setQuery( $query );
				$count	= $db->loadResult();
				$entries[$i]->commentCount = $count;
			}elseif($_JB_CONFIGURATION->get('useComment')){
				$query	= "SELECT COUNT(*) FROM #__joomblog_comment AS a WHERE a.contentid='" .$entries[$i]->id . "' ";
				$db->setQuery( $query );
				$count	= $db->loadResult();
				$entries[$i]->commentCount = $count;
			}
			if ($_JB_CONFIGURATION->get('useComment')=="2")
			{
				$entries[$i]->commentCount = jbGetDisqusComments($entries[$i]);
			}
			$entries[$i]->cats = $this->getMulticats($entries[$i]->id);
		}

		$config = array();
		$total	= count($entries);

		
		$posts_action = JFactory::getApplication()->input->get('posts_action');

		//Delete list of posts
		if ($posts_action == 'delete')
		{
			$url = 'index.php?option=com_joomblog&task=adminhome&blogid=' . $blogid.'&itemid='. $itemid;
			$cid = JFactory::getApplication()->input->get('cid',Array(),'array');
			if (!count($cid) AND !$comment_id)
			{
				JError::raiseWarning( 100, JText::_('COM_JOOMBLOG_SELECT_POSTS') );

			}
			else
			{
				for ($j=0;$j<count($cid);$j++)
				{
					if ($my->authorise('core.delete', 'com_joomblog.article.'.$cid[$j])) {
					$blog = JTable::getInstance( 'Posts' , 'Table' );
					$blog->load( $cid[$j] );
					$blog->delete();
					}
					else
					{
						$mainframe->redirect( $url , JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_DELETE') );
					}
				}
				$mainframe->redirect( $url ,  JText::_('COM_JOOMBLOG_BLOG_ENTRIES_DELETED') );
			}
		}

		//Publish list of posts
		if ($posts_action == 'publish')
		{
			$url = 'index.php?option=com_joomblog&task=adminhome&blogid=' . $blogid.'&itemid='. $itemid;
			$cid = JFactory::getApplication()->input->get('cid',Array(),'array');
			if (!count($cid) AND !$comment_id)
			{
				JError::raiseWarning( 100, JText::_('COM_JOOMBLOG_SELECT_POSTS') );

			}
			else
			{
				for ($j=0;$j<count($cid);$j++)
				{
					if ($my->authorise('core.edit', 'com_joomblog.article.'.$cid[$j]))
					{
						$query	= "UPDATE #__joomblog_posts SET `state`='1' WHERE `id`=".$cid[$j];
						$db->setQuery( $query );
						$db->execute();
					}
					else
					{
						$mainframe->redirect( $url , JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_PUBLISH') );
					}
				}
				$mainframe->redirect( $url ,  JText::_('COM_JOOMBLOG_BLOG_ENTRIES_PUBLISHED') );
			}
		}

		//Unpublish list of posts
		if ($posts_action == 'unpublish')
		{
			$url = 'index.php?option=com_joomblog&task=adminhome&blogid=' . $blogid.'&itemid='. $itemid;
			$cid = JFactory::getApplication()->input->get('cid',Array(),'array');
			if (!count($cid) AND !$comment_id)
			{
				JError::raiseWarning( 100, JText::_('COM_JOOMBLOG_SELECT_POSTS') );

			}
			else
			{
				for ($j=0;$j<count($cid);$j++)
				{
					if ($my->authorise('core.edit', 'com_joomblog.article.'.$cid[$j]))
					{
						$query	= "UPDATE #__joomblog_posts SET `state`='0' WHERE `id`=".$cid[$j];
						$db->setQuery( $query );
						$db->execute();
					}
					else
					{
						$mainframe->redirect( $url , JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_PUBLISH') );
					}
				}
				$mainframe->redirect( $url ,  JText::_('COM_JOOMBLOG_BLOG_ENTRIES_UNPUBLISHED') );
			}
		}

		//Revert to draft list of posts
		if ($posts_action == 'draft')
		{
			$url = 'index.php?option=com_joomblog&task=adminhome&blogid=' . $blogid.'&itemid='. $itemid;
			$cid = JFactory::getApplication()->input->get('cid',Array(),'array');
			if (!count($cid) AND !$comment_id)
			{
				JError::raiseWarning( 100, JText::_('COM_JOOMBLOG_SELECT_POSTS') );

			}
			else
			{
				for ($j=0;$j<count($cid);$j++)
				{
					if ($my->authorise('core.edit', 'com_joomblog.article.'.$cid[$j]))
					{
						$query	= "UPDATE #__joomblog_posts SET `state`='-2' WHERE `id`=".$cid[$j];
						$db->setQuery( $query );
						$db->execute();
					}
					else
					{
						$mainframe->redirect( $url , JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_REVERT_DRAFT') );
					}
				}
				$mainframe->redirect( $url ,  JText::_('COM_JOOMBLOG_BLOG_ENTRIES_REVERT_TO_DRAFT') );
			}
		}

		$pagination	= new jbPagination( $entries_count , $limitstart , $deflimit );
		echo $db->getErrorMsg();
		jbAddEditorHeader();

		$tpl = new JoomblogTemplate();
		$tpl->set('categories',$categories);
		$tpl->set('entries_count', $entries_count);
		$tpl->set('published_count', $published_count);
		$tpl->set('unpublished_count', $unpublished_count);
		$tpl->set('drafts_count', $drafts_count);
		$tpl->set('tags',$tags);
		$tpl->set('blogid', $blogid);
		$tpl->set('blog_title', $blog_title);
		$tpl->set('filter_search', $filter_search);
		$tpl->set('postingRights', jbGetUserCanPost());
		$tpl->set('publishRights', jbGetUserCanPublish());
		$tpl->set('jbitemid', jbGetItemId());
		$tpl->set('pagination', $pagination->getPagesLinks() );
		$tpl->set('jbentries', $entries);
		$tpl->set( 'limit' , $limit);
		$tpl->set( 'search_cat' , $search_cat);
		$tpl->set( 'search_tag' , $search_tag);
		$tpl->set( 'filter_state' , $filter_state);
		$html = $tpl->fetch(JB_TEMPLATE_PATH."/admin/home.html");
		return $html;
	}
	
	public function getMulticats($id=0)
	{
		$db	= JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('mc.cid, c.title ');
		$query->from('#__joomblog_multicats AS mc');
		$query->join('LEFT', '#__categories AS c ON c.id=mc.cid');
		$query->where('aid='.$id);
		$db->setQuery($query);
		$items = $db->loadObjectList();
		
		return $items;
	}
}


