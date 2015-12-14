<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;

class JoomBlogHelper
{
	//----------------------------------------------------------------------------------------------------
	public static function showTitle($viewTitle = '', $toolbarIconClass = '')
	{
		$title = JText::_('COM_JOOMBLOG');

		if ($viewTitle != '') $title .= ' : ' . $viewTitle;

		$document = JFactory::getDocument();

		$document->setTitle($title);

		JToolBarHelper::title($title, $toolbarIconClass);
	}

	public static function approveStates()
	{
		return array(
			0 => array(
				'task'				=> 'approve',
				'text'				=> '',
				'active_title'		=> 'COM_JOOMBLOG_APPROVED',
				'inactive_title'	=> '',
				'tip'				=> true,
				'active_class'		=> 'unpublish',
				'inactive_class'	=> 'unpublish'
			),
			1 => array(
				'task'				=> 'unapprove',
				'text'				=> '',
				'active_title'		=> 'COM_JOOMBLOG_UNAPPROVED',
				'inactive_title'	=> '',
				'tip'				=> true,
				'active_class'		=> 'publish',
				'inactive_class'	=> 'publish'
			)
		);
	}

	public static function getSidebarMenu($view)
	{
		$currentViewName = strtolower($view->getName());

		switch ($currentViewName)
		{
			case 'settings':
			case 'plugins':
			{
				JHtmlSidebar::addEntry('<i class="icon-options"></i> ' . JText::_('COM_JOOMBLOG_BE_MENU_CONFIGURATION'), 'index.php?option=com_joomblog&view=settings', ($currentViewName == 'settings'));
				JHtmlSidebar::addEntry('<i class="icon-power-cord"></i> ' . JText::_('COM_JOOMBLOG_BE_MENU_PLUGINS'), 'index.php?option=com_joomblog&view=plugins', ($currentViewName == 'plugins'));
				break;
			}
			case 'categories':
			case 'category':
			case 'blogs':
			case 'blog':
			case 'comments':
			case 'comment':
			case 'posts':
			case 'post':
			case 'tags':
			case 'tag':
			case 'users':
			case 'user':
			case 'export':
			{
				JHtmlSidebar::addEntry('<i class="icon-folder"></i> ' . JText::_('COM_JOOMBLOG_BE_SUBMENU_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_joomblog', ($currentViewName == 'categories' || $currentViewName == 'category'));
				JHtmlSidebar::addEntry('<i class="icon-book"></i> ' . JText::_('COM_JOOMBLOG_BE_SUBMENU_BLOGS'), 'index.php?option=com_joomblog&view=blogs', ($currentViewName == 'blogs' || $currentViewName == 'blog'));
				JHtmlSidebar::addEntry('<i class="icon-list-2"></i> ' . JText::_('COM_JOOMBLOG_BE_SUBMENU_POSTS'), 'index.php?option=com_joomblog&view=posts', ($currentViewName == 'posts' || $currentViewName == 'post'));
				JHtmlSidebar::addEntry('<i class="icon-comment"></i> ' . JText::_('COM_JOOMBLOG_BE_SUBMENU_COMMENTS'), 'index.php?option=com_joomblog&view=comments', ($currentViewName == 'comments' || $currentViewName == 'comment'));
				JHtmlSidebar::addEntry('<i class="icon-tags"></i> ' . JText::_('COM_JOOMBLOG_BE_SUBMENU_TAGS'), 'index.php?option=com_joomblog&view=tags', ($currentViewName == 'tags' || $currentViewName == 'tag'));
				JHtmlSidebar::addEntry('<i class="icon-users"></i> ' . JText::_('COM_JOOMBLOG_BE_MENU_BLOGGERS'), 'index.php?option=com_joomblog&view=users', ($currentViewName == 'users' || $currentViewName == 'user'));
				JHtmlSidebar::addEntry('<i class="icon-stack"></i> ' . JText::_('COM_JOOMBLOG_BE_MENU_EXPORT'), 'index.php?option=com_joomblog&view=export', ($currentViewName == 'export'));
				break;
			}
		}
	}

	public static function getMenuPanel()
	{
		$html = array();

		$html[] = '<div id="tm-navbar" class="navbar navbar-static navbar-inverse joomblog_menu_panel">';
		$html[] = 	'<div class="navbar-inner">';
		$html[] = 		'<div class="container" style="width:auto;">';
		$html[] = 			'<a class="brand" href="http://www.joomplace.com" target="_blank">';
		$html[] = 				'<img src="'.JUri::root().'administrator/components/com_joomblog/assets/images/joomplace_logo_64.png" class="tm-panel-logo">  JOOMPLACE';
		$html[] = 			'</a>';
		$html[] = 			'<ul class="nav" role="navigation">';
		$html[] = 				'<li class="dropdown">';
		$html[] = 					'<a id="control-panel" href="index.php?option=com_joomblog" role="button" class="dropdown-toggle">';
		$html[] = 						JText::_('COM_JOOMBLOG_BE_MENU_CONTROL_PANEL');
		$html[] = 					'</a>';
		$html[] = 				'</li>';
		$html[] = 			'</ul>';
		$html[] = 			'<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse-joomblog">';
		$html[] = 				'<span class="icon-bar"></span>';
		$html[] = 				'<span class="icon-bar"></span>';
		$html[] = 				'<span class="icon-bar"></span>';
		$html[] = 			'</a>';
		$html[] = 			'<div class="nav-collapse-joomblog nav-collapse collapse">';
		$html[] = 				'<ul class="nav" role="navigation">';

		$html[] = 					'<li class="dropdown">';
		$html[] = 						'<a href="#" id="drop-customization" role="button" class="dropdown-toggle" data-toggle="dropdown">';
		$html[] = 							JText::_('COM_JOOMBLOG_BE_MENU_MANAGEMENT') . '<b class="caret"></b>';
		$html[] = 						'</a>';
		$html[] = 						'<ul class="dropdown-menu" role="menu" aria-labelledby="drop-customization">';
		$html[] = 							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="index.php?option=com_categories&view=categories&extension=com_joomblog"><i class="icon-folder"></i> ';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_CATEGORIES');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] = 							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="index.php?option=com_joomblog&view=blogs"><i class="icon-book"></i> ';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_BLOGS');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] = 							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="index.php?option=com_joomblog&view=posts"><i class="icon-list-2"></i> ';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_POSTS');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] =							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="index.php?option=com_joomblog&view=comments"><i class="icon-comment"></i> ';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_COMMENTS');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] =							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="index.php?option=com_joomblog&view=tags"><i class="icon-tags"></i> ';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_TAGS');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] = 						'</ul>';
		$html[] = 					'</li>';

		$html[] = 					'<li>';
		$html[] = 						'<a href="index.php?option=com_joomblog&view=users" id="drop-customization" role="button" class="dropdown-toggle">';
		$html[] = 							JText::_('COM_JOOMBLOG_BE_MENU_BLOGGERS');
		$html[] = 						'</a>';
		$html[] = 					'</li>';

		$html[] = 					'<li>';
		$html[] = 						'<a href="index.php?option=com_joomblog&view=settings" id="drop-customization" role="button" class="dropdown-toggle">';
		$html[] = 							JText::_('COM_JOOMBLOG_BE_MENU_CONFIGURATION');
		$html[] = 						'</a>';
		$html[] = 					'</li>';


		$html[] = 				'</ul>';
		$html[] = 				'<ul class="nav pull-right" role="navigation">';
		$html[] = 					'<li class="dropdown">';
		$html[] = 						'<a href="#" id="drop-customization" role="button" class="dropdown-toggle" data-toggle="dropdown">';
		$html[] = 							JText::_('COM_JOOMBLOG_BE_MENU_HELP') . '<b class="caret"></b>';
		$html[] = 						'</a>';
		$html[] = 						'<ul class="dropdown-menu" role="menu" aria-labelledby="drop-customization">';
		$html[] = 							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="http://www.joomplace.com/video-tutorials-and-documentation/joomblog/description.htm">';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_HELP');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] = 							'<li role="presentation" class="divider"></li>';
		$html[] = 							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="http://www.joomplace.com/forum/joomla-components/joomblog.html" target="_blank">';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_SUPPORT_FORUM');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] = 							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="http://www.joomplace.com/support/helpdesk.html" target="_blank">';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_HELPDESK');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] = 							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="http://www.joomplace.com/support/helpdesk/post-purchase-questions/ticket/create.html" target="_blank">';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_SUBMIT_REQUEST');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] = 							'<li role="presentation" class="divider"></li>';
		$html[] = 							'<li role="presentation">';
		$html[] = 								'<a role="menuitem" tabindex="-1" href="index.php?option=com_joomblog&view=sampledata">';
		$html[] = 									JText::_('COM_JOOMBLOG_BE_SUBMENU_SAMPLEDATA');
		$html[] = 								'</a>';
		$html[] = 							'</li>';
		$html[] = 						'</ul>';
		$html[] = 					'</li>';
		$html[] = 				'</ul>';
		$html[] = 			'</div>';
		$html[] = 		'</div>';
		$html[] = 	'</div>';
		$html[] = '</div>';

		return implode('', $html);
	}

	public static function addSubmenu($submenu) 
	{
		if ($submenu == 'categories') {
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::root().'administrator/components/com_joomblog/assets/css/joomblog.css');

			$controller = JControllerLegacy::getInstance('Categories');
			$view = $controller->getView('categories', 'html');
			$view->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_joomblog/helpers/html');
			$view->setLayout('categories');

			JoomBlogHelper::showTitle(JText::_('COM_CATEGORIES_CATEGORIES_TITLE'), 'folder');
		}
	}

	public static function getActions($categoryId = 0, $postId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($postId) && empty($categoryId)) {
			$assetName = 'com_joomblog';
		}
		else if (empty($postId)) {
			$assetName = 'com_joomblog.category.'.(int)$categoryId;
		}
		else {
			$assetName = 'com_joomblog.article.'.(int)$postId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function getVersion() 
	{
		$params = self::getManifest();
		return $params->version;
	}

	public static function getManifest()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT `manifest_cache` FROM #__extensions WHERE element="com_joomblog"');
		$params = json_decode($db->loadResult());
		return $params;
	}

	public static function getParams()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT `params` FROM #__extensions WHERE element="com_joomblog"');
		$params = json_decode($db->loadResult());
		return $params;
	}
	
	public static function loadData($file = 'custom.xml')
	{
		$xmlFile = self::getDataFile($file);
		$xml = simplexml_load_file($xmlFile);
		return $xml;
	}

	public static function saveData($xml, $file = 'custom.xml')
	{
		$xmlString = $xml->asXML();
		$xmlFile = self::getDataFile($file);
		if ($xmlFile = fopen($xmlFile, 'w')) {
			fwrite($xmlFile, $xmlString);
			fclose($xmlFile);
			return true;
		}

		return false;
	}

	public static function getSettings()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT `params` FROM #__extensions WHERE element="com_joomblog"');
		$params = json_decode($db->loadResult());
		return $params;
	}
	
	public static function getDataFile($file) {
		$mediaFile = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.$file;
		if (file_exists($mediaFile)) {
			$xmlFile = $mediaFile;
		} else {
			$xmlFile = JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.$file;
		}
		return $xmlFile;
	}
}
