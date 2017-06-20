<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

global $_JOOMBLOG, $_JB_CONFIGURATION, $Itemid;

$mainframe	= JFactory::getApplication();
$my	= JFactory::getUser();
$component_task = JFactory::getApplication()->input->get( 'task' , '' , 'GET' );
if (empty($my->id) AND ($component_task =='dashboard' OR $component_task =='adminhome' OR $component_task =='showcomments'  OR $component_task =='bloggerstats'  OR $component_task =='editblog'  OR $component_task =='newblog'  OR $component_task =='bloggerpref'))
{
	$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&view=default',false));
} 

jimport('joomla.html.parameter');

require_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'defines.joomblog.php' );
require_once( JB_COM_PATH.DIRECTORY_SEPARATOR.'task'.DIRECTORY_SEPARATOR.'base.php' );
require_once( JB_COM_PATH.DIRECTORY_SEPARATOR.'functions.joomblog.php' );
require_once( JB_LIBRARY_PATH.DIRECTORY_SEPARATOR.'datamanager.php' );
require_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomblog'.DIRECTORY_SEPARATOR.'config.joomblog.php' );
require_once( JB_COM_PATH.DIRECTORY_SEPARATOR.'template.php' );

JTable::addIncludePath(JB_COM_PATH.DIRECTORY_SEPARATOR.'tables');

$_JB_CONFIGURATION = new JB_Configuration();

$mainframe	= JFactory::getApplication();
$jinput = JFactory::getApplication()->input;
if($jinput->get('Itemid') != 0) {
  $Itemid = $jinput->get('Itemid');
}else{
  $Itemid = 0;
}

$sectionid 	= $_JB_CONFIGURATION->get('postSection');
$catid 	= $_JB_CONFIGURATION->get('catid');
$sections	= $_JB_CONFIGURATION->get('managedSections');

if ($sections == ""){
	$sections = "-1";
}


function jbfPublishedcomment(){
	global $_JB_CONFIGURATION;
	
	$mainframe	= JFactory::getApplication();
	$my	= JFactory::getUser();
	$db	= JFactory::getDBO();
	$jinput = JFactory::getApplication()->input;
	$params = $jinput->get('params','');
	$contentid = $jinput->get('contentid',0);
	$id = $jinput->get('id',0);
	
	if(!$my->get('id') || !$_JB_CONFIGURATION->get('useComment')){
		$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_NOT_CHANGING_COMMENT'));
	}
	
	if($_JB_CONFIGURATION->get('useComment')){
		$date = JFactory::getDate();
		$strSQL	= "UPDATE #__joomblog_comment SET `published`='".$params."', `modified`='".$date->toSql()."' WHERE `id`= $id AND `contentid` = $contentid ";
		$db->setQuery($strSQL);
		$db->execute();
	}
	
	if($params == '1'){
		$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_PUBLISHED_COMMENT'));
	}else{
		$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_UNPUBLISHED_COMMENT'));
	}
}

function jbfSavecomment(){
	global $_JB_CONFIGURATION;
	
	$mainframe = JFactory::getApplication(); 
	$row = JTable::getInstance( 'Comments' , 'Table' );
	$jinput = JFactory::getApplication()->input;
	$contentid = $jinput->get('contentid',0);
	
	$user =  JFactory::getUser();
	
	$comments = $jinput->get( 'editcomment' , 0 , 'REQUEST' );
	
	if(!$user->get('id') || !$_JB_CONFIGURATION->get('useComment')){
		$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_NOT_SAVING_COMMENT'));
	}
	
	if (!class_exists('HTML_BBCodeParser') AND !function_exists('BBCode')) {
		include_once (JB_LIBRARY_PATH.DIRECTORY_SEPARATOR."bbcodeparser.php");
	}
	
	if($comments){
		
		$date = JFactory::getDate();
		
		foreach($comments as $key => $value){
			$row->load($key);
			if($row->id && $row->user_id == $user->get('id')){
				$row->comment = BBCode($value);
				$row->modified = $date->toSql();
				$row->modified_by = $user->get('id');
				$row->store();
			}
		}
	}
	
	$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_UPDATE_COMMENT'));
}

function jbfAddcomment(){
	global $_JB_CONFIGURATION;

	$mainframe = JFactory::getApplication(); 
	$sessions = JFactory::getSession();
	$db	= JFactory::getDBO();
	$my	= JFactory::getUser();
	$row = JTable::getInstance( 'Comments' , 'Table' );
	$jinput = JFactory::getApplication()->input;
	$contentid = $jinput->get('contentid',0);
	$data['name']= JFactory::getApplication()->input->post->get('name');
	$data['email']= JFactory::getApplication()->input->get('email','','RAW');
	$data['comment']= JFactory::getApplication()->input->post->get('comment','','HTML');
	$data['comment'] = nl2br($data['comment']);
	$data['recaptcha_challenge_field']= JFactory::getApplication()->input->post->get('recaptcha_challenge_field','','string');
	$data['recaptcha_response_field']= JFactory::getApplication()->input->post->get('recaptcha_response_field','','string');
	$data['contentid']= JFactory::getApplication()->input->post->get('contentid');
	$data['task']= JFactory::getApplication()->input->post->get('task');
	$data['option']= JFactory::getApplication()->input->post->get('option');
	
	if (!class_exists('HTML_BBCodeParser') AND !function_exists('BBCode')) {
		include_once (JB_LIBRARY_PATH.DIRECTORY_SEPARATOR."bbcodeparser.php");
	}
	if(!JFactory::getUser()->id OR(JFactory::getUser()->id AND $_JB_CONFIGURATION->get('useCommentCaptchaRegisteredUsers'))){
		if($_JB_CONFIGURATION->get('useCommentCaptcha') == 2){
			require_once( JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'recaptchalib.php' );
			$privatekey = $_JB_CONFIGURATION->get('recaptcha_privatekey');
			if (empty($privatekey))
			{
				$privatekey = "6LefN9USAAAAAPKe9gjZt5SS9dhblDtuROBQUcMe";
			}
			$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], JFactory::getApplication()->input->post->get('recaptcha_challenge_field','','string'), JFactory::getApplication()->input->post->get('recaptcha_response_field','','string'));
			if (!$resp->is_valid) {
				$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_CAPTCHA_NOT_CORRECT'));
			}
		}
		elseif($_JB_CONFIGURATION->get('useCommentCaptcha')){
			$captcha = $jinput->get('captchacode','','string');
			if($captcha != $sessions->get("captcha")){
				$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_CAPTCHA_NOT_CORRECT'));
			}
		}
	}
	if($_JB_CONFIGURATION->get('useComment')){
		if($_JB_CONFIGURATION->get('useCommentOnlyRegisteredUsers')){
			if(!$my->get('id')){
				$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_ONLY_REGISTERED_USERS'));
			}
		}
		
		$row->bind( $data , true);

		jimport('joomla.mail.helper');	
		if(!JMailHelper::isEmailAddress($row->email)){
			$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_NOT_CORRECT_EMAIL'));
		}
			
		$row->comment = BBCode($row->comment);
		
		if($my->get('id')){
			$row->user_id = $my->get('id');
		}
		
		$row->ip = $_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR') ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');

    $date = JFactory::getDate();
    $row->created = $date->toSql();

		if($_JB_CONFIGURATION->get('allowModerateComment')){
			$row->published = 1;
		}
		$row->store();
		
		$query = "SELECT * FROM `#__joomblog_posts` WHERE `id`='{$row->contentid}' ";
    $db->setQuery( $query );
    $content = $db->loadObject();
		
		if($_JB_CONFIGURATION->get('notifyCommentAdmin') && !array_diff($my->groups, array('Super User')) ){
      jbNotifyCommentAdmin($row->id, $data['name'], $data['title'], $comment);
		}
	
		if($_JB_CONFIGURATION->get('notifyCommentAuthor') && $my->get('id') != $content->created_by ){
      jbNotifyCommentAuthor($row->id, $data['name'], $data['title'], $comment);
		}


		if(!$_JB_CONFIGURATION->get('allowModerateComment')){
			$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_ADDING_COMMENT_AND_MODERATE'));
		}else{
			$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_ADDING_COMMENT'));
		}
		
	}else{
		$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$contentid.'&Itemid='.jbGetItemId(),false),JText::_('COMMENT_NOT_ADDING_COMMENT'));
	}
}

