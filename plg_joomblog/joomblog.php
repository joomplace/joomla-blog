<?php
/**
 * JoomBlog Image Plugin for Joomla
 * @version    $Id: joomblog.php 2011-03-16 17:30:15
 * @package    JoomBlog
 * @subpackage joomblog.php
 * @author     JoomPlace Team
 * @Copyright  Copyright (C) JoomPlace, www.joomplace.com
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.plugin.plugin');

class plgSystemJoomblog extends JPlugin
{
	function __construct(& $subject, $config)
	{
		parent :: __construct($subject, $config);
	}

	function onAfterInitialise()
	{
		$this->_setUserMediaDirectory();
	}

	function _setUserMediaDirectory()
	{
		$mainframe = JFactory::getApplication();

		if ($mainframe->isAdmin())
		{
			return;
		}

		$jinput = $mainframe->input;

		if ($jinput->getCmd('option') == 'com_media' && ($jinput->getCmd('view') == 'images' || $jinput->getCmd('view') == 'imagesList' || $jinput->getCmd('task') == 'file.upload'))
		{
			$my = JFactory::getUser();

			if ($my->get('id'))
			{
				$user_path = 'images/stories/users/' . $my->get('id');
				$user_url_path = 'images/stories/users/' . $my->get('id');

				if (!JFolder::exists(JPATH_ROOT . DS . $user_path))
				{
					JFolder::create(JPATH_ROOT . DS . $user_path);
				}

				$params = JComponentHelper::getParams('com_media');
				$params->set('image_path', $user_url_path);

				//define('COM_MEDIA_BASE',JPath::clean(JPATH_ROOT.DS.$user_path));
				//define('COM_MEDIA_BASEURL',JURI::root(true).'/'.$user_url_path);
			}
		}
	}
}