<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewPosts extends JViewLegacy
{
	protected $posts;
	protected $state;
	protected $blogs;
	protected $categories;
	protected $users;
	protected $pagination;
	protected $user;
	protected $canDo;
	protected $sidebar;
	protected $tags;

	public $messageTrigger = false;

	function display($tpl = null) 
	{
		$this->posts = $this->get('Items');
		$this->state = $this->get('State');
		$this->blogs = $this->get('Blogs');
		$this->messageTrigger = $this->get('CurrDate');
		$this->categories = $this->get('Categories');
		$this->users = $this->get('Users');
		$this->pagination = $this->get('Pagination');
		$this->tags = $this->get('FilteredTags');

		$this->user = JFactory::getUser();

		$this->addSidebar();
		$this->sidebar = JHtmlSidebar::render();
		
		$model = $this->getModel();
		if (sizeof($this->posts))
		{
			foreach ( $this->posts as $post )
		 	{
		 		$post->cats = $model->getMulticats($post->id);
		 		$post->tags = $model->getTags($post->id);
		 	}
		}
			
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->addToolBar();

		parent::display($tpl);
	}

	protected function addSidebar()
	{
		JoomBlogHelper::getSideBarMenu($this);

		JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', $this->categories, 'id', 'title', $this->state->get('filter.category_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_JOOMBLOG_OPTION_SELECT_BLOG'),
			'filter_blog_id',
			JHtml::_('select.options', $this->blogs, 'id', 'title', $this->state->get('filter.blog_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_JOOMBLOG_OPTION_SELECT_AUTHOR'),
			'filter_author_id',
			JHtml::_('select.options', $this->users, 'id', 'name', $this->state->get('filter.author_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_JOOMBLOG_FIELD_HEADING_TAG'),
			'filter_tags_id',
			JHtml::_('select.options', $this->tags, 'id', 'name', $this->state->get('filter.tags_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_JOOMBLOG_OPTION_SELECT_STATE'),
			'filter_status',
			JHtml::_('select.options', $this->getStateFilter(), 'id', 'value', $this->state->get('filter.status'))
		);
	}

	protected function getStateFilter()
	{
		return array(
			array (
				"id" => "1", "value" => JText::_('COM_JOOMBLOG_PUBLISHED')
			),
			array (
				"id" => "0", "value" => JText::_('COM_JOOMBLOG_UNPUBLISHED')
			),
			array (
				"id" => "-2", "value" => JText::_('COM_JOOMBLOG_DRAFT')
			)
		);
	}

	protected function addToolBar() 
	{
		$this->canDo = $canDo = JoomBlogHelper::getActions($this->state->get('filter.category_id'));

		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_MANAGER_POSTS'), 'list-2');

		if ($canDo->get('core.create') and $this->categories) {
			JToolBarHelper::addNew('post.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit') or $canDo->get('core.edit.own')) {
			JToolBarHelper::editList('post.edit', 'JTOOLBAR_EDIT');
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('posts.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('posts.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'posts.delete', 'JTOOLBAR_DELETE');
		}
		if ($canDo->get('core.admin')) {
			//JToolBarHelper::divider();
			//JToolBarHelper::preferences('com_joomblog');
		}
	}
}