function jbfSavenewblog()
{
	global $JBBLOG_LANG, $_JB_CONFIGURATION;

	$db	= JFactory::getDBO();
	$mainframe	= JFactory::getApplication();
	$my	= JFactory::getUser();
	$row = JTable::getInstance( 'Blog' , 'Table' );
	$isNew = true;
	$formData = JFactory::getApplication()->input->post->get('jform', array(), 'array');
	$data['title']= JFactory::getApplication()->input->post->get('title','','string');
	$data['description']= JFactory::getApplication()->input->post->get('description','','string');
	$data['header']= JFactory::getApplication()->input->post->get('header','','raw');
	$data['viewpostrules']= JFactory::getApplication()->input->post->get('viewpostrules');
	$data['viewcommrules']= JFactory::getApplication()->input->post->get('viewcommrules');
	$data['metakey']= JFactory::getApplication()->input->post->get('metakey','','string');
	$data['metadesc']= JFactory::getApplication()->input->post->get('metadesc','','string');
	$data['alias'] = $db->escape($formData['alias']);
	if(empty($data['alias'])) $data['alias'] = trim(jbTitleToLink($data['title']));
	if (trim(str_replace('-','',$data['alias'])) == '') {
	$data['alias'] = JFactory::getDate()->format('Y-m-d-H-i-s');
	}	

	$row->bind( $data , true);
	
	if(!$my->authorise('blog.create', 'com_joomblog')){
		$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_POST'));
		return;
	}

	$title = $data['title'];
	$validation	= array();

	if( empty( $title ) || $title == JText::_('COM_JOOMBLOG_BLOG_TITLE') )
	{
		$validation['title'] = JText::_('COM_JOOMBLOG_TITLE_IS_REQUIRED');
	}
	if(!$row->checkAlias()){
	    $validation['alias'] = JText::_('COM_JOOMBLOG_BLOG_NON_UNIQUE_ALIAS');
	}

	$date = JFactory::getDate();
	$row->create_date = $date->format('Y-m-d H:i:s');
	
	$row->title = stripslashes($row->title);
	$row->user_id = $my->id;
	
	$row->published = $_JB_CONFIGURATION->get('autoapproveblogs');
	$row->approved = $_JB_CONFIGURATION->get('autoapproveblogs');
	
	if(empty($validation))
	{
		 $row->store();
		 
		 jbBlogNotifyAdmin($row,$isNew);
		 
		if ($_JB_CONFIGURATION->get('autoapproveblogs'))
		{
			$url = 'index.php?option=com_joomblog&blogid='.$row->id.'&view=blogger&Itemid='.jbGetItemId();
			$newpost_url = 'index.php?option=com_joomblog&blogid='.$row->id.'&task=write&id=0&Itemid='.jbGetItemId();
			$ms = JText::_('COM_JOOMBLOG_BLOG_SUCCESSFULLY_SAVED').' '.JText::sprintf('COM_JOOMBLOG_BLOG_CAN_CREATE_POSTS' , $newpost_url );
		} 
		else 
		{
			$url = 'index.php?option=com_joomblog&task=blogs&Itemid='.jbGetItemId();
			$ms = JText::_('COM_JOOMBLOG_BLOG_SUCCESSFULLY_SAVED_NEED_APPROVE');
		}
		
		$mainframe->redirect(JRoute::_($url,false),$ms);
		
	}else{ 
		$errors			= '';
		foreach( $validation as $error )
		{
			$errors	.= '<div style="margin-bottom: 5px">' . $error . '</div>';
		}

		$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&task=blogs&Itemid='.jbGetItemId(),false),$errors."</br>");
	}
	
	return;
}

function jbfSaveeditedblog()
{
	$db	= JFactory::getDBO();
	$mainframe	= JFactory::getApplication();
	$formdata = JFactory::getApplication()->input->get('jform',Array(),'array');
	$title = $db->Escape($formdata['title']);
	$blogid = $formdata['blogid'];
	$alias = $db->Escape($formdata['alias']);
	$alias = trim($alias);
	$alias = str_replace(' ','-',$alias);
	if(empty($alias)) $alias = trim(jbTitleToLink($title));
	if (trim(str_replace('-','',$alias)) == '') {
	$alias = JFactory::getDate()->format('Y-m-d-H-i-s');
	}
	$description = $db->Escape($formdata['description']);
    $header = str_replace("'","\'",JFactory::getApplication()->input->post->get('header','','raw'));
	$waccess = $formdata['waccess'];
	$access = $formdata['access'];
	$metakey = $db->Escape($formdata['metakey']);
	$metadesc = $db->Escape($formdata['metadesc']);
	$query = $db->getQuery(true);
	$query->update('#__joomblog_list_blogs');
	$query->where('`id`='.(int)$blogid);
	$query->set('`title`='.$db->Quote($title));
	$query->set('`alias`='.$db->Quote($alias));
	$query->set('`description`='.$db->Quote($description));
	$query->set("`header`='".$header."'");
	$query->set('`access`='.(int)$access);
	$query->set('`waccess`='.(int)$waccess);
	$query->set('`alias`='.$db->Quote($alias));
	$query->set('`metakey`='.$db->Quote($metakey));
	$query->set('`metadesc`='.$db->Quote($metadesc));
	$db->setQuery($query);
	$db->execute();
	$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&view=blogger&blogid='.$blogid.'&Itemid='.jbGetItemId(),false),JText::_('COM_JOOMBLOG_BLOG_SAVED')."</br>");
}

