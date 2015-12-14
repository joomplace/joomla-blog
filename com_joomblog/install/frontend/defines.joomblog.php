<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restrictedaccess');

define('JB_COM_PATH',JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog');
define('JB_COM_LIVE',rtrim(JURI::root(),'/').'/components/com_joomblog');
define('JB_ADMIN_COM_PATH',JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog');
define('JB_LIBRARY_PATH',JB_COM_PATH.'/libraries');
define('JB_TASK_PATH',JB_COM_PATH.'/task');
define('JB_FRONTVIEW_PATH',JB_COM_PATH.'/frontview');
define('JB_FRONTADMIN_PATH',JB_COM_PATH.'/frontadmin');
define('JB_TEMPLATE_PATH',JB_COM_PATH."/templates");
define('JB_MODEL_PATH',	JB_COM_PATH.'/model');
define('JB_CACHE_PATH',	JPATH_ROOT.DIRECTORY_SEPARATOR.'cache');
define('JB_DEFAULT_LIMIT',30);
define('JB_TOOLBAR_HOME','home');
define('JB_TOOLBAR_BLOGS','blogs');
define('JB_TOOLBAR_BLOGGER','blogger');
define('JB_TOOLBAR_ACCOUNT','account');
define('JB_TOOLBAR_TAGS','tags');
define('JB_TOOLBAR_CATEGORIES','categories');
define('JB_TOOLBAR_SEARCH',	'search');
define('JB_TOOLBAR_FEED','feed');
