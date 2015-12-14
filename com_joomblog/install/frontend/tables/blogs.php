<?php
/**
 * JoomBlog component for Joomla 1.6 & 1.7
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.database.tableasset');

class TableBlogs extends JTable
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

	function getParagraphCount($text)
	{
		$position = -1;
		$count = 0;

		while (($position = JString::strpos($text, '</p>', $position + 1)) !== false)
		{
			$count++;
		}

		return $count;
	}

	function getBrowseText(&$row)
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


	public function load($keys = null, $reset = true)
	{
		$id = $keys;
		$mainframe =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$originalid = $id;
		$user =& JFactory::getUser();

		if (is_numeric($id))
		{
			parent::load($id);
		}

		$sql = "SELECT `posts`,`comments` "
			. " \n FROM #__joomblog_privacy "
			. " \n WHERE `isblog`=0 AND `postid`='{$this->id}'";
		$db->setQuery($sql);
		$priv = $db->loadObject();

		$query = $db->getQuery(true);
		$query->select('b.blog_id, p.posts, p.jsviewgroup');
		$query->from('#__joomblog_blogs AS b');
		$query->leftJoin('#__joomblog_privacy AS p ON p.postid=b.blog_id AND p.isblog=1');
		$query->where('b.content_id=' . $id);
		$db->setQuery($query);
		$bid = $db->loadObject();

		if ($bid)
		{
			switch ($bid->posts)
			{
				case 0:
					break;
				case 1:
					if (!$user->id)
					{
						$priv->posts = $bid->posts;
					}
				case 2:
					if (!$user->id)
					{
						$priv->posts = $bid->posts;
					}
					else
					{
						if (!$this->isFriends($user->id, $row->created_by) && $user->id != $row->created_by)
						{
							$priv->posts = $bid->posts;
						}
					}
					break;
				case 3:
					if (!$user->id)
					{
						$priv->posts = $bid->posts;
					}
					else
					{
						if ($user->id != $post->created_by)
						{
							$priv->posts = $bid->posts;
						}
					}
					break;
				case 4:
					if (!$user->id)
					{
						$priv->posts = $bid->posts;
					}
					else
					{
						if (!jbInJSgroup($user->id, $bid->jsviewgroup))
						{
							$priv->posts = $bid->posts;
						}
						else $priv->posts = 0;
					}
					break;
			}
		}

		isset($priv->posts) ? $priv->posts : $priv->posts = 0;
		isset($priv->comments) ? $priv->comments : $priv->comments = 0;
		$this->comm = $priv->comments;

		switch ($priv->posts)
		{
			case 0:
				break;
			case 1:
				if (!$user->id)
				{
					JError::raiseWarning(403, JText::_('COM_JOOMBLOG_PRIVACY_NOTACCESS'));
					return false;
				}
				break;
			case 2:
				if (!$user->id)
				{
					JError::raiseWarning(403, JText::_('COM_JOOMBLOG_PRIVACY_NOTACCESS'));
					return false;
				}
				else
				{
					if (!$this->isFriends($user->id, $this->created_by) && $user->id != $this->created_by)
					{
						JError::raiseWarning(403, JText::_('COM_JOOMBLOG_PRIVACY_NOTACCESS'));
						return false;
					}
				}
				break;
			case 3:
				if (!$user->id)
				{
					JError::raiseWarning(403, JText::_('COM_JOOMBLOG_PRIVACY_NOTACCESS'));
					return false;
				}
				else
				{
					if ($user->id != $this->created_by)
					{
						JError::raiseWarning(403, JText::_('COM_JOOMBLOG_PRIVACY_NOTACCESS'));
						return false;
					}
				}
				break;
			case 4:
				JError::raiseWarning(403, JText::_('COM_JOOMBLOG_PRIVACY_NOTACCESS'));
				return false;
				break;
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
		$db->setQuery($sql);
		$this->tagobj = $db->loadObjectList();

		$tags = array();
		if ($this->tagobj)
		{
			foreach ($this->tagobj as $tag)
			{
				$tags[] = $tag->name;
			}
		}
		$this->tags = implode(',', $tags);

		$db->setQuery("SELECT *, round( rating_sum / rating_count ) AS rating FROM #__joomblog_posts_rating WHERE content_id='{$this->id}'");
		$rating = $db->loadObject();

		$query = 'SELECT COUNT(vote) FROM #__joomblog_votes WHERE vote = -1 AND contentid = ' . $this->id;
		$db->setQuery($query);
		$notlike = $db->loadResult();

		$query = 'SELECT COUNT(vote) FROM #__joomblog_votes WHERE vote = 1 AND contentid = ' . $this->id;
		$db->setQuery($query);
		$like = $db->loadResult();

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
		return true;
	}

	protected function isFriends($id1 = 0, $id2 = 0)
	{
		$db =& JFactory::getDBO();
		$db->setQuery(" SELECT `connection_id` FROM `#__community_connection` " .
			" WHERE connect_from=" . (int) $id1 . " AND connect_to=" . (int) $id2 . " AND `status`=1 ");
		$frindic = $db->loadResult();
		if ($frindic) return true;
		else return false;
	}

	public function bind($vars, $editorSave = false)
	{
		$db =& JFactory::getDBO();

		if (empty($vars))
			return;

		if (trim($vars['title']) == '')
		{
			$this->setError(JText::_('COM_CONTENT_WARNING_PROVIDE_VALID_NAME'));
			return false;
		}

		$rules_array = array();
		$rules_array['core.edit.state'] = array();
		$rules_array['core.edit'] = array();
		$rules_array['core.delete'] = array();
		$rules = new JRules($rules_array);
		$this->setRules($rules);

		$result = parent::bind($vars);

		if ($editorSave && empty($vars['introtext']))
		{
			$this->_splitReadmoreOnSave();
		}

		return $result;
	}

	publi function store($updateNulls = false)
	{
		global $_JB_CONFIGURATION;
		if (jbGetUserCanPost() && $_JB_CONFIGURATION->defaultPublishStatus)
		{
			$this->state = 1;
		}
		$mainframe =& JFactory::getApplication();
		$my =& JFactory::getUser();
		$db =& JFactory::getDBO();
		$isNew = ($this->id == 0) ? true : false;

		$this->title = trim(html_entity_decode($this->title));
		$alias = trim(jbTitleToLink($this->title));

		if (trim(str_replace('-', '', $alias)) == '')
		{
			$alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		$query = "SELECT a.title FROM #__joomblog_posts AS a WHERE  a.id = '{$this->id}' ";
		$db->setQuery($query);
		$current_title = $db->loadResult();

		if ($current_title != $this->title)
		{
			$query = "SELECT a.id FROM #__joomblog_posts AS a, #__categories AS c WHERE c.extension = 'com_joomblog' AND c.id = a.catid AND a.alias = '{$alias}' ";
			$db->setQuery($query);
			$isAlias = $db->loadResult();

			if ($isAlias)
			{
				$query = "SELECT MAX(a.id) FROM #__joomblog_posts AS a, #__categories AS c WHERE c.extension = 'com_joomblog' AND c.id = a.catid ";
				$db->setQuery($query);
				$num = $db->loadResult();
				$num++;
				$this->alias = $alias . "-" . $num;
			}
			else
			{
				$this->alias = $alias;
			}
		}

		unset($this->rating);
		unset($this->rating_count);

		if ($this->id != NULL && $this->id != 0)
		{
			$this->modified = strftime("%Y-%m-%d %H:%M:%S", time() + ($mainframe->getCfg('offset') * 60 * 60));
		}
		else
		{
			$this->created_by = $my->id;
		}

		if (empty($this->publish_up))
		{
			$this->publish_up = $this->created;
		}

		if (empty($this->sectionid))
		{
			$this->sectionid = $_JB_CONFIGURATION->get('postSection');
		}

		if (empty($this->catid))
		{
			$section_obj = jbGetCategoryList($_JB_CONFIGURATION->get('managedSections'));
			$this->catid = $section_obj[0]->id;
		}


		$currentId = $this->id;

		$res = parent::store($updateNulls);
		$this->saveprivacy();
		
		return $res;
	}

	function saveprivacy()
	{
		if ($this->id)
		{
			$db =& JFactory::getDBO();
			$posts = JRequest::getInt('viewpostrules', 0);
			$comments = JRequest::getInt('viewcommrules', 0);


			$query = "SELECT `id` FROM `#__joomblog_privacy` WHERE `isblog`=0 AND `postid`='" . $this->id . "' ";
			$db->setQuery($query);
			$isset = $db->loadResult();
			if ($isset)
			{
				$query = "UPDATE `#__joomblog_privacy` SET `posts`='" . $posts . "', `comments`='" . $comments . "' WHERE `isblog`=0 AND `postid`='" . $this->id . "' ";
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				$query = "INSERT INTO `#__joomblog_privacy` (`id` ,`postid` ,`posts`,`comments`,`isblog`) VALUES ( NULL , '" . $this->id . "', '" . $posts . "', '" . $comments . "',0);";
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	function validate_save()
	{
		$validate = array();

		if (empty($this->title))
		{
			$validate[] = "Title is empty";
		}

		if (empty($this->fulltext))
		{
			$validate[] = "You cannot save a blank entry. ";
		}

		return $validate;
	}

	public function delete($pk = NULL)
	{
		global $_JB_CONFIGURATION;

		$id = $this->id;

		if ($id)
		{
			$db =& JFactory::getDBO();
			$query = "DELETE FROM #__joomblog_posts WHERE id=$id";
			$db->setQuery($query);
			$db->execute();

			$query = "DELETE FROM #__joomblog_content_tags WHERE contentid=$id";
			$db->setQuery($query);
			$db->execute();

			$query = "DELETE FROM #__assets WHERE name='com_joomblog.article.$id' ";
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
} 