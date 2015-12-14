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
require_once( JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'avatar.php' );

class JbblogProfileTask extends JbblogBaseController
{
	function JbblogProfileTask()
	{
		$this->toolbar	= JB_TOOLBAR_ACCOUNT;
	}

	function display()
	{
		global $_JB_CONFIGURATION, $Itemid;

		$mainframe	= JFactory::getApplication();

		$jinput = JFactory::getApplication()->input;
		$id = $jinput->get('id',0);
		$user = JTable::getInstance( 'BlogUsers' , 'Table' );
		$user->load($id);

		$avatar	= 'Jb' . ucfirst($_JB_CONFIGURATION->get('avatar')) . 'Avatar';
		$avatar	= new $avatar($user->user_id, 0);
		$user->src	= $avatar->get();

		$pathway = $mainframe->getPathway();

		$pathway->addItem(JText::_( 'COM_JOOMBLOG_USER_PROFILE_DETAILS'),'');

		$postsModel = $this->getModel('Posts', 'JoomblogModel', true);
		$postsModel->setState('list.limit', $_JB_CONFIGURATION->get('BloggerRecentPosts'));
		$postsModel->setState('filter.author_id', $user->user_id);
		$lastPosts = $postsModel->getItems();
		foreach ($lastPosts as $i => $post)
		{
			$lastPosts[$i]->bid = $lastPosts[$i]->blogid;
			$lastPosts[$i]->btitle = $lastPosts[$i]->blogtitle;
			$lastPosts[$i]->multicats = false;
			$cats = jbGetMultiCats($lastPosts[$i]->id);

			if (sizeof($cats))
			{
				$jcategories = array();
				foreach ($cats as $cat)
				{
					$catlink = JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $cat . '&Itemid=' . $Itemid);
					$jcategories [] = ' <a class="category" href="' . $catlink . '">' . jbGetJoomlaCategoryName($cat) . '</a> ';
				}
				if (sizeof($jcategories) > 1) $lastPosts[$i]->multicats = true;
				if (sizeof($jcategories)) $lastPosts[$i]->jcategory = implode(',', $jcategories);

			}
			else $lastPosts[$i]->jcategory = '<a class="category" href="' . JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $lastPosts[$i]->catid . '&Itemid=' . $Itemid . $tmpl) . '">' . jbGetJoomlaCategoryName($lastPosts[$i]->catid) . '</a>';


			$lastPosts[$i]->categories = jbCategoriesURLGet($lastPosts[$i]->id, true);
		}
		$totalEntries = $postsModel->getTotal();

		jbAddEditorHeader();

		$tpl = new JoomblogTemplate();
		$tpl->set('user', array($user));
		$tpl->set('lastPosts', $lastPosts);
		$tpl->set('jbitemid', jbGetItemId());
		$tpl->set('totalArticle', $totalEntries);
		$tpl->set('categoryDisplay', $_JB_CONFIGURATION->get('categoryDisplay'));
		$tpl->set('Itemid', $Itemid);
		
		$path = $this->_getTemplateName( 'profile' );
		$content = $tpl->fetch( $path );
		return $content;
	}
}


