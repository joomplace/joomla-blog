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
JLoader::register('XMLHelper', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'xml.php');
class JoomBlogModelExport extends JModelAdmin
{
    
	public function getForm($data = array(), $loadData = true)
	{
	    $form = $this->loadForm('com_joomblog.import', 'import', array('control' => 'jform', 'load_data' => false));
	    $importPreferences = JFactory::getApplication()->getUserState('com_joomblog.import.preferences');
	    $form->bind($importPreferences);
	    if (empty($form)) {
		    return false;
	    }
	    return $form;
	}
	
	public function getBlogs()
	{
	    $model = JModelLegacy::getInstance('Blogs', 'JoomBlogModel', array('ignore_request'=>true));
	    $items = $model->getItems();
	    return $items;
	}
	
	public function getImportPosts()
	{
	    $items = new stdClass();
	    jimport( 'joomla.filesystem.file' );
	    $xmlFile = JFactory::getApplication()->getUserState('com_joomblog.import.file_name');
	    if(!empty($xmlFile) && JFile::exists(JFactory::getConfig()->get('tmp_path').DIRECTORY_SEPARATOR.'joomblog_'.md5($xmlFile).'.xml')){
		$xml = file_get_contents(JFactory::getConfig()->get('tmp_path').DIRECTORY_SEPARATOR.'joomblog_'.md5($xmlFile).'.xml');
		$domObj = new DOMDocument('1.0', 'UTF-8');
		$domObj->loadXML($xml);
		$items = XMLHelper::domToObject($domObj);
	    }
	    return $items;
	}

	public function getImportArticlePosts()
	{
		$importPreferences = JFactory::getApplication()->getUserState('com_joomblog.import.preferences');

		require_once(JPATH_BASE . '/components/com_content/models/articles.php');

		$articlesModel = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request'=>true));
		$articlesModel->setState('filter.category_id', $importPreferences['article_catid']);
		$items = $articlesModel->getItems();

		$db = JFactory::getDbo();
		$db->setQuery( "SELECT id, title FROM #__categories WHERE id IN (".implode(', ', $importPreferences['article_catid']).")");
		$categories = $db->loadAssocList('id');

		foreach ($items as $k=>$v)
			$items[$k]->category_title = $categories[ $v->catid ]['title'];

		return $items;
	}
	

	public function importArticles( $ids )
	{
		if ( empty($ids) )
			return false;

		$importPreferences = JFactory::getApplication()->getUserState('com_joomblog.import.preferences');
		$db = JFactory::getDbo();

		$db->setQuery("SELECT * FROM #__content WHERE id IN (".implode(',', $ids).")");
		$articles = $db->loadAssocList();

		foreach ($articles as $article)
		{

			$user = $this->getUser($importPreferences['user_id']);

			$item = new stdClass();
			$item->tags = '';
			$item->blog_id = $importPreferences['article_blog_id'];
			//$item->asset_id = $importPreferences['article_blog_id'];
			$item->title = $article['title'];
			$item->alias = $article['alias'];
			$item->introtext = $article['introtext'];
			$item->fulltext = $article['fulltext'];
			$item->state = $importPreferences['article_published'];
			$item->catid = (int)$importPreferences['article_default_catid'];
			$item->created = $article['created'];
			$item->created_by = $user->id;
			$item->publish_up = $article['publish_up'];
			$item->attribs = (object)array('alternative_readmore'=>'read more ...');
			$item->version = $article['version'];
			$item->parentid = 0;
			$item->metakey = $article['metakey'];
			$item->metadesc = $article['metadesc'];
			$item->access = $article['access'];
			$item->language = '*';
			$item->username = $user->name;

			$post = $this->savePost($item);

			$db->setQuery("INSERT INTO #__joomblog_multicats (`aid`,`cid`) VALUES ('".$post->id."', '".(int)$importPreferences['article_default_catid']."')");
			$db->execute();
		}
	}

