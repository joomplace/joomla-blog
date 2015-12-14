<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewComments extends JViewLegacy
{
	protected $comments;
	protected $state;
	protected $pagination;
	protected $canDo;
	protected $sidebar;

	public $messageTrigger = false;

	function display($tpl = null) 
	{
		$this->comments = $this->get('Items');
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

		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_MANAGER_COMMENTS'), 'comments');

		if ($canDo->get('core.manage')) {
			JToolBarHelper::addNew('comment.add', 'JTOOLBAR_NEW');
			JToolBarHelper::editList('comment.edit', 'JTOOLBAR_EDIT');
			JToolBarHelper::divider();
			JToolBarHelper::custom('comments.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('comments.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'comments.delete', 'JTOOLBAR_DELETE');
		}
	}

	protected function crop($text, $needle = ' ', $offset = 60) {
		if (strlen($text) <= $offset) return $text;
		$rest = substr($text, 0, $offset);
		$pos = strrpos($rest, $needle);
		if ($pos !== FALSE) {
			$rest = substr($rest, 0, $pos);
		}
		return $rest.' ...';
	}
}
