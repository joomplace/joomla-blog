<?php
/**
 * JoomPortfolio component for Joomla 1.6
 * @package   JoomPortfolio
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomBlogViewExport extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;
	protected $blogs;
	protected $sidebar;
	protected $importForm;
	protected $importData;

	function display($tpl = null)
	{
		$this->blogs = $this->get('Blogs');
		$this->importForm = $this->get('Form');
		foreach ($this->blogs as $blog)
		{
			$postsModel = JModelLegacy::getInstance('Posts', 'JoomBlogModel', array('ignore_request' => true));
			$postsModel->setState('filter.blog_id', $blog->id);
			$postsData = $postsModel->getItems();
			$blog->totalPosts = count($postsData);
		}
		$layout = $this->getLayout();

		if ($layout == 'import')
		{
			$this->importData = $this->get('importPosts');
			$this->importData = $this->getModel()->prepareImportDefaultData($this->importData);
		}
		if ($layout == 'import_article')
		{
			$this->importData = $this->get('importArticlePosts');
		}

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		JoomBlogHelper::getSideBarMenu($this);
		JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());
		$this->sidebar = JHtmlSidebar::render();

		JoomBlogHelper::showTitle(JText::_('COM_JOOMBLOG_SUBMENU_EXPORT_IMPORT'), 'stack');

		parent::display($tpl);
	}
}