	public function prepareImportDefaultData($items){
	    if(!empty($items->blogs)){
		$categories = $this->getCategories();
		$importPreferences = JFactory::getApplication()->getUserState('com_joomblog.import.preferences');
		$defaultUser = $this->getUser($importPreferences['user_id']);
		$defaultCategory = $categories[(int)$importPreferences['catid']];
		$defaultStatus = (int)$importPreferences['published'];
		$updateExisting = (int)$importPreferences['updateExisting'];
		foreach($items->blogs as $blog_id=>$blog){
		    if(!empty($blog->user_id)){
			$user = $this->getUser($blog->user_id);
			if(!$user){
			    $items->blogs[$blog_id]->user_id = $defaultUser->id;
			    $items->blogs[$blog_id]->username = $defaultUser->name;
			}else $items->blogs[$blog_id]->username = $user->name;
		    }else{
			$items->blogs[$blog_id]->user_id = $defaultUser->id;
			$items->blogs[$blog_id]->username = $defaultUser->name;
		    }
		    if(!$updateExisting){
			if(!empty($blog->id)) unset($items->blogs[$blog_id]->id);
		    }else{
			if(!$this->getBlog($blog->id)) unset($items->blogs[$blog_id]->id);
		    }
		    if($defaultStatus == 1 || $defaultStatus == 0){
			$items->blogs[$blog_id]->state = $defaultStatus;
		    }
		    if(!empty($blog->posts)){
			foreach($blog->posts as $post_id=>$post){
			    if(!empty($post->created_by)){
				$user = $this->getUser($post->created_by);
				if(!$user){
				    $items->blogs[$blog_id]->posts[$post_id]->created_by = $defaultUser->id;
				    $items->blogs[$blog_id]->posts[$post_id]->username = $defaultUser->name;
				}else $items->blogs[$blog_id]->posts[$post_id]->username = $user->name;
			    }else{
				$items->blogs[$blog_id]->posts[$post_id]->created_by = $defaultUser->id;
				$items->blogs[$blog_id]->posts[$post_id]->username = $defaultUser->name;
			    }
			    if(!$updateExisting){
				if(!empty($post->id)) unset($items->blogs[$blog_id]->posts[$post_id]->id);
			    }else{
				if(!$this->getPost($post->id)) unset($items->blogs[$blog_id]->posts[$post_id]->id);
			    }
			    if(!empty($post->cats) && count($post->cats)>0){
				foreach($post->cats as $cat_id=>$cat){
				    if(empty($categories[$cat->cid])){
					unset($items->blogs[$blog_id]->posts[$post_id]->cats[$cat_id]);
				    }
				}
			    }
			    if(empty($items->blogs[$blog_id]->posts[$post_id]->cats)){
				$items->blogs[$blog_id]->posts[$post_id]->cats = array(0=>new stdClass());
				$items->blogs[$blog_id]->posts[$post_id]->cats[0]->cid = (int)$importPreferences['catid'];
				$items->blogs[$blog_id]->posts[$post_id]->cats[0]->title = $defaultCategory;
			    }
			    if($defaultStatus == 1 || $defaultStatus == 0){
				$items->blogs[$blog_id]->posts[$post_id]->state = $defaultStatus;
			    }
			}
		    }
		}
	    }
	    return $items;
	}
	
	public function saveBlog($blogData){
	    $blogTable = $this->getTable('Blog', 'JoomBlogTable');
	    $blogTable->bind((array)$blogData);
	    $blogTable->store();
	    if(!empty($blogTable->id)) return $blogTable;
	    else return false;
	}
	
	public function savePost($postData){
	    $postTable = $this->getTable('Post', 'JoomBlogTable');
	    $postTable->bind((array)$postData);
	    if(empty($postData->id)){
		$alias = $postData->alias;
		if(empty($alias)) $alias = JFilterOutput::stringURLSafe($postData->title);
		$i = 1;
		while(!$postTable->checkAlias()){
		    $postTable->alias = $alias.'-'.$i++;
		}
	    }
	    $postTable->store();
	    if(!empty($postTable->id)) return $postTable;
	    else return false;
	}
	
	private function getBlog($blogId){
	    $blogId = (int)$blogId;
	    if($blogId > 0){
		$blogTable = $this->getTable('Blog', 'JoomBlogTable');
		$blogTable->load($blogId);
		if($blogTable->id > 0) return $blogTable;
	    }
	    return false;
	}
	
	private function getPost($postId){
	    $postId = (int)$postId;
	    if($postId > 0){
		$postTable = $this->getTable('Post', 'JoomBlogTable');
		$postTable->load($postId);
		if($postTable->id > 0) return $postTable;
	    }
	    return false;
	}
	
	private function getUser($userId){
	    $userId = (int)$userId;
	    $user = JFactory::getUser($userId);
	    if($user->id) return $user;
	    return false;
	}
	
	private function getCategories() {
	    $categories = array();
	    $options = JHtml::_('category.options', 'com_joomblog');
	    foreach($options as $option){
		if($option->value > 0){
		    $categories[$option->value] = $option->text;
		}
	    }
	    return $categories;
	}

}
