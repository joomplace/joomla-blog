<?php
/**
 * JoomBlog Tags Module for Joomla
 * @version    $Id: helper.php 2011-03-16 17:30:15
 * @package    JoomBlog
 * @subpackage helper.php
 * @author     JoomPlace Team
 * @Copyright  Copyright (C) JoomPlace, www.joomplace.com
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class modJbTagsHelper
{
	function getList(&$params)
	{
		global $mainframe;

		$wrapperTag = isset($params) ? $params->get('wrapTag', 'div') : 'div';

		require_once(JPATH_ROOT . '/components/com_joomblog/task/tagslist.php');

		$jbItemid = jbGetItemID();
		$objFrontView = new JbblogTagslistTask();
		$tagCloud = $objFrontView->display('id="blog-tags-mod"', $wrapperTag);
		$mainframe =& JFactory::getApplication();

		$tagCloud = str_ireplace("<p>", "", $tagCloud);
		$tagCloud = str_ireplace("</p>", "", $tagCloud);
		$tagCloud = str_ireplace("<br/>", "", $tagCloud);

		if (trim($tagCloud) != "")
			return $tagCloud;

		return NULL;
	}
}