function jbfDeleteblog()
{
	$db	= JFactory::getDBO();
	$mainframe	= JFactory::getApplication();
	$blog_id = JFactory::getApplication()->input->get('blogid');
	$query = "SELECT * FROM #__joomblog_blogs WHERE `blog_id`=".$blog_id;
	$db->setQuery($query);
	$have_posts = $db->loadObjectList();
	if (!empty($have_posts))
	{
		$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&task=editblog&blogid='.$blog_id.'&Itemid='.jbGetItemId(),false),JText::_('COM_JOOMBLOG_CANNOT_DELETE_NOT_EMPTY_BLOG')."</br>");
	}
	else
	{
		$query = "DELETE FROM #__joomblog_list_blogs WHERE `id`=".$blog_id;
		$db->setQuery($query);
		$db->execute();
		$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&view=default&Itemid='.jbGetItemId(),false),JText::_('COM_JOOMBLOG_BLOG_DELETION_SUCCESS')."</br>");
	}
}

function jbfSaveblog()
{
	global $JBBLOG_LANG, $_JB_CONFIGURATION;

	$db	= JFactory::getDBO();
	$mainframe	= JFactory::getApplication();
	$my	= JFactory::getUser();
	$row = JTable::getInstance( 'Posts' , 'Table' );
	$isNew = true;
	$data['title']= JFactory::getApplication()->input->get('title','','string');
	$data['id']= JFactory::getApplication()->input->get('id','','integer');
	$data['publish_up']= JFactory::getApplication()->input->get('publish_up','0000-00-00 00:00','string');
	$data['publish_down']= JFactory::getApplication()->input->get('publish_down','0000-00-00 00:00','string');
	$formdata = JFactory::getApplication()->input->get('jform',Array(),'array');
	$data['blog_id'] = $formdata['blog_id'];
	$data['catid'] = $formdata['catid'];
	$data['alias'] = $formdata['alias'];
	$data['defaultimage'] = $formdata['defaultimage'];
	$data['access'] = $formdata['access'];
	$data['caccess'] = $formdata['caccess'];
	$data['alternative_readmore']= JFactory::getApplication()->input->get('alternative_readmore','','string');
	$data['introtext']= $_POST['fulltext'];
	$data['introtext'] = preg_replace('~[\r\n]+~', '', $data['introtext']);
	// $data['fulltext'] = nl2br($data['fulltext']);
	$data['introtext'] = stripslashes($data['introtext']);
	$data['introtext'] = $db->Escape($data['introtext'],false);
    $data['fulltext'] = '';
	$data['viewpostrules']= JFactory::getApplication()->input->get('viewpostrules');
	$data['language']= JFactory::getApplication()->input->get('language','*','string');
	$data['viewcommrules']= JFactory::getApplication()->input->get('viewcommrules');
	$metadata_array = array('metakey' => JFactory::getApplication()->input->get('metakey','','string'), 'metadesc' => JFactory::getApplication()->input->get('metadesc','','string'), 'page_image' => JFactory::getApplication()->input->get('page_image','','string'), 'ogdesc' => JFactory::getApplication()->input->get('ogdesc','','string'));
	$metadata_json = json_encode ($metadata_array);
	$data['metadata']= $metadata_json ;

    $row->bind( $data , true);
    $row->state = 0;
    if($row->id) $isNew	= false;

    if (isset($data['introtext']))
    {
        //$pattern = '#<hr\s+id=("|\")system-readmore("|\")\s*\/*>#i';
        //$tagPos = preg_match($pattern, $data['introtext']);
        $pattern = '<hr id=\"system-readmore\">';
        $tagPos = strpos($data['introtext'], $pattern);

        if ($tagPos == 0)
        {
            $row->introtext = $data['introtext'];
            $row->fulltext = '';
        }
        else
        {
            list ($row->introtext, $row->fulltext) = explode($pattern, $data['introtext']);
            $data['introtext']=$row->introtext;
            $data['fulltext']=$row->fulltext;
        }
    }

	if ($data['catid'])
		{
			if (sizeof($data['catid']))
			{
				$allcategs = $data['catid'];
				$row->catid = $allcategs[0];
			}
		}
					
	$categoryId	= $row->catid;//$data['catid'];
	
	if($isNew && !jbGetUserCanPost()){
		$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_POST'));
		return;
	}
	
	if($data['blog_id'] > 0 && !jbGetUserCanPostToBlog($data['blog_id'])){
		$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_POST'));
		return;
	}

	if(!$my->authorise('core.create', 'com_joomblog.category.'.$categoryId)){
		$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_POST_IN_CATEGORY'));
		return;
	}

	if($isNew){
		$params = new JRegistry();
		$params->def('show_title', "");
		$params->def('link_titles', "");
		$params->def('show_intro', "");
		$params->def('show_category', "");
		$params->def('link_category', "");
		$params->def('show_parent_category', "");
		$params->def('link_parent_category', "");
		$params->def('show_author', "");
		$params->def('link_author', "");
		$params->def('show_create_date', "");
		$params->def('show_modify_date', "");
		$params->def('show_publish_date', "");
		$params->def('show_item_navigation', "");
		$params->def('show_icons', "");
		$params->def('show_print_icon', "");
		$params->def('show_email_icon', "");
		$params->def('show_vote', "");
		$params->def('show_hits', "");
		$params->def('show_noauth', "");
		$params->def('alternative_readmore', "");
		$params->def('article_layout', "");
		$params->def('alternative_readmore',$data['alternative_readmore']);
		$row->attribs = (string)$params;
	}else{
		$params = new JRegistry();
		$params->loadString($row->attribs);
		$params->set('alternative_readmore',$data['alternative_readmore']);
		$row->attribs = (string)$params;
	}
		
	$title = trim($data['title']);
	$validation	= array();

	if( empty( $title ) || $title == JText::_('COM_JOOMBLOG_BLOG_TITLE') )
	{
		$validation['title'] = JText::_('COM_JOOMBLOG_TITLE_IS_REQUIRED');
	}

	$fulltext = (empty($data['fulltext']) ? $data['introtext'] : $data['fulltext'] );

	if( empty( $fulltext ) )
	{
		$validation['fulltext'] = JText::_('COM_JOOMBLOG_CANNOT_BE_EMPTY');
	}

	if(empty( $categoryId ) )
	{
		$validation['catid'] = JText::_('COM_JOOMBLOG_CATEGORY_MUST_BE_SELECTED');
	}

	if(empty( $data['blog_id'] ) )
	{
		$validation['blogid'] = JText::_('COM_JOOMBLOG_BLOG_MUST_BE_SELECTED');
	}

	
	$createdDate = $data['publish_up'];

	if(isset($createdDate) && !empty($createdDate))
	{
		$date = JFactory::getDate( $row->publish_up );
		$row->created = $date->format('Y-m-d H:i:s');
	}
	else
	{
		$date = JFactory::getDate();
		$row->created = $date->format('Y-m-d H:i:s');
	}
	
	if(!$isNew){
		$row->modified = $date->toSql();	
		$row->modified_by = $my->id;
	}else{
	    $row->created_by = $my->id;
	}


	$row->publish_up = $row->created;

	$jcStatus		= isset( $data['jcState'] ) && !empty( $data['jcState'] ) ? $data['jcState'] : false;
	$row->fulltext 	= stripslashes($row->fulltext);
	$row->introtext = stripslashes($row->introtext);
	$row->title 	= stripslashes($row->title);

	if( $jcStatus !== false )
	{
		if($jcStatus == 'enabled')
		{
			$row->fulltext  .= '{jomcomment}';
		}
		else if($jcStatus == 'disabled')
		{
		    $row->fulltext  .= '{!jomcomment}';
		}
	}
	if(!$row->checkAlias()){
	    $validation['alias'] = JText::_('COM_JOOMBLOG_NON_UNIQUE_ALIAS');
	}

	
	if(empty($validation))
	{
		$row->store();
		$query = 'SELECT id FROM #__joomblog_blogs WHERE content_id = "'.$row->id.'"';
		$db->setQuery($query);
		$exists = $db->loadResult();
		
		if (!$exists){
			$query = 'INSERT INTO #__joomblog_blogs (`id`, `content_id`, `blog_id`) VALUES ("", "'.$row->id.'", "'.$data['blog_id'].'")';
			$db->setQuery($query);
			$db->execute();
		} else {
			$query = 'UPDATE #__joomblog_blogs SET blog_id = "'.$data['blog_id'].'" WHERE content_id="'.$row->id.'"';
			$db->setQuery($query);
			$db->execute();
		}
		
		$jform = JFactory::getApplication()->input->post->get('jform', array(), 'array');
		if($jform['access'] == -4){
		    $js_group = (int)$jform['access_gr'];
		    $query	= "UPDATE `#__joomblog_posts` SET access_gr=$js_group WHERE `id`='".$row->id."' ";
		    $db->setQuery( $query );
		    $db->execute();
		}
		if($jform['caccess'] == -4){
		    $js_group = (int)$jform['caccess_gr'];
		    $query	= "UPDATE `#__joomblog_posts` SET caccess_gr=$js_group WHERE `id`='".$row->id."' ";
		    $db->setQuery( $query );
		    $db->execute();

		}
		
		$row->load($row->id);
		$jinput = JFactory::getApplication()->input;

		if( $jinput->get('tags','','string') )
		{
			//add tags
			$query	= "DELETE FROM #__joomblog_content_tags WHERE contentid=".$row->id." ";
			$db->setQuery( $query );
			$db->execute();
				
			$tags = explode(',',$jinput->get('tags','','string'));	

			if( is_array( $tags ) ){
				foreach($tags as $tag){
					$tagid = jbfAddtag($tag);
			
					$query	= "INSERT INTO #__joomblog_content_tags "
							. "(`contentid`,`tag`) VALUES (".$row->id.", $tagid)";
					$db->setQuery( $query );
					$db->execute();
				}
			}else{
				$tagid = jbfAddtag($tag);
			
				$query	= "INSERT INTO #__joomblog_content_tags "
						. "(`contentid`,`tag`) VALUES (".$row->id.", $tagid)";
				$db->setQuery( $query );
				$db->execute();
			}
		}
		
	/*** MULTI CATS ***/
		if (sizeof($allcategs))
		{
			$query = $db->getQuery(true);
			$query->delete();
			$query->from('#__joomblog_multicats');
			$query->where('aid='.(int)$row->id);
			$db->setQuery($query);
			$db->execute();
			
			foreach ( $allcategs as $alc ) 
			{
				$query = $db->getQuery(true);
				$query->insert('#__joomblog_multicats');
				$query->set('aid='.(int)$row->id);
				$query->set('cid='.(int)$alc);
				$db->setQuery($query);
				$db->execute();
			}					
		}								
	/*****************/
		
		jbNotifyAdmin($row->id, jbGetAuthorName($row->created_by, $_JB_CONFIGURATION->get('useFullName')), $row->title, $row->introtext . $row->fulltext, $isNew);
		
		jbSortOrder($row);
		$query = "SELECT `blog_id` FROM #__joomblog_blogs WHERE content_id = '".(int)$row->id."' ";
		$db->setQuery( $query );
		$blogid=$db->loadResult();

		if(JComponentHelper::getParams('com_joomblog')->get('defaultPublishStatus')) $mainframe->redirect(JRoute::_('index.php?option=com_joomblog&show='.$row->id.'&Itemid='.jbGetItemId(),false),JText::_('COM_JOOMBLOG_BLOG_ENTRY_SAVED'));
		else $mainframe->redirect(JRoute::_('index.php?option=com_joomblog&task=write&id='.$row->id.'&Itemid='.jbGetItemId(),false),JText::_('COM_JOOMBLOG_BLOG_ENTRY_SAVED_MODERATE'));
		}else{ 
			$errors			= '';
			foreach( $validation as $error )
			{
				$errors	.= '<div style="margin-bottom: 5px">' . $error . '</div>';
			}
			JFactory::getApplication()->setUserState('com_joomblog.write.form', $data);
			$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&task=write&id='.$row->id.'&error=true&Itemid='.jbGetItemId(),false),$errors."</br>");
		}
		
	return;
}

