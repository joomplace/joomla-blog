<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

include_once(JB_COM_PATH . '/task/base.php');

class JbblogCategorylistTask extends JbblogBaseController
{	
	function JbblogCategorylistTask()
	{
		$this->toolbar = JB_TOOLBAR_CATEGORIES;
	}
	
	function display()
	{
		global $_JB_CONFIGURATION;
	
		$mainframe	= JFactory::getApplication();
		$db			= JFactory::getDBO();
		
		$sections = implode(',',jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));
		
		jbAddPageTitle( JText::_('COM_JOOMBLOG_SHOW_CATEGORIES_TITLE') );
		jbAddPathway( JText::_('COM_JOOMBLOG_SHOW_CATEGORIES_TITLE') );

		$query = "SELECT c.* , COUNT(a.catid) as count  
			FROM #__categories AS c 
			LEFT JOIN #__joomblog_posts AS a ON  c.id = a.catid AND a.state = 1  
			WHERE c.published = 1 AND c.id IN (".$sections.") GROUP BY (a.catid) ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$content = "";
		
		if($rows){
			foreach($rows as $value){
				$template = new JoomblogTemplate();
				$template->set( 'title' , $value->title );
				$template->set( 'category' , $value->id );
				$template->set( 'count' , $value->count );
				
				$content .= $template->fetch( $this->_getTemplateName('categories') );
				
				unset($template);
			}
		}
		
		return $content;
	}
	
}
