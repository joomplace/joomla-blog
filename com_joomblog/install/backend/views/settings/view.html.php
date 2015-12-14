<?php
/**
 * JoomPortfolio component for Joomla 1.6
 * @package   JoomPortfolio
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewSettings extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;
	protected $custom;
	protected $sidebar;
	public $messageTrigger = false;

	function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->custom = $this->get('Custom');
		$this->messageTrigger = $this->get('CurrDate');

		JoomBlogHelper::getSideBarMenu($this);
		JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());
		$this->sidebar = JHtmlSidebar::render();

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->addToolBar();

		parent::display($tpl);
	}

	protected function addToolBar()
	{
		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_EDIT_SETTINGS'), 'options');

		JToolBarHelper::apply('settings.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::custom('settings.reset', 'checkbox-partial', 'checkbox-partial', 'COM_JOOMBLOG_SETTINGS_RESET_SETTINGS', FALSE);
		JToolBarHelper::cancel('settings.cancel', 'JTOOLBAR_CANCEL');
	}
}