function jbfSavedraft()
{
	global $JBBLOG_LANG, $_JB_CONFIGURATION;
	
	$db	= JFactory::getDBO();
	$mainframe	= JFactory::getApplication();
	$my	= JFactory::getUser();
	$row = JTable::getInstance( 'Posts' , 'Table' );
	
	$isNew = true;
	
	$metadata_array = array('metakey' => JFactory::getApplication()->input->post->get('metakey','','string'), 'metadesc' => JFactory::getApplication()->input->post->get('metadesc','','string'), 'page_image' => JFactory::getApplication()->input->post->get('page_image','','string'), 'ogdesc' => JFactory::getApplication()->input->post->get('ogdesc','','string'));
	if (!empty($metadata_array))
	{
		$metadata_json = json_encode ($metadata_array);
		$data['metadata']= $metadata_json ;	
	}
	$data['fulltext']= $_POST['fulltext'];
	$data['fulltext'] = preg_replace('~[\r\n]+~', '', $data['fulltext']);
	$data['fulltext'] = $db->Escape($data['fulltext'],false);
	$data['language'] = (isset($_POST['language']))?stripslashes($_POST['language']):'*';
	$formData = JFactory::getApplication()->input->get('jform', array(), 'array');
	$data['catid'] = (!empty($formData['catid']))?(array)$formData['catid']:Array();
	$data['blog_id'] = (!empty($formData['blog_id']))?(int)$formData['blog_id']:0;
	$data['access'] = (!empty($formData['access']))?(int)$formData['access']:0;
	$data['caccess'] = (!empty($formData['caccess']))?(int)$formData['caccess']:0;
	$data['title']=JFactory::getApplication()->input->get('title','','string');
	$data['alias'] = (!empty($formData['alias']))?$db->Escape($formData['alias']):'';
	if ($data['alias'] == '')
	{
		$data['alias'] = trim(str_replace('-','',$data['title']));
		if ($data['alias'] == '') 
		{
			$data['alias'] = JFactory::getDate()->format('Y-m-d-H-i-s');
		}
	}
	$data['defaultimage'] = (!empty($formData['defaultimage']))?$db->Escape($formData['defaultimage']):'';
	$data['alternative_readmore']=JFactory::getApplication()->input->get('alternative_readmore','','string');	
	$data['publish_up']=JFactory::getApplication()->input->get('publish_up','0000-00-00');
	$data['created_by']=$my->id;

	$post_id = JFactory::getApplication()->input->get('id');
	if ($post_id >0)
	{
		$data['id']=$post_id;
	}

	$row->bind( $data , true);
	if (empty($row->defaultimage))  $row->defaultimage = $data['defaultimage'];

	if($row->id) $isNew	= false;

	if ($data['catid'])
		{
			if (sizeof($data['catid']))
			{
				$allcategs = $data['catid'];
				$row->catid = $allcategs[0];
			}
		}
			
	$categoryId	= $row->catid;//$data['catid'];
	
	if($isNew && !jbGetUserCanPost()){
		$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_POST'));
		return;
	}
	
	if($data['blog_id'] > 0 && !jbGetUserCanPostToBlog($data['blog_id'])){
		$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_POST'));
		return;
	}
	
	if(!$my->authorise('core.create', 'com_joomblog.category.'.$categoryId)){
		$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_POST_IN_CATEGORY'));
		return;
	}

	if($isNew){
		$params = new JRegistry();
		$params->def('show_title', "");
		$params->def('link_titles', "");
		$params->def('show_intro', "");
		$params->def('show_category', "");
		$params->def('link_category', "");
		$params->def('show_parent_category', "");
		$params->def('link_parent_category', "");
		$params->def('show_author', "");
		$params->def('link_author', "");
		$params->def('show_create_date', "");
		$params->def('show_modify_date', "");
		$params->def('show_publish_date', "");
		$params->def('show_item_navigation', "");
		$params->def('show_icons', "");
		$params->def('show_print_icon', "");
		$params->def('show_email_icon', "");
		$params->def('show_vote', "");
		$params->def('show_hits', "");
		$params->def('show_noauth', "");
		$params->def('alternative_readmore', "");
		$params->def('article_layout', "");
		$params->def('alternative_readmore',$data['alternative_readmore']);
		$row->attribs = (string)$params;
	}else{
		$params = new JRegistry();
		$params->loadString($row->attribs);
		$params->set('alternative_readmore',$data['alternative_readmore']);
		$row->attribs = (string)$params;
	}
		
	$title = trim($data['title']);
	
	$createdDate = $data['publish_up'];

	if(isset($createdDate) && !empty($createdDate))
	{
		$date = JFactory::getDate( $row->publish_up );
		$row->created = $date->Format('Y-m-d H:i:s');
	}
	
	if(!$isNew){
		$row->modified = $date->toSQL();	
		$row->modified_by = $my->id;
	}else{
	    $row->created_by = $my->id;
	}
	
	$row->publish_up = $row->created;

	$jcStatus		= isset( $data['jcState'] ) && !empty( $data['jcState'] ) ? $data['jcState'] : false;
	$row->fulltext 	= stripslashes($row->fulltext);
	$row->introtext = stripslashes($row->introtext);
	$row->title 	= stripslashes($row->title);
	
	if( $jcStatus !== false )
	{
		if($jcStatus == 'enabled')
		{
			$row->fulltext  .= '{jomcomment}';
		}
		else if($jcStatus == 'disabled')
		{
		    $row->fulltext  .= '{!jomcomment}';
		}
	}
	$row->state = '-2';
		$row->store();
		$query = 'SELECT id FROM #__joomblog_blogs WHERE content_id = "'.$row->id.'"';
		$db->setQuery($query);
		$exists = $db->loadResult();
		
		if (!$exists){
			$query = 'INSERT INTO #__joomblog_blogs (`id`, `content_id`, `blog_id`) VALUES ("", "'.$row->id.'", "'.$data['blog_id'].'")';
			$db->setQuery($query);
			$db->execute();
		} else {
			$query = 'UPDATE #__joomblog_blogs SET blog_id = "'.$data['blog_id'].'" WHERE content_id="'.$row->id.'"';
			$db->setQuery($query);
			$db->execute();
		}
		
		$jform = JRequest::getVar('jform');
		if($jform['access'] == -4){
		    $js_group = (int)$jform['access_gr'];
		    $query	= "UPDATE `#__joomblog_posts` SET access_gr=$js_group WHERE `id`='".$row->id."' ";
		    $db->setQuery( $query );
		    $db->execute();
		}
		if($jform['caccess'] == -4){
		    $js_group = (int)$jform['caccess_gr'];
		    $query	= "UPDATE `#__joomblog_posts` SET caccess_gr=$js_group WHERE `id`='".$row->id."' ";
		    $db->setQuery( $query );
		    $db->execute();
		}
		
		$row->load($row->id);

		if( JRequest::getString('tags','') )
		{
			//add tags
			$query	= "DELETE FROM #__joomblog_content_tags WHERE contentid=".$row->id." ";
			$db->setQuery( $query );
			$db->execute();
				
			$tags = explode(',',JRequest::getString('tags',''));	

			if( is_array( $tags ) ){
				foreach($tags as $tag){
					$tagid = jbfAddtag($tag);
			
					$query	= "INSERT INTO #__joomblog_content_tags "
							. "(`contentid`,`tag`) VALUES (".$row->id.", $tagid)";
					$db->setQuery( $query );
					$db->execute();
				}
			}else{
				$tagid = jbfAddtag($tag);
			
				$query	= "INSERT INTO #__joomblog_content_tags "
						. "(`contentid`,`tag`) VALUES (".$row->id.", $tagid)";
				$db->setQuery( $query );
				$db->execute();
			}
		}
		
	/*** MULTI CATS ***/
		if (sizeof($allcategs))
		{
			$query = $db->getQuery(true);
			$query->delete();
			$query->from('#__joomblog_multicats');
			$query->where('aid='.(int)$row->id);
			$db->setQuery($query);
			$db->execute();
			
			foreach ( $allcategs as $alc ) 
			{
				$query = $db->getQuery(true);
				$query->insert('#__joomblog_multicats');
				$query->set('aid='.(int)$row->id);
				$query->set('cid='.(int)$alc);
				$db->setQuery($query);
				$db->execute();
			}					
		}								
	/*****************/
	jbSortOrder($row);
	$query = "SELECT `blog_id` FROM #__joomblog_blogs WHERE content_id = '".(int)$row->id."' ";
	$db->setQuery( $query );
	$blogid=$db->loadResult();
	$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&task=write&id='.$row->id.'&blogid='.$blogid.'&Itemid='.jbGetItemId(),false),JText::_('COM_JOOMBLOG_BLOG_DRAFT_SAVED'));
	
	return;
}

