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
	require_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'defines.joomblog.php');

require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'config.joomblog.php');
require_once(JB_COM_PATH.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'datamanager.php');
require_once(JB_COM_PATH.DIRECTORY_SEPARATOR.'functions.joomblog.php');

require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'router.php');

global $_JB_CONFIGURATION;
$count_blogs = $params->get('count_blogs');

$jbItemid = jbGetItemID();

if ( !$blogs = jbCacheChecker('mod_blogs', $count_blogs.'-'.$jbItemid) )
{
	$blogs	= jbGetBlogsList($count_blogs);
	jbCacheChecker('mod_blogs', $count_blogs.'-'.$jbItemid, $blogs);
}

require(JModuleHelper::getLayoutPath('mod_jb_blogs'));

