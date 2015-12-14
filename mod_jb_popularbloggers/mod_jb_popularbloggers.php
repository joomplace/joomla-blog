<?php
/**
 * JoomBlog Popular Bloggers Module for Joomla
 * @version    $Id: mod_jb_popularbloggers.php 2011-03-16 17:30:15
 * @package    JoomBlog
 * @subpackage mod_jb_popularbloggers.php
 * @author     JoomPlace Team
 * @Copyright  Copyright (C) JoomPlace, www.joomplace.com
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

if (!defined('JB_COM_PATH'))
	require_once(JPATH_ROOT . '/components/com_joomblog/defines.joomblog.php');

require_once(JPATH_ROOT . '/administrator/components/com_joomblog/config.joomblog.php');
require_once(JB_COM_PATH . '/libraries/datamanager.php');
require_once(JB_COM_PATH . '/functions.joomblog.php');
require_once(JPATH_ROOT . '/components/com_joomblog/libraries/avatar.php');

require_once(JPATH_SITE . '/components/com_joomblog/router.php');
require_once(dirname(__FILE__) . '/helper.php');

global $_JB_CONFIGURATION;

$showAvatar = $params->get('displayAvatar', 1);
$postedByDisplay = $params->get('popularBlogsPostedBy', 0);

$jbItemid = jbGetItemID();

$rows = modJbPopularbloggersHelper::getList($params);

require(JModuleHelper::getLayoutPath('mod_jb_popularbloggers'));

