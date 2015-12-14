<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once( JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'base.php' );
require_once( JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'avatar.php' );

class JbblogShowBase extends JbblogBaseController
{
	function _buildParams()
	{
		$mainframe	= JFactory::getApplication();
		$params = new JRegistry();
		$params->def('link_titles', $mainframe->getCfg('link_titles'));
		$params->def('author', !$mainframe->getCfg('hideAuthor'));
		$params->def('createdate', !$mainframe->getCfg('hideCreateDate'));
		$params->def('modifydate', !$mainframe->getCfg('hideModifyDate'));
		$params->def('print', !$mainframe->getCfg('hidePrint'));
		$params->def('pdf', !$mainframe->getCfg('hidePdf'));
		$params->def('email', !$mainframe->getCfg('hideEmail'));
		$params->def('rating', $mainframe->getCfg('vote'));
		$params->def('icons', $mainframe->getCfg('icons'));
		$params->def('readmore', $mainframe->getCfg('readmore'));
		$params->def('popup', $mainframe->getCfg('popup'));
		$params->def('image', 1);
		$params->def('section', 0);
		$params->def('section_link', 0);
		$params->def('category', 0);
		$params->def('category_link', 0);
		$params->def('introtext', 1);
		$params->def('pageclass_sfx', '');
		$params->def('item_title', 1);
		$params->def('url', 1);
		$params->set('intro_only', 0);

		return $params;
	}
}