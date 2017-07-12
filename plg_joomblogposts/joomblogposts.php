<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');

if(!class_exists('plgCommunityjoomblogposts'))
{
	class plgCommunityjoomblogposts extends CApplications
	{
		var $_user		= null;
		var $_name		= "My Blog Posts";
		var $name		= "My Blog Posts"; //old versions
		var $_path		= '';
		var $db			= null;
		var $doc		= null;
		var $my			= null;
		var $_count		= 5;
		
	    function plgCommunityjoomblogposts(& $subject, $config)
	    {
			$this->_user	= CFactory::getRequestUser();
			$this->db   	=& JFactory::getDBO();
			$this->doc		=& JFactory::getDocument();
			$this->my		= CFactory::getUser();
			$this->_path	= JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_joomblog'; 
			parent::__construct($subject, $config); 
	    }
 	 	 	 		
		function onProfileDisplay()
		{
			//Load language file.
			 JPlugin::loadLanguage( 'plg_joomblogposts', JPATH_ADMINISTRATOR );
			
			//Attach JS & CSS
			 $this->doc->addStyleSheet( JURI::root() . 'plugins/community/joomblogposts/joomblogposts/style.css');
			 $this->doc->addScript( JURI::root() . 'plugins/community/joomblogposts/joomblogposts/blogpostwindow.js' );
			 
			//If JoomBlog not exists
			 if(!file_exists($this->_path . DS . 'joomblog.php' ) )
				{
					$content = "<table class='joomblogposts-notice'>
									<tr>
										<td>
							            	<img src='".JURI::root()."components/com_community/assets/error.gif' alt='' />
							        	</td>
							        	<td> " .JText::_('PLG_JOOMBLOGPOSTS_JOOMBLOGNOTFOUND') . "</td>
									</tr>
								</table>";
				}else
				{
					//Get Blog Posts
					$items = $this->_getBlogPosts();
					
					//Get owner
					$isOwner	= ($this->my->id == $this->_user->id ) ? true : false;
					$ownerId 	= (int) $this->_user->id;
					$ownerMame	= $this->_getUsernameById($ownerId);
					
					//Cache work
					$caching = $this->params->get('cache', 1);		
						if ($caching) { $caching = JFactory::getApplication()->getCfg('caching');}
					
					$cache =& JFactory::getCache('plgCommunityjoomblogposts');
					$cache->setCaching($caching);
					$callback = array('plgCommunityjoomblogposts', '_getBlogHTML');
					$content = $cache->call($callback, $items, $isOwner, $ownerMame, $this->params);
				}					
			return $content;
		}
		
		private function _getBlogPosts()
		{
		    if(!defined('JB_COM_PATH')) require_once( JPATH_ROOT.DS.'components'.DS.'com_joomblog'.DS.'defines.joomblog.php');
		    require_once(JB_COM_PATH.DS.'functions.joomblog.php');
		    require_once(JB_COM_PATH.DS.'task'.DS.'base.php');
		    $postsModel = JbblogBaseController::getModel('Posts', 'JoomblogModel', true, true);
		    $postsModel->setState('filter.user_id', $this->_user->id);
		    $postsModel->setState('list.limit', $this->params->get('count', 5));
		    $posts = $postsModel->getItems();

		    foreach($posts as $i=>$post){
				$posts[$i]->bid = $post->blogid;
				$posts[$i]->btitle = $post->blogtitle;
				$cats = jbGetMultiCats($post->id);

				if (sizeof($cats))
				{
					$jcategories = array();
					foreach ( $cats as $cat )
					{
						$catlink = JRoute::_('index.php?option=com_joomblog&task=tag&category='.$cat );
						$jcategories []= ' <a class="category" href="' .$catlink. '">' . jbGetJoomlaCategoryName($cat).'</a> ';
					}
					if (sizeof($jcategories)) $posts[$i]->jcategory = implode(',', $jcategories);

				}
					else $posts[$i]->jcategory	= '<a class="category" href="' . JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $post->catid ). '">' . jbGetJoomlaCategoryName( $post->catid ) . '</a>';
		    }
		    return $posts;
		}
		
		function _getBlogHTML($items=null, $isOwner=false, $ownerMame='', $params=null)
		{
			$html = "";
			
			if(!empty($items))
			{	
				if ($isOwner)
				{
					$bid = array_slice($items, 0, 1);
					$blogModerateLink = "javascript:blogShowWindow('".JRoute::_("index.php?option=com_joomblog&task=adminhome&blogid=".$bid[0]->bid."&tmpl=component")."');";
					$html .= '<div class="moderatelink">
									<a href="javascript:void(0);" onclick="'.$blogModerateLink.'">'.
											JText::_('PLG_JOOMBLOGPOSTS_MODERATE_BLOGPOSTS').'</a>
								</div>';
					
					$blogWriteLink = JRoute::_("index.php?option=com_joomblog&task=write&id=0&blogid=".$bid[0]->bid."&tmpl=component");
					$blogWriteLink ="javascript:blogShowWindow('".$blogWriteLink."');";
					
					$html .= '<div class="createlink">
									<a href="javascript:void(0);" onclick="'.$blogWriteLink.'">'.
											JText::_('PLG_JOOMBLOGPOSTS_NEW_BLOGPOSTS').'</a>
								</div><br/>';
				}
				$html .= '<ul class="joomblogposts" id="joomblogposts">';
				
				$limit = $params->get('count', 5);
				$t=0;
				foreach($items as $blogger)
				{	
					if ($t>$limit) break;	
					$t++;
					$html .= '<li>';	
					$blogLink 	 = JRoute::_('index.php?option=com_joomblog&blogid='.$blogger->bid.'&view=blogger');
					$articleLink = JRoute::_('index.php?option=com_joomblog&show='.$blogger->id);
																
					$html .= '<table cellpadding="0" cellspacing="0" border="0" width="100%" class="joomblogposts-header">';
					$html .= '	<tr>';
					$html .= '		<td height="20">' .
										'<div class="joomblogposts-title">' .
											'<a href="'.$blogLink.'">'.$blogger->btitle.'</a> ' .
											'<span class="blog-sep">→</span> ' .
											'<a href="'.$articleLink.'" class="blog">'.$blogger->title.'</a>' .
										'</div>' .
									'</td>';
					$html .= '		<td valign="top" width="200" class="blogcreatedate">'.
									date(JText::_('DATE_FORMAT_LC2'), strtotime($blogger->created));
					$html .='<div><small class="category-bgk">'.$blogger->jcategory.'</small></div></td>';
					$html .= '	</tr>';
										
					$html .= '</table>';	
					$html .= '</li>';
			
				}
				$html .= '</ul>';
				
				$blogAllLink = "javascript:blogShowWindow('".JRoute::_("index.php?option=com_joomblog&user=$ownerMame&tmpl=component")."');";				
				$html .= '<div style="float:right;">
								<a href="javascript:void(0);" onclick="'.$blogAllLink.'">'.
										JText::_('PLG_JOOMBLOGPOSTS_SHOW_ALL').'</a>
							</div>';
			}else{
				$html .= '<div>'.JText::_('PLG_JOOMBLOGPOSTS_NO_BLOGPOSTS').'</div>';
			}				
			$html .= '<div style="clear:both;"></div>';
			return $html;
		}
		
		function onAppDisplay(){
			ob_start();
			$limit=0;
			$html= $this->onProfileDisplay($limit);
			echo $html;
			
			$content	= ob_get_contents();
			ob_end_clean(); 
		
			return $content;		
		}
		

		
		private function _getJoomlaCategoryName($id)
		{			
			$db		=& JFactory::getDBO();
			$query	= "SELECT `title` FROM #__categories WHERE `id`='$id' ";
			$db->setQuery( $query );
			return $db->loadResult();
		}

		private function _getTags($contentid)
		{
			$db =& JFactory::getDBO();
			
			$query = "SELECT b.id,b.name, '' AS slug FROM #__joomblog_content_tags as a,#__joomblog_tags as b WHERE b.id=a.tag AND a.contentid='$contentid' ORDER BY b.name DESC";
			$db->setQuery( $query );
			$result = $db->loadObjectList();
			echo $db->getErrorMsg();
			for($i = 0; $i < count($result); $i++)
			{
				$tag	= $result[$i];
				
				if($tag->slug == '')
					$tag->slug	= $tag->name;
			}
		
			return $result;
		}
		
		private function _categoriesURLGet($contentid, $linkCats = true, $task="")
		{			
			$result		= $this->_getTags($contentid);
			$catCount	= count($result);
			$link		= "";
			$task		= empty($task) ? '&task=tag' : "&task=$task" ;
			
			if($result)
			{
				$count = 0;
				
				foreach ($result as $row)
				{
					if ($link != "")
						$link .= ",&nbsp;";
					
					if ($linkCats)
					{
						$url	= JRoute::_('index.php?option=com_joomblog' . $task . '&tag=' . urlencode($row->slug));				
						$link .= "<a href=\"$url\">$row->name</a>";
					}
					else
					{
						$link .= $row->name;
					}
					$count++;
				}
			}
			else
			{
				$link .= "<em>". JText::_('BLOG UNTAGGED') ."</em>&nbsp;";
			}
			
			return $link;
		}
		
		private function _getUsernameById($ownerId=42)
		{
			$db		=& JFactory::getDBO();
			$query	= "SELECT `username` FROM #__users WHERE `id`='$ownerId' ";
			$db->setQuery( $query );
			return $db->loadResult();	
		}
	}
}