function jbfAddtag($newtag){
	$db	= JFactory::getDBO();
	
	$id = 0;
	
	$newtag	= $db->escape( trim($newtag) );
	
	if(!$newtag) return false;

	$query = "SELECT COUNT(*) FROM `#__joomblog_tags` WHERE `name`='{$newtag}' ";
	$db->setQuery( $query );
	$count_tags = $db->loadResult();

	if($count_tags == 0){
		$query  = "INSERT INTO `#__joomblog_tags` (`name`) VALUES ('{$newtag}')";
		$db->setQuery($query);
		$db->execute();
		echo $db->getErrorMsg();
		$id = $db->insertId();
	}else{
		$query 	= "SELECT `id` FROM `#__joomblog_tags` WHERE `name`='{$newtag}'";
		$db->setQuery($query);
		$id = $db->loadResult();
	}
	
	return $id;
}

function isValidMember()
{
	$my	= JFactory::getUser();
		
	if($my->id == '0')
		return false;
	
	return true;
}

function isEditable($contentId)
{
	$db	= JFactory::getDBO();
	$my	= JFactory::getUser();
	$strSQL	= "SELECT `created_by` FROM #__joomblog_posts WHERE `id`='{$contentId}'";
	
	$db->setQuery($strSQL);
	$creator = $db->loadResult();
	
	if($my->id != $creator && $my->id != '42')
		return false;
	
	return true;
}

