<?php
/**
 * JoomBlog Latest Post Module for Joomla
 * @version    $Id: mod_jb_latestposts.php 2011-03-16 17:30:15
 * @package    JoomBlog
 * @subpackage mod_jb_latestposts.php
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
require_once(dirname(__FILE__) . '/helper.php');

$document = JFactory::getDocument();
$document->addStyleSheet(rtrim(JURI::root(), '/') . '/modules/mod_jb_latestposts/style.css');

$postedByDisplay = $params->get('latestEntriesPostedBy');
$showAuthor = $params->get('showAuthor', 0);
$showIntro = $params->get('showIntro', 0);
$showDate = $params->get('showDate', 1);
$showReadmore = $params->get('showReadmore', 0);
$titleMaxLength = $params->get('titleMaxLength', 20);

if (!is_numeric($titleMaxLength) or $titleMaxLength == "0")
	$titleMaxLength = 20;

$jbItemid = 1;
$jbItemid = jbGetItemID();

$list = modJbLatestPostsHelper::getList($params);
if ($list)
{
	require(JModuleHelper::getLayoutPath('mod_jb_latestposts'));
}


