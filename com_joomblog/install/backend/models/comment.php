<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');

class JoomBlogModelComment extends JModelAdmin
{
	protected $context = 'com_joomblog';

	protected function allowEdit($data = array(), $key = 'id')
	{
		return JFactory::getUser()->authorise('core.admin', 'com_joomblog');
	}

	public function getTable($type = 'Comment', $prefix = 'JoomBlogTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		$form = $this->loadForm('com_joomblog.comment', 'comment', array('control' => 'jform', 'load_data' => false));
		
		$form->bind($this->getItem());

		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}

	public function getItem($pk = null)
	{
		if (!isset($this->item)) {
			$pk		= (!empty($pk)) ? $pk : (int) $this->getState($this->getName().'.id');
			$table	= $this->getTable();

			if ($pk > 0) {
				$return = $table->load($pk);

				if ($return === false && $table->getError()) {
					$this->setError($table->getError());
					return false;
				}
			}

			$properties = $table->getProperties(1);
			$this->item = new JObject($properties);
		}

		return $this->item;
	}
}