function jbfTogglepublish()
{
	$mainframe = JFactory::getApplication(); 
	$jinput = JFactory::getApplication()->input;
	$id = $jinput->get('id',0);
	
	$db	= JFactory::getDBO();	
	$my	= JFactory::getUser();
	
	if( !jbGetUserCanPublish() || !$my->authorise('core.edit.state', 'com_joomblog.article.'.$id) ){
		$mainframe->redirect($_SERVER['HTTP_REFERER'],JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_PUBLISH'));
		return;
	}
	
	//while (@ ob_end_clean());
	@ob_end_clean();
	$db->setQuery("SELECT state FROM #__joomblog_posts WHERE id=$id");
	$publish = $db->loadResult();
	
	$publish = intval(!($publish));
	$db->setQuery("UPDATE #__joomblog_posts SET state='$publish' WHERE id=$id");
	$db->execute();
	
	$mainframe->redirect($_SERVER['HTTP_REFERER'], JText::_('COM_JOOMBLOG_BLOG_UPDATED'));
	
	return true;
}

function jbfAddvote()
{
	$mainframe = JFactory::getApplication(); 
	$my	= JFactory::getUser();
	$db	= JFactory::getDBO();
	$jinput = JFactory::getApplication()->input;
	$vote = $jinput->get('vote',0);
	$id = $jinput->get('id',0);
	
	if($vote != 1 && $vote != -1){
		echo '{"msg":"Error"}';
		return;
	}
	$mid = $my->get('id');
	
	//joomplace hack
	//if(!$mid){	if (isset($_COOKIE['bl'])) 	{ $mid=$_COOKIE['bl']; } }
	//
	$registry = new JRegistry();
	
	$msg = "";
	
	$ip = $_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR') ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');
	
	$query	= 'SELECT id FROM #__joomblog_posts WHERE created_by = '.$mid.' AND id = '.$id;
	$db->setQuery( $query );
	$isOwner = $db->loadResult();
	
	if(!$mid){
		$msg = JText::_("VOTES_NOT_USER_REGISTERED");
	}elseif($isOwner){
		$msg = JText::_("VOTES_NOT_ADDED");
	}else{
		if($isOwner){
			$msg = JText::_("VOTES_NOT_ADDED");
		}else

		$query	= 'SELECT * FROM #__joomblog_votes WHERE contentid = '.$id.' AND userid = '.$mid;
		$db->setQuery( $query );
		$isVote = $db->loadObject();
		
		
		if($isVote){
			if($isVote->vote != $vote){
				$vote = $isVote->vote+$vote;
				
				if(!$vote){
					$db->setQuery("DELETE FROM #__joomblog_votes WHERE userid = ".$mid." AND contentid = ".$id);
				}else{
					$db->setQuery("UPDATE #__joomblog_votes SET vote = $vote  WHERE userid = ".$mid." AND contentid = ".$id);
				}
				
				if(!$db->execute()){
					$msg = $db->getErrorMsg();
				}else{
					if(!$vote){
						$msg = JText::_("VOTES_REMOVED");
					}else{
						$msg = JText::_("VOTES_ADDING");
					}
				}
				
				$query	= 'SELECT COUNT(vote) FROM #__joomblog_votes WHERE vote = -1 AND contentid = '.$id;

				$db->setQuery( $query );
				$notlike = $db->loadResult();
				
				$query	= 'SELECT COUNT(vote) FROM #__joomblog_votes WHERE vote = 1 AND contentid = '.$id;
				$db->setQuery( $query );
				$like = $db->loadResult();
				
				$registry->set('sumvote',$like-$notlike);
				
			}else{
				$msg = JText::_("VOTES_YET_ADDED");
			}
		}else{
			$query	= "INSERT INTO #__joomblog_votes "
				. "(`userid`,`contentid`,`vote`) VALUES (".$mid.", ".$id.", ".$vote.")";
			$db->setQuery( $query );

			if(!$db->execute()){
				$msg = $db->getErrorMsg();
			}else{
				$msg = JText::_("VOTES_ADDING");
			}
			
			$query	= 'SELECT COUNT(vote) FROM #__joomblog_votes WHERE vote = -1 AND contentid = '.$id;
			
			$db->setQuery( $query );
			$notlike = $db->loadResult();
			
			$query	= 'SELECT COUNT(vote) FROM #__joomblog_votes WHERE vote = 1 AND contentid = '.$id;
			$db->setQuery( $query );
			$like = $db->loadResult();
			
			$registry->set('sumvote',$like-$notlike);
		}
	}
	
	$registry->set('msg',$msg);
	
	echo (string)$registry;
	
	return;
}

