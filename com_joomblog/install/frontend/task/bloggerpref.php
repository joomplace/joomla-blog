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

class JbblogBloggerprefTask extends JbblogBaseController
{

	function JbblogBloggerprefTask()
	{
		$this->toolbar	= JB_TOOLBAR_BLOGGER;
	}
	
	function display()
	{
		global $_JB_CONFIGURATION;
		$my	= JFactory::getUser();
		$db = JFactory::getDBO();		
		$query = "SELECT `name` FROM `#__users` WHERE id=".$my->id;
		$db->setQuery($query);
		$user_fullname = $db->LoadResult();
		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();		
		$user = JTable::getInstance( 'BlogUsers' , 'Table' );
		$user->load( $my->id );
		$jinput = JFactory::getApplication()->input;
		if( JInput::getMethod() == 'POST' ){
				$file		= $jinput->files->get( 'Filedata' , Array() , 'array' );
			    $removeAvatar = $jinput->get('removeAvatar', false);
			    if( (!isset( $file['tmp_name'] ) || empty( $file['tmp_name'] )) && !$removeAvatar ){
					$profile = $jinput->get('blog-subtitle','', 'POST');
					$feedburner	= $jinput->get('feedburnerURL','','POST');
					$title = $jinput->get('blog-title', '', 'POST');
					$about = $jinput->get('blog-aboutme', '', 'POST');
					$twitter = $jinput->get('blog-twitter', '', 'POST');
					$site = $jinput->get('blog-site', '', 'POST');
					$facebook = $jinput->get('blog-facebook', '', 'POST');
					$google_plus = $jinput->get('blog-googleplus', '', 'POST');
					$day = $jinput->get('blog-day', 0);
					$month = $jinput->get('blog-month', 0);
					$year = $jinput->get('blog-year', 0);
					$remove_avatar = $jinput->get('remove_avatar', '', 'POST');
					$date_birthday = sprintf("%02d",$year)."-".sprintf("%02d",$month)."-".sprintf("%02d",$day);
					$user->description	= strip_tags($profile);
					$user->feedburner = $feedburner;
					$user->title = strip_tags( $title );
					$user->site = strip_tags( $site );
					$user->facebook = strip_tags( $facebook );
					$user->google_plus = strip_tags( $google_plus );
					$user->about = strip_tags( $about );
					$user->twitter = strip_tags( $twitter );
					$user->birthday	= $date_birthday;
					$user->post_count = $jinput->get('post_count', '', 'POST');

					if( $user->store() ){
						$user_fullname = $jinput->get('user-fullname', '', 'POST');
						$query = "UPDATE `#__users` SET `name` ='".$db->Escape($user_fullname)."' WHERE id=".$my->id;
						$db->setQuery($query);
						$db->execute();
						$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&task=dashboard',false),JText::_('COM_JOOMBLOG_BLOG_ADMIN_PROFILE_UPDATED'));
					}
				
				}else  { 
					if($removeAvatar){

					    JbblogBloggerprefTask::removeAvatar ($my->id);
					    die();
					}else JbblogBloggerprefTask::uploadAvatar($my->id);
			    }
		}

		$pathway->addItem(JText::_('COM_JOOMBLOG_ADMIN_MENU_PREFERENCES'),'');

		$showFeedburner	=$_JB_CONFIGURATION->get('userUseFeedBurner') ? true : false;
		
		jbAddEditorHeader();
		
		$tpl = new JoomblogTemplate();

		$user->name = $my->get('name');
		$user->day = date("d",strtotime($user->birthday." 00:00:00"));
		$user->month = date("n",strtotime($user->birthday." 00:00:00"));
		$user->year = date("Y",strtotime($user->birthday." 00:00:00"));

		$tpl->set('showFeedburner', $showFeedburner);
		$tpl->set('user' , array( $user ) );		
		$tpl->set('Jbitemid', JbGetItemId());
		$tpl->set('user_fullname',$user_fullname);
		$tpl->set('postingRights', JbGetUserCanPost());
		$tpl->set('description', stripslashes($user->description));
		$tpl->set('descColor', $user->getStyle('blog-subtitle-color'));
		$tpl->set('title', stripslashes($user->title));
		$tpl->set('titleColor', $user->getStyle('blog-title-color'));
		JFormHelper::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
		$avatar = JFormHelper::loadFieldType('Ajaxavatar', false);
		if(!empty($user->avatar)) $avatar->setValue($user->avatar);
		$tpl->set('avatar', $avatar, true);

		$html = $tpl->fetch(JB_TEMPLATE_PATH."/admin/blogger_profile.html");

		return $html;
	}
	
