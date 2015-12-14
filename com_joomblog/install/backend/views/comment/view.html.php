<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewComment extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $sidebar;
	protected $custom;

	public function display($tpl = null) 
	{
		JoomBlogHelper::getSideBarMenu($this);

		JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());
		$this->sidebar = JHtmlSidebar::render();

		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->custom = $this->get('Custom');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->addToolBar();

		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		$isNew = $this->item->id == 0;
		$canDo = JoomBlogHelper::getActions();

		JoomBlogHelper::showTitle($isNew ? JText::_('COM_JOOMBLOG_COMMENT_CREATING') : JText::_('COM_JOOMBLOG_COMMENT_EDITING'), 'comments');

		if ($canDo->get('core.manage')) {
			JToolBarHelper::apply('comment.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('comment.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('comment.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			if (!$isNew) {
				JToolBarHelper::custom('comment.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('comment.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
