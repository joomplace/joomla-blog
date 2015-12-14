<?php
/**
 * JoomBlog Categories Module for Joomla
 * @version    $Id: mod_jb_categories.php 2011-03-16 17:30:15
 * @package    JoomBlog
 * @subpackage mod_jb_categories.php
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

require_once(JPATH_SITE . '/components/com_joomblog/router.php');

global $_JB_CONFIGURATION;

$jbItemid = jbGetItemID();
if (!$categories = jbCacheChecker('mod_categories', $jbItemid, false, 600))
{
	$categories = jbGetCategoryList($_JB_CONFIGURATION->get('managedSections'));
	jbCacheChecker('mod_categories', $jbItemid, $categories);
}

require(JModuleHelper::getLayoutPath('mod_jb_categories'));

