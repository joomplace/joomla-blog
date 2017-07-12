<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.database.tableasset');

class TablePosts extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__joomblog_posts', 'id', $db);
	}

	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		return 'com_joomblog.article.' . (int) $this->$k;
	}

	protected function _getAssetTitle()
	{
		return $this->title;
	}

	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$assetId = null;
		$db = $this->getDbo();

		if ($this->catid)
		{
			$query = $db->getQuery(true);
			$query->select('asset_id');
			$query->from('#__categories');
			$query->where('id = ' . (int) $this->catid);

			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	function autoCloseTags($string)
	{
		$donotclose = array('br', 'img', 'input');

		$tagstoclose = '';
		$tags = array();

		preg_match_all("/<(([A-Z]|[a-z]).*)(( )|(>))/isU", $string, $result);
		$openedtags = $result[1];
		$openedtags = array_reverse($openedtags);

		preg_match_all("/<\/(([A-Z]|[a-z]).*)(( )|(>))/isU", $string, $result2);
		$closedtags = $result2[1];

		for ($i = 0; $i < count($openedtags); $i++)
		{
			if (in_array($openedtags[$i], $closedtags))
			{
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
			else array_push($tags, $openedtags[$i]);
		}
		$tags = array_reverse($tags);

		for ($x = 0; $x < count($tags); $x++)
		{
			$add = strtolower(trim($tags[$x]));
			if (!in_array($add, $donotclose)) $tagstoclose .= '</' . $add . '>';
		}

		return $tagstoclose;
	}

	static function getParagraphCount($text)
	{
		$position = -1;
		$count = 0;

		while (($position = JString::strpos($text, '</p>', $position + 1)) !== false)
		{
			$count++;
		}

		return $count;
	}

	static function getBrowseText(&$row)
	{
		global $_JB_CONFIGURATION;

		if ($_JB_CONFIGURATION->get('useIntrotext'))
		{
			if (empty($row->fulltext))
			{
				$ending = JString::strpos($row->introtext, '</p>');

				$pos = -1;
				$pos_array = array();
				while (($pos = JString::strpos($row->introtext, '</p>', $pos + 1)) !== false)
					$pos_array[] = $pos;

				$pNum = $_JB_CONFIGURATION->get('autoReadmorePCount');
				if (count($pos_array) <= $pNum)
				{
					$row->text = $row->introtext;
				}
				else
				{
					$ending = $pos_array[$pNum - 1];
					$row->introtext = JString::substr($row->introtext, 0, $ending + 4);
					$row->introtext = jbCloseTags(preg_replace('#\s*<[^>]+>?\s*$#', '', $row->introtext));
				}
			}
			else if (!empty($row->fulltext) && empty($row->introtext))
			{
				$ending = JString::strpos($row->fulltext, '</p>');

				$pos = -1;
				$pos_array = array();
				while (($pos = JString::strpos($row->fulltext, '</p>', $pos + 1)) !== false)
					$pos_array[] = $pos;

				$pNum = $_JB_CONFIGURATION->get('autoReadmorePCount');
				if (count($pos_array) <= $pNum)
				{
					$row->text = $row->fulltext;
				}
				else
				{
					$ending = $pos_array[$pNum - 1];
					$row->fulltext = JString::substr($row->fulltext, 0, $ending + 4);
					$row->fulltext = jbCloseTags(preg_replace('#\s*<[^>]+>?\s*$#', '', $row->fulltext));
				}
			}

			if (empty($row->introtext))
			{
				$row->text = $row->fulltext;
			}
			else
			{
				$row->text = $row->introtext;
			}
		}
		else
		{
			if (empty($row->fulltext))
			{
				$row->text = $row->introtext;
			}
			else
			{
				$row->text = $row->fulltext;
			}
		}
	}

	function _splitReadmoreOnSave()
	{

		$this->fulltext = preg_replace('#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i', '{readmore}', $this->fulltext);
		$pos = JString::strpos($this->fulltext, '{readmore}');

		if ($pos === false)
		{
			$this->introtext = $this->fulltext;
			$this->fulltext = '';
		}
		else
		{
			$this->introtext = JString::substr($this->fulltext, 0, $pos);
			$this->fulltext = JString::substr($this->fulltext, $pos + 10);
		}
	}

	public function load($keys = null, $reset = true, $ignoreAccess = false)
	{
		if (empty($keys))
		{
			// If empty, use the value of the current key
			$keyName = $this->_tbl_key;
			$keyValue = $this->$keyName;

			// If empty primary key there's is no need to load anything
			if (empty($keyValue))
			{
				return true;
			}

			$keys = array($keyName => $keyValue);
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keys = array($this->_tbl_key => $keys);
		}

		if ($reset)
		{
			$this->reset();
		}


		// Initialise the query.
		$query = $this->_db->getQuery(true);
		$query->select('a.*, b.blog_id');
		$query->from($this->_tbl . ' as a');
		$query->join('LEFT', '#__joomblog_blogs AS b ON b.content_id = a.id');
		$fields = array_keys($this->getProperties());

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.
			if (!in_array($field, $fields))
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_IS_MISSING_FIELD', get_class($this), $field));
				$this->setError($e);
				return false;
			}
			// Add the search tuple to the query.
			$query->where('a.' . $this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
		}

		if (!in_array('8', JFactory::getUser()->getAuthorisedGroups()) && !$ignoreAccess)
		{

			$query->join('LEFT', '#__joomblog_list_blogs AS lb ON lb.id = b.blog_id');
			$query->join('LEFT', '#__joomblog_multicats AS mc ON mc.aid = a.id');
			$query->join('LEFT', '#__categories AS c ON c.id = mc.cid');
			$query->group("a.id");
			$user = JFactory::getUser();
			$user_id = (int) $user->id;
			$groups = implode(',', $user->getAuthorisedViewLevels());
			if (!JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false))
			{
				$query->where('(a.access IN (' . $groups . ') OR a.created_by=' . $user_id . ')');
				$query->where('(c.access IN (' . $groups . ') OR (a.created_by=' . $user_id . '))');
				$query->where('(lb.access IN (' . $groups . ') OR lb.user_id=' . $user_id . ' OR (a.created_by=' . $user_id . '))');
			}
			else
			{
				$userJSGroups = JbblogBaseController::getJSGroups($user->id);
				$userJSFriends = JbblogBaseController::getJSFriends($user->id);
				if (count($userJSGroups) > 0)
				{
					$tmpQ1 = ' OR (a.access=-4 AND a.access_gr IN (' . implode(',', $userJSGroups) . ')) ';
					$tmpQ2 = ' OR (lb.access=-4 AND lb.access_gr IN (' . implode(',', $userJSGroups) . ')) ';
				}
				else
				{
					$tmpQ1 = ' ';
					$tmpQ2 = '';
				}
				if (count($userJSFriends) > 0)
				{
					$tmpQ11 = ' OR (a.access=-2 AND a.created_by IN (' . implode(',', $userJSFriends) . ')) ';
					$tmpQ22 = ' OR (lb.access=-2 AND lb.user_id IN (' . implode(',', $userJSFriends) . ')) ';
				}
				else
				{
					$tmpQ11 = ' ';
					$tmpQ22 = '';
				}
				$query->where('(a.access IN (' . $groups . ') 
				    OR a.created_by=' . $user_id . ' ' . $tmpQ1 . ' ' . $tmpQ11 . ' )');
				$query->where('(c.access IN (' . $groups . ') OR (a.created_by=' . $user_id . '))');
				$query->where('(lb.access IN (' . $groups . ') 
				    OR lb.user_id=' . $user_id . ' ' . $tmpQ2 . ' ' . $tmpQ22 . ' OR (a.created_by=' . $user_id . '))');
			}
		}

		$this->_db->setQuery($query);

		try
		{
			$row = $this->_db->loadAssoc();
		}
		catch (RuntimeException $e)
		{
			$je = new JException($e->getMessage());
			$this->setError($je);
			return false;
		}

		if (!empty($row['custom_metatags']) && !is_array($row['custom_metatags']))
			$row['custom_metatags'] = unserialize($row['custom_metatags']);

		// Legacy error handling switch based on the JError::$legacy switch.
		// @deprecated  12.1
		if (JError::$legacy && $this->_db->getErrorNum())
		{
			$e = new JException($this->_db->getErrorMsg());
			$this->setError($e);
			return false;
		}

		// Check that we have a result.
		if (empty($row))
		{
			if (!$ignoreAccess) JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		$this->bind($row);
		$this->blog_id = $row['blog_id'];
		if (!empty($priv))
		{
			isset($priv->posts) ? $priv->posts : $priv->posts = 0;
			isset($priv->comments) ? $priv->comments : $priv->comments = 0;
			$this->comm = $priv->comments;
		}

		$pos = JString::strpos($this->fulltext, '{readmore}');

		if ($pos === false)
		{

		}
		else
		{
			$this->introtext .= JString::substr($this->fulltext, 0, $pos);
			$this->fulltext = JString::substr($this->fulltext, $pos + 10);
		}

		if (!empty($this->fulltext) && empty($this->introtext))
		{
			$this->introtext = $this->fulltext;
			$this->fulltext = '';
		}

		$sql = "SELECT `tag`.`id` ,`tag`.`name` "
			. " \n FROM #__joomblog_tags as tag ,  #__joomblog_content_tags as c "
			. " \n WHERE tag.`id` = c.tag AND c.contentid='{$this->id}'";
		$this->_db->setQuery($sql);
		$this->tagobj = $this->_db->loadObjectList();

		$tags = array();
		if ($this->tagobj)
		{
			foreach ($this->tagobj as $tag)
			{
				$tags[] = $tag->name;
			}
		}
		$this->tags = implode(',', $tags);

		$this->_db->setQuery("SELECT *, round( rating_sum / rating_count ) AS rating FROM #__joomblog_posts_rating WHERE content_id='{$this->id}'");
		$rating = $this->_db->loadObject();

		$query = 'SELECT COUNT(vote) FROM #__joomblog_votes WHERE vote = -1 AND contentid = ' . $this->id;
		$this->_db->setQuery($query);
		$notlike = $this->_db->loadResult();

		$query = 'SELECT COUNT(vote) FROM #__joomblog_votes WHERE vote = 1 AND contentid = ' . $this->id;
		$this->_db->setQuery($query);
		$like = $this->_db->loadResult();

		$this->sumvote = $like - $notlike;

		if ($rating)
		{
			$this->rating = $rating->rating;
			$this->rating_count = $rating->rating_count;
		}

		$this->introtext = str_replace('src="images', 'src="' . rtrim(JURI::base(), '/') . '/images', $this->introtext);
		$this->fulltext = str_replace('src="images', 'src="' . rtrim(JURI::base(), '/') . '/images', $this->fulltext);

		$this->title = htmlspecialchars($this->title);

		$this->introtext = str_replace('{readmore}', '', $this->introtext);
		$this->fulltext = str_replace('{readmore}', '', $this->fulltext);

		$this->introtext = trim($this->introtext);
		$this->fulltext = trim($this->fulltext);

		if (!empty($this->defaultimage))
		{
			jimport('joomla.filesystem.file');
			if (!JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $this->defaultimage))
			{
				$this->defaultimage = '';
			}
		}
		// Bind the object with the row and return.
		return true;
	}

	public function bind($vars, $editorSave = false)
	{
		$db = JFactory::getDBO();

		if (empty($vars))
			return;

		$rules_array = array();
		$rules_array['core.edit.state'] = array();
		$rules_array['core.edit'] = array();
		$rules_array['core.delete'] = array();
		$rules = new JAccessRules($rules_array);
		$this->setRules($rules);

		$result = parent::bind($vars);
		if ($editorSave && empty($vars['introtext']))
		{
			$this->_splitReadmoreOnSave();
		}

		return $result;
	}

	public function store($updateNulls = false)
	{
		/* CUSTOM TAGS */
		$custom_tags = array();
		$custom_tags_names = JFactory::getApplication()->input->get('cm_names', array(), 'array');
		$custom_tags_values = JFactory::getApplication()->input->get('cm_values', array(), 'array');

		if (!empty($custom_tags_names))
		{
			foreach ($custom_tags_names as $k => $custom_name)
				$custom_tags[$custom_name] = $custom_tags_values[$k];
		}

		$this->custom_metatags = serialize($custom_tags);

		if ($this->created == '')
		{
			$date = JFactory::getDate();
			jimport('joomla.version');
			$version = new JVersion();
			$joomla_version = $version->getShortVersion();
			$this->created = $date->toSql();
		}
		if (jbGetUserCanPost() && JComponentHelper::getParams('com_joomblog')->get('defaultPublishStatus') && empty($this->state))
		{
			$this->state = 1;
		}
		$allcategs = array();
		$isNew = false;
		if (empty($this->id))
		{
			$isNew = true;
		}
		if ($this->catid)
		{
			if (sizeof($this->catid))
			{
				$allcategs = $this->catid;
				if (is_array($this->catid)) $this->catid = $allcategs[0];
			}
		}
		if (parent::store($updateNulls))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			if ($isNew)
			{
				$query->insert('#__joomblog_blogs');
			}
			else
			{
				$query->update('#__joomblog_blogs');
				$query->where('`content_id`=' . (int) $this->id);
			}

			$query->set('content_id=' . (int) $this->id);
			$query->set('blog_id=' . (int) $this->blog_id);

			$db->setQuery($query);
			$db->execute();

			/*** MULTI CATS ***/
			if (!empty($allcategs))
			{
				$query = $db->getQuery(true);
				$query->delete();
				$query->from('#__joomblog_multicats');
				$query->where('aid=' . (int) $this->id);
				$db->setQuery($query);
				$db->execute();

				foreach ($allcategs as $alc)
				{
					$query = $db->getQuery(true);
					$query->insert('#__joomblog_multicats');
					$query->set('aid=' . (int) $this->id);
					$query->set('cid=' . (int) $alc);
					$db->setQuery($query);
					$db->execute();
				}
			}

			/*****************/

			if (isset($this->tags))
			{

				$this->tags = explode(',', $this->tags);
				array_walk($this->tags, create_function('&$val', '$val = trim($val);'));

				$query = $db->getQuery(true);
				$query->delete();
				$query->from('#__joomblog_content_tags');
				$query->where('contentid=' . (int) $this->id);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true);
				$query->select('name');
				$query->from('#__joomblog_tags');
				$db->setQuery($query);
				$list = $db->loadResultArray();


				foreach ($this->tags as $tag)
				{
					$ttable = JTable::getInstance('Tag', 'JoomBlogTable');
					if (!in_array($tag, $list))
					{

						$tag = trim($tag);
						$data = array(
							'name' => $tag,
							'default' => null,
							'slug' => null
						);
						if ($tag) $ttable->save($data);
					}
					else
					{
						$ttable->loadByName($tag);
					}
					if ($ttable->id)
					{
						$query = $db->getQuery(true);
						$query->insert('#__joomblog_content_tags');
						$query->set('contentid=' . (int) $this->id);
						$query->set('tag=' . (int) $ttable->id);
						$db->setQuery($query);
						$db->execute();
					}
				}

			}

			if ($this->id)
			{
				$jform = JFactory::getApplication()->input->get('jform', '', 'array');
				if ($this->access == -4)
				{
					$js_group = (int) $jform['access_gr'];
					$query = "UPDATE `#__joomblog_posts` SET access_gr=$js_group WHERE `id`='" . $this->id . "' ";
					$db->setQuery($query);
					$db->execute();
				}
				if ($this->caccess == -4)
				{
					$js_group = (int) $jform['caccess_gr'];
					$query = "UPDATE `#__joomblog_posts` SET caccess_gr=$js_group WHERE `id`='" . $this->id . "' ";
					$db->setQuery($query);
					$db->execute();
				}
			}
			return true;
		}

		return false;
	}

	public function checkAlias()
	{
		$alias = $this->alias;
		if (empty($alias)) $alias = trim(jbTitleToLink($this->title));

		if (trim(str_replace('-', '', $alias)) == '')
		{
			$alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		$table = JTable::getInstance('Posts', 'Table');
		if ($table->load(array('alias' => $alias), true, true) && ($this->id == 0))
		{
			return false;
		}
		$this->alias = $alias;
		return true;
	}

	public function delete($pk = NULL)
	{
		global $_JB_CONFIGURATION;

		$id = $this->id;

		if ($id)
		{
			$db = JFactory::getDBO();
			$query = "SELECT `id` FROM #__joomblog_comment WHERE contentid=$id";
			$db->setQuery($query);
			$comments = $db->loadColumn();
			if (!empty($comments))
			{
				$comments_list = "(" . implode(',', $comments) . ")";
				$query = "DELETE FROM #__joomblog_comment WHERE id IN " . $comments_list;
				$db->setQuery($query);
				$db->execute();

				$query = "DELETE FROM #__joomblog_comment_votes WHERE commentid IN " . $comments_list;
				$db->setQuery($query);
				$db->execute();
			}

			$query = "DELETE FROM #__joomblog_posts WHERE id=$id";
			$db->setQuery($query);
			$db->execute();

			$query = "DELETE FROM #__joomblog_content_tags WHERE contentid=$id";
			$db->setQuery($query);
			$db->execute();

			$query = "DELETE FROM #__joomblog_blogs WHERE content_id=$id";
			$db->setQuery($query);
			$db->execute();

			$query = "DELETE FROM #__assets WHERE name='com_joomblog.article.$id' ";
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
} 