<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

include_once(JB_COM_PATH . '/task/base.php');

class JbblogTagslistTask extends JbblogBaseController
{	
	function JbblogTagslistTask()
	{
		$this->toolbar = JB_TOOLBAR_TAGS;
	}
	
	function display($styleid = '', $wrapTag = 'div')
	{
		$mainframe	= JFactory::getApplication();
		
		if(empty($styleid))
		{
			jbAddPageTitle( JText::_('COM_JOOMBLOG_SHOW_TAGS_TITLE') );
		}
		$jinput = JFactory::getApplication()->input;
		if($jinput->get('task') == 'tagslist'){
			jbAddPathway( JText::_('COM_JOOMBLOG_SHOW_TAGS_TITLE') );
		}
		
		$subWrap = 'li';
		if($wrapTag == 'ul')
		{
			$subWrap= 'li';
		}
		else
		{
			$subWrap = '';
		}

		$blogger	= $jinput->get( 'user' , '' , 'GET' );
		$mbItemid	= jbGetItemId();
		$content = '<'.$wrapTag.' class="blog-tags" '.$styleid.'>';
		$query = JFactory::getDbo()->getQuery(true);
		$query->select("'' AS slug, t.name, COUNT(DISTINCT a.id) as frequency");
		$query->from("#__joomblog_content_tags as ct");
		$query->join("LEFT", "#__joomblog_posts as a ON a.id = ct.contentid");
		$query->join("LEFT", "#__joomblog_tags as t ON t.id = ct.tag");
		$query->join('LEFT', '#__joomblog_multicats AS mc ON mc.aid = a.id');
		$query->join('LEFT', '#__categories AS c ON c.id = mc.cid');
		$query->join('LEFT', '#__joomblog_blogs AS b ON b.content_id = a.id');
		$query->join('LEFT', '#__joomblog_list_blogs AS lb ON lb.id = b.blog_id');
		$query->where("t.name <> ''");
		$query->group("ct.tag");
		$query->order("frequency ASC");
		//acccess check to show correct tags
		if (!in_array('8', JFactory::getUser()->getAuthorisedGroups())) {
			$user	= JFactory::getUser();
			$user_id = (int)$user->id;
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			if(!JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false)){
			    $query->where('(a.access IN ('.$groups.') OR a.created_by='.$user_id.')');
			    $query->where('c.access IN ('.$groups.')');
			    $query->where('(lb.access IN ('.$groups.') OR lb.user_id='.$user_id.')');
			}else{
			    $userJSGroups = JbblogBaseController::getJSGroups($user->id);
			    $userJSFriends = JbblogBaseController::getJSFriends($user->id);
			    if(count($userJSGroups)>0){
				$tmpQ1 = ' OR (a.access=-4 AND a.access_gr IN ('.implode(',', $userJSGroups).')) ';
				$tmpQ2 = ' OR (lb.access=-4 AND lb.access_gr IN ('.implode(',', $userJSGroups).')) ';
			    }else{
				$tmpQ1 = ' ';
				$tmpQ2 = '';
			    }
			    if(count($userJSFriends)>0){
				$tmpQ11 = ' OR (a.access=-2 AND a.created_by IN ('.implode(',', $userJSFriends).')) ';
				$tmpQ22 = ' OR (lb.access=-2 AND lb.user_id IN ('.implode(',', $userJSFriends).')) ';
			    }else{
				$tmpQ11 = ' ';
				$tmpQ22 = '';
			    }
			    $query->where('(a.access IN ('.$groups.') 
				    OR a.created_by='.$user_id.' '.$tmpQ1.' '.$tmpQ11.' )');
			    $query->where('c.access IN ('.$groups.')');
			    $query->where('(lb.access IN ('.$groups.') 
				    OR lb.user_id='.$user_id.' '.$tmpQ2.' '.$tmpQ22.' )');
			}
		}

		$categoriesArray = jbGetTagClouds($query, 8);
		$categories = "";
		
		if ($categoriesArray)
		{
			foreach ($categoriesArray as $category)
			{
				$catclass = "tag" . $category['cloud'];
				$catname = $category['name'];
				$tagSlug	= $category['slug'];

				$tagSlug	= ($tagSlug == '') ? $category['name'] : $category['slug'];
				$tagSlug	= urlencode($tagSlug);

				$tagSlug = str_replace('&', '-and-', $tagSlug);
				$tagSlug = str_replace('%26', '-and-', $tagSlug);
				$tagSlug = str_replace('+', '_', $tagSlug);
				if(!empty($subWrap))
				{
					$categories .= "<{$subWrap} class=\"$catclass\">";
					
					if(isset($blogger) && !empty($blogger))
					{
						$categories .= "<a href=\"" . JRoute::_("index.php?option=com_joomblog&tag=" . $tagSlug . "&user=$blogger&Itemid=$mbItemid") . "\">$catname</a> ";
					} else {
						$categories .= "<a href=\"" . JRoute::_("index.php?option=com_joomblog&task=tag&tag=" . $tagSlug . "&Itemid=$mbItemid") . "\">$catname</a> ";
					}			
					$categories .= "</$subWrap>";
				}
				else
				{
					if(isset($blogger) && !empty($blogger))
					{
						$categories .= "<a class=\"$catclass\" href=\"" . JRoute::_("index.php?option=com_joomblog&tag=" . $tagSlug . "&user=$blogger&Itemid=$mbItemid") . "\">$catname</a> ";
					}
					else
					{
						$categories .= "<a class=\"$catclass\" href=\"" . JRoute::_("index.php?option=com_joomblog&task=tag&tag=" . $tagSlug . "&Itemid=$mbItemid") . "\">$catname</a> ";
					}
					
				}
			}
		}

		$content .= trim($categories, ",");
		$content .= "</{$wrapTag}>";
		return $content;
	}
	
}
