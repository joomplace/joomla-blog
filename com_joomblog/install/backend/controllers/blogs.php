<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

class JoomBlogControllerBlogs extends JControllerAdmin
{
	
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Define standard task mappings.
			$this->registerTask('approve',	'publish_approve');
			$this->registerTask('unapprove',	'publish_approve');
	}
		
	
	public function getModel($name = 'Blog', $prefix = 'JoomBlogModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	public function delete ()
	{
		$db	= JFactory::getDBO();
		$cid = JRequest::getVar('cid', array(), '', 'array');
		$delete = array();
		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
		}
		for ($j=0; $j<count($cid); $j++ )
		{
			$db->setQuery("SELECT `id` FROM #__joomblog_blogs WHERE `blog_id`=".$cid[$j]);
			$blog_items = $db->loadObjectList();
			if ($blog_items)
			{
				$db->setQuery("SELECT `title` FROM #__joomblog_list_blogs WHERE `id`=".$cid[$j]);
				$blog_name = $db->loadResult();
				JError::raiseWarning(500, sprintf(JText::_('COM_JOOMBLOG_CAN_NOT_DELETE_BLOG'),$blog_name));
			}
			else
			{
				$delete[]=$cid[$j];
				$query	= "DELETE FROM `#__assets` WHERE `name`='com_joomblog.blog.".$cid[$j]."'";
				$db->setQuery( $query );
				$db->execute();
			}
		}
		if (count($delete)>0)
		{
			$delete_string = '('.implode(',',$delete).')';
			$query	= "DELETE FROM `#__joomblog_blogs` WHERE `blog_id` IN $delete_string ";
			$db->setQuery( $query );
			$db->execute();
			$query	= "DELETE FROM `#__joomblog_list_blogs` WHERE `id` IN $delete_string ";
			$db->setQuery( $query );
			$db->execute();
			$this->setRedirect(JRoute::_('index.php?option=com_joomblog&view=blogs', false), sprintf(Jtext::_('COM_JOOMBLOG_BLOGS_SUCCESS_DELETION'),count($delete)) );
			$this->redirect();
		}
	}

	
	public function publish_approve()
       {
       	  	// Check for request forgeries

       	  	JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$session	= JFactory::getSession();
		$registry	= $session->get('registry');

		// Get items to publish from the request.
		$cid	= JFactory::getApplication()->input->get('cid', array(), '', 'array');
		$data	= array('approve' => 1, 'unapprove' => 0);
		$task 	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid)) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->publish_approve($cid, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
			else {
				if ($value == 1) {
					$ntext = $this->text_prefix.'_N_ITEMS_APPROVED';
				}
				else if ($value == 0) {
					$ntext = $this->text_prefix.'_N_ITEMS_UNAPPROVED';
				}
				else if ($value == 2) {
					$ntext = $this->text_prefix.'_N_ITEMS_ARCHIVED';
				}
				else {
					$ntext = $this->text_prefix.'_N_ITEMS_TRASHED';
				}
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
		$extension = JFactory::getApplication()->input->get('extension');
		$extensionURL = ($extension) ? '&extension=' . $extension : '';
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$extensionURL, false));
	}
}
