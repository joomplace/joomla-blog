<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once( JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'base.php' );

class JbblogCategoriesTask extends JbblogBaseController{
	
	function JbblogCategoriesTask(){
		$this->toolbar = JB_TOOLBAR_BLOGGER;
	}
	
	function display(){
	
		global $_JB_CONFIGURATION, $Itemid;
	
		$mainframe	= JFactory::getApplication();
		$db			= JFactory::getDBO();
		$total = 0;
		
		$option = 'com_joomblog';
		$limit		= $mainframe->getUserStateFromRequest( $option.'category.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( $option.'category.limitstart', 'limitstart', 0, 'int' );		
		
		$rows = array();
		$sections = implode(',',jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));
		$rows = jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections'));
		$categoriesHTML = '';
		
		jbAddPageTitle( JText::_('COM_JOOMBLOG_SHOW_CATEGORIES_TITLE') );
		jbAddPathway( JText::_('COM_JOOMBLOG_SHOW_CATEGORIES_TITLE') );
		
		$query = JFactory::getDbo()->getQuery(true);
		$query->select("c.*");
		$query->from("#__categories AS c");
		$query->join("INNER", "#__joomblog_multicats AS mc ON  mc.cid = c.id");
		$query->join("LEFT", "#__joomblog_posts as a ON a.catid = c.id");
		$query->join('LEFT', '#__joomblog_blogs AS b ON b.content_id = a.id');
		$query->join('LEFT', '#__joomblog_list_blogs AS lb ON lb.id = b.blog_id');
		$query->where("c.id IN (".$sections.")");
		$query->group("c.id");
		
		//acccess check to show correct tags
		$groups	= implode(',', JFactory::getUser()->getAuthorisedViewLevels());
		//$query->where('a.access IN ('.$groups.')');
		$query->where('c.access IN ('.$groups.')');
		//$query->where('lb.access IN ('.$groups.')');
		
		$db->setQuery($query);
		
		$categories = $db->loadObjectList();
		$total = count($categories);
		
		$pageNav = new JBPagination($total, $limitstart, $limit);
		
		if(count($categories)>$limit){
		    $db->setQuery($query, $limitstart, $limit);
		    $categories = $db->loadObjectList();
		}

		$content = "";
		
		if (!empty($categories))
		{
			for ( $i = 0, $n = sizeof( $categories ); $i < $n; $i++ ) {
				$category=$categories[$i];
				$category->link = JRoute::_('index.php?option=com_joomblog&task=tag&category='.$category->id.'&Itemid='.$Itemid);
				$postsModel = $this->getModel('Posts', 'JoomblogModel', true);
				$postsModel->setState('list.limit', $_JB_CONFIGURATION->get('CategoriesRecentPosts'));
				$postsModel->setState('filter.category_id', $category->id);
				$articles = $postsModel->getItems();
				JbblogCategoriesTask::_getCategories($articles);
				$categories[$i]->count = $postsModel->getTotal();
				$article = array();
				$article[0] = $articles;
				$params = JComponentHelper::getParams('com_joomblog');
				array_splice($article[0], (int) $params->get('CategoriesRecentPosts'));
				$cat = array();
				$cat[0] = $category;
				$template		= new JoomblogTemplate();
				$template->set( 'Itemid' , $Itemid );
				$template->set( 'articles' , $article );
				$template->set( 'category' , $cat );
				
				$categoriesHTML .= $template->fetch( $this->_getTemplateName('category_list') );
				
				unset( $template );
				
			}
			
			$content .= '<div id="jblog-section">'.JText::_('COM_JOOMBLOG_SHOW_CATEGORIES_TITLE').'</div><div id="categories">'.$categoriesHTML.'</div>';
			$content .= '<div class="jb-pagenav">' . $pageNav->getPagesLinks() . '</div>';
			
			return $content;
		}else{
			$content .= '<div id="jblog-section"  class="jb-section">'.JText::_('COM_JOOMBLOG_SHOW_CATEGORIES_TITLE').'</div><div id="categories">'.JText::_('COM_JOOMBLOG_NO_CATEGORIES_CREATED').'</div>';
			
			return $content;

		}
		
	}
	
