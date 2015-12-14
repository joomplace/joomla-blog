<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewDrafts extends JViewLegacy
{
	protected $drafts;
	protected $state;
	protected $blogs;
	protected $categories;
	protected $users;
	protected $pagination;
	protected $user;

	function display($tpl = null) 
	{
		$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');

		$this->drafts = $this->get('Items');
		$this->state = $this->get('State');
		$this->blogs = $this->get('Blogs');
		$this->categories = $this->get('Categories');
		$this->users = $this->get('Users');
		$this->pagination = $this->get('Pagination');

		$this->user = &JFactory::getUser();
		
		$model = $this->getModel();
		 if (sizeof($this->drafts))
		 {
		 	foreach ( $this->drafts as $draft ) 
		 	{
		 		$draft->cats = $model->getMulticats($draft->id);
		 		$draft->tags = $model->getTags($draft->id);
		 	}
		 }
			
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->addToolBar();
		$this->setDocument();

		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		$this->canDo = $canDo = JoomBlogHelper::getActions($this->state->get('filter.category_id'));
		JToolBarHelper::title(JText::_('COM_JOOMBLOG_MANAGER_DRAFTS'), 'drafts');
		if ($canDo->get('core.create') and $this->categories) {
			JToolBarHelper::addNew('post.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit') or $canDo->get('core.edit.own')) {
			JToolBarHelper::editList('post.edit', 'JTOOLBAR_EDIT');
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('drafts.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('drafts.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'drafts.delete', 'JTOOLBAR_DELETE');
		}
		if ($canDo->get('core.admin')) {
		}
	}

	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JOOMBLOG_MANAGER_DRAFTS'));
	}
}
