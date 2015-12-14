<?php
/**
* JoomBlog Archive Module for Joomla
* @version $Id: mod_jb_archive.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage mod_jb_archive.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php');

global $_JB_CONFIGURATION;

$doc = JFactory::getDocument(); 
$mainframe = JFactory::getApplication();
	
$file	= JURI::base().'components/com_joomblog/templates/_default/module.archive.css';

if ($_JB_CONFIGURATION->get('overrideTemplate')){
  $file	= JURI::base().'templates/'.$mainframe->getTemplate().'/com_joomblog/module.archive.css';
}else{
  $file	= JURI::base().'components/com_joomblog/templates/'.$_JB_CONFIGURATION->get('template').'/module.archive.css';
}

$doc->addStyleSheet($file);

$list = modJbArchivePostsHelper::getList($params);
$itemid = modJbArchivePostsHelper::getItemid();
require(JModuleHelper::getLayoutPath('mod_jb_archive'));