	protected function getRowsByPrivacyFilter($rows=null)
	{
		$this->deltotal=0;
		$user	= JFactory::getUser();
		if (sizeof($rows))
		{
			for ( $i = 0, $n = sizeof( $rows ); $i < $n; $i++ ) 
			{
				$post = &$rows[$i];
				$post->posts?$post->posts:$post->posts=0;
				if (!isset($post->blogtitle)) {unset($rows[$i]); $this->deltotal++;continue;}
				
				switch ( $post->posts ) 
				{
					case 0:	break;
					case 1:	
						if (!$user->id) 
						{
							$rows[$i]=null;
							unset($rows[$i]);
							$this->deltotal++;
						}
					break;
					case 2:	
						if (!$user->id) 
						{
							unset($rows[$i]);
							$this->deltotal++;
							
						}else
						{
							if (!$this->isFriends($user->id, $post->created_by) && $user->id!=$post->created_by)
							{
								unset($rows[$i]);
								$this->deltotal++;
								
							}	
						}						
					break;
					case 3:	
						if (!$user->id) 
						{
							unset($rows[$i]);
							$this->deltotal++;
							
						}else
						{
							if ($user->id!=$post->created_by)
							{
								unset($rows[$i]);
								$this->deltotal++;
								
							}	
						}						
					break;
					case 4:
						unset($rows[$i]);
						$this->deltotal++;
					break;
				}
				
			}
			$rows = array_values($rows);
		}
		return $rows;
	}
	
	protected function isFriends($id1=0,$id2=0)
	{
		$db	= JFactory::getDBO();
		$db->setQuery(	" SELECT `connection_id` FROM `#__community_connection` " .
						" WHERE connect_from=".(int)$id1." AND connect_to=".(int)$id2." AND `status`=1 ");
		$frindic = $db->loadResult();
		if ($frindic) return true; else return false;				
	}
	
	function _getCategories(&$rows)
	{
		global $Itemid;
		if(count($rows)){
			for($i =0; $i < count($rows); $i++){
				$row    =& $rows[$i];

				$row->categories	= jbCategoriesURLGet($row->id, true);

			}
		}
	}
	
	function _getBlogs(&$rows){
		
		$db			= JFactory::getDBO();
		$user	= JFactory::getUser();
		
		if(count($rows)){
			for($i =0; $i < count($rows); $i++){
				$row    =& $rows[$i];
			
				$db->setQuery(" SELECT b.blog_id, lb.title,p.posts, p.comments, p.jsviewgroup  " .
						      " FROM #__joomblog_blogs as b, #__joomblog_list_blogs as lb " .
						      " LEFT JOIN `#__joomblog_privacy` AS `p` ON `p`.`postid`=`lb`.`id` AND `p`.`isblog`=1 ".
						      " WHERE b.content_id=".$row->id." AND lb.id = b.blog_id AND lb.approved=1 AND lb.published=1 ");
				$blogs = $db->loadObjectList();
				if (sizeof($blogs)) 
				{
				switch ( $blogs[0]->posts ) 
				{
					case 0:	break;
					case 1:	
						if (!$user->id) 
						{
							$row->posts = $blogs[0]->posts;
						}
					break;
					case 2:	
						if (!$user->id) 
						{
							$row->posts = $blogs[0]->posts;
						}else
						{
							if (!$this->isFriends($user->id, $row->created_by) && $user->id!=$row->created_by)
							{
								$row->posts = $blogs[0]->posts;
							}	
						}
					break;
					case 3:	
						if (!$user->id) 
						{
							$row->posts = $blogs[0]->posts;
						}else
						{
							if ($user->id!=$post->created_by)
							{
								$row->posts = $blogs[0]->posts;
							}	
						}						
					break;
					case 4:	
						if (!$user->id) 
						{
							$row->posts = $blogs[0]->posts;
						}else
						{
							if (!jbInJSgroup($user->id, $blog[0]->jsviewgroup))
							{
								$row->posts = $blogs[0]->posts;
							}else $row->posts = 0;
						}
					break;
				}					
				$row->blogid = $blogs[0]->blog_id;
				$row->blogtitle = $blogs[0]->title;
				}
			}
		}
	}
	
}