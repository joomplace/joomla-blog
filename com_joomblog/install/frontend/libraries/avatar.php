<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.filesystem.file' );

class JBAvatarBase
{
	var $_width		= '';
	var $_height	= '';
	var $_user		= '';

	var $_userObject = false;

	var	$_default	= '';
	var $_email		= '';

	var $_display = 1;
	
	function JBAvatarBase($userId, $display = 1, $userObject = false )
	{
		global $_JB_CONFIGURATION;
		
		$this->_user	= $userId;
		$this->_userObject	= $userObject;
		$this->_email	= jbGetAuthorEmail( $userId );
				
		$this->_width	= $_JB_CONFIGURATION->get('avatarWidth');
		$this->_height	= $_JB_CONFIGURATION->get('avatarHeight');
		
		$this->_default	= JB_COM_LIVE . '/images/user_thumb.png';
		$this->_display	= (int)$display;
	}
	
	// Return the HTML code to be inserted as avatar
	function display($url = false)
	{
		global $_JB_CONFIGURATION;


		$url		= !empty($url) ? $url : $this->_default;

		if ( !$this->_display )
			return $url;

		$alt		= jbGetAuthorName($this->_user, $_JB_CONFIGURATION->get('useFullName'));
		$content	= '';
		$link		= $this->_link();

		if($this->_link() && $_JB_CONFIGURATION->get('linkAvatar'))
		{
			$content	= '<a href="' . $link . '">'
						. '<img src="' . $url . '" alt="' . $alt . '" />'
						. '</a>';
		}
		else
		{
			$content	= '<img src="' . $url . '" alt="' . $alt . '" />';
		}
		
		return $content;
	}
}

class JBGravatarAvatar extends JBAvatarBase
{
	function get()
	{
		global $_JB_CONFIGURATION;
		
		$url	= 'http://www.gravatar.com/avatar.php?gravatar_id=' . md5(jbGetAuthorEmail($this->_user))
				. '&default=' . urlencode($this->_default)	. '&size=' . $this->_width;
	
		return $this->display($url);
	}
	
	function _link()
	{
		return false;
	}
}


class JBFireboardAvatar extends JBAvatarBase
{
	var $_config	= '';
	
	var $_version	= '';
	var $_src		= '';
	
	function _init()
	{

		if( file_exists( $this->_config ) )
		{
			require_once( $this->_config );
			global $fbConfig;
				
			$this->_config	= new fb_config();
			$this->_config->load();
		}
		else
		{
			$this->_config  = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_fireboard' . DIRECTORY_SEPARATOR . 'fireboard_config.php';
			include($this->_config);
			
			$this->_version	= $fbConfig['version'];
			$this->_src		= $fbConfig['avatar_src'];
		}
	}

	function _loadVars()
	{
		if( !is_object( $this->_config ) )
		{
			require( $this->_config );
			global $fbConfig;
		
			$obj	= new stdclass();
				
			$obj->avatar_src	= $fbConfig['avatar_src'];
			$obj->version		= $fbConfig['version'];
			
			$this->_config	= $obj;
		}
		
	}
		
	function get()
	{
		$this->_config	= JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_fireboard' . DIRECTORY_SEPARATOR . 'sources' . DIRECTORY_SEPARATOR . 'fb_config.class.php';

		$this->_init();
		$this->_loadVars();
		
		if($this->_config->avatar_src == 'fb')
		{
			$query	= "SELECT `avatar` FROM #__fb_users WHERE `userid`='{$this->_user}'";
			
			$db		= JFactory::getDBO();
			
			$db->setQuery( $query );

			$relativePath	= $db->loadResult();
			
			if($relativePath)
			{
				$avatar		= '';
				
				if($this->_config->_version == '1.0.2' || $this->_config->_version == '1.0.3')
				{
					$avatar	= '/components/com_fireboard/avatars/' . $relativePath;
				}
				else
				{
					$avatar	= '/images/fbfiles/avatars/' . $relativePath;
				}
				
				
				
				if( JFile::exists( JPATH_ROOT . $avatar ) )
				{
					return $this->display( rtrim( JURI::root() , '/' ) . $avatar);
				}
			}
		} else if($this->_config->avatar_src != 'cb')
		{
		
		}
		return $this->display();
	}
	
	function _link()
	{
		global $Itemid;
		
		$link	= JRoute::_( 'index.php?option=com_fireboard&func=fbprofile&task=showprf&userid=' . $this->_user . '&Itemid=' . $Itemid );
		
		return $link;
	}
}

class JBCbAvatar extends JBAvatarBase
{
	
	function get()
	{
		$query	= "SELECT `avatar` FROM #__comprofiler WHERE `user_id`='{$this->_user}' AND `avatarapproved`='1'";
		$db		= JFactory::getDBO();
		$db->setQuery( $query );

		$result	= $db->loadResult();

		if($result)
		{
			if( JFile::exists( JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_comprofiler' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $result))
			{
				$url	= rtrim( JURI::root() , '/' ) . '/components/com_comprofiler/images/' . $result;

				return $this->display($url);
			}
			else if( JFile::exists( JPATH_ROOT . DIRECTORY_SEPARATOR .'images' . DIRECTORY_SEPARATOR .'comprofiler' . DIRECTORY_SEPARATOR . $result))
			{
				$url	= rtrim( JURI::root() , '/' ) . '/images/comprofiler/' . $result;
				return $this->display($url);
			}
		}
		return $this->display();
	}
	
	function _link()
	{
		$link	= JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $this->_user );

		return $link;
	}
}

class JBSmfAvatar extends JBAvatarBase
{