function jbfAddcommentvote()
{
	$mainframe = JFactory::getApplication(); 
	$my	= JFactory::getUser();
	$db	= JFactory::getDBO();
	$jinput = JFactory::getApplication()->input;
	$vote = $jinput->get('vote',0);
	$id = $jinput->get('id',0);
	
	if($vote != 1 && $vote != -1){
		echo '{"msg":"Error"}';
		return;
	}
	
	$registry = new JRegistry();
	
	$msg = "";
	
	$ip = $_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR') ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');
	
	if($my->get('id')){
		$query	= 'SELECT id FROM #__joomblog_comment WHERE user_id = '.$my->get('id').' AND id = '.$id;
	}else{
		$query	= 'SELECT id FROM #__joomblog_comment WHERE ip = '.$ip.' AND id = '.$id;
	}
	$db->setQuery( $query );
	$isOwner = $db->loadResult();
	
	if(!$my->get('id')){
		$msg = JText::_("COMMENT_VOTES_NOT_USER_REGISTERED");
	}elseif($isOwner){
		$msg = JText::_("VOTES_NOT_ADDED");
	}else{
		$query	= 'SELECT * FROM #__joomblog_comment_votes WHERE commentid = '.$id.' AND userid = '.$my->get('id');
		$db->setQuery( $query );
		$isVote = $db->loadObject();

		if($isVote){
			if($isVote->vote != $vote){
				$voted = $isVote->vote+$vote;

				if(!$voted){
					$db->setQuery("DELETE FROM #__joomblog_comment_votes WHERE userid = ".$my->get('id')." AND commentid = ".$id);
				}else{
					$db->setQuery("UPDATE #__joomblog_comment_votes SET vote = $voted  WHERE userid = ".$my->get('id')." AND commentid = ".$id);
				}

				if(!$db->execute()){
					$msg = $db->getErrorMsg();
				}else{
					if(!$vote){
						$msg = JText::_("VOTES_REMOVED");
					}else{
						$msg = JText::_("VOTES_ADDING");
					}
				}

				$db->setQuery("UPDATE #__joomblog_comment SET voted = voted + $vote  WHERE id ='$id' ");
				if(!$db->execute()){
					$msg = $db->getErrorMsg();
				}
				
				$query	= "SELECT voted FROM #__joomblog_comment WHERE id ='$id' ";
				$db->setQuery( $query );
				
				$registry->set('sumcommentvote',$db->loadResult());
				
			}else{
				$msg = JText::_("VOTES_YET_ADDED");
			}
		}else{
			$query	= "INSERT INTO #__joomblog_comment_votes "
				. "(`userid`,`commentid`,`vote`) VALUES (".$my->get('id').", ".$id.", ".$vote.")";
			$db->setQuery( $query );

			if(!$db->execute()){
				$msg = $db->getErrorMsg();
			}else{
				$msg = JText::_("COMMENT_VOTES_ADDING");
			}
			
			$db->setQuery("UPDATE #__joomblog_comment SET voted = voted + $vote  WHERE `id`='$id'");
			$db->execute();
			
			$query	= 'SELECT voted FROM #__joomblog_comment WHERE id = '.$id;
			$db->setQuery( $query );
			
			$registry->set('sumcommentvote',$db->loadResult());
		}
	}
	
	$registry->set('msg',$msg);
	
	echo (string)$registry;
	
	return;
}

function jbfTogglecommentpublish()
{
	global $JBBLOG_LANG, $_JB_CONFIGURATION;

	$mainframe = JFactory::getApplication(); 
	$db	= JFactory::getDBO();
	$my	= JFactory::getUser();
	$jinput = JFactory::getApplication()->input;
	$id = $jinput->get('id',0);
	
	if($_JB_CONFIGURATION->get('useJomComment')){
		$query	= 'SELECT contentid FROM #__jomcomment WHERE `id`=' . $db->Quote( $id );
		$db->setQuery( $query );
		$cid	= $db->loadResult();
	}else{
		$query	= 'SELECT contentid FROM #__joomblog_comment WHERE `id`=' . $db->Quote( $id );
		$db->setQuery( $query );
		$cid	= $db->loadResult();
	}

	$query	= 'SELECT created_by FROM #__joomblog_posts WHERE `id`=' . $db->Quote( $cid );
	$db->setQuery( $query );

	$author = $db->loadResult();
	
	if($author != $my->id){
		return;
	}
	
	if($_JB_CONFIGURATION->get('useJomComment')){
		$db->setQuery("SELECT published FROM #__jomcomment WHERE `id`='$id'");
		$publish = $db->loadResult();

		$publish = intval(!($publish));
		$db->setQuery("UPDATE #__jomcomment SET published='$publish' WHERE `id`='$id'");
		$db->execute();
	}else{
		$db->setQuery("SELECT published FROM #__joomblog_comment WHERE `id`='$id'");
		$publish = $db->loadResult();

		$publish = intval(!($publish));
		$db->setQuery("UPDATE #__joomblog_comment SET published='$publish' WHERE `id`='$id'");
		$db->execute();
	}
	
	$mainframe->redirect($_SERVER['HTTP_REFERER']);
	
	return;
}

function jbfCommentapproveall()
{	
	global $JBBLOG_LANG, $_JB_CONFIGURATION;

	$mainframe = JFactory::getApplication(); 
	$db	= JFactory::getDBO();
	$my	= JFactory::getUser();
	
	$strSQL	= "SELECT id FROM #__joomblog_posts WHERE created_by='{$my->id}'";
	$db->setQuery( $strSQL );
	$result	= $db->loadObjectList();
	$rows	= array();
	
	foreach($result as $row)
	{
		$rows[]	= $row->id;
	}
	$rows	= implode(',', $rows);
	
	if($_JB_CONFIGURATION->get('useJomComment')){
		$strSQL	= "UPDATE #__jomcomment SET `published`='1' WHERE `contentid` IN({$rows}) "
				. "AND `published`='0'";
		$db->setQuery($strSQL);
		$db->execute();
	}else{
		$strSQL	= "UPDATE #__joomblog_comment SET `published`='1' WHERE `contentid` IN({$rows}) "
				. "AND `published`='0'";
		$db->setQuery($strSQL);
		$db->execute();
	}
	
	$itemId	= jbGetItemId();
	$link	= JRoute::_('index.php?option=com_joomblog&task=showcomments&Itemid=' . $itemId , false);
	
	$mainframe->redirect($link, JText::_('COM_JOOMBLOG_UNPBL_COMNTS'));
		
	return;
}

