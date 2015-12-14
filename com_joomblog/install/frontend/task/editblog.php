<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class JbblogEditBlogTask extends JbBlogBaseController{
	
	function JbblogEditBlogTask(){
		$this->toolbar = JB_TOOLBAR_BLOGGER;
	}

	
	function display()
	{
		global $_JB_CONFIGURATION, $Itemid;

		$mainframe	= JFactory::getApplication();
		$my	= JFactory::getUser();		
		$doc = JFactory::getDocument();
		
		if(!jbCanBlogCreate()){
			$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_EDIT'));
			return;
		}
		$data = array();
		if (JFactory::getApplication()->input->get('blogid'))
		{
			$db			= JFactory::getDBO();
			$blog_id = JFactory::getApplication()->input->get('blogid');
			$query		= "SELECT * from #__joomblog_list_blogs as lb WHERE lb.id=".$blog_id;
			$db->setQuery( $query );
			$blog_data = $db->loadObject();
			$data['id'] = $blog_data->id;
			$data['user_id'] = $blog_data->user_id;
			$data['published'] = $blog_data->published;
			$data['create_date'] = $blog_data->create_date;
			$data['title'] = $blog_data->title;
			$data['alias'] = $blog_data->alias;
			$data['description'] = $blog_data->description;
			$data['header'] = $blog_data->header;
			$data['metadesc'] = $blog_data->metadesc;
			$data['metakey'] = $blog_data->metakey;
			$data['asset_id'] = $blog_data->asset_id;
			$data['approved'] = $blog_data->approved;
			$data['access'] = $blog_data->access;
			$data['waccess'] = $blog_data->waccess;
			if(empty($data['alias'])) $data['alias'] = trim(jbTitleToLink($data['title']));
			if (trim(str_replace('-','',$data['alias'])) == '') {
			$data['alias'] = JFactory::getDate()->format('Y-m-d-H-i-s');
			}			
		}
        //$template ='<div class="jb-blogs" style="background: url(\\\'http://cdn.designrshub.com/wp-content/uploads/2012/08/22_free_linen_texture.jpg\\\');"> <div class="jb-blogs-head clearfix jb-postsofblog-head"><div class="jb-blog-imgcont">{standart_blog_image}</div><div class="jb-blog-info"><h3 class="jb-blog-title">'.JText::_('COM_JOOMBLOG_BLOG').': '.' {glog_title}</h3><div class="jb-blog-authors">{blog_profile_link}</div> <div class="jb-blog-desc">{blog_desc}</div></div></div></div>';
        $template ='<div class="jb-blogs"> <div class="jb-blogs-head clearfix jb-postsofblog-head"><div class="jb-blog-imgcont">{standart_blog_image}</div><div class="jb-blog-info"><h3 class="jb-blog-title">'.JText::_('COM_JOOMBLOG_BLOG').': '.' {blog_title}</h3><div class="jb-blog-authors">{blog_profile_link}</div> <div class="jb-blog-desc">{blog_desc}</div></div></div></div>';

        if(!$data['header']) $data['header'] = $template;

		$pathway = $mainframe->getPathway();
		$tpl = new JoomblogTemplate();
 		$blogModel = $this->getModel('Blog', 'JoomblogModel');
		$blogForm = $blogModel->getForm($data);
		$tpl->set('blogForm', $blogForm, true);
		$tpl->set('data', $data, true);
		$tpl->set('template', $template, true);

        JFormHelper::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
        $jEditor = JFormHelper::loadFieldType('Html5editor', false);
        $jEditor->setName('header');
        $jEditor->setValue($data['header']);
        $jEditor->setLabel(JText::_('COM_JOOMBLOG_ITEM_DETAILS_HEADER'));
        $tpl->set('jEditor', $jEditor, true);

		$html = $tpl->fetch(JB_TEMPLATE_PATH."/admin/editblog.html");
		$html = str_replace("src=\"icons", "src=\"" . rtrim( JURI::base() , '/' ) . "/components/com_joomblog/templates/admin/icons", $html);
		return $html;
	}
}