	var $_path	= '';
	var $_db;
	
	function _selectDB( $server , $user , $password , $db )
	{
		static $selected;
		
		$selected	= false;
		if( !$selected )
		{
			$resource	= jbsql_connect( $server , $user , $password );
		
			jbsql_select_db( $db , $resource );
			
			$selected	= true;
		}
		return true;
	}
	
	function get()
	{
		global $_JB_CONFIGURATION;
		
		$mainframe		= JFactory::getApplication();
		$this->_path	= rtrim(trim($_JB_CONFIGURATION->get('smfPath')), '/');
		

		if(!$this->_path || $this->_path == '' || !JFile::exists($this->_path . '/Settings.php')){
			$this->_path	= JPATH_ROOT . DIRECTORY_SEPARATOR . 'forum';
		}

		if(!$this->_path || $this->_path == '' || !JFile::exists($this->_path . '/Settings.php'))
		{
			$query	= "SELECT `id` FROM #__extensions WHERE `element`='com_smf'";
			$db		= JFactory::getDBO();
			$db->setQuery( $query );

			if( $db->loadResult() )
			{
				$query	= "SELECT `value1` FROM #__smf_config WHERE variable='smf_path'";
				$db->setQuery( $query );

				$this->_path	= rtrim(str_replace("\\", "/", $db->loadResult() ) , "/");
			}
		}

		if(JFile::exists($this->_path . '/Settings.php'))
		{
			include($this->_path . '/Settings.php');

			jbsql_select_db($mainframe->getCfg('db') , $this->cms->db->db);

			$this->_selectDB($db_server , $db_user , $db_passwd , $db_name );
			
			$strSQL	= sprintf("SELECT avatar, ID_MEMBER FROM {$db_prefix}members WHERE emailAddress='{$this->_email}'");
			$result	= jbsql_query($strSQL);

			if($result)
			{
				$result_row	= jbsql_fetch_array($result);
				jbsql_select_db($mainframe->getCfg('db'));
				
				if($result_row){
					$id_member	= $result_row[1];
					
					if(trim($result_row[0]) != ''){
						if(substr($result_row[0], 0, 7) != 'http://'){
							$url	= $boardurl . '/avatars/' . $result_row[0];
							return $this->display($url);
						}else{
							$url	= $result_row[0];
							return $this->display($url);
						}
							
					} else {
						jbsql_select_db($db_name);
						$strSQL	= sprintf("SELECT ID_ATTACH FROM {$db_prefix}attachments WHERE ID_MEMBER='{$id_member}' AND ID_MSG=0 AND attachmentType=0");
						$result = jbsql_query($strSQL);
						
						if($result){
							$result_avatar = jbsql_fetch_array($result);
							jbsql_select_db($mainframe->getCfg('db'));
							if ($result_avatar[0]){
								$url = "$boardurl/index.php?action=dlattach;attach=" . $result_avatar[0] . ";type=avatar";
								return $this->display($url);
							}
						}
					}
				}
			}
		}
		return $this->display();
	}
	
	function _link()
	{
		$mainframe	= JFactory::getApplication();
		
		if( JFile::exists($this->_path . '/Settings.php'))
		{
			include($this->_path . '/Settings.php');

			$this->_selectDB($db_server , $db_user , $db_passwd , $db_name );
			
			$strSQL	= sprintf("SELECT ID_MEMBER FROM {$db_prefix}members WHERE emailAddress='%s'", jbsql_real_escape_string($this->_email));
			$result = jbsql_query($strSQL);
			$result_row = @jbsql_fetch_array($result);
			jbsql_select_db($mainframe->getCfg('db'));
				
			if($result_row)
			{
				$link = $boardurl . "/index.php?action=profile&u=" . $result_row[0];
				return $link;
			}		
		}
		return false;
	}
}

class JBJuserAvatar extends JBAvatarBase
{

	function get()
	{
		if ( !$this->_userObject ) {
			$table = JPATH_ROOT . '/components/com_joomblog/tables/blogusers.php';

			if ( !JFile::exists($table) )
					return $this->display('');

			include_once($table);

			$this->_userObject = JTable::getInstance('BlogUsers', 'Table');

			if ( $this->_userObject ) {
				$this->_userObject->load($this->_user);
			}
		}

		if ( empty($this->_userObject->avatar) ) {
			return $this->display('');
		} else {
			if ( JFile::exists(JPATH_SITE . '/images/joomblog/avatar/' . $this->_userObject->avatar) ) return $this->display(JUri::root() . 'images/joomblog/avatar/' . $this->_userObject->avatar);
			else return $this->display('');
		}
	}

	function _link()
	{
		return false;
	}
}

class JBJomsocialAvatar extends JBAvatarBase
{
	function _getPath()
	{
		$path	= JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_community' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'core.php';
		
		return $path;
	}
	
	function get()
	{
		$core	= $this->_getPath();

		if( JFile::exists( $core ))
		{
			require_once( $core );
			
			$user	=& CFactory::getUser( $this->_user );

			return $this->display( $user->getThumbAvatar() );
		}

		return $this->display();
	}
	
	function _link()
	{
		$core	= $this->_getPath();
		
		if( file_exists( $core ))
		{
			require_once( $core );
			
			$url 	= CRoute::_('index.php?option=com_community&view=profile&userid=' . $this->_user );
			
			return $url;
		}
		return false;
	}
}

class JBNoneAvatar extends JBAvatarBase{
	
	function get(){
		return "";
	}
}