function jbfCommentremoveunpublished()
{
	global $JBBLOG_LANG, $_JB_CONFIGURATION;
	
	$mainframe = JFactory::getApplication(); 
	$db	= JFactory::getDBO();
	$my	= JFactory::getUser();
	
	$strSQL	= "SELECT id FROM #__joomblog_posts WHERE created_by='{$my->id}'";
	$db->setQuery($strSQL);
	$result	= $db->loadObjectList();
	$rows	= array();
	
	foreach($result as $row)
	{
		$rows[]	= $row->id;
	}
	$rows	= implode(',', $rows);
	
	if($_JB_CONFIGURATION->get('useJomComment')){
		$strSQL	= "DELETE FROM #__jomcomment WHERE `published`='0' AND `contentid` IN({$rows})";
		$db->setQuery($strSQL);
		$db->execute();
	}else{
		$strSQL	= "DELETE FROM #__joomblog_comment WHERE `published`='0' AND `contentid` IN({$rows})";
		$db->setQuery($strSQL);
		$db->execute();
	}

	$link	= JRoute::_('index.php?option=com_joomblog&task=showcomments&Itemid='.jbGetItemId() , false);

	$mainframe->redirect($link, JText::_('COM_JOOMBLOG_UNPBL_COMNTS_REMOVE'));
	
	return ;
}

class Joomblog
{
	var $task;
	var $adminTask ;
	
	function __construct()
	{	
		$this->adminTask = array('adminhome', 'edit', 'delete', 'write', 'showcomments', 'bloggerpref', 'bloggerstats', 'media');
	}
	
	function init()
	{
		global $Itemid;
		$jinput = JFactory::getApplication()->input;
		$task = $jinput->get( 'task' , '' , 'REQUEST' );
		if ($task =='mainpage') $task='';
		$this->task	= $task;
		if($jinput->get( 'view' , '' , 'REQUEST' )=='adminhome'){
			$this->task	= 'adminhome';
		}
		
		$view = $jinput->get( 'view' , '' , 'GET' );		
		if(empty($this->task))
		{
			$this->task = 'browse';
			
			$show 	= false;
			$preview = false;
			$show	= $jinput->get( 'show' , '' , 'GET' );
			$preview = $jinput->get( 'preview' , '' , 'GET' );

			if(!empty($preview))
				$this->task = 'preview';
			
			if(!empty($show))
				$this->task = 'show';

			$author	= $jinput->get( 'user' , '' , 'GET' );
			
			if (isset($view) && $view == 'user')
			{
				$menu = JSite::getMenu();
				$item   = $menu->getActive();
				$params   =& $menu->getParams($item->id);
				$user = JFactory::getUser($params->get('user'));
				
				$author = $user->username;
			}
			
			if (isset($view) && $view == 'category')
			{				
				$this->task = 'tag';
			}

			if (isset($view) && $view == 'dashboard')
			{				
				$my	= JFactory::getUser();
				$mainframe	= JFactory::getApplication();
				if ($my->id>0)
				{
					$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&task=dashboard&Itemid='.jbGetItemId(),false));					
				}
				else
				{
					$mainframe->redirect(JRoute::_('index.php?option=com_joomblog&view=default',false),JText::_('COM_JOOMBLOG_LOGIN_FIRST'));
				}
				
			}
			
			if (isset($view) && $view == 'archives')
			{				
				$this->task = 'archive';
			}
			
			if (isset($view) && $view == 'tags')
			{				
				$this->task = 'viewtags';
			}
			
			if(!empty($author) && $show == '')
				$this->task = 'author';
		}		
	}
	 	
	function index()
	{
		if(in_array($this->task, $this->adminTask))
		{
			jimport( 'joomla.filesystem.file' );
			
			$file	= JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . strtolower( $this->task ) . '.php';
			
			if( !JFile::exists($file ) )
			{
				JError::raiseError( 404 , JText::_('COM_JOOMBLOG_INV_TASK' ) );
			}
			require_once( $file );
			
			$cName	= 'Jbblog' . ucfirst($this->task) . 'Task';
			$obj	= new $cName();
			$obj->execute();
		}else{
			$this->browse();
		}
	}
	
	function view()
	{
		$this->show();
	}
	
	function printblog()
	{
		$this->show();
	}
	
	function userblog()
	{
		$my		= JFactory::getUser();
		
		if ($my->id == "0")
		{
			echo '<div id="fp-content">';
			echo JText::_('COM_JOOMBLOG_LOGIN_TO_VIEW_BLOG');
			echo '</div>';
		}
		else
		{
			echo '<div id="joomBlog-wrap">';
			mb_showViewerToolbar("home");
			$frontview = new MB_Frontview();
			$frontview->attachHeader();
			echo $frontview->browse('userblog');
			echo '</div>';
			echo getPoweredByLink();
		}
	}
	
	function execute()
	{
		global $_JB_CONFIGURATION;

		$mainframe = JFactory::getApplication(); 
		
		if(in_array($this->task, $this->adminTask))
		{
			$jinput = JFactory::getApplication()->input;
			$session	= $jinput->get( 'session' , '' , 'GET' );
			$my			= JFactory::getUser();
			
			if ( ($my->id == "0" || !empty($session) )&& $this->task == 'write')
			{
				$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login',false));
				return;
			}
			
			if ($my->id == "0")
			{
				echo '<div id="fp-content">';
				echo JText::_('COM_JOOMBLOG_DONTPERM');
				echo '</div>';
				return;
			}
		}
		
    jimport( 'joomla.filesystem.file' );
    
    $file	= JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . JString::strtolower( $this->task ) . '.php';
    if( JFile::exists( $file ) )
    {
      require_once( $file );
      $cname = 'Jbblog'.ucfirst($this->task).'Task';
      $obj = new $cname();
      if (method_exists($obj,'execute')) $obj->execute();
    }
    else
    {	
      $func = 'jbf'.ucfirst($this->task);
      if (function_exists($func)) {
        call_user_func($func);
      }else{
        echo JText::_('COM_JOOMBLOG_INV_TASK');
      }
    }	
	}
}

$Joomblog = new Joomblog();
$Joomblog->init();
$itemid = JFactory::getApplication()->input->get('itemid');
if (empty($itemid))
{
	$itemid = jbGetItemId(); 
	JFactory::getApplication()->input->set('itemid',$itemid);
}
$task = $Joomblog->task;

$Joomblog->execute();	
