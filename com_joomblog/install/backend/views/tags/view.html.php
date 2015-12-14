<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewTags extends JViewLegacy
{
	protected $tags;
	protected $state;
	protected $pagination;
	protected $canDo;
	protected $sidebar;

	public $messageTrigger = false;

	function display($tpl = null) 
	{
		$this->tags = $this->get('Items');
		$this->state = $this->get('State');
		$this->messageTrigger = $this->get('CurrDate');
		$this->pagination = $this->get('Pagination');

		JoomBlogHelper::getSideBarMenu($this);
		JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());
		$this->sidebar = JHtmlSidebar::render();

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->addToolBar();

		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		$this->canDo = $canDo = JoomBlogHelper::getActions();

		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_MANAGER_TAGS'), 'tags');

		if ($canDo->get('core.manage'))
		{
			JToolBarHelper::addNew('tag.add', 'JTOOLBAR_NEW');
			JToolBarHelper::editList('tag.edit', 'JTOOLBAR_EDIT');
			JToolBarHelper::deleteList('', 'tags.delete', 'JTOOLBAR_DELETE');
		}
	}
}
