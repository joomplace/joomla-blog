<?php
/**
 * JoomBlog component for Joomla 1.6 & 1.7
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

class TableListBlogs extends JTable
{
	var $id = null;
	var $user_id = null;
	var $published = null;
	var $create_date = null;
	var $hits = null;
	var $private = 0;
	var $title = 'A title about your blog';
	var $description = "A short description about your blog";
	var $metadata = null;
	var $metakey = null;

	function __construct(&$db)
	{
		parent::__construct('#__joomblog_list_blogs', 'id', $db);
	}

	public function load($keys = null, $reset = true)
	{
		$id = $keys;
		$db =& $this->getDBO();
		parent::load($id);
	}

	public function store($updateNulls = false)
	{
		$db =& $this->getDBO();
		$data = new stdClass();

		$data->id = $this->id;
		$data->user_id = $this->user_id;
		$data->description = $this->description;
		$data->title = $this->title;
		$data->published = $this->published;
		$data->create_date = $this->create_date;
		$data->hits = $this->hits;
		$data->private = $this->private;
		$data->metadata = $this->metadata;
		$data->metakey = $this->metakey;

		if (!$data->id && $data != '')
		{
			$db->insertObject('#__joomblog_list_blogs', $data, 'id');
			$return = $db->insertid();
		}
		else
		{
			$db->updateObject('#__joomblog_list_blogs', $data, 'id');
			$return = $data->id;
		}

		return $return;
	}
}