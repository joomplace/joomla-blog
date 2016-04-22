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
require_once( JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'avatar.php' );

class JbblogBlogsTask extends JbblogBaseController
{

	function JbblogBlogsTask(){
		parent::JbblogBaseController();
	
		$this->toolbar = JB_TOOLBAR_BLOGS;
	}

	function display($styleid = '', $wrapTag = 'div')
	{
		global $JBBLOG_LANG, $_JB_CONFIGURATION, $Itemid;

		$mainframe	= JFactory::getApplication();
		$option = 'com_joomblog';
		
		jbAddPathway(JText::_('COM_JOOMBLOG_ALL_BLOGS_TITLE'));	
		jbAddPageTitle(JText::_('COM_JOOMBLOG_ALL_BLOGS_TITLE'));
		$limit		= $mainframe->getUserStateFromRequest( $option.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );
		$sections	= implode(',',jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));
		
		$db			= JFactory::getDBO();
		
		$query		= "SELECT distinct c.created_by from #__joomblog_posts c left outer join #__joomblog_user m on (m.user_id=c.created_by) WHERE m.user_id IS NULL and catid in ($sections)";
		$db->setQuery( $query );

		if( $db->loadObjectlist() )
		{
			$not_in_joomblog = $db->loadObjectlist();
			foreach($not_in_joomblog as $to_insert)
			{
				$db->setQuery("INSERT INTO #__joomblog_user SET user_id='" . $to_insert->created_by . "',description=''");
				$db->execute();
			}
		}