		public static function uploadAvatar($cid)

{		
		global $_JB_CONFIGURATION;

		$result = array('status'=>'bad', 'message'=>'Unknown error');
		$db = JFactory::getDBO();
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');
		$user			= JFactory::getUser($cid);			
		
		$mainframe = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;		
		$file		= $jinput->files->get( 'Filedata' , Array() , 'array' );
		$userid		= $user->id;
		$url		= 'index.php?option=com_joomblog&task=bloggerpref';
		include_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'image.php');
		
		
		if( !isset( $file['tmp_name'] ) || empty( $file['tmp_name'] ) )
		{	
			return true;
		}
		else
		{
					
			$uploadLimit	= (double) $_JB_CONFIGURATION->get('maxFileSize');
			$uploadLimit	= ( $uploadLimit * 1024 * 1024 );

			// @rule: Limit image size based on the maximum upload allowed.
			if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )
			{
				$result['message'] = JText::_('COM_JOOMBLOG_IMAGEFILESIZEEXCEEDED');
				echo(json_encode($result));
				die();

			}
			
            if( !JBImageHelper::isValidType( $file['type'] ) )
			{
				
				$result['message'] = JText::_('COM_JOOMBLOG_IMAGEFILENOTSUPPORTED');
				echo(json_encode($result));
				die();

            }

			if( !JBImageHelper::isValid($file['tmp_name'] ) )
			{
				$result['message'] = JText::_('COM_JOOMBLOG_IMAGEFILENOTSUPPORTED');
				echo(json_encode($result));
				die();
			}
			else
			{
				
				// @todo: configurable width?
				$imageMaxWidth	= $_JB_CONFIGURATION->get('avatarWidth');

				// Get a hash for the file name.
				
				$fileName		= JApplication::getHash( $file['tmp_name'] . time() );
				$hashFileName	= JString::substr( $fileName , 0 , 24 );
	
				//@todo: configurable path for avatar storage?

				$storage			= JPATH_ROOT . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'joomblog' . DIRECTORY_SEPARATOR . 'avatar';
				$storageImage		= $storage . DIRECTORY_SEPARATOR . $hashFileName . JBImageHelper::getExtension( $file['type'] );
				$storageThumbnail	= $storage . DIRECTORY_SEPARATOR . 'thumb_' . $hashFileName . JBImageHelper::getExtension( $file['type'] );
				$image				= 'images/joomblog/avatar/' . $hashFileName . JBImageHelper::getExtension( $file['type'] );
				$thumbnail			= 'images/joomblog/avatar/' . 'thumb_' . $hashFileName . JBImageHelper::getExtension( $file['type'] );

				// Only resize when the width exceeds the max.
				if( !JBImageHelper::resizeProportional( $file['tmp_name'] , $storageImage , $file['type'] , $imageMaxWidth ) )
				{
					$result['message'] = JText::_('COM_JOOMBLOG_ERROR_MOVING_UPLOADED_FILE');
					echo(json_encode($result));
					die();
				}

				// Generate thumbnail
				if(!JBImageHelper::createThumb( $file['tmp_name'] , $storageThumbnail , $file['type'] ))
				{
					$result['message'] = JText::_('COM_JOOMBLOG_ERROR_MOVING_UPLOADED_FILE');
					echo(json_encode($result));
					die();
				}			
				JbblogBloggerprefTask::removeAvatar($cid);
	
				
				$db->setQuery("UPDATE #__joomblog_user SET avatar = '".$hashFileName . JBImageHelper::getExtension( $file['type'] )."' WHERE user_id =".$cid);
				$db->execute();
				
				$result['status'] = 'ok';
				$result['message'] = 'ok';
				$result['image'] = $hashFileName . JBImageHelper::getExtension( $file['type'] );
				echo(json_encode($result));
				die();

			}
		}
	}
	
	public static function removeAvatar($cid)
	{
		$db = JFactory::getDBO();
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');
		
		$mainframe = JFactory::getApplication();
		$db->setQuery("SELECT avatar FROM #__joomblog_user WHERE user_id=".$cid);
		$avatar = $db->loadResult();
		$db->setQuery("UPDATE `#__joomblog_user` SET `avatar`='' WHERE user_id=".$cid);
		$db->execute();
		$filename = JPATH_ROOT.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'joomblog'.DIRECTORY_SEPARATOR.'avatar'.DIRECTORY_SEPARATOR.$avatar;
		$ThumbName = JPATH_ROOT.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'joomblog'.DIRECTORY_SEPARATOR.'avatar'.DIRECTORY_SEPARATOR.'thumb_'.$avatar;
		$url		= 'index.php?option=com_joomblog&task=bloggerpref';
		
		if ($avatar){
			if (JFile::exists($filename) && JFile::exists($ThumbName)){
				if (JFile::delete($filename) && JFile::delete($ThumbName)){
					return true;
				} else {

				}
			} else {

			}
		}
	}
}
