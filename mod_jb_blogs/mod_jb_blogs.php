<?php
/**
* JoomBlog List of Blogs Module for Joomla
* @version $Id: mod_jb_blogs.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage mod_jb_blogs.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

if(!defined('JB_COM_PATH'))
	require_once( JPATH_ROOT.DS.'components'.DS.'com_joomblog'.DS.'defines.joomblog.php');

require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_joomblog'.DS.'config.joomblog.php');
require_once(JB_COM_PATH.DS.'libraries'.DS.'datamanager.php');
require_once(JB_COM_PATH.DS.'functions.joomblog.php');

require_once(JPATH_SITE.DS.'components'.DS.'com_joomblog'.DS.'router.php');

global $_JB_CONFIGURATION;
$count_blogs = $params->get('count_blogs');

$jbItemid = jbGetItemID();

if ( !$blogs = jbCacheChecker('mod_blogs', $count_blogs.'-'.$jbItemid) )
{
	$blogs	= jbGetBlogsList($count_blogs);
	jbCacheChecker('mod_blogs', $count_blogs.'-'.$jbItemid, $blogs);
}

require(JModuleHelper::getLayoutPath('mod_jb_blogs'));

