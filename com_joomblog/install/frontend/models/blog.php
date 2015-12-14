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
	
	public function getForm($data = array(), $loadData = true) 
	{
		$form = $this->loadForm('com_joomblog.blog', 'blog', array('control' => 'jform', 'load_data' => false));
		$item = $this->getItem();

		if (empty($item->id)) {
			$app = JFactory::getApplication();
			$item->set('user_id', $app->getUserStateFromRequest('com_joomblog.filter.author_id', 'filter_author_id'));
		}
		if (!empty($data))
		{
			$item->id = $data['id'];
			$item->user_id = $data['user_id'] ;
			$item->published = $data['published'] ;
			$item->create_date = $data['create_date'] ;
			$item->title = $data['title'] ;
			$item->alias = $data['alias'] ;
			$item->description = $data['description'] ;
			$item->metadesc = $data['metadesc'] ;
			$item->metakey = $data['metakey'] ;
			$item->asset_id = $data['asset_id'] ;
			$item->approved = $data['approved'] ;
			$item->access = $data['access'] ;
			$item->waccess = $data['waccess'] ;
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
}
