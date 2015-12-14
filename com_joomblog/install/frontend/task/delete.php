<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JbblogDeleteTask extends JbblogBaseController
{
	function display()
	{
		$mainframe	= JFactory::getApplication();
		$my	= JFactory::getUser();		
		$jinput = JFactory::getApplication()->input;
		$id	= $jinput->get('id',0);
		$blogid	= $jinput->get('blogid',0);

		$url = 'index.php?option=com_joomblog&task=adminhome&blogid=' . $blogid.'&itemid='. jbGetItemId();
		if ($my->authorise('core.delete', 'com_joomblog.article.'.$id)) {
			$blog = JTable::getInstance( 'Posts' , 'Table' );
			$blog->load( $id );
					
			if( $blog->created_by != $my->id ){
				$mainframe->redirect( $url , JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_DELETE') );
				return;	
			}
			
			$blog->delete();
			
			$mainframe->redirect( $url ,  JText::_('COM_JOOMBLOG_BLOG_ENTRY_DELETED') );
		}else{
			$mainframe->redirect( $url , JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_DELETE') );
		}
	}
}