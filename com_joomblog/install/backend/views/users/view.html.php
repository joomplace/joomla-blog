<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewUsers extends JViewLegacy
{
	protected $users;
	protected $state;
	protected $groups;
	protected $pagination;
	protected $canDo;
	protected $sidebar;

	public $messageTrigger = false;

	function display($tpl = null) 
	{
		$this->users = $this->get('Items');
		$this->state = $this->get('State');
		$this->groups = $this->get('Groups');
		$this->messageTrigger = $this->get('CurrDate');
		$this->pagination = $this->get('Pagination');

		JoomBlogHelper::getSideBarMenu($this);
		JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());

		JHtmlSidebar::addFilter(
			JText::_('COM_JOOMBLOG_OPTION_SELECT_GROUP'),
			'filter_group_id',
			JHtml::_('select.options', $this->groups, 'id', 'title', $this->state->get('filter.group_id'))
		);

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
		$this->canDo = $canDo = $this->getActions();

		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_MANAGER_USERS'), 'users');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('user.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit') or $canDo->get('core.edit.own')) {
			JToolBarHelper::editList('user.edit', 'JTOOLBAR_EDIT');
		}
		JToolBarHelper::divider();
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('users.unblock', 'unblock.png', 'unblock_f2.png', 'COM_JOOMBLOG_TOOLBAR_UNBLOCK', true);
			JToolBarHelper::custom('users.block', 'unpublish.png', 'unpublish_f2.png','COM_JOOMBLOG_TOOLBAR_BLOCK', true);
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'users.delete', 'JTOOLBAR_DELETE');
		}
	}

	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JOOMBLOG_MANAGER_USERS'));
	}

	public function getActions()
	{
		if (empty($this->actions)) {
			$user = JFactory::getUser();
			$this->actions = new JObject;

			$actions = array(
				'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
			);

			foreach ($actions as $action) {
				$this->actions->set($action, $user->authorise($action, 'com_users'));
			}
		}

		return $this->actions;
	}
}
