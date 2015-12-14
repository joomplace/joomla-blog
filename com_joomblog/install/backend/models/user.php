<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

// For security reasons use build in user model class
require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_users'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'user.php');
jimport('joomla.utilities.date');

class JoomBlogModelUser extends UsersModelUser
{
	public function getForm($data = array(), $loadData = true) 
	{
		JForm::addFormPath(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_users'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'forms');
		$form = $this->loadForm('com_users.user', 'user', array('control' => 'jform', 'load_data' => false));
		$file = JoomBlogHelper::getDataFile('custom.xml');
		$form->loadFile($file, 'custom');
		$item = $this->getItem();
		$form->bind( $this->getItem());
		@$form->bind($item->custom);		
		$form->setValue('password', null, '');
		
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}

	public function getItem($pk = null) {
		$item = parent::getItem($pk);
		
		if ($item->id) {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__joomblog_user');
			$query->where('user_id='.(int)$item->id);
			
			$db->setQuery($query);
			$result = $db->loadObject();
			
			if ($result) {
				$custom = new JObject();
				foreach ($result as $key=>$value) {
					if ($value) {
						$custom->set($key, $value);
					}
				}

				$item->set('custom', $custom);
			}
			
			if (isset($item->custom->birthday)) {
				$date = new JDate($item->custom->birthday);
				$item->custom->birthday = getdate($date->toUnix());
			}
		}
		
		return $item;
	}
	
	public function save($data) {
		if (parent::save($data)) {
			$pk = $this->getState('user.id');
			$db = JFactory::getDBO();	
			$custom['user_id'] = $pk;
			$custom['about'] = $data['about'];
			$date = new JDate($data['birthday']['year'].'-'.$data['birthday']['mon'].'-'.$data['birthday']['mday']);
			$custom['birthday'] = $date->toSql();
			$custom['twitter'] = $data['twitter'];
			$custom['site'] = $data['site'];
			$custom['facebook'] = $data['facebook'];
			$custom['google_plus'] = $data['google_plus'];

			$custom['resetAvatar'] = JFactory::getApplication()->input->get('resetAvatar', false, 'post', 'bool');
			$custom['avatarFile'] = JFactory::getApplication()->input->files->get('avatarFile', array(), 'files', 'array');
			if (!empty($custom['avatarFile']['name'])) {
				$custom['avatar'] = $this->uploadAvatar($pk);
			}

			if($custom['resetAvatar']) {
				$custom['avatar'] = $this->resetAvatar($pk);
			}
			$query = "SELECT * FROM `#__joomblog_user` WHERE `user_id`=".$custom['user_id'];
			$db->setQuery($query);
			$result = $db->loadObject();
			if (!empty($result))
			{
				$query	= "UPDATE `#__joomblog_user` SET `about`=".$db->Quote($custom['about']).", `birthday`='".$custom['birthday']."', `twitter`='".$custom['twitter']."', `site`='".$custom['site']."', `facebook`='".$custom['facebook']."',
				`google_plus`='".$custom['google_plus']."' WHERE `user_id`=".$custom['user_id'];
				$db->setQuery( $query );
				$db->execute();
			}
			else 
			{
				$query	= "INSERT INTO `#__joomblog_user` (`user_id`,`about`,`site`,`twitter`,`birthday`,`avatar`,`facebook`,`google_plus`) VALUES ('".$custom['user_id']."',".$db->Quote($custom['about']).",'".$custom['site']."',
					'".$custom['twitter']."','".$custom['birthday']."','".$custom['avatarFile']['name']."','".$custom['facebook']."',
				'".$custom['google_plus']."')";
				$db->setQuery( $query );
				$db->execute();
			}
			return true;
		}

		return false;
	}

