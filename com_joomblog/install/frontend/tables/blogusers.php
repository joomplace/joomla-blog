<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class TableBlogUsers extends JTable
{
	var $user_id = null;
	var $description = "A short description about your blog";
	var $title = 'A title about your blog';
	var $feedburner = '';
	var $style = null;
	var $userStyle = null;
	var $width = null;
	var $height = null;
	var $params = null;
	var $about = null;
	var $twitter = null;
	var $site = null;
	var $birthday = null;
	var $avatar = null;
	var $post_count = null;
	var $facebook = null;
	var $google_plus = null;

	function __construct(&$db)
	{
		parent::__construct('#__joomblog_user', 'user_id', $db);
	}

	function getStyle($style)
	{
		if (isset($this->userStyle[$style]))
		{
			return $this->userStyle[$style];
		}
		else
			return '';
	}

	public function load($keys = null, $reset = true)
	{
		$id = $keys;
		$db = $this->getDBO();

		if ($id != 0)
		{
			$query = "SELECT `user_id` FROM #__joomblog_user WHERE `user_id`='{$id}'";
			$db->setQuery($query);

			$userId = $db->loadResult();

			if (!$userId)
			{
				$data = new stdClass();
				$data->user_id = $id;
				$data->description = 'A short description about your blog';

				$db->insertObject('#__joomblog_user', $data);
			}
		}
		parent::load($id);

		$user = JFactory::getUser($id);
		$this->name = $user->get('name');
		$this->registered = $user->get('registerDate');
		$this->lastvisit = $user->get('lastvisitDate');
		$this->username = $user->get('username');

	}

	public function store($isNew = false)
	{
		$db = $this->getDBO();
		$data = new stdClass();

		$data->user_id = $this->user_id;
		$data->description = $this->description;
		$data->title = $this->title;
		$data->feedburner = $this->feedburner;
		$data->style = $this->style;
		$data->params = $this->params;
		$data->about = $this->about;
		$data->twitter = $this->twitter;
		$data->site = $this->site;
		$data->birthday = $this->birthday;
		$data->avatar = $this->avatar;
		if (empty($this->post_count)) $this->post_count = 5;
		$data->post_count = $this->post_count;
		$data->facebook = $this->facebook;
		$data->google_plus = $this->google_plus;

		if ($isNew)
		{
			$id = $db->insertObject('#__joomblog_user', $data);
		}
		else
		{
			$db->updateObject('#__joomblog_user', $data, 'user_id');
			$id = $data->user_id;
		}

		return $id;
	}
} 