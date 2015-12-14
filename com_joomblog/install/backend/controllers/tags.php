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

class JoomBlogControllerTags extends JControllerAdmin
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('defaults', 'changeDefault');
		$this->registerTask('nodefault', 'changeDefault');
	}

	public function getModel($name = 'Tag', $prefix = 'JoomBlogModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	public function changeDefault()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$jinput = JFactory::getApplication()->input;
		$ids	= $jinput->get('cid', array(), '', 'array');
		$values	= array('defaults' => 1, 'nodefault' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_JOOMBLOG_USERS_NO_ITEM_SELECTED'));
		} else {
			$model = $this->getModel();

			if (!$model->setDefault($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			} else {
				if ($value == 1){
					$this->setMessage(JText::plural('COM_JOOMBLOG_N_TAGS_DEFAULT', count($ids)));
				} else if ($value == 0){
					$this->setMessage(JText::plural('COM_JOOMBLOG_N_TAGS_UNDEFAULT', count($ids)));
				}
			}
		}

		$this->setRedirect('index.php?option=com_joomblog&view=tags');
	}
}
