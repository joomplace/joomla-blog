<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

// For security reasons use build in content model class
require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'article.php');

class JoomBlogModelPost extends ContentModelArticle
{
	protected $context = 'com_joomblog';

	protected function canDelete($data, $key = 'id') {
		return JFactory::getUser()->authorise('core.delete', 'com_joomblog.article.'.((int) isset($data->$key) ? $data->$key : 0));
		return true;
	}

	public function getTable($type = 'Post', $prefix = 'JoomBlogTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ( !empty($item->custom_metatags) && !is_array($item->custom_metatags) )
			$item->custom_metatags = unserialize( $item->custom_metatags );

		return $item;
	}


	public function getForm($data = array(), $loadData = false) 
	{
		$form = $this->loadForm('com_joomblog.post', 'post', array('control' => 'jform', 'load_data' => $loadData));
		$item = $this->getItem();

		
		$form->setFieldAttribute('catid', 'extension', 'com_joomblog');

		if (empty($item->id)) {
			$app = JFactory::getApplication();
			$item->set('catid', $app->getUserStateFromRequest('com_joomblog.filter.category_id', 'filter_author_id'));
			$item->set('blog_id', $app->getUserStateFromRequest('com_joomblog.filter.blog_id', 'filter_author_id'));
			$item->set('created_by', $app->getUserStateFromRequest('com_joomblog.filter.author_id', 'filter_author_id'));
		}

		$form->bind($item);

		if (empty($form)) {
			return false;
		}
		return $form;
	}

	public function save($data)
	{
		$custom_tags = array();
		$custom_tags_names = JFactory::getApplication()->input->get('cm_names', array(), 'array');
		$custom_tags_values = JFactory::getApplication()->input->get('cm_values', array(), 'array');

		if ( !empty($custom_tags_names) )
		{
			foreach ( $custom_tags_names as $k => $custom_name )
				$custom_tags[ $custom_name ] = $custom_tags_values[ $k ];
		}

		$data['custom_metatags'] = serialize($custom_tags);

		return parent::save($data);
	}
}
