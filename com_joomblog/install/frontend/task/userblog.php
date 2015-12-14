<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once( JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'author.php' );

class JbblogUserblogTask extends JbblogAuthorTask
{
	
	function JbblogUserblogTask()
	{
		parent::JbblogBrowseBase();
		
		$this->toolbar = JB_TOOLBAR_BLOGGER;
		
		$my				= JFactory::getUser();
		$authorId = $author = $my->id; 
		
		$this->authorId = $authorId;
		
		$this->author = JTable::getInstance( 'BlogUsers' , 'Table' );
		$this->author->load($authorId);
	}
	
	function display()
	{
		$my		= JFactory::getUser();
		
		if( $my->id == 0 )
		{
			echo '<div id="fp-content">';
			echo JText::_('COM_JOOMBLOG_LOGIN_TO_VIEW_BLOG');
			echo '</div>';
		}
		else
		{
			$content	= parent::display();
			$db = JFactory::getDBO();		
			$query = "SELECT `name` FROM `#__users` WHERE id=".$my->id;
			$db->setQuery($query);
			$user_fullname = $db->LoadResult();
			jbAddPageTitle( $user_fullname . "'s Blog");
			return $content;
		}
	}
}
