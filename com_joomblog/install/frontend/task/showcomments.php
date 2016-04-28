<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JbblogShowcommentsTask extends JbblogBaseController
{
	function JbblogShowcommentsTask()
	{
		$this->toolbar	= JB_TOOLBAR_BLOGGER;
	}
	
	function display()
	{
		global $_JB_CONFIGURATION;

		$mainframe = JFactory::getApplication(); 
		$blogid = JFactory::getApplication()->input->get('blogid',0);
		$itemId = JFactory::getApplication()->input->get('Itemid');
		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();
		//Get blog titles
		
		$query = "SELECT `id`, `title` FROM `#__joomblog_list_blogs` WHERE `user_id`=".$user->get('id');
		$db->setQuery($query);
		$blog_titles = $db->loadObjectList();
		
		// get List of content id by this blogger
		$cats = implode(',',jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));
		$pathway = $mainframe->getPathway();
		
		
		$db->setQuery("SELECT `title` FROM #__joomblog_list_blogs WHERE id='$blogid'");
		$blog_title = $db->loadResult();

		$search = $mainframe->getUserStateFromRequest("search.com_joomblog.comments", 'filter_search', '');
		$comments_state = $mainframe->getUserStateFromRequest("search.com_joomblog.comments_state", 'comments_state', '');

		$search = $db->escape(JString::trim(JString::strtolower($search)));

		$search_query = "";
		if($search){
			$search_query = " AND comment LIKE '%$search%' ";
		}
		if (empty($comments_state) OR $comments_state == 'Published')
		{
			$search_query .=" AND spam=0 ";
		}
		if ($comments_state == 'Spam')
		{
			$search_query .=" AND spam=1 ";
		}
		$published_count = 0;
		$spam_count = 0;
		$db->setQuery( "SELECT a.id FROM #__joomblog_posts AS a LEFT JOIN #__joomblog_blogs AS jb ON jb.content_id=a.id WHERE jb.blog_id=$blogid AND a.catid IN ({$cats}) " );
		$contents = $db->loadObjectList();
		$sections = array();
		foreach($contents as $row){
			$sections[] = $row->id;
		}
			
		$limitComment = $_JB_CONFIGURATION->get('limitComment');
			
		// Make sure that there are indeed some article written by the author
		if(!empty($sections))
		{
			$jinput = JFactory::getApplication()->input;
			$limitstart	= $jinput->get( 'limitstart' , '' , 'GET' );
			$limit		= $limitstart ? "LIMIT $limitstart, ".$limitComment : 'LIMIT '.$limitComment;
			//Count comments
			$query = "SELECT COUNT(*) FROM #__joomblog_comment WHERE  contentid IN (". implode(',', $sections).") AND spam=0";
			$db->setQuery( $query );
			$published_count = $db->loadResult();

			$query = "SELECT COUNT(*) FROM #__joomblog_comment WHERE  contentid IN (". implode(',', $sections).") AND spam=1";
			$db->setQuery( $query );
			$spam_count = $db->loadResult();

			$query = "SELECT *, created AS date FROM #__joomblog_comment WHERE  contentid IN (". implode(',', $sections).") ".$search_query." ORDER BY created DESC $limit ";
			$db->setQuery( $query );
			$comments = $db->loadObjectList();
		 
			// Add pagination
			$query = "SELECT COUNT(*) FROM #__joomblog_comment WHERE  contentid IN (". implode(',', $sections).") ".$search_query;
			$db->setQuery( $query );
			$total = $db->loadResult();

			$pagination	= new JBPagination( $total , $limitstart , $limitComment );
			$pagination	= $pagination->getPagesLinks();
		}
		else
		{
			$pagination = '';
			$comments = array();
		}
		
		for($i = 0; $i < count($comments); $i ++)
		{
			$query = "SELECT title FROM #__joomblog_posts WHERE id=".$comments[$i]->contentid ;
			$db->setQuery( $query );
			$comments[$i]->content_title = $db->loadResult();

			if( !isset($comments[$i]->referer) || $comments[$i]->referer == '')
			{
				$comments[$i]->referer	= jbGetPermalinkURL($comments[$i]->contentid) . '#comment' . $comments[$i]->id;
			}
			if ($comments[$i]->user_id > 0)
			{
				$query = "SELECT name, username FROM #__users WHERE  id=".$comments[$i]->user_id ;
				$db->setQuery( $query );
				$comment_author = $db->loadObject();
				$comments[$i]->author_name =  $comment_author->name;
				$comments[$i]->author_username =  $comment_author->username; 
			}
		}
		
		jbAddEditorHeader();

		$comm_action = JFactory::getApplication()->input->get('comm_action');
		$comment_id = JFactory::getApplication()->input->get('comment_id');

		//Delete comments
		if ($comm_action == 'delete')
		{
			$cid = JFactory::getApplication()->input->get('cid',Array(),'array');
			if (!count($cid) AND !$comment_id)
			{
				JError::raiseWarning( 100, JText::_('COM_JOOMBLOG_SELECT_COMMENTS') );

			}
			else
			{
				if ($user->authorise('core.edit', 'com_joomblog.blog.'.$blogid)) 
				{
					if (empty($comment_id)) 
						{
							$where = " WHERE `id` IN (".implode(',', $cid).")";
							$cv_where = " WHERE `commentid` IN (".implode(',', $cid).")";
						}
					else 
						{
							$where = ' WHERE `id` ='.$comment_id ;
							$cv_where = ' WHERE `commentid` ='.$comment_id ;
						}
					$query	= "DELETE FROM #__joomblog_comment_votes".$cv_where;
					$db->setQuery( $query );
					$db->execute();

					$query	= "DELETE FROM #__joomblog_comment ".$where;
					$db->setQuery( $query );
					$db->execute();

					$mainframe->redirect(JRoute::_(Juri::root().'index.php?option=com_joomblog&blogid='.$blogid.'&task=showcomments&Itemid='.$itemId, false),JText::_('COM_JOOMBLOG_COMMENTS_DELETED'));
				}
			}
		}

		//Move to spam
		if ($comm_action == 'spam_comm')
		{
			$cid = JFactory::getApplication()->input->get('cid',Array(),'array');
			if (!count($cid) AND !$comment_id)
			{
				JError::raiseWarning( 100, JText::_('COM_JOOMBLOG_SELECT_COMMENTS') );

			}
			else
			{
				if ($user->authorise('core.edit', 'com_joomblog.blog.'.$blogid)) 
				{
					if (empty($comment_id)) $where = " WHERE `id` IN (".implode(',', $cid).")";
					else $where = ' WHERE `id` ='.$comment_id ;
					$query	= "UPDATE #__joomblog_comment SET `spam`=1 ".$where;
					$db->setQuery( $query );
					$db->execute();
					$mainframe->redirect(JRoute::_(Juri::root().'index.php?option=com_joomblog&blogid='.$blogid.'&task=showcomments&Itemid='.$itemId, false),JText::_('COM_JOOMBLOG_COMMENTS_MOVED_TO_SPAM'));
				}
			}
		}

		//Move from spam
		if ($comm_action == 'notspam_comm')
		{
			$cid = JFactory::getApplication()->input->get('cid',Array(),'array');
			if (!count($cid) AND !$comment_id)
			{
				JError::raiseWarning( 100, JText::_('COM_JOOMBLOG_SELECT_COMMENTS') );

			}
			else
			{
				if ($user->authorise('core.edit', 'com_joomblog.blog.'.$blogid)) 
				{
					if (empty($comment_id)) $where = " WHERE `id` IN (".implode(',', $cid).")";
					else $where = ' WHERE `id` ='.$comment_id ;
					$query	= "UPDATE #__joomblog_comment SET `spam`=0 ".$where;
					$db->setQuery( $query );
					$db->execute();
					$mainframe->redirect(JRoute::_(Juri::root().'index.php?option=com_joomblog&blogid='.$blogid.'&task=showcomments&Itemid='.$itemId, false),JText::_('COM_JOOMBLOG_COMMENTS_MOVED_FROM_SPAM'));
				}
			}
		}

		//Remove content from comments
		if ($comm_action == 'content_remove')
		{

			$cid = JFactory::getApplication()->input->get('cid',Array(),'array');
			if (!count($cid) AND !$comment_id)
			{
				JError::raiseWarning( 100, JText::_('COM_JOOMBLOG_SELECT_COMMENTS') );

			}
			else
			{
				if ($user->authorise('core.edit', 'com_joomblog.blog.'.$blogid)) 
				{
					if (empty($comment_id)) $where = " WHERE `id` IN (".implode(',', $cid).")";
					else $where = ' WHERE `id` ='.$comment_id ;
					$query	= "UPDATE #__joomblog_comment SET `comment`='".JText::_('COM_JOOMBLOG_COMMENT_CONTENT_REMOVED_BY_ADMINISTRATOR')."'".$where;
					$db->setQuery( $query );
					$db->execute();

					$mainframe->redirect(JRoute::_(Juri::root().'index.php?option=com_joomblog&blogid='.$blogid.'&task=showcomments&Itemid='.$itemId, false),JText::_('COM_JOOMBLOG_COMMENT_CONTENT_REMOVED'));
				}
			}
		}

		$pathway->addItem(JText::_('COM_JOOMBLOG_ADMIN_COMMENTS'),'');
		$tpl = new JoomblogTemplate();
		$tpl->set('published_count', $published_count);
		$tpl->set('spam_count', $spam_count);
		$tpl->set('blogid', $blogid);
		$tpl->set('blog_title', $blog_title);
		$tpl->set('blog_titles', $blog_titles);
		$tpl->set('itemId', $itemId);
		$tpl->set('filter_search', $search);
		$tpl->set('comments_state',$comments_state);
		$tpl->set('myitemid', jbGetItemId());
		$tpl->set('pagination', $pagination);
		$tpl->set('comments', $comments);
		$tpl->set('search', $search);
		$tpl->set('postingRights', jbGetUserCanPost());
		$html = $tpl->fetch(JB_TEMPLATE_PATH."/admin/comments.html");
		
		return $html;
	}
}