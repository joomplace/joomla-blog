<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewPlugins extends JViewLegacy
{
	protected $plugins;
	protected $state;
	protected $pagination;
	protected $user;
	protected $canDo;
	protected $sidebar;
	public $messageTrigger = false;

	function display($tpl = null) 
	{
		$this->plugins = $this->get('Items');
		$this->state = $this->get('State');
		$this->messageTrigger = $this->get('CurrDate');
		$this->pagination = $this->get('Pagination');

		$this->user = JFactory::getUser();

		JoomBlogHelper::getSideBarMenu($this);
		JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());

		$sortFields = array(
			'content'     => JText::_( 'content' ),
			'editors-xtd' => JText::_( 'editors-xt' )
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_JOOMBLOG_PLUGINS_SELECT_TYPE'),
			'search_plugin_type',
			JHtml::_('select.options', $sortFields, 'value', 'text', $this->state->get('filter.search_plugin_type'))
		);

		$this->sidebar = JHtmlSidebar::render();

		$this->addToolBar();

		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		$this->canDo = $canDo = JoomBlogHelper::getActions();
		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_MANAGER_PLUGINS'), 'power-cord');
		
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('plugins.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('plugins.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
		}
	}
}
