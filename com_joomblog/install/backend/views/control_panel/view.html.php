<?php
defined('_JEXEC') or die('Restricted access');
/*
* JoomBlog Component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

class JoomBlogViewControl_Panel extends JViewLegacy
{
	protected $config;
	//----------------------------------------------------------------------------------------------------
	function display($tpl = null) 
	{
		$document = JFactory::getDocument();
		$document->addScript(JURi::root() . 'administrator/components/com_joomblog/assets/js/MethodsForXml.js');
		$document->addScript(JURi::root() . 'administrator/components/com_joomblog/assets/js/MyAjax.js');

		@$this->config->version = JoomBlogHelper::getVersion();

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_BE_MENU_CONTROL_PANEL'), 'generic');
	}
}