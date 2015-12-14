<?php
/**
* JoomBlog Plugin for Joomla
* @version $Id: joombloguser.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage joombloguser.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');


class plgUserJoombloguser extends JPlugin
{
	
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$app = JFactory::getApplication();

		$args = array();
		$args['username']	= $user['username'];
		$args['email']		= $user['email'];
		$args['fullname']	= $user['name'];
		$args['password']	= $user['password'];
		
		$db = JFactory::getDBO();
		
		$db->setQuery("SELECT * FROM #__joomblog_user WHERE `user_id` = ".$user['id']);
		$row = $db->loadObject();
		
		if ($isnew) {
			
			$row	= new stdClass();
		
			$row->user_id		= $user['id'];
			$row->description	= null;
			$row->title			= null;
			$row->feedburner	= null;
			$row->style			= '0000-00-00 00:00:00';
			$row->about			= '';
			$row->birthday		= '0000-00-00';
			$row->twitter		= null;
			$row->site			= null;
			$row->avatar		= '';
			
			$db->insertObject( '#__joomblog_user' , $row );
			
		}
		else {
			$db->updateObject( '#__joomblog_user' , $row , 'user_id' );
		}
	}

	
	public function onUserAfterDelete($user, $succes, $msg)
	{
		$db = JFactory::getDBO();
		
		$db->setQuery("SELECT id FROM #__joomblog_user WHERE user_id=".$user['id']);
		$exists = $db->loadResult();
		
		if ($exists){
			$db->setQuery("DELETE FROM #__joomblog_user WHERE user_id=".$user['id']);
			$db->execute();
		}
	}

	
}
