<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');


require_once (JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'tables'.DIRECTORY_SEPARATOR.'content.php');

class JoomBlogTablePost extends JTableJoomblogPosts
{
	protected $tags;
	protected $blog_id;

	public function delete($pk = null)
	{
		if (parent::delete($pk)) {
			$db = JFactory::getDBO();

			$query	= "SELECT `id` FROM #__joomblog_comment WHERE contentid=".(int)$pk;
			$db->setQuery( $query );
			$comments = $db->loadColumn();
			if (!empty($comments))
			{
				$comments_list = "(".implode(',',$comments).")";
				$query	= "DELETE FROM #__joomblog_comment WHERE id IN ".$comments_list;
				$db->setQuery( $query );
				$db->execute();

				$query	= "DELETE FROM #__joomblog_comment_votes WHERE commentid IN ".$comments_list;
				$db->setQuery( $query );
				$db->execute();
			}	

			$query = $db->getQuery(true);
			$query->delete();
			$query->from('#__joomblog_blogs');
			$query->where('content_id='.(int)$pk);
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->delete();
			$query->from('#__joomblog_content_tags');
			$query->where('contentid='.(int)$pk);
			$db->setQuery($query);
			$db->execute();
			
			$query = $db->getQuery(true);
			$query->delete();
			$query->from('#__joomblog_multicats');
			$query->where('aid='.(int)$pk);
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		
		return false;
	}
	
	public function store($updateNulls = false)
	{
		if ($this->created == '')
		{
			$date = JFactory::getDate();
			$this->created = $date->toSql();
		}
		$allcategs = array();
		$isNew = false;
		if (empty($this->id)) {
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

		unset($this->newTags); //Fix for Joomla 3.x

		if (parent::store($updateNulls)) {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			if ($isNew) {
				$query->insert('#__joomblog_blogs');
			} else {
				$query->update('#__joomblog_blogs');
				$query->where('`content_id`='.(int)$this->id);
			}

			$query->set('content_id='.(int)$this->id);
			$query->set('blog_id='.(int)$this->blog_id);

			$db->setQuery($query);
			$db->execute();
			
			/*** MULTI CATS ***/
				if (sizeof($allcategs))
				{
					$query = $db->getQuery(true);
					$query->delete();
					$query->from('#__joomblog_multicats');
					$query->where('aid='.(int)$this->id);
					$db->setQuery($query);
					$db->execute();
					
					foreach ( $allcategs as $alc ) 
					{
						$query = $db->getQuery(true);
						$query->insert('#__joomblog_multicats');
						$query->set('aid='.(int)$this->id);
						$query->set('cid='.(int)$alc);
						$db->setQuery($query);
						$db->execute();
					}					
				}
								
			/*****************/
			
			if (isset($this->tags)) {
			
				$this->tags = explode(',', $this->tags);
				array_walk($this->tags, create_function('&$val', '$val = trim($val);'));
				$query = $db->getQuery(true);
				$query->delete();
				$query->from('#__joomblog_content_tags');
				$query->where('contentid='.(int)$this->id);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true);
				$query->select('name');
				$query->from('#__joomblog_tags');
				$db->setQuery($query);
				$list = $db->loadColumn();

				foreach ($this->tags as $tag) {
					$ttable = JTable::getInstance('Tag', 'JoomBlogTable');
					if (!in_array($tag, $list)) {
				
						$tag = trim ($tag);
						$data = array(
							'name' => $tag,
							'default' => null,
							'slug' => null
						);
						if ($tag) $ttable->save($data);
					} else {
						$ttable->loadByName($tag);
					}
					 if ($ttable->id)
					 {
						$query = $db->getQuery(true);
						$query->insert('#__joomblog_content_tags');
						$query->set('contentid='.(int)$this->id);
						$query->set('tag='.(int)$ttable->id);
						$db->setQuery($query);
						$db->execute();
					 }
				}
				
			}
			
		if ($this->id)
		{
			$db	= JFactory::getDBO();
			$jinput = JFactory::getApplication()->input;
			$jform = $jinput->get('jform');
			if($jform['access'] == -4){
			    $js_group = (int)$jform['access_gr'];
			    $query	= "UPDATE `#__joomblog_posts` SET access_gr=$js_group WHERE `id`='".$this->id."' ";
			    $db->setQuery( $query );
			    $db->execute();
			}
			if($jform['caccess'] == -4){
			    $js_group = (int)$jform['caccess_gr'];
			    $query	= "UPDATE `#__joomblog_posts` SET caccess_gr=$js_group WHERE `id`='".$this->id."' ";
			    $db->setQuery( $query );
			    $db->execute();
			}

			$posts = $jform['viewpostrules'];
			$comments = $jform['viewcommrules'];
			
			$query	= "SELECT `id` FROM `#__joomblog_privacy` WHERE `isblog`=0 AND `postid`='".$this->id."' ";
				$db->setQuery( $query );
				$isset = $db->loadResult();
			if ($isset)
			{
				$query	= "UPDATE `#__joomblog_privacy` SET `posts`='".$posts."',`comments`='".$comments."' WHERE `isblog`=0 AND `postid`='".$this->id."' ";
				$db->setQuery( $query );
				$db->execute();
			}else
			{
				$query	= "INSERT INTO `#__joomblog_privacy` (`id` ,`postid` ,`posts`,`comments`,`isblog`) VALUES ( NULL , '".$this->id."', '".$posts."', '".$comments."','0');";
				$db->setQuery( $query );
				$db->execute();
			}	
		}	
			return true;
		}
		
		return false;
	}
	
	public function checkAlias(){
		$alias = $this->alias;
		if(empty($alias)) $alias = JFilterOutput::stringURLSafe($this->title);

		if (trim(str_replace('-','',$alias)) == '') {
			$alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}
		$table = JTable::getInstance('Post', 'JoomBlogTable');
		if ($table->load(array('alias' => $alias), true, true) && ($table->id != $this->id || $this->id == 0))
		{
			return false;
		}
		$this->alias = $alias;
		return true;
	}


	public function load($keys = null, $reset = true) {
		if (parent::load($keys, $reset)) {
			$db = $this->getDbo();

			$query = $db->getQuery(true);
			$query->select('blog_id');
			$query->from('#__joomblog_blogs');
			$query->where('content_id='.(int)$this->id);
			$db->setQuery($query);
			$this->blog_id = $db->loadResult();

			$query = $db->getQuery(true);
			$query->select('GROUP_CONCAT(t.name) AS tags');
			$query->from('#__joomblog_content_tags AS ct');
			$query->join('left', '#__joomblog_tags AS t ON t.id=ct.tag');
			$query->where('contentid='.(int)$this->id);
			$db->setQuery($query);
			$this->tags = $db->loadResult();
		
			return true;
		}
		
		return false;
	}

	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		$tmp = $this->$k;
		return 'com_joomblog.article.'.(int)$this->$k;
	}

	protected function _getAssetTitle()
	{
		return $this->title;
	}

	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$assetId = null;
		$db = $this->getDbo();
		if ($this->catid) {
			$query	= $db->getQuery(true);
			$query->select('asset_id');
			$query->from('#__categories');
			$query->where('id = '.(int)$this->catid);

			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult()) {
				$assetId = (int)$result;
			}
		}
		if ($assetId) {
			return $assetId;
		} else {
			return parent::_getAssetParentId($table, $id);
		}
	}
}
