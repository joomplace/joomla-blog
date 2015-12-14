<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JbblogBloggerstatsTask extends JbblogBaseController
{
	function JbblogBloggerstatsTask()
	{
		$this->toolbar	= JB_TOOLBAR_BLOGGER;
	}
	
	function display()
	{
		$mainframe	= JFactory::getApplication();
		$blogid = JFactory::getApplication()->input->get('blogid');
		$itemId = JFactory::getApplication()->input->get('Itemid');
		$user	= JFactory::getUser();
		if (empty($user->id))
		{
			$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&view=default',false));
		}
		$db		= JFactory::getDBO();

		$db->setQuery("SELECT `title` FROM #__joomblog_list_blogs WHERE id='$blogid'");
		$blog_title = $db->loadResult();
		
		if(!class_exists('JoomblogTemplate'))
		{
			require_once( JB_COM_PATH.DIRECTORY_SEPARATOR.'template.php' );
		}
		
		$tpl	= new JoomblogTemplate();

		$tpl->set('num_entries', JbCountBlogEntry($blogid));
		$tpl->set('blog_title', $blog_title);
		$tpl->set('blogid', $blogid);
				
		// Need to check if integrations with jomcomment is enabled.
		if(JbGetJomComment())
		{
		    $tpl->set('jomcomment',true);
		    $tpl->set('num_comments', JbCountBlogComment($blogid));
		}
		
		$tpl->set('num_hits', JbCountBlogHits($blogid));
		$tpl->set('tags', jbGetBlogUsedTags($blogid));
		$tpl->set('Jbitemid', $itemId);
		$html = $tpl->fetch(JB_TEMPLATE_PATH."/admin/blogger_stats.html");
		return $html;
	}
}