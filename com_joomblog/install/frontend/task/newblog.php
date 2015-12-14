<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JbblogNewBlogTask extends JbBlogBaseController{
	
	function JbblogNewBlogTask(){
		$this->toolbar = JB_TOOLBAR_BLOGGER;
	}

	
	function display()
	{
		global $_JB_CONFIGURATION, $Itemid;

		$mainframe	= JFactory::getApplication();
		$my	= JFactory::getUser();		
		$doc = JFactory::getDocument();
		
		if(!jbCanBlogCreate()){
			$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_CREATE'));
			return;
		}
		
		$pathway = $mainframe->getPathway();
		$tpl = new JoomblogTemplate();
 		$blogModel = $this->getModel('Blog', 'JoomblogModel');
		$blogForm = $blogModel->getForm();
		$tpl->set('blogForm', $blogForm, true);

        //$template ='<div class="jb-blogs" style="background: url(\\\'http://cdn.designrshub.com/wp-content/uploads/2012/08/22_free_linen_texture.jpg\\\');"> <div class="jb-blogs-head clearfix jb-postsofblog-head"><div class="jb-blog-imgcont">{standart_blog_image}</div><div class="jb-blog-info"><h3 class="jb-blog-title">'.JText::_('COM_JOOMBLOG_BLOG').': '.' {glog_title}</h3><div class="jb-blog-authors">{blog_profile_link}</div> <div class="jb-blog-desc">{blog_desc}</div></div></div></div>';
        $template ='<div class="jb-blogs"> <div class="jb-blogs-head clearfix jb-postsofblog-head"><div class="jb-blog-imgcont">{standart_blog_image}</div><div class="jb-blog-info"><h3 class="jb-blog-title">'.JText::_('COM_JOOMBLOG_BLOG').': '.' {blog_title}</h3><div class="jb-blog-authors">{blog_profile_link}</div> <div class="jb-blog-desc">{blog_desc}</div></div></div></div>';

        $tpl->set('template', $template, true);

        JFormHelper::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
        $jEditor = JFormHelper::loadFieldType('Html5editor', false);
        $jEditor->setName('header');
        $jEditor->setValue($template);
        $jEditor->setLabel(JText::_('COM_JOOMBLOG_POST_CONTENT'));
        $tpl->set('jEditor', $jEditor, true);

		$html = $tpl->fetch(JB_TEMPLATE_PATH."/admin/newblog.html");
		$html = str_replace("src=\"icons", "src=\"" . rtrim( JURI::base() , '/' ) . "/components/com_joomblog/templates/admin/icons", $html);
		return $html;
	}
}
