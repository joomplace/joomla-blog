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

class JbblogDashboardTask extends JbblogBaseController{
	
	function __construct(){
		$this->toolbar = JB_TOOLBAR_BLOGGER;
	}
	
	function display(){
	
		global $_JB_CONFIGURATION;
		$option = 'com_joomblog';
		$mainframe	= JFactory::getApplication();		
		$db			= JFactory::getDBO();
		$limit		= $mainframe->getUserStateFromRequest( $option.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );
		$sections	= implode(',',jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));
		$my	= JFactory::getUser();
		$query = "SELECT `name` FROM `#__users` WHERE id=".$my->id;
		$db->setQuery($query);
		$user_fullname = $db->LoadResult();
		
		//Get blogs 
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
		$query->select(" distinct(u.id) as user_id, `lb`.description,u.username, COUNT( jb.content_id ) AS count, u.name, SUM( ps.hits ) AS sum_hits, MAX( ps.publish_up ) AS last_published, `lb`.title, `lb`.id, ub.avatar");
		$query->from("#__joomblog_list_blogs AS lb");
		$query->join("LEFT", "#__joomblog_user as ub ON ub.user_id = lb.user_id");
		$query->join("LEFT", "#__users as u ON u.id = ub.user_id");
		$query->join("LEFT", "#__joomblog_blogs AS jb ON jb.blog_id = lb.id");
		$query->join("LEFT", "#__joomblog_posts AS ps ON jb.content_id = ps.id");
		$query->where('lb.published = 1');
		$query->where('lb.approved = 1');
		$query->order('lb.ordering');
		$query->group('lb.title');
		if (!in_array('8', JFactory::getUser()->getAuthorisedGroups())) {
			$user	= JFactory::getUser();
			$user_id = (int)$user->id;
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			if(!JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false)){
			    $query->where('(lb.waccess IN ('.$groups.') OR lb.user_id='.$user_id.')');
			}else{
			    $userJSGroups = JbblogBaseController::getJSGroups($user->id);
			    $userJSFriends = JbblogBaseController::getJSFriends($user->id);
			    if(count($userJSGroups)>0){
				$tmpQ2 = ' OR (lb.waccess=-4 AND lb.waccess_gr IN ('.implode(',', $userJSGroups).')) ';
			    }else{
				$tmpQ2 = '';
			    }
			    if(count($userJSFriends)>0){
				$tmpQ22 = ' OR (lb.waccess=-2 AND lb.user_id IN ('.implode(',', $userJSFriends).')) ';
			    }else{
				$tmpQ22 = '';
			    }
			    $query->where('(lb.waccess IN ('.$groups.') 
				    OR lb.user_id='.$user_id.' '.$tmpQ2.' '.$tmpQ22.' )');
			}
		}
		$db->setQuery($query);
		$blogs = $db->loadObjectList();

		//Get user information
		
		$user = JTable::getInstance( 'BlogUsers' , 'Table' );
		$user->load($my->id);

		$avatar	= 'Jb' . ucfirst($_JB_CONFIGURATION->get('avatar')) . 'Avatar';
		$avatar	= new $avatar($user->user_id, 0, $user);
		$user->src	= $avatar->get();

		$user_description = $user->about;
		$user_avatar = $user->src;


		//Get user reading list post count
		$query = JFactory::getDbo()->getQuery(true);
		$query->select("`u`.post_count");
		$query->from("#__joomblog_user AS u");
		$query->where('u.user_id ='.$my->id);
		$db->setQuery($query);
		$user_postcount = $db->loadResult();
		if (!$user_postcount) $user_postcount=5;
		$reading_blogs = '';
		//Get reading list
		$db->setQuery("SELECT `reading_list` FROM #__joomblog_user WHERE user_id = ".$user->user_id);
		$reading_list = $db->loadResult();
		if ($reading_list)
		{
			$query = JFactory::getDbo()->getQuery(true);
			$query->select(" `lb`.title, `lb`.id");
			$query->from("#__joomblog_list_blogs AS lb");
			$query->join("LEFT", "#__joomblog_user as ub ON ub.user_id = lb.user_id");
			$query->join("LEFT", "#__users as u ON u.id = ub.user_id");
			$query->where('lb.published = 1');
			$query->where('lb.approved = 1');
			$query->where("lb.id IN (".$reading_list.")");
			$query->order('lb.ordering');

			if (!in_array('8', JFactory::getUser()->getAuthorisedGroups())) {
				$user	= JFactory::getUser();
				$user_id = (int)$user->id;
				$groups	= implode(',', $user->getAuthorisedViewLevels());
			    $query->where('(lb.access IN ('.$groups.') OR lb.user_id='.$user_id.')');
			}
			$db->setQuery($query);
			$reading_blogs = $db->loadObjectList();
		}

		$dashboardHTML = '';
		
		jbAddPageTitle( JText::_('COM_JOOMBLOG_SHOW_DASHBOARD_TITLE') );
		jbAddPathway( JText::_('COM_JOOMBLOG_SHOW_DASHBOARD_TITLE') );

		$content = "";
		$template		= new JoomblogTemplate();
		(isset($user->site))? $template->set( 'user_site' , $user->site ):$template->set( 'user_site' , '' );
		(isset($user->facebook))? $template->set( 'user_facebook' , $user->facebook ):$template->set( 'user_facebook' , '' );
		(isset($user->google_plus))? $template->set( 'user_googleplus' , $user->google_plus ):$template->set( 'user_googleplus' , '' );
		(isset($user->twitter))? $template->set( 'user_twitter' , $user->twitter ):$template->set( 'user_twitter' , '' );
		$template->set( 'user_description' , $user_description );
		$template->set( 'reading_blogs' ,$reading_blogs);
		$template->set( 'user_fullname' ,$user_fullname);
		$template->set( 'user_postcount' ,$user_postcount);
		$template->set( 'user_avatar' , $user_avatar );
		$template->set('newblogRights', jbCanBlogCreate());
		$template->set( 'blogs' , $blogs );
		$dashboardHTML .= $template->fetch( $this->_getTemplateName('dashboard') );
		unset( $template );
		$content .= '<div id="jblog-section"></div><div id="dashboard">'.$dashboardHTML.'</div>';
		return $content;
	}
}