	function resetAvatar($cid)
	{
		$db = JFactory::getDBO();
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');

		$db->setQuery("SELECT avatar FROM #__joomblog_user WHERE user_id=".$cid);
		$avatar = $db->loadResult();
		$db->setQuery("UPDATE `#__joomblog_user` SET `avatar`='' WHERE user_id=".$cid);
		$db->execute();
		$fileName = JPATH_ROOT.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'joomblog'.DIRECTORY_SEPARATOR.'avatar'.DIRECTORY_SEPARATOR.$avatar;
		$thumbName = JPATH_ROOT.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'joomblog'.DIRECTORY_SEPARATOR.'avatar'.DIRECTORY_SEPARATOR.'thumb_'.$avatar;

		if (JFile::exists($fileName) and JFile::exists($thumbName)) {
			JFile::delete($fileName);
			JFile::delete($thumbName);
		}
		
		return true;
	}

	function uploadAvatar($cid)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');	
		$db = JFactory::getDBO();
		$app  = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;
		$file		= $jinput->files->get('avatarFile', '', 'files', 'array');
		$url		= 'index.php?option=com_joomblog&view=users';
		$params 	= JComponentHelper::getParams('com_joomblog');
		if( !isset($file['tmp_name']) || empty( $file['tmp_name'])) {
			return true;
		} else {
			$media_config = JComponentHelper::getParams('com_media');
			$uploadLimit	= (double)$params->get('maxFileSize', $media_config->get('upload_maxsize', 2));
			$uploadLimit	= ($uploadLimit*1024*1024);

			if(filesize($file['tmp_name']) > $uploadLimit && $uploadLimit != 0) {
				$app->enqueueMessage( JText::_('IMAGE FILE SIZE EXCEEDED') , 'error');
				$app->redirect($url);
			}

            if(!JBImageHelper::isValidType($file['type'])) {
				$app->enqueueMessage(JText::_('IMAGE FILE NOT SUPPORTED'), 'error');
				$app->redirect($url);
            }
			
			$imageMaxWidth	= $params->get('avatarWidth');
			$imageMaxHeight	= $params->get('avatarHeight');
			if (empty($imageMaxWidth)) $imageMaxWidth = 200;
			if (empty($imageMaxHeight)) $imageMaxHeight = 200;


			// Get a hash for the file name.
			$fileName		= JApplication::getHash($file['tmp_name'].time());
			$hashFileName	= JString::substr($fileName,0,24);

			//@todo: configurable path for avatar storage?
			$storage			= JPATH_ROOT.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'joomblog'.DIRECTORY_SEPARATOR.'avatar';
			$storageImage		= $storage.DIRECTORY_SEPARATOR.$hashFileName.JBImageHelper::getExtension($file['type']);
			$storageThumbnail	= $storage.DIRECTORY_SEPARATOR.'thumb_'.$hashFileName.JBImageHelper::getExtension($file['type']);
			$image				= 'images/joomblog/avatar/'.$hashFileName.JBImageHelper::getExtension($file['type']);
			$thumbnail			= 'images/joomblog/avatar/'.'thumb_'.$hashFileName.JBImageHelper::getExtension($file['type']);

			// Only resize when the width exceeds the max.
			if(!JBImageHelper::resizeProportional($file['tmp_name'], $storageImage, $file['type'], $imageMaxWidth, $imageMaxHeight)) {
				$app->enqueueMessage(JText::sprintf('ERROR_MOVING_UPLOADED_FILE', $storageImage), 'error');
				$app->redirect($url);
			}

			// Generate thumbnail

			if(!JBImageHelper::createThumb($file['tmp_name'], $storageThumbnail, $file['type'])) {
				$app->enqueueMessage(JText::sprintf('ERROR_MOVING_UPLOADED_FILE' , $storageThumbnail), 'error');
				$app->redirect($url);
			}

			$db->setQuery("UPDATE #__joomblog_user SET avatar = '".$hashFileName . JBImageHelper::getExtension( $file['type'] )."' WHERE user_id =".$cid);
			$db->execute();
			
			return $hashFileName.JBImageHelper::getExtension($file['type']);
		}
	}
}
