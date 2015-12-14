<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewPost extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $custom;
	protected $jEditor;
	protected $_JB_CONFIGURATION;

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

		$jEditor = JFormHelper::loadFieldType('Html5editor', false);
		$jEditor->setName($this->form->getField('articletext')->name);
		$jEditor->setValue($this->form->getField('articletext')->value);
		$jEditor->setId($this->form->getField('articletext')->id);
		$jEditor->setLabel(JText::_('COM_JOOMBLOG_FIELD_HEADING_TEXT'));
		$jEditor->postId = $this->item->id;
		$this->jEditor = $jEditor;

		require_once(JPATH_COMPONENT_ADMINISTRATOR . '/config.joomblog.php');
		$this->_JB_CONFIGURATION = new JB_Configuration();

		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		$user = JFactory::getUser();
		$isNew = $this->item->id == 0;
		$canDo = JoomBlogHelper::getActions(false, $this->item->id);

		JoomBlogHelper::showTitle($isNew ? JText::_('COM_JOOMBLOG_ITEM_CREATING') : JText::_('COM_JOOMBLOG_ITEM_EDITING'), 'list-2');

		if ($isNew) {
			if ($canDo->get('core.create')) {
				JToolBarHelper::apply('post.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('post.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('post.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
		} else {
			if ($canDo->get('core.edit') or ($canDo->get('core.edit.own') and ($this->item->created_by == $user->id))) {
				JToolBarHelper::apply('post.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('post.save', 'JTOOLBAR_SAVE');
				if ($canDo->get('core.create')) {
					JToolBarHelper::custom('post.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					JToolBarHelper::custom('post.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
			}
		}
		JToolBarHelper::cancel('post.cancel', 'JTOOLBAR_CANCEL');
	}
}
