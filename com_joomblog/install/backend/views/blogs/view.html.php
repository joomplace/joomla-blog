<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewBlogs extends JViewLegacy
{
	public $messageTrigger = false;
	protected $blogs;
	protected $state;
	protected $users;
	protected $pagination;
	protected $canDo;
	protected $user;
	protected $sidebar;
	
	function display($tpl = null) 
	{
		$this->blogs = $this->get('Items');
		$this->state = $this->get('State');
		$this->users = $this->get('Users');
		$this->messageTrigger = $this->get('CurrDate');
		$this->pagination = $this->get('Pagination');

		JoomBlogHelper::getSideBarMenu($this);

		JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());

		JHtmlSidebar::addFilter(
			JText::_('COM_JOOMBLOG_OPTION_SELECT_AUTHOR'),
			'filter_author_id',
			JHtml::_('select.options', $this->users, 'id', 'name', $this->state->get('filter.author_id'))
		);

		$this->sidebar = JHtmlSidebar::render();

		$this->user = JFactory::getUser();

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
		$this->canDo = $canDo = JoomBlogHelper::getActions();

		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_MANAGER_BLOGS'), 'book');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('blog.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit') or $canDo->get('core.edit.own')) {
			JToolBarHelper::editList('blog.edit', 'JTOOLBAR_EDIT');
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('blogs.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('blogs.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'blogs.delete', 'JTOOLBAR_DELETE');
		}
		if ($canDo->get('core.admin')) {
			//JToolBarHelper::divider();
			//JToolBarHelper::preferences('com_joomblog');
		}
	}
}
