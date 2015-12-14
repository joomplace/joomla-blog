<?php
/**
* JoomBlog Tags Module for Joomla
* @version $Id: mod_jb_tags.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage mod_jb_tags.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

if(!defined('JB_COM_PATH'))
require_once( JPATH_ROOT . '/components/com_joomblog/defines.joomblog.php');
require_once(JPATH_ROOT . '/administrator/components/com_joomblog/config.joomblog.php');
require_once(JB_COM_PATH . '/libraries/datamanager.php');
require_once(JB_COM_PATH . '/functions.joomblog.php');
require_once(JB_COM_PATH . '/template.php' );

jimport( 'joomla.filesystem.file' );

require_once(JPATH_SITE . '/components/com_joomblog/router.php');
require_once(dirname(__FILE__) . '/helper.php');

global $_JB_CONFIGURATION;
$mainframe = JFactory::getApplication();
$doc =& JFactory::getDocument(); 
	
$file	= JURI::base().'components/com_joomblog/templates/_default/module.tags.css';

if ($_JB_CONFIGURATION->get('overrideTemplate')){
  $file	= JURI::base().'templates/'.$mainframe->getTemplate().'/com_joomblog/module.tags.css';
}else{
  $file	= JURI::base().'components/com_joomblog/templates/'.$_JB_CONFIGURATION->get('template').'/module.tags.css';
}

$doc->addStyleSheet($file);

$list = modJbTagsHelper::getList($params);
require(JModuleHelper::getLayoutPath('mod_jb_tags'));

