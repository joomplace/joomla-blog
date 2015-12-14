<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

class JoomBlogViewSampledata extends JViewLegacy
{
	protected $canDo;

	function display($tpl = null) 
	{
		$this->canDo = $canDo = JoomBlogHelper::getActions();

		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_MANAGER_SAMPLEDATA'), 'download');

		parent::display($tpl);
	}
}