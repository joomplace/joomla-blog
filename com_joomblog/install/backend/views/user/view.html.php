<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewUser extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $groups;
	protected $sidebar;
	protected $actions;

	public function display($tpl = null) 
	{
		JoomBlogHelper::getSideBarMenu($this);

		JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());
		$this->sidebar = JHtmlSidebar::render();

		JHtml::addIncludePath(JPATH_ROOT . '/administrator/components/com_users/helpers/html');

		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->groups = $this->get('AssignedGroups');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->addToolBar();

		$lang = JFactory::getLanguage();
		$lang->load('com_users', JPATH_ADMINISTRATOR);

		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		$isNew = $this->item->id == 0;
		$canDo = $this->getActions();

		JoomBlogHelper::showTitle($isNew ? JText::_('COM_JOOMBLOG_USER_CREATING') : JText::_('COM_JOOMBLOG_USER_EDITING'), 'user');

		if ($canDo->get('core.admin')) {
			JToolBarHelper::apply('user.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('user.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('user.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		JToolBarHelper::cancel('user.cancel', 'JTOOLBAR_CANCEL');
	}

	public function getActions()
	{
		if (empty($this->actions)) {
			$user	= JFactory::getUser();
			$this->actions	= new JObject;

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