		$query = JFactory::getDbo()->getQuery(true);
		$query->select(" distinct(u.id) as user_id, `lb`.description,u.username, u.name, `lb`.title, `lb`.id, ub.avatar");
		$query->from("#__joomblog_list_blogs AS lb");
		$query->join("LEFT", "#__joomblog_user as ub ON ub.user_id = lb.user_id");
		$query->join("LEFT", "#__users as u ON u.id = ub.user_id");
		$query->where('lb.published = 1');
		$query->where('lb.approved = 1');
		$query->order('lb.ordering');
		if (!in_array('8', JFactory::getUser()->getAuthorisedGroups())) {
			$user	= JFactory::getUser();
			$user_id = (int)$user->id;
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			if(!JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false)){
			    $query->where('(lb.access IN ('.$groups.') OR lb.user_id='.$user_id.')');
			}else{
			    $userJSGroups = JbblogBaseController::getJSGroups($user->id);
			    $userJSFriends = JbblogBaseController::getJSFriends($user->id);
			    if(count($userJSGroups)>0){
				$tmpQ2 = ' OR (lb.access=-4 AND lb.access_gr IN ('.implode(',', $userJSGroups).')) ';
			    }else{
				$tmpQ1 = ' ';
				$tmpQ2 = '';
			    }
			    if(count($userJSFriends)>0){
				$tmpQ22 = ' OR (lb.access=-2 AND lb.user_id IN ('.implode(',', $userJSFriends).')) ';
			    }else{
				$tmpQ11 = ' ';
				$tmpQ22 = '';
			    }
			    $query->where('(lb.access IN ('.$groups.') 
				    OR lb.user_id='.$user_id.' '.$tmpQ2.' '.$tmpQ22.' )');
			}
		}
		
		$db->setQuery($query);
		
		$blogs = $db->loadObjectList();
		$total = count($blogs);
		
		if(count($blogs)>$limit){
		    $db->setQuery($query, $limitstart, $limit);
		    $blogs = $db->loadObjectList();
		}

		$bloggerHTML	= '';
		$jinput = JFactory::getApplication()->input;
		$tmpl = $jinput->get('tmpl');
		if ($tmpl=='component') $tmpl='&tmpl=component'; else $tmpl='';
		
		if ($blogs)
		{
			foreach ($blogs as $blog)
			{		
				$postsModel = $this->getModel('Posts', 'JoomblogModel', true);
				$postsModel->setState('list.limit', $_JB_CONFIGURATION->get('BloggerRecentPosts'));
				$postsModel->setState('filter.blogid', $blog->id);
				
				$bloggers = $postsModel->getItems();
				foreach($bloggers as $i=>$blogger){
				    $bloggers[$i]->bid = $bloggers[$i]->blogid;
				    $bloggers[$i]->btitle = $bloggers[$i]->blogtitle;
				    $bloggers[$i]->multicats = false;
				    $cats = jbGetMultiCats($bloggers[$i]->id);

				    if (sizeof($cats))
				    {
					    $jcategories = array();
					    foreach ( $cats as $cat ) 
					    {
						    $catlink = JRoute::_('index.php?option=com_joomblog&task=tag&category='.$cat.'&Itemid='.$Itemid );
						    $jcategories []= ' <a class="category" href="' .$catlink. '">' . jbGetJoomlaCategoryName($cat).'</a> ';	
					    }
					    if (sizeof($jcategories)>1) $bloggers[$i]->multicats = true;
					    if (sizeof($jcategories)) $bloggers[$i]->jcategory = implode(',', $jcategories);

				    }else $bloggers[$i]->jcategory	= '<a class="category" href="' . JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $bloggers[$i]->catid.'&Itemid='.$Itemid.$tmpl ). '">' . jbGetJoomlaCategoryName( $bloggers[$i]->catid ) . '</a>';

				    $bloggers[$i]->categories	= jbCategoriesURLGet($bloggers[$i]->id, true);
				}
				$this->totalEntries = $postsModel->getTotal();
				
				$blog->title = $blog->title?$blog->title:$blog->username."'s blog";
								
				$blog->numEntries	= jbCountUserEntry($blog->user_id, "1");
				$blog->numHits		= jbCountUserHits($blog->user_id);
				$blog->blogLink		= JRoute::_("index.php?option=com_joomblog&blogid=" . $blog->id . "&view=blogger&Itemid=".$Itemid.$tmpl);

				$avatar	= 'Jb' . ucfirst($_JB_CONFIGURATION->get('avatar')) . 'Avatar';
				$avatar	= new $avatar($blog->user_id, 0);
				$blog->src	= $avatar->get();

				if($_JB_CONFIGURATION->get('integrJoomSoc') && file_exists(JPATH_ROOT.'/components/com_community/libraries/core.php')){
				    include_once JPATH_ROOT.'/components/com_community/libraries/core.php';
				    // Get CUser object
				    $blog->authorLink = CRoute::_('index.php?option=com_community&view=profile&userid='.$blog->user_id);
				}else{
					$blog->authorLink	= JRoute::_("index.php?option=com_joomblog&task=profile&id=" . $blog->user_id . "&Itemid=".$Itemid.$tmpl);
				}
				//$blog->description	= strip_tags($blog->description, '<u> <i> <b>');
				$blog->description	= $blog->description;
				$db->setQuery("SELECT datediff(curdate(),MAX(created)) from #__joomblog_posts WHERE sectionid IN ($sections) AND created_by='" . $blog->user_id . "' and state='1' and publish_up < now()");
				$lastUpdated		= $db->loadResult();

				if(!empty( $lastUpdated ) )
				{
					if( $lastUpdated > 0 )
					{
						$lastUpdated	= ( $lastUpdated == 1 ? JText::_('COM_JOOMBLOG_BLOG_UPDATED_YESTERDAY') : JText::sprintf( 'COM_JOOMBLOG_BLOG_UPDATED_DAYS_AGO' , $lastUpdated ) );
					}
					else
					{
						$lastUpdated	= JText::_('COM_JOOMBLOG_BLOG_UPDATED_TODAY');
					}

					$blog->last_updated = $lastUpdated;
				}
				else
				{
					$blog->last_updated	= JText::_('COM_JOOMBLOG_BLOG_WAS_NEVER_UPDATED');
				}

				if ($blog->user_id) $categories	= jbGetUserTags($blog->user_id);
				else $categories = '';
				
				$tmpArray		= array();
				$blogs[0]		= $blog;
				
				$template		= new JoomblogTemplate();
				$recent = array();
				$recent[0] = $bloggers;
				$template->set( 'recent' , $recent );
				$template->set( 'totalArticle' ,  $this->totalEntries);

				$template->set( 'avatarWidth' , $_JB_CONFIGURATION->get('avatarWidth' ) );
				$template->set( 'avatarLeftPadding' , ($_JB_CONFIGURATION->get('avatarWidth' ) + 30 ) );
				$template->set( 'useFullName' , $_JB_CONFIGURATION->get('useFullName') );
				$template->set( 'blogs' , $blogs );
				$template->set( 'categories' , $categories );
				$template->set( 'Itemid' , $Itemid );
				
				$bloggerHTML	.= $template->fetch( $this->_getTemplateName('blogs_blogger') );
				
				unset( $template );
			}
		}else{
		   $bloggerHTML .= '<div>'.JText::_('COM_JOOMBLOG_NO_BLOGS_CREATED').'</div>';

		}

		$buttonHTML='';
		$template	= new JoomblogTemplate();
		$template->set( 'buttonHTML' , $buttonHTML );
		$template->set( 'bloggerHTML' , $bloggerHTML );
		$content	= $template->fetch($this->_getTemplateName('blogs'));
		
		$queryString = $_SERVER['QUERY_STRING'];
		$queryString = preg_replace("/\&limit=[0-9]*/i", "", $queryString);
		$queryString = preg_replace("/\&limitstart=[0-9]*/i", "", $queryString);
		$pageNavLink = $_SERVER['REQUEST_URI'];
		$pageNavLink = preg_replace("/\&limit=[0-9]*/i", "", $pageNavLink);
		$pageNavLink = preg_replace("/\&limitstart=[0-9]*/i", "", $pageNavLink);

		$pageNav		= new JBPagination($total, $limitstart, $limit);		
		$content .= '<div class="jb-pagenav">' . $pageNav->getPagesLinks('index.php?' . $queryString) . '</div>';


		return $content;
	}
	protected function isFriends($id1=0,$id2=0)
	{
		$db	= JFactory::getDBO();
		$db->setQuery(	" SELECT `connection_id` FROM `#__community_connection` " .
						" WHERE connect_from=".(int)$id1." AND connect_to=".(int)$id2." AND `status`=1 ");
		$frindic = $db->loadResult();
		if ($frindic) return true; else return false;				
	}
}