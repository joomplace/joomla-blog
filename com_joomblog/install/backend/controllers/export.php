<?php

/**
 * JoomBlog component for Joomla 3.x
 * @package JoomBlog
 * @author JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controllerform');
JLoader::register('XMLHelper', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomblog' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'xml.php');
class JoomBlogControllerExport extends JControllerForm
{
	public function export()
	{
		$blogs = JFactory::getApplication()->input->get('exportBlogs', array(), 'array');
		$exportUserIds = JFactory::getApplication()->input->get('exportUserIds', 0, 'integer');
		$exportCatIds = JFactory::getApplication()->input->get('exportCatIds', 0, 'integer');
		if ( count($blogs) == 0 )
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_EXPORT_SELECT_BLOG_TO_EXPORT'), 'error');
			JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export');
		} else {
			$resultXml = new DOMDocument('1.0', 'UTF-8');
			$blogsXML = $resultXml->createElement('blogs');
			foreach ($blogs as $blog_id) {
				$blogModel = JModelLegacy::getInstance('Blog', 'JoomBlogModel', array('ignore_request' => true));
				$blogData = $blogModel->getItem($blog_id);
				if ( !empty($blogData) ) {
					unset($blogData->_errors);
					if ( $exportUserIds == 0 ) unset($blogData->user_id);
					$postsModel = JModelLegacy::getInstance('Posts', 'JoomBlogModel', array('ignore_request' => true));
					$postsModel->setState('filter.blog_id', $blog_id);
					$postsData = $postsModel->getItems();
					foreach ($postsData as $post) {
						$postModel = JModelLegacy::getInstance('Post', 'JoomBlogModel', array('ignore_request' => true));
						$postData = $postModel->getItem($post->id);
						unset($postData->_errors);
						unset($postData->articletext);
						if ( $exportUserIds == 0 ) unset($postData->created_by);
						if ( $exportCatIds == 1 ) {
							$postData->cats = $postsModel->getMulticats($postData->id);
						}

						$postData->custom_metatags = (!empty($postData->custom_metatags) ? serialize($postData->custom_metatags) : '');
						$blogData->posts[] = $postData;

					}
					$blogsXML->appendChild(XMLHelper::addDomElement($blogData, 'blog', $resultXml));
				}
			}
			$resultXml->appendChild($blogsXML);
			$resultXml->preserveWhiteSpace = false;
			$resultXml->formatOutput = true;

			header('Content-Disposition: attachment; filename="joomblog_export_' . date("d.m.Y") . '.xml"');
			echo $resultXml->saveXML();
		}
		die();
	}

	public function cancel()
	{
		$this->setRedirect('index.php?option=com_joomblog&view=export');
	}

	public function importarticle()
	{
		$importPreferences = JFactory::getApplication()->input->get('jform', array(), 'array');
		JFactory::getApplication()->setUserState('com_joomblog.import.preferences', $importPreferences);
		if ( empty($importPreferences['article_user_id']) ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_EMPTY_DEFAULT_USER'), 'error');
		}
		if ( JFactory::getApplication()->getMessageQueue() ) {
			JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export');
		}

		JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export&layout=import_article');
	}

	public function import()
	{
		$importFile = JFactory::getApplication()->input->files->get('jform');
		$importPreferences = JFactory::getApplication()->input->get('jform', array(), 'array');
		JFactory::getApplication()->setUserState('com_joomblog.import.preferences', $importPreferences);
		if ( empty($importFile) ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_NO_FILE'), 'error');
		} else {
			$importFile = $importFile['importFile'];
		}
		if ( !empty($importFile['type']) && $importFile['type'] != 'text/xml' ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_WRONG_FILE_TYPE'), 'error');
		}
		if ( empty($importFile['size']) || $importFile['size'] == 0 ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_EMPTY_FILE'), 'error');
		}
		if ( empty($importPreferences['user_id']) ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_EMPTY_DEFAULT_USER'), 'error');
		}
		if ( empty($importPreferences['catid']) ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_EMPTY_DEFAULT_CATEGORY'), 'error');
		}
		if ( JFactory::getApplication()->getMessageQueue() ) {
			JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export');
		}

		jimport('joomla.filesystem.file');

		if ( !JFile::move($importFile['tmp_name'], JFactory::getConfig()->get('tmp_path') . DIRECTORY_SEPARATOR . 'joomblog_' . md5($importFile['name']) . '.xml') ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_MOVE_FILE_ERROR'), 'error');
			JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export');
		}
		JFactory::getApplication()->setUserState('com_joomblog.import.file_name', $importFile['name']);
		$model = $this->getModel();
		$result = $model->getImportPosts();
		if ( empty($result->blogs) ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_NOTHING_TO_IMPORT'), 'error');
			JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export');
		}
		JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export&layout=import');
	}

	public function importArticleSave()
	{
		$importArticles = JFactory::getApplication()->input->get('cid', array(), 'array');

		$model = $this->getModel();
		$result = $model->importArticles( $importArticles );

		JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export&layout=importdone');
	}

	public function importSave()
	{
		$importBlogs = JFactory::getApplication()->input->get('importBlogs', array(), 'array');
		$importPosts = JFactory::getApplication()->input->get('importPosts', array(), 'array');
		if ( count($importBlogs) == 0 ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_SELECT_BLOG_TO_IMPORT'), 'error');
			JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export&layout=import');
		}
		$model = $this->getModel();
		$result = $model->getImportPosts();
		$result = $model->prepareImportDefaultData($result);
		if ( empty($result->blogs) ) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMBLOG_TAB_IMPORT_ERROR_READING_XML'), 'error');
			JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export&layout=import');
		}
		foreach ($importBlogs as $blog_id) {
			if ( !empty($result->blogs[$blog_id]) ) {
				$blogData = $result->blogs[$blog_id];
				if ( !empty($result->blogs[$blog_id]->posts) ) {
					$postsData = $blogData->posts;
					unset($blogData->posts);
				} else $postsData = array();
				$blogData = $model->saveBlog($blogData);
				if ( $blogData ) {
					foreach ($postsData as $post_id => $postData) {
						if ( in_array($post_id, $importPosts[$blog_id]) ) {
							$postData->blog_id = $blogData->id;
							$postData->catid = array();
							foreach ($postData->cats as $cat) {
								$postData->catid[] = $cat->cid;
							}
							$postData = $model->savePost($postData);
						}
					}
				}
			}
		}
		JFactory::getApplication()->redirect('index.php?option=com_joomblog&view=export&layout=importdone');
	}
}
