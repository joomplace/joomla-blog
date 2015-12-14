<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JbblogBlogsListTask extends JbBlogBaseController{
	
	function display()
	{
		global $_JB_CONFIGURATION, $Itemid;

		$mainframe	= JFactory::getApplication();
		$my	= JFactory::getUser();
		$doc = JFactory::getDocument();
		$tpl = new JoomblogTemplate();
		$db	= JFactory::getDBO();
		$query = JFactory::getDbo()->getQuery(true);
		$query->select(" `u`.reading_list");
		$query->from("#__joomblog_user AS u");
		$query->where('u.user_id ='.$my->id);
		$db->setQuery($query);
		$reading_list = $db->loadResult();
		$reading_blogs = array();
		if (!empty($reading_list))
		{
			$reading_blogs = explode(',',$reading_list);
		}
		$sections	= implode(',',jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));
		$form_submit = JFactory::getApplication()->input->get('form_submit');
		if ($form_submit)
		{
			$cid = JFactory::getApplication()->input->get('cid',Array(),'array');
			if (count($cid))
			{
				$cid_string = implode(",", $cid);
				$query	= "UPDATE #__joomblog_user SET `reading_list`='".$cid_string."' WHERE `user_id`=".$my->id;
				$db->setQuery( $query );
				$db->execute();
				echo '<script type="text/javascript"> window.parent.location.reload(false); </script>';
			}
			else
			{
				$query	= "UPDATE #__joomblog_user SET `reading_list`='' WHERE `user_id`=".$my->id;
				$db->setQuery( $query );
				$db->execute();
				echo '<script type="text/javascript"> window.parent.location.reload(false); </script>';
			}			
		}
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
		$query->select(" `lb`.title, `lb`.id");
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
		    $query->where('(lb.access IN ('.$groups.') OR lb.user_id='.$user_id.')');
		}
		$db->setQuery($query);
		$blogs_list = $db->loadObjectList();

		$tpl->set('blogs_list', $blogs_list, true);
		$tpl->set('reading_blogs', $reading_blogs);

		$tpl->set('jbitemid', jbGetItemId());
		$html = $tpl->fetch(JB_TEMPLATE_PATH."/default/blogs_list.tmpl.html");
		return $html;
	}
}
