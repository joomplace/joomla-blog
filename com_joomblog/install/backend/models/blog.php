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

class JoomBlogModelBlog extends JModelAdmin
{
	protected $context = 'com_joomblog';

	public function getTable($type = 'Blog', $prefix = 'JoomBlogTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function canDelete($record)
	{
		return true;
	}


	public function delete(&$pks)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user		= JFactory::getUser();
		$pks		= (array) $pks;
		$table		= $this->getTable();

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {

			if ($table->load($pk)) {

				if ($this->canDelete($table)) {

					$context = $this->option.'.'.$this->name;

					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					}

				} else {
					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();
					if ($error) {
						JError::raiseWarning(500, $error);
					}
					else {
						JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
					}
				}

			} else {
				$this->setError($table->getError());
				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		$form = $this->loadForm('com_joomblog.blog', 'blog', array('control' => 'jform', 'load_data' => false));
		$item = $this->getItem();

		if (empty($item->id)) {
			$app = JFactory::getApplication();
			$item->set('user_id', $app->getUserStateFromRequest('com_joomblog.filter.author_id', 'filter_author_id'));
		}

		$form->bind($item);

		if (empty($form)) {
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
	
	public function publish_approve(&$pks, $value = 1)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user		= JFactory::getUser();
		$table		= $this->getTable();
		$pks		= (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk) {
			$table->reset();

			if ($table->load($pk)) {
				if (!$this->canEditState($table)) {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
					return false;
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->publish_approve($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		$context = $this->option.'.'.$this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}
