<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package JoomBlog
 * @author JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

global $_JB_CONFIGURATION;
if ( !defined('JB_COM_PATH') )
	require_once(JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomblog' . DIRECTORY_SEPARATOR . 'defines.joomblog.php');
require_once(JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'base.php');

if ( !$_JB_CONFIGURATION ) {
	require_once(JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomblog' . DIRECTORY_SEPARATOR . 'config.joomblog.php');
	$_JB_CONFIGURATION = new JB_Configuration();
}

function jbSortOrder(&$entry)
{
	$db = JFactory::getDBO();
	$strSQL = "SELECT * FROM #__joomblog_posts WHERE `catid`='{$entry->catid}'";
	$db->setQuery($strSQL);

	$result = $db->loadObjectList();

	if ( !$entry->ordering ) {
		if ( count($result) == 0 ) {
			$entry->ordering = 1;
		} else {
			$entry->ordering = count($result) + 1;
		}

		$strSQL = "UPDATE #__joomblog_posts SET `ordering`='{$entry->ordering}' WHERE `id`='{$entry->id}'";
		$db->setQuery($strSQL);
		$db->execute();
	}
}

function jbPagination($total, $limitstart, $limit)
{
	$pagination = new stdClass();
	$pageNav = new JBPagination($total, $limitstart, $limit);
	$pagination->limitstart = $limitstart;
	$pagination->limit = $limit;
	$pagination->total = $total;
	$pagination->footer = $pageNav->getListFooter();
	$pagination->links = $pageNav->getPagesLinks();

	return $pagination;
}

function jbAllowedGuestView($context)
{
	global $_JB_CONFIGURATION;

	$jb = JFactory::getUser();
	$allowed = true;

	if ( $context == 'intro' ) {
		if ( $_JB_CONFIGURATION->get('viewIntro') == '2' && $jb->id == '0' )
			$allowed = false;
	} else if ( $context == 'entry' ) {
		if ( $_JB_CONFIGURATION->get('viewEntry') == '2' && $jb->id == '0' )
			$allowed = false;
	}
	return $allowed;
}

function jbAddTags($contentId, $tags)
{
	global $_JB_CONFIGURATION;

	include_once(JB_LIBRARY_PATH . '/tags.php');

	$tagObj = new JBTags();
	$db = JFactory::getDBO();

	$strSQL = "DELETE FROM #__joomblog_content_tags WHERE `contentid`='{$contentId}'";
	$db->setQuery($strSQL);
	$db->execute();

	if ( is_array($tags) ) {
		foreach ($tags as $tag) {
			$tagObj->add(trim($tag));

			$strSQL = "INSERT INTO #__joomblog_content_tags "
				. "(`contentid`,`tag`) VALUES ($contentId, " . $tagObj->insertId . ")";
			$db->setQuery($strSQL);
			$db->execute();
		}
	} else {
		$tagObj->add(trim($tags));

		$strSQL = "INSERT INTO #__joomblog_content_tags "
			. "(`contentid`,`tag`) VALUES ($contentId, " . $tagObj->insertId . ")";
		$db->setQuery($strSQL);
		$db->execute();
	}
}


function jbGetTagId($tagName)
{
	$db = JFactory::getDBO();
	$db->setQuery("SELECT `id` FROM #__joomblog_tags WHERE `name`='{$tagName}'");
	return $db->loadResult();
}


function jbGetTagName($tag)
{
	$db = JFactory::getDBO();
	$strSQL = "SELECT name FROM #__joomblog_tags WHERE `name` LIKE '{$tag}' ";
	$db->setQuery($strSQL);

	return $db->loadResult();
}

function jbGetUsedTags($userId)
{
	$db = JFactory::getDBO();

	$strSQL = "SELECT DISTINCT c.name, '' AS slug FROM #__joomblog_posts AS a, "
		. "#__joomblog_content_tags AS b, "
		. "#__joomblog_tags AS c "
		. "WHERE a.id=b.contentid "
		. "AND c.id=b.tag "
		. "AND a.created_by='{$userId}'";

	$db->setQuery($strSQL);
	return $db->loadObjectList();
}

function jbGetBlogUsedTags($blogid)
{
	$db = JFactory::getDBO();

	$strSQL = "SELECT DISTINCT c.name, '' AS slug FROM #__joomblog_posts AS a LEFT JOIN  #__joomblog_content_tags AS b ON a.id=b.contentid LEFT JOIN #__joomblog_tags AS c ON c.id=b.tag LEFT JOIN #__joomblog_blogs AS jb ON a.id=jb.content_id WHERE jb.blog_id=" . $blogid;
	$db->setQuery($strSQL);
	return $db->loadObjectList();
}

function jbGetTags($contentid)
{
	$db = JFactory::getDBO();

	$query = "SELECT b.id,b.name, '' AS slug FROM #__joomblog_content_tags as a,#__joomblog_tags as b WHERE b.id=a.tag AND a.contentid='$contentid' ORDER BY b.name DESC";
	$db->setQuery($query);
	$result = $db->loadObjectList();
	echo $db->getErrorMsg();
	for ($i = 0; $i < count($result); $i++) {
		$tag = $result[$i];

		if ( $tag->slug == '' )
			$tag->slug = $tag->name;
	}

	return $result;
}

function jbGetSid($length = 12)
{
	$token = md5(uniqid('a'));
	$sid = md5(uniqid(rand(), true));

	$return = '';
	for ($i = 0; $i < $length; $i++) {
		$return .= substr($sid, rand(1, ($length - 1)), 1);
	}
	return $return;
}

function jbGetExternalLink($url, $xhtml = false)
{
	$uri = JURI::getInstance();
	$base = $uri->toString(array('scheme', 'host', 'port'));

	$sefUrl = JRoute::_($url);

	if ( strpos($url, 'http') === false ) {
		$subcat = JURI::base(true);
		$sefUrl = substr(JURI::root(), 0, strlen(JURI::root()) - 1) . str_replace($subcat, '', $sefUrl);
	}

	return $sefUrl;
}

function jbNotifyAdmin($contentId, $author = '', $title = '', $text = '', $isNew)
{
	global $_JB_CONFIGURATION, $joomblog_LANG;

	$mainframe = JFactory::getApplication();
	$status = false;
	$db = JFactory::getDBO();
	$query = "SELECT `blog_id` FROM `#__joomblog_blogs` WHERE `content_id`='" . $contentId . "' ";
	$db->setQuery($query);
	$blog_id = $db->loadResult();

	if ( $_JB_CONFIGURATION->get('allowNotification') ) {
		$emails = $_JB_CONFIGURATION->get('adminEmail');

		if ( !class_exists('JBMailer') ) include_once(JB_LIBRARY_PATH . '/mail.php');

		$sid = jbGetSid();
		$date = strftime("%Y-%m-%d %H:%M:%S", time() + ($mainframe->getCfg('offset') * 60 * 60));

		/*OLD approve;*/
		$strSQL = "DELETE FROM #__joomblog_admin WHERE `sid`='{$sid}'";
		$db->setQuery($strSQL);
		$db->execute();

		$strSQL = "INSERT INTO #__joomblog_admin SET `sid`='{$sid}', `cid`='{$contentId}', `date`='{$date}', `type` = '0' ";
		$db->setQuery($strSQL);
		$db->execute();

		$publish = jbGetExternalLink('index.php?option=com_joomblog&task=admin&operation=publish&sid=' . $sid, true);
		$unpublish = jbGetExternalLink('index.php?option=com_joomblog&task=admin&operation=unpublish&sid=' . $sid, true);
		$delete = jbGetExternalLink('index.php?option=com_joomblog&task=admin&operation=remove&sid=' . $sid, true);
		$blog_link = jbGetExternalLink('index.php?option=com_joomblog&blogid=' . $blog_id . '&view=blogger', true);

		$template = new JoomblogTemplate();

		$text = strip_tags($text);

		if ( $isNew ) {
			$content = $template->fetch(JB_TEMPLATE_PATH . "/default/new.notify.tmpl.html");
			$content = str_replace('%PUBLISH%', $publish, $content);
			$content = str_replace('%UNPUBLISH%', $unpublish, $content);
			$content = str_replace('%DELETE%', $delete, $content);
			$content = str_replace('%AUTHOR%', $author, $content);
			$content = str_replace('%TITLE%', $title, $content);
			$content = str_replace('%BLOG%', $blog_link, $content);
			$content = str_replace('%ENTRY%', $text, $content);
			$content = str_replace('%DATE%', $date, $content);

			$title = JText::_('COM_JOOMBLOG_NEW_POST_OK');
		} else {
			$content = $template->fetch(JB_TEMPLATE_PATH . "/default/update.notify.tmpl.html");
			$content = str_replace('%UNPUBLISH%', $unpublish, $content);
			$content = str_replace('%DELETE%', $delete, $content);
			$content = str_replace('%AUTHOR%', $author, $content);
			$content = str_replace('%TITLE%', $title, $content);
			$content = str_replace('%BLOG%', $blog_link, $content);
			$content = str_replace('%ENTRY%', $text, $content);
			$content = str_replace('%DATE%', $date, $content);

			$title = JText::_('COM_JOOMBLOG_UPD_POST_OK');
		}

		if ( $emails ) {
			$emails = explode(',', $emails);
			if ( sizeof($emails) ) {
				foreach ($emails as $mailaddress) {
					$mail = new JBMailer();
					if ( $mail->send($mainframe->getCfg('mailfrom'), trim($mailaddress), $title, $content) ) $status = true;
					unset($mail);
				}
			}

		}
	}
	return $status;
}

function jbNotifyCommentAdmin($commentId, $author = '', $title = '', $text = '')
{
	global $_JB_CONFIGURATION, $joomblog_LANG;

	$mainframe = JFactory::getApplication();
	$status = false;
	$db = JFactory::getDBO();

	if ( $_JB_CONFIGURATION->get('notifyCommentAdmin') ) {
		$emails = $_JB_CONFIGURATION->get('adminEmail');

		$recipients = split(',', $_JB_CONFIGURATION->get('adminEmail') ? $_JB_CONFIGURATION->get('adminEmail') : $mainframe->getCfg('mailfrom'));

		if ( !class_exists('JBMailer') )
			include_once(JB_LIBRARY_PATH . '/mail.php');

		$sid = jbGetSid();
		$date = strftime("%Y-%m-%d %H:%M:%S", time() + ($mainframe->getCfg('offset') * 60 * 60));

		$strSQL = "DELETE FROM #__joomblog_admin WHERE `sid`='{$sid}'";
		$db->setQuery($strSQL);
		$db->execute();

		$strSQL = "INSERT INTO #__joomblog_admin SET `sid`='{$sid}', `cid`='{$commentId}', `date`='{$date}', `type` = '1' ";
		$db->setQuery($strSQL);
		$db->execute();

		$strSQL = "SELECT * FROM #__joomblog_comment WHERE `id`='{$commentId}' ";
		$db->setQuery($strSQL);
		$comment = $db->loadObject();

		$publish = jbGetExternalLink('index.php?option=com_joomblog&task=admin&operation=publish&sid=' . $sid, false);

		$unpublish = jbGetExternalLink('index.php?option=com_joomblog&task=admin&operation=unpublish&sid=' . $sid, false);

		$delete = jbGetExternalLink('index.php?option=com_joomblog&task=admin&operation=remove&sid=' . $sid, false);

		$template = new JoomblogTemplate();

		$text = strip_tags($text);

		$content = $template->fetch(JB_TEMPLATE_PATH . "/default/comment.notifyadmin.tmpl.html");

		$content = str_replace('%DATE%', $comment->created, $content);
		$content = str_replace('%PUBLISH%', $publish, $content);
		$content = str_replace('%UNPUBLISH%', $unpublish, $content);
		$content = str_replace('%DELETE%', $delete, $content);
		$content = str_replace('%AUTHOR%', $author, $content);
		$content = str_replace('%TITLE%', $title, $content);
		$content = str_replace('%COMMENT%', $text, $content);

		$title = JText::_('COM_JOOMBLOG_ADD_NEW_COMMENT');

		$mail = new JBMailer();

		$status = $mail->send($mainframe->getCfg('mailfrom'), $recipients, $title, $content);
	}
	return $status;
}

function jbNotifyCommentAuthor($commentId, $author = '', $title = '', $text = '')
{
	global $_JB_CONFIGURATION, $joomblog_LANG;

	$mainframe = JFactory::getApplication();
	$status = false;
	$db = JFactory::getDBO();

	if ( $_JB_CONFIGURATION->get('notifyCommentAuthor') ) {
		if ( !class_exists('JBMailer') )
			include_once(JB_LIBRARY_PATH . '/mail.php');

		$sid = jbGetSid();
		$date = strftime("%Y-%m-%d %H:%M:%S", time() + ($mainframe->getCfg('offset') * 60 * 60));

		$strSQL = "DELETE FROM #__joomblog_admin WHERE `sid`='{$sid}'";
		$db->setQuery($strSQL);
		$db->execute();

		$strSQL = "INSERT INTO #__joomblog_admin SET `sid`='{$sid}', `cid`='{$commentId}', `date`='{$date}', `type` = '1' ";
		$db->setQuery($strSQL);
		$db->execute();

		$strSQL = "SELECT * FROM #__joomblog_comment WHERE `id`='{$commentId}' ";
		$db->setQuery($strSQL);
		$comment = $db->loadObject();

		$strSQL = "SELECT * FROM #__joomblog_posts WHERE `id`='{$comment->contentid}' ";
		$db->setQuery($strSQL);
		$blog = $db->loadObject();

		$recipients = array(jbGetAuthorEmail($blog->created_by));

		$publish = jbGetExternalLink('index.php?option=com_joomblog&task=admin&operation=publish&sid=' . $sid, false);

		$unpublish = jbGetExternalLink('index.php?option=com_joomblog&task=admin&operation=unpublish&sid=' . $sid, false);

		$delete = jbGetExternalLink('index.php?option=com_joomblog&task=admin&operation=remove&sid=' . $sid, false);

		$template = new JoomblogTemplate();

		$text = strip_tags($text);

		$content = $template->fetch(JB_TEMPLATE_PATH . "/default/comment.notifyauthor.tmpl.html");

		$content = str_replace('%DATE%', $comment->created, $content);
		$content = str_replace('%PUBLISH%', $publish, $content);
		$content = str_replace('%UNPUBLISH%', $unpublish, $content);
		$content = str_replace('%DELETE%', $delete, $content);
		$content = str_replace('%AUTHOR%', $author, $content);
		$content = str_replace('%TITLE%', $title, $content);
		$content = str_replace('%COMMENT%', $text, $content);

		$title = JText::_('COM_JOOMBLOG_ADD_NEW_COMMENT');

		$mail = new JBMailer();

		$status = $mail->send($mainframe->getCfg('mailfrom'), $recipients, $title, $content);
	}
	return $status;
}

function jbClearCache()
{
	$cache = JFactory::getCache();

	$cache->clean();
}

function jbGetUser($session)
{
	if ( $session ) {
		$vars = explode(':', $session);

		$strSQL = "SELECT userid, username, usertype FROM #__session WHERE `userid`='" . $vars[0] . "' "
			. "AND `session_id`='" . $vars[1] . "'";

		$db = JFactory::getDBO();
		$db->setQuery($strSQL);
		$data = $db->loadObject();

		if ( $data ) {
			$jb = JFactory::getUser();
			$jb->id = $data->userid;
			$jb->username = $data->username;
			$jb->usertype = $data->usertype;

			return $jb;
		}
	}
	return false;
}


function socialDefaultAdd($button_style, $sefUrl, $host)
{
	$html = '';

	switch ($button_style) {
		case 'style1':
			$html .= '<a class="addthis_button_preferred_1" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_preferred_2" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_preferred_3" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_preferred_4" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_compact" addthis:url="' . $sefUrl . '"></a><a class="addthis_counter addthis_bubble_style" addthis:url="' . $sefUrl . '"></a>';
			break;
		case 'style2':
			$html .= '<a class="addthis_button_preferred_1" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_preferred_2" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_preferred_3" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_preferred_4" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_compact" addthis:url="' . $sefUrl . '"></a><a class="addthis_counter addthis_bubble_style" addthis:url="' . $sefUrl . '"></a>';
			break;
		case 'style3':
			$html .= '<a class="addthis_button_facebook_like" fb:like:layout="button_count" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_tweet" addthis:url="' . $sefUrl . '"></a><a class="addthis_counter addthis_pill_style" addthis:url="' . $sefUrl . '"></a>';
			break;
		case 'style4':
			$html .= '<a class="addthis_button_google_plusone" addthis:url="' . $sefUrl . '"></a><a class="addthis_counter addthis_pill_style" addthis:url="' . $sefUrl . '"></a>';
			break;
		case 'style5':
			$html .= '<a href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=' . $host . '" class="addthis_button_compact" addthis:url="' . $sefUrl . '">Share</a><span class="addthis_separator">|</span>
<a class="addthis_button_preferred_1" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_preferred_2" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_preferred_3" addthis:url="' . $sefUrl . '"></a><a class="addthis_button_preferred_4" addthis:url="' . $sefUrl . '"></a>';
			break;
		case 'style6':
			$html .= '<a href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=' . $host . '" class="addthis_button_compact" addthis:url="' . $sefUrl . '">Share</a>';
			break;
		case 'style7':
			$html .= '<a class="addthis_counter" addthis:url="' . $sefUrl . '"></a>';
			break;
		case 'style8':
			$html .= '<a class="addthis_counter addthis_pill_style" addthis:url="' . $sefUrl . '"></a>';
			break;
		case 'style9':
			$html .= '<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=' . $host . '" addthis:url="' . $sefUrl . '"><img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a>';
			break;
		case 'style10':
			$html .= '<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=' . $host . '" addthis:url="' . $sefUrl . '"><img src="http://s7.addthis.com/static/btn/sm-share-en.gif" width="83" height="16" alt="Bookmark and Share" style="border:0"/></a>';
			break;
	}
	return $html;
}

function socialCustomAdd($service, $styles, $sefUrl, $host)
{
	$html = '';
	switch (trim($service)) {
		case 'fb':
			if ( $styles['fb'] == 'fb-style1' ) $html .= '<a class="addthis_button_facebook_like" fb:like:layout="button_count" fb:like:action="recommend" addthis:url="' . $sefUrl . '"></a>';
			if ( $styles['fb'] == 'fb-style2' ) $html .= '<a class="addthis_button_facebook_like" fb:like:layout="box_count" addthis:url="' . $sefUrl . '"></a>';
			if ( $styles['fb'] == 'fb-style3' ) $html .= '<a class="addthis_button_facebook_like" fb:like:layout="standard" addthis:url="' . $sefUrl . '"></a>';
			break;
		case 'tw':
			if ( $styles['tw'] == 'tw-style1' ) $html .= '<a class="addthis_button_tweet" tw:count="vertical" addthis:url="' . $sefUrl . '"></a>';
			if ( $styles['tw'] == 'tw-style2' ) $html .= '<a class="addthis_button_tweet" tw:via="addthis" addthis:url="' . $sefUrl . '"></a>';
			if ( $styles['tw'] == 'twf-style1' ) $html .= '<a class="addthis_button_twitter_follow_native" addthis:url="' . $sefUrl . '"></a>';
			break;
			break;
		case 'gp':
			if ( $styles['gp'] == 'gp-style1' ) $html .= '<a class="addthis_button_google_plusone" g:plusone:size="medium" addthis:url="' . $sefUrl . '"></a></a>';
			if ( $styles['gp'] == 'gp-style2' ) $html .= '<a class="addthis_button_google_plusone" g:plusone:size="tall" addthis:url="' . $sefUrl . '"></a></a>';
			if ( $styles['gp'] == 'gp-style3' ) $html .= '<a class="addthis_button_google_plusone" g:plusone:count="false" addthis:url="' . $sefUrl . '"></a></a>';
			break;
		case 'sh':
			if ( $styles['sh'] == 'sh-style1' ) $html .= '<a class="addthis_counter" addthis:url="' . $sefUrl . '"></a>';
			if ( $styles['sh'] == 'sh-style2' ) $html .= '<a class="addthis_counter addthis_pill_style" addthis:url="' . $sefUrl . '"></a>';
			if ( $styles['sh'] == 'sh-style3' ) $html .= '<a href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=' . $host . '" class="addthis_button_compact" addthis:url="' . $sefUrl . '">Share</a>';
			if ( $styles['sh'] == 'sh-style4' ) $html .= '<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=' . $host . '" addthis:url="' . $sefUrl . '"><img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a>';
			break;
	}

	return $html;
}

/*PRIVACY*/

function jbCanBlogCreate()
{
	$user = JFactory::getUser();
	if ( $user->id ) {
		if ( $user->authorise('blog.create', 'com_joomblog') ) return true;
	}
	return false;
}

function jbGetCurrentPrivacy($id = 0)
{
	if ( $id ) {
		$db = JFactory::getDBO();
		$query = "SELECT * FROM `#__joomblog_privacy` WHERE `postid`='" . $id . "' AND `isblog`=0 ";
		$db->setQuery($query);
		$rules = $db->loadObject();
		if ( $rules ) return $rules;
	}
	return null;
}

function jbGetPrivacyList($selected = 0, $name = 'viewpostrules', $isblog = 0, $jbtype = 'view')
{
	global $_JB_CONFIGURATION;
	$attribs = null;
	$state[] = JHTML::_('select.option', '0', JText::_('COM_JOOMBLOG_PRIVACY_ALL'));
	$state[] = JHTML::_('select.option', '1', JText::_('COM_JOOMBLOG_PRIVACY_MEMBERS'));
	if ( $_JB_CONFIGURATION->get('integrJoomSoc') ) {
		$state[] = JHTML::_('select.option', '2', JText::_('COM_JOOMBLOG_PRIVACY_FRIENDS'));
		if ( $isblog ) {
			$state[] = JHTML::_('select.option', '4', JText::_('COM_JOOMBLOG_PRIVACY_GMEMBERS'));
			$attribs .= ' onchange="checkselperm_' . $jbtype . '(this);"';
		}
	}
	$state[] = JHTML::_('select.option', '3', JText::_('COM_JOOMBLOG_PRIVACY_ONLYME'));
	$privacy = JHTML::_('select.genericlist', $state, $name, $attribs . ' class="jb-selectlist jb-small-select"', $key = 'value', $text = 'text', $selected, $idtag = false, $translate = false);

	if ( $_JB_CONFIGURATION->get('integrJoomSoc') ) {
		if ( $isblog ) {

			$doc = JFactory::getDocument();
			ob_start();
			?>
			function checkselperm_<?php echo $jbtype; ?>(ob)
			{
			if (ob.value==4)
			document.getElementById('jsgroups_<?php echo $jbtype; ?>').style.display='block';
			else document.getElementById('jsgroups_<?php echo $jbtype; ?>').style.display='none';
			}
			<?php
			$js = ob_get_contents();
			ob_end_clean();
			$doc->addScriptDeclaration($js);
			$groups = jbGetJSGroups($jbtype);
			$st = "display:none;";
			if ( $groups ) {
				$privacy .= $groups;
			} else $privacy .= '<div id="jsgroups_' . $jbtype . '" style="' . $st . '">' . JText::_('COM_JOOMBLOG_PRIVACY_NOMEMBER') . '</div>';
		}
	}

	return $privacy;
}

function jbGetJSGroups($jbtype = 'view')
{
	$uid = JFactory::getUser()->id;
	if ( !$uid ) return null;
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	$query->select('g.name as `text`, g.id as `value`');
	$query->from('#__community_groups AS `g`');
	$query->join('INNER', '#__community_groups_members AS `m` ON m.groupid=g.id');
	$query->where('m.memberid=' . (int)$uid);
	$query->order('g.name', 'ASC');
	$db->setQuery($query);
	$groups = $db->loadObjectList();
	$sel = 0;

	if ( sizeof($groups) )
		return JHtml::_('select.genericlist', $groups, 'jsgroups_' . $jbtype, 'style="display:none;float:left;" id="jsgroups_' . $jbtype . '"', 'value', 'text', $sel);
	else return null;
}

/**/

function jbGetUserCanPost($id = 0)
{
	global $_JB_CONFIGURATION;

	$posterIds = explode(',', $_JB_CONFIGURATION->get('allowedPosters'));
	$disallowedUsers = explode(',', $_JB_CONFIGURATION->get('disallowedPosters'));
	$return = false;

	if ( $id == 0 ) {
		$jb = JFactory::getUser();
	} else {
		$jb = JFactory::getUser($id);
	}

	$userId = $jb->id;
	$userType = $jb->groups;
	$userName = $jb->username;

	if ( in_array($userId, $posterIds) || in_array(jbUserGetName($userId), $posterIds) ) {
		return true;
	}

	if ( in_array($userId, $disallowedUsers) || in_array($userName, $disallowedUsers) )
		return false;

	if ( !$return ) {
		$return = $jb->authorise('core.create', 'com_joomblog');
	}

	return $return;
}

function jbGetUserCanPostToBlog($blogId)
{
	global $_JB_CONFIGURATION;

	if ( !$blogId ) {
		return false;
	} else {
		$db = JFactory::getDbo();
		$blogId = (int)$blogId;
		$query = $db->getQuery(true);
		$query->select('lb.*,u.username, u.name, ub.avatar');
		$query->from('#__joomblog_list_blogs lb');
		$query->join('LEFT', '#__users u ON u.id=lb.user_id');
		$query->join('LEFT', '#__joomblog_user ub ON ub.user_id=lb.user_id');
		$query->where("lb.id=" . $db->quote($blogId));

		//acccess check
		if ( !in_array('8', JFactory::getUser()->getAuthorisedGroups()) ) {
			$user = JFactory::getUser();
			$user_id = (int)$user->id;
			$groups = implode(',', $user->getAuthorisedViewLevels());
			if ( !JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false) ) {
				$query->where('(lb.waccess IN (' . $groups . ') OR lb.user_id=' . $user_id . ')');
			} else {
				$userJSGroups = JbblogBaseController::getJSGroups($user->id);
				$userJSFriends = JbblogBaseController::getJSFriends($user->id);
				if ( count($userJSGroups) > 0 ) {
					$tmpQ2 = ' OR (lb.waccess=-4 AND lb.waccess_gr IN (' . implode(',', $userJSGroups) . ')) ';
				} else {
					$tmpQ1 = ' ';
					$tmpQ2 = '';
				}
				if ( count($userJSFriends) > 0 ) {
					$tmpQ22 = ' OR (lb.waccess=-2 AND lb.user_id IN (' . implode(',', $userJSFriends) . ')) ';
				} else {
					$tmpQ11 = ' ';
					$tmpQ22 = '';
				}
				$query->where('(lb.access IN (' . $groups . ')
				OR lb.user_id=' . $user_id . ' ' . $tmpQ2 . ' ' . $tmpQ22 . ' )');
			}
		}
		$db->setQuery($query);
		$blog = $db->loadObject();
		if ( !$blog ) return false;
	}

	return true;
}


function jbGetUserCanPublish()
{
	global $_JB_CONFIGURATION;

	$publishRights = false;

	$user = JFactory::getUser();
	$userId = $user->id;
	$userType = $user->groups;
	$posterIds = explode(',', $_JB_CONFIGURATION->get('allowedPublishers'));
	if ( in_array($userId, $posterIds) AND $userId !== 0 ) {
		$publishRights = true;
	}
	if ( !$publishRights ) {
		$publishRights = $user->authorise('core.publish', 'com_joomblog');
	}
	return $publishRights;
}

function jbGetJomComment()
{
	global $_JB_CONFIGURATION;

	jimport('joomla.filesystem.file');

	$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jomcomment' . DIRECTORY_SEPARATOR . 'config.jomcomment.php';

	if ( $_JB_CONFIGURATION->get('useComment') && JFile::exists($file) )
		return true;

	return false;
}

function jbGetUserTags($uid)
{
	global $_JB_CONFIGURATION;
	$db = JFactory::getDBO();

	$sections = implode(',', jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));

	$db->setQuery("SELECT distinct(b.name) from #__joomblog_posts as c,#__joomblog_content_tags as a,#__joomblog_tags as b WHERE c.id=a.contentid and b.id=a.tag and c.created_by=$uid and c.state='1' and c.catid in ($sections) and c.publish_up < now() ORDER BY b.name DESC");
	return $db->loadObjectList();
}

function jb_strict_nl2br($text, $replac = " <br />")
{
	return preg_replace("/\r\n|\n|\r/", $replac, $text);
}

function jbCountUserEntry($uid, $exclude_drafts = "")
{
	global $_JB_CONFIGURATION;

	$sections = implode(',', jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));

	$db = JFactory::getDBO();

	$extra = "";

	$date = JFactory::getDate();

	if ( $exclude_drafts == "1" ) {
		$extra = "and state='1' and publish_up < '" . $date->toSql() . "'";
	}

	$db->setQuery("SELECT COUNT(*) FROM #__joomblog_posts WHERE created_by='$uid' and catid IN ($sections) $extra");
	$result = $db->loadResult();

	if ( $result == "" )
		return 0;

	return $result;
}

function jbCountBlogEntry($blogid, $exclude_drafts = "")
{
	global $_JB_CONFIGURATION;
	$sections = implode(',', jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));
	$db = JFactory::getDBO();
	$extra = "";
	$date = JFactory::getDate();
	if ( $exclude_drafts == "1" ) {
		$extra = "and a.state='1' and a.publish_up < '" . $date->toSql() . "'";
	}

	$db->setQuery("SELECT COUNT(*) FROM #__joomblog_posts AS a LEFT JOIN #__joomblog_blogs AS jb ON a.id=jb.content_id WHERE jb.blog_id='$blogid' and a.catid IN ($sections) $extra");
	$result = $db->loadResult();

	if ( $result == "" )
		return 0;

	return $result;
}

function jbCountUserComment($uid)
{
	global $_JB_CONFIGURATION;

	$db = JFactory::getDBO();

	$strSQL = "SELECT COUNT(*) FROM #__jomcomment AS a "
		. "INNER JOIN #__joomblog_posts AS b "
		. "WHERE b.id=a.contentid "
		. "AND a.option='com_joomblog' "
		. "AND b.created_by='{$uid}'";
	$db->setQuery($strSQL);

	$result = $db->loadResult();

	if ( $result == "" )
		return 0;

	return $result;
}

function jbCountBlogComment($blogid)
{
	global $_JB_CONFIGURATION;

	$db = JFactory::getDBO();
	$strSQL = "SELECT COUNT(*) FROM #__jomcomment AS a "
		. "INNER JOIN #__joomblog_posts AS b ON b.id=a.contentid "
		. "LEFT JOIN #__joomblog_blogs AS jb ON b.id=jb.content_id "
		. "WHERE a.option='com_joomblog' AND jb.blog_id='" . $blogid . "'";
	$db->setQuery($strSQL);

	$result = $db->loadResult();

	if ( $result == "" )
		return 0;

	return $result;
}

function jbCountUserHits($uid)
{
	global $_JB_CONFIGURATION;

	$db = JFactory::getDBO();
	$sections = implode(',', jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));

	$db->setQuery("SELECT SUM(hits) FROM #__joomblog_posts WHERE created_by='$uid' and catid in ($sections) and state='1' and publish_up < NOW()");
	$result = $db->loadResult();

	if ( $result == "" )
		return 0;

	return $result;
}

function jbCountBlogHits($blogid)
{
	global $_JB_CONFIGURATION;

	$db = JFactory::getDBO();
	$sections = implode(',', jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));

	$db->setQuery("SELECT SUM(hits) FROM #__joomblog_posts AS a LEFT JOIN #__joomblog_blogs AS jb ON a.id=jb.content_id WHERE jb.blog_id='$blogid' and a.catid in ($sections) and a.state='1' and a.publish_up < NOW()");
	$result = $db->loadResult();

	if ( $result == "" )
		return 0;

	return $result;
}

function jbGetUserDraft($uid)
{
	global $joomblog_LANG, $Itemid, $_JB_CONFIGURATION;

	$db = JFactory::getDBO();
	$sections = implode(',', jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));

	$db->setQuery("SELECT title,created,id FROM #__joomblog_posts WHERE created_by='$uid' AND state=0 and catid in ($sections) ORDER BY created");
	$result = $db->loadObjectList();
	$drafts = "<em>";

	if ( $result ) {
		foreach ($result as $row) {
			$draftlink = JRoute::_("index.php?option=com_joomblog&no_html=1&task=edit&id=$row->id&Itemid=$Itemid&admin=1&tmpl=component");

			if ( $drafts != "<em>" )
				$drafts .= ", ";
			$drafts .= "<a class=\"draftLink\" href=\"$draftlink\">" . $row->title . "</a>";
		}
	} else {
		$drafts = $joomblog_LANG['_MB_NO_DRAFTS'];
	}

	$drafts .= "</em>";

	return $drafts;
}

function jbCountJomcomment($article_Id, $com = "com_joomblog")
{
	global $_JB_CONFIGURATION;

	$db = JFactory::getDBO();
	$user = JFactory::getUser();

	$query = "SELECT * FROM #__joomblog_posts WHERE id = " . $article_Id . "  ";
	$db->setQuery($query);
	$content = $db->loadObject();
	$query = "SELECT `id` FROM #__usergroups WHERE title='Manager' OR title='Administrator' OR title='Super Users'";
	$db->setQuery($query);
	$user_groups = $db->loadColumn();
	$intersect = array_intersect($user->groups, $user_groups);

	if ( $_JB_CONFIGURATION->get('useComment') && $_JB_CONFIGURATION->get('useJomComment') ) {
		if ( !empty($intersect) || $content->created_by == $user->get('id') ) {
			$published = '  ';
		} else {
			$published = ' AND published = \'1\' ';
		}

		$db->setQuery("SELECT COUNT(*) FROM #__jomcomment WHERE contentid='$article_Id' AND (`option`='$com' OR) $published ");
		$result = $db->loadResult();
	} elseif ( $_JB_CONFIGURATION->get('useComment') ) {
		if ( !empty($intersect) || $content->created_by == $user->get('id') ) {
			$published = '  ';
		} else {
			$published = ' published = 1 AND  ';
		}
		$query = "SELECT COUNT(*) FROM #__joomblog_comment WHERE " . $published . " contentid='$article_Id' ";
		$db->setQuery($query);
		$result = $db->loadResult();
	}

	return $result;
}

function jbCategoriesURLGet($contentid, $linkCats = true, $task = "")
{
	global $Itemid, $joomblog_LANG;

	$result = jbGetTags($contentid);
	$catCount = count($result);
	$link = "";
	$task = empty($task) ? '&task=tag' : "&task=$task";

	if ( $result ) {
		$count = 0;

		foreach ($result as $row) {
			if ( $link != "" )
				$link .= "&nbsp;&nbsp;";

			if ( $linkCats ) {
				$tmpl = JFactory::getApplication()->input->get('tmpl');
				if ( $tmpl == 'component' ) $tmpl = '&tmpl=component'; else $tmpl = '';
				$url = JRoute::_('index.php?option=com_joomblog' . $task . '&tag=' . urlencode($row->slug) . '&Itemid=' . $Itemid . $tmpl);
				$link .= "<a class=\"label label-default\" href=\"$url\">$row->name</a>";
			} else {
				$link .= $row->name;
			}
			$count++;
		}
	} else {
		$link .= "<em>" . JText::_('COM_JOOMBLOG_BLOG_UNTAGGED') . "</em>&nbsp;";
	}

	return $link;
}

function jbCommentsURLGet($contentid, $addCommentCount = true, $task = "")
{
	global $Itemid, $joomblog_LANG;

	$link = "";

	if ( $task != "" )
		$task = "&task=$task";

	if ( $addCommentCount ) {
		$numcomment = intval(jbCountJomcomment($contentid));
	}

	return JText::sprintf('COM_JOOMBLOG_BLOG_COMMENTS', $numcomment); //$link;
}

function jbGetAuthorName($uid, $useFullName = "")
{
	$db = JFactory::getDBO();

	$select = "username";

	if ( $useFullName == "1" )
		$select = "name";

	$db->setQuery("SELECT $select FROM #__users WHERE id='$uid'");
	$result = $db->loadResult();

	return $result;
}

function jbGetAuthorEmail($uid)
{
	$db = JFactory::getDBO();

	$db->setQuery("SELECT `email` FROM #__users WHERE id='$uid'");
	$result = $db->loadResult();

	return $result;
}

function jbGetAuthorId($name, $useFullName = "")
{
	$db = JFactory::getDBO();

	$name = urldecode($name);

	if ( !$name )
		return '0';

	if ( $useFullName == "1" ) {
		$db->setQuery("SELECT id FROM #__users WHERE name RLIKE '[[:<:]]$name" . "[[:>:]]' or username RLIKE '[[:<:]]$name" . "[[:>:]]' ");
		$result = $db->loadResult();
		return ($result ? $result : "0");
	}

	$name = $db->escape($name);

	$db->setQuery("SELECT id FROM #__users WHERE username='$name'");

	$result = $db->loadResult();

	if ( !$result ) {
		$db->setQuery("SELECT id FROM #__users WHERE username RLIKE '[[:<:]]$name" . "[[:>:]]'");
		$result = $db->loadResult();
	}

	if ( !$result ) {
		$db->setQuery("SELECT id FROM #__users WHERE name RLIKE '[[:<:]]$name" . "[[:>:]]'");
		$result = $db->loadResult();
	}

	return ($result ? $result : "0");
}

function jbUserGetName($uid, $useFullName = "")
{
	$db = JFactory::getDBO();

	$uid = intval($uid);
	$select = "username";

	if ( $useFullName == "1" )
		$select = "name";

	$db->setQuery("SELECT $select FROM #__users WHERE id=$uid");
	$username = $db->loadResult();

	return $username;
}

function jbGetPermalinkUrl($uid, $task = "", $blogger = '')
{
	$db = JFactory::getDBO();
	$Itemid = jbGetItemId();

	if ( $task != "" ) {
		$task = "&task=$task";
	}

	if ( $blogger != "" ) {
		$blogger = "&user=$blogger";
	}

	$url = "index.php?option=com_joomblog&show={$uid}{$task}{$blogger}&Itemid={$Itemid}";
	$sefUrl = JRoute::_($url);

	if ( strpos($url, 'http') === false ) {
		$subcat = JURI::base(true);
		$sefUrl = substr(JURI::root(), 0, strlen(JURI::root()) - 1) . str_replace($subcat, '', $sefUrl);
	}

	return $sefUrl;
}

function jbGetPermalink($uid, $task = "")
{
	return $uid;
}

function jbGetTagClouds($query, $clouds = 8)
{
	$db = JFactory::getDBO();
	$db->setQuery($query);
	$rows = $db->loadObjectList();

	if ( !$rows )
		return "";

	$vals = array();

	foreach ($rows as $row) {
		$vals["{$row->frequency}"] = $row->frequency;
	}
	$maxFreq = max($vals);
	$minFreq = min($vals);

	$freqSize = $maxFreq - $minFreq;
	$freqSpacing = $freqSize / $clouds;

	if ( $freqSpacing < 1 ) {
		$freqSpacing = 1;
	}

	foreach ($rows as $row) {
		$tagClass = round($row->frequency / $freqSpacing);
		$result[] = array(
			'name' => $row->name,
			'cloud' => $tagClass,
			'slug' => $row->slug
		);
	}
	usort($result, 'jbSortTags');
	return $result;
}

function jbSortTags($a, $b)
{
	return (strtoupper($a['name']) < strtoupper($b['name'])) ? -1 : 1;
}

function jbWritePagesLinks($link, $total, $limitstart, $limit)
{
	$total = (int)$total;
	$limitstart = (int)max($limitstart, 0);
	$limit = (int)max($limit, 0);
	$txt = '';
	$displayed_pages = 10;
	$total_pages = $limit ? ceil($total / $limit) : 0;
	$this_page = $limit ? ceil(($limitstart + 1) / $limit) : 1;
	$start_loop = (floor(($this_page - 1) / $displayed_pages)) * $displayed_pages + 1;

	if ( $start_loop + $displayed_pages - 1 < $total_pages ) {
		$stop_loop = $start_loop + $displayed_pages - 1;
	} else {
		$stop_loop = $total_pages;
	}

	$link .= '&amp;limit=' . $limit;

	if ( !defined('_PN_LT') || !defined('_PN_RT') ) {
		DEFINE('_PN_LT', '&lt;');
		DEFINE('_PN_RT', '&gt;');
	}

	if ( !defined('_PN_START') )
		define('_PN_START', 'Start');

	if ( !defined('_PN_PREVIOUS') )
		define('_PN_PREVIOUS', 'Previous');

	if ( !defined('_PN_END') )
		define('_PN_END', 'End');

	if ( !defined('_PN_NEXT') )
		define('_PN_NEXT', 'Next');

	$pnSpace = '';

	if ( _PN_LT || _PN_RT )
		$pnSpace = "&nbsp;";

	if ( $this_page > 1 ) {
		$page = ($this_page - 2) * $limit;
		$txt .= '<a href="' . JRoute::_("$link&limitstart=0") . '" class="pagenav" title="' . _PN_START . '"><img border=0 src="components/com_joomblog/images/Backward_16x16.png" alt=""/>' . $pnSpace . _PN_START . '</a> ';
		$txt .= '<a href="' . JRoute::_("$link&limitstart=$page") . '" class="pagenav" title="' . _PN_PREVIOUS . '"><img border=0 src="components/com_joomblog/images/Play2_16x16.png" alt=""/>' . $pnSpace . _PN_PREVIOUS . '</a> ';
	} else {
		$txt .= '';
		$txt .= '';
	}

	for ($i = $start_loop; $i <= $stop_loop; $i++) {
		$page = ($i - 1) * $limit;

		if ( $i == $this_page ) {
			$txt .= '<span class="pagenav">' . $i . '</span> ';
		} else {
			$txt .= '<a href="' . JRoute::_($link . '&amp;limitstart=' . $page) . '" class="pagenav"><strong>' . $i . '</strong></a> ';
		}
	}

	if ( $this_page < $total_pages ) {
		$page = $this_page * $limit;
		$end_page = ($total_pages - 1) * $limit;
		$txt .= '<a href="' . JRoute::_($link . '&amp;limitstart=' . $page) . ' " class="pagenav" title="' . _PN_NEXT . '">' . _PN_NEXT . $pnSpace . '<img border=0 src="components/com_joomblog/images/Play_16x16.png" alt=""/></a> ';
		$txt .= '<a href="' . JRoute::_($link . '&amp;limitstart=' . $end_page) . ' " class="pagenav" title="' . _PN_END . '">' . _PN_END . $pnSpace . '<img border=0 src="components/com_joomblog/images/Forward_16x16.png" alt=""/></a>';
	} else {
		$txt .= '';
		$txt .= '';
	}

	return $txt;
}

function jbGetAuthorDescription($userid)
{
	$db = JFactory::getDBO();

	$db->setQuery("SELECT description FROM #__joomblog_user WHERE user_id='$userid'");
	$desc = $db->loadResult();

	if ( !$desc ) {
		$desc = "<p>No desc available</p>";
	}

	return $desc;
}

function jbGetAuthorTitle($userid)
{
	$db = JFactory::getDBO();

	$db->setQuery("SELECT `title` FROM #__joomblog_user WHERE user_id='$userid'");

	$title = $db->loadResult();

	return $title;
}


function jbCloseTags($string)
{
	$donotclose = array('br', 'img', 'input');
	$tagstoclose = '';
	$tags = array();
	preg_match_all("/<(([A-Z]|[a-z]).*)(( )|(>))/isU", $string, $result);
	$openedtags = $result[1];
	$openedtags = array_reverse($openedtags);
	preg_match_all("/<\/(([A-Z]|[a-z]).*)(( )|(>))/isU", $string, $result2);
	$closedtags = $result2[1];
	for ($i = 0; $i < count($openedtags); $i++) {
		if ( in_array($openedtags[$i], $closedtags) ) {
			unset($closedtags[array_search($openedtags[$i], $closedtags)]);
		} else array_push($tags, $openedtags[$i]);
	}
	for ($x = 0; $x < count($tags); $x++) {
		$add = strtolower(trim($tags[$x]));
		if ( !in_array($add, $donotclose) ) $tagstoclose .= '</' . $add . '>';
	}
	return $string . $tagstoclose;
}


function jbTitleToLink($link)
{
	global $_JB_CONFIGURATION;

	$link = strtr($link, $_JB_CONFIGURATION->replacements_array);

	$link = preg_replace(array('/\'/', '/[^a-zA-Z0-9\-.+]+/', '/(^_|_$)/'), array('', '-', ''), $link);
	$link = preg_replace('/-(-)*/', '-', $link);
	$link = preg_replace('/^-/', '', $link);

	$link = JString::str_ireplace('.', '', $link);
	$link = JString::strtolower($link);

	return $link;
}

function jbTrim(&$string)
{
	$string = trim($string);
}

function i8n_date($date)
{
	global $joomblog_LANG;

	if ( $joomblog_LANG ) {
		foreach ($joomblog_LANG as $key => $val) {
			$date = str_replace($key, $val, $date);
		}
	}

	return $date;
}

function jbGetPDFLink($contentId, $ItemId)
{
	$link = JRoute::_('index.php?view=article&id=' . $contentId . '&option=com_content&format=pdf');
	$str = '';
	return $str;
}

function jbGetPrintLink($contentId, $ItemId)
{
	$link = JRoute::_('index.php?index.php?option=com_joomblog&show=' . $contentId . '&tmpl=component&print=1&task=printblog');
	$str = '<a title="Print" onClick="window.open(\'' . $link . '\',\'win2\',\'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\'); return false;" ';
	$str .= 'target="_blank" href="' . $link . '">';
	$str .= '<img border="0" name="Print" alt="Print" src="' . rtrim(JURI::base(), '/') . '/media/system/images/printButton.png"></a>';

	return $str;
}

function jbGetBackLink()
{
	global $joomblog_LANG;

	$str = '<div class="back_button"><a href="javascript:void(0);" onClick="javascript:history.go(-1);">' . JText::_('COM_JOOMBLOG_BACK_BUTTON') . '</a></div>';

	return $str;
}

function jbGetDefaultItemId()
{
	global $Itemid;
	$db = JFactory::getDBO();
	$strSQL = "SELECT `id` FROM #__menu WHERE "
		. "(type='component' OR type='components') "
		. "AND `link`='index.php?option=com_joomblog&view=default' "
		. "AND `published`='1'";
	$db->setQuery($strSQL);
	$mbItemid = $db->loadResult();

	if ( !$mbItemid ) $mbItemid = $Itemid;

	return $mbItemid;
}

function jbGetBlogItemId()
{
	static $mbItemid = -1;

	if ( $mbItemid == -1 ) {
		global $Itemid;

		$db = JFactory::getDBO();
		$mbItemid = $Itemid;

		$strSQL = "SELECT `id` FROM #__menu WHERE "
			. "`link` LIKE '%option=com_joomblog%' "
			. "AND `link` NOT LIKE '%option=com_joomblog&task=adminhome%' "
			. "AND `published`='1' "
			. "AND `id`='{$Itemid}'";
		$db->setQuery($strSQL);

		if ( !$db->loadResult() ) {
			$strSQL = "SELECT `id` FROM #__menu WHERE "
				. "(type='component' OR type='components') "
				. "AND `link`='index.php?option=com_joomblog' "
				. "AND `published`='1'";
			$db->setQuery($strSQL);
			$mbItemid = $db->loadResult();
		}
	}
	return $mbItemid;
}

function jbGetItemId()
{
	static $mbItemid = -1;

	if ( $mbItemid == -1 ) {
		global $Itemid;
		$db = JFactory::getDBO();
		$mbItemid = $Itemid;
		$db->setQuery("select id from #__menu where link LIKE '%option=com_joomblog%' and published='1' AND `id`='$Itemid'");
		if ( !$db->loadResult() ) {
			$db->setQuery("select id from #__menu where (type='component' or type='components') and ( link='index.php?option=com_joomblog' OR link='index.php?option=com_joomblog&view=default' ) and published='1'");
			$mbItemid = $db->loadResult();
		}
	}
	$tmpl = JFactory::getApplication()->input->get('tmpl');
	if ( $tmpl == 'component' ) {
		$mbItemid = str_replace('&tmpl=component', '', $mbItemid) . '&tmpl=component';
	}

	if ( !$mbItemid ) {
		$Itemid = JFactory::getApplication()->input->get('Itemid');

		$menu = JSite::getMenu();
		$items = $menu->getItems('link', 'index.php?option=com_joomblog&view=default');

		$mbItemid = $items ? $items[0]->id : $Itemid;
	}

	return $mbItemid;
}

function jbGetAdminItemId()
{
	static $mbItemid = -1;

	if ( $mbItemid == -1 ) {
		global $Itemid;

		$db = JFactory::getDBO();
		$mbItemid = $Itemid;
		$db->setQuery("select id from #__menu where link LIKE '%option=com_joomblog%' AND published='1' AND `id`='$Itemid' ");
		if ( !$db->loadResult() ) {
			$db->setQuery("select id from #__menu where `link` LIKE '%option=com_joomblog&task=adminhome%' AND published='1' AND `menutype`='usermenu' ");
			$mbItemid = $db->loadResult();
		}

		if ( !$mbItemid ) {
			$db->setQuery("select id from #__menu where `link` LIKE '%option=com_joomblog&task=adminhome%' AND published='1'");
			$mbItemid = $db->loadResult();
		}

		if ( !$mbItemid ) {
			$db->setQuery("select id from #__menu where `link` LIKE '%option=com_joomblog%' AND published='1'");
			$mbItemid = $db->loadResult();
		}
	}

	$tmpl = JFactory::getApplication()->input->get('tmpl');
	if ( $tmpl == 'component' ) {
		$mbItemid = str_replace('&tmpl=component', '', $mbItemid) . '&tmpl=component';
	}

	return $mbItemid;
}

function jbGetDashboardLinks()
{
	global $_JB_CONFIGURATION;

	$jbitemid = jbGetAdminItemId();

	$links = array(
		JRoute::_('index.php?option=com_joomblog&task=adminhome&Itemid=' . $jbitemid),
		JRoute::_('index.php?option=com_joomblog&task=bloggerpref&Itemid=' . $jbitemid),
		JRoute::_('index.php?option=com_joomblog&task=bloggerstats&Itemid=' . $jbitemid)
	);

	if ( $_JB_CONFIGURATION->get('useComment') ) {
		$links[] = JRoute::_('index.php?option=com_joomblog&task=showcomments&Itemid=' . $jbitemid);
	}
	return $links;
}

function jbGetDashboardLinksTitle()
{
	global $_JB_CONFIGURATION;

	include(JB_COM_PATH . '/language/' . $_JB_CONFIGURATION->language);

	$captions = array(
		$joomblog_LANG['TPL_MNU_JOOMBLOGS'],
		$joomblog_LANG['TPL_MNU_PREF'],
		$joomblog_LANG['TPL_MNU_STATS']
	);

	if ( $_JB_CONFIGURATION->get('useComment') ) {
		$captions[] = $joomblog_LANG['TPL_MNU_COMMENTS'];
	}
	return $captions;
}

function jbUnhtmlspecialchars($string)
{
	$string = str_replace('&amp;', '&', $string);
	$string = str_replace('&#039;', '\'', $string);
	$string = str_replace('&quot;', '"', $string);
	$string = str_replace('&lt;', '<', $string);
	$string = str_replace('&gt;', '>', $string);
	$string = str_replace('&uuml;', 'ü', $string);
	$string = str_replace('&Uuml;', 'Ü', $string);
	$string = str_replace('&auml;', 'ä', $string);
	$string = str_replace('&Auml;', 'Ä', $string);
	$string = str_replace('&ouml;', 'ö', $string);
	$string = str_replace('&Ouml;', 'Ö', $string);
	return $string;
}

function jbGetCategoryList($sectionid)
{
	$db = JFactory::getDBO();

	$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());
	// Filter by start and end dates.
	$nullDate = $db->Quote($db->getNullDate());
	$nowDate = $db->Quote(JFactory::getDate()->toSql());

	$query = "SELECT c.*, (
			SELECT COUNT(distinct a.id) FROM #__joomblog_posts as a
				LEFT JOIN #__joomblog_multicats as mc ON mc.aid = a.id WHERE a.state = 1 AND mc.cid = c.id
				 AND (a.publish_up = $nullDate OR a.publish_up <= $nowDate)
				 AND (a.publish_down = $nullDate OR a.publish_down >= $nowDate)
			) as `count`
			FROM #__categories as c
			WHERE c.`published`='1' AND c.extension ='com_joomblog' AND c.access IN (" . $groups . ") ORDER BY c.`lft`";

	$db->setQuery($query);
	$categories = $db->loadObjectList();

	return $categories;
}


function jbGetBlogsListPrivate($userid)
{
	$blogs = array();
	$user = JFactory::getUser();
	$current_user = JFactory::getUser($userid);

	$db = JFactory::getDBO();
	$db->setQuery(" SELECT `lb`.*,p.comments AS `posts` FROM #__joomblog_list_blogs AS `lb` " .
		" LEFT JOIN `#__joomblog_privacy` AS `p` ON `p`.`postid`=`lb`.`id` AND `p`.`isblog`=1 " .
		" WHERE (`lb`.`published` = 1 AND `lb`.`private` = 0 AND `lb`.`approved`=1) " .
		" OR ( `lb`.`published` = 1 AND `lb`.`user_id` = " . $userid . "  AND `lb`.`approved`=1)");
	$blogs = $db->loadObjectList();

	if ( sizeof($blogs) ) {
		for ($i = 0, $n = sizeof($blogs); $i < $n; $i++) {
			$row =& $blogs[$i];
			if ( $current_user->authorise('core.create', 'com_joomblog.blog.' . $row->id) != 1 ) {
				unset($blogs[$i]);
			}
			switch ($row->posts) {
				case 0:
					break;
				case 1:
					if ( !$user->id ) {
						unset($blogs[$i]);
					}
					break;
				case 2:
					if ( !$user->id ) {
						unset($blogs[$i]);
					} else {
						if ( !isFriends($user->id, $row->user_id) && $user->id != $row->user_id ) {
							unset($blogs[$i]);
						}
					}
					break;
				case 3:
					if ( !$user->id ) {
						unset($blogs[$i]);
					} else {
						if ( $user->id != $row->user_id ) {
							unset($blogs[$i]);
						}
					}
					break;
			}
		}
		$blogs = array_values($blogs);
	}
	return $blogs;
}

function isFriends($id1 = 0, $id2 = 0)
{
	$db = JFactory::getDBO();
	$db->setQuery(" SELECT `connection_id` FROM `#__community_connection` " .
		" WHERE connect_from=" . (int)$id1 . " AND connect_to=" . (int)$id2 . " AND `status`=1 ");
	$frindic = $db->loadResult();
	if ( $frindic ) return true; else return false;
}

function jbGetBlogsList($count_blogs = 0)
{
	$db = JFactory::getDbo();

	// Filter by start and end dates.
	$nullDate = $db->Quote($db->getNullDate());
	$nowDate = $db->Quote(JFactory::getDate()->toSql());

	$query = JFactory::getDbo()->getQuery(true);
	$query->select(" distinct(lb.user_id) as user_id, `lb`.description, `lb`.title, `lb`.id,
			(SELECT COUNT(*) FROM #__joomblog_posts as a
				LEFT JOIN #__joomblog_blogs AS b ON b.content_id = a.id
				WHERE b.blog_id = lb.id AND a.state=1
				AND (a.publish_up = $nullDate OR a.publish_up <= $nowDate)
				AND (a.publish_down = $nullDate OR a.publish_down >= $nowDate)
			) as `count`
			");
	$query->from("#__joomblog_list_blogs AS lb");


	$query->where('lb.published = 1');
	$query->where('lb.approved = 1');

	$query->order('lb.ordering');

	if ( !in_array('8', JFactory::getUser()->getAuthorisedGroups()) ) {
		$user = JFactory::getUser();
		$user_id = (int)$user->id;
		$groups = implode(',', $user->getAuthorisedViewLevels());
		if ( !JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false) ) {
			$query->where('(lb.access IN (' . $groups . ') OR lb.user_id=' . $user_id . ')');
		} else {
			$userJSGroups = JbblogBaseController::getJSGroups($user->id);
			$userJSFriends = JbblogBaseController::getJSFriends($user->id);
			if ( count($userJSGroups) > 0 ) {
				$tmpQ2 = ' OR (lb.access=-4 AND lb.access_gr IN (' . implode(',', $userJSGroups) . ')) ';
			} else {
				$tmpQ1 = ' ';
				$tmpQ2 = '';
			}
			if ( count($userJSFriends) > 0 ) {
				$tmpQ22 = ' OR (lb.access=-2 AND lb.user_id IN (' . implode(',', $userJSFriends) . ')) ';
			} else {
				$tmpQ11 = ' ';
				$tmpQ22 = '';
			}
			$query->where('(lb.access IN (' . $groups . ')
			    OR lb.user_id=' . $user_id . ' ' . $tmpQ2 . ' ' . $tmpQ22 . ' )');
		}
	}

	$db->setQuery($query, 0, $count_blogs);
	$blogs = $db->loadObjectList();
	return $blogs;
}


function jbGetCategoryArray($sectionid)
{
	$query = "SELECT * FROM #__categories WHERE extension ='com_joomblog' AND `published`='1' ORDER BY title";

	if ( !$categories = jbCacheChecker('categories_list', $query, false, 120) ) {
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$categories = $db->loadColumn();
		if ( !in_array(0, $categories) ) $categories[] = 0;

		jbCacheChecker('categories_list', $query, $categories);
	}
	return $categories;
}


function jbGetCategoryId($categoryName)
{
	$db = JFactory::getDBO();
	$db->setQuery("SELECT `id` FROM #__categories WHERE `title`='{$categoryName}'");
	return $db->loadResult();
}

function jbGetPost($id)
{
	$db = JFactory::getDBO();
	$db->setQuery("SELECT * FROM #__joomblog_posts WHERE `id`='{$id}'");
	return $db->loadObject();
}

function jbGetCategory($id)
{
	$db = JFactory::getDBO();
	$db->setQuery("SELECT * FROM #__categories WHERE `id`='{$id}'");
	return $db->loadObject();
}

function jbTagCmp($a, $b)
{
	return strcmp($a->name, $b->name);
}

function jbCountTagUsed($tagid)
{
	$db = JFactory::getDBO();
	$query = "SELECT COUNT(*) FROM #__joomblog_content_tags WHERE `tag`='$tagid'";
	$db->setQuery($query);
	return $db->loadResult();
}

function jbGetTagsSelectHtml($contentid = 0)
{
	$db = JFactory::getDBO();
	$query = "SELECT * FROM #__joomblog_tags";
	$db->setQuery($query);

	$result = $db->loadObjectList();
	$currentTags = array();

	if ( $contentid ) {
		$ctags = jbGetTags($contentid);
		if ( !empty($ctags) ) {
			foreach ($ctags as $ct)
				$currentTags[] = $ct->id;
		}
	}

	usort($result, 'jbTagCmp');

	$html = '<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" ><tbody id="tagListings">';
	foreach ($result as $row) {
		$row->useCount = jbCountTagUsed($row->id);

		$checked = in_array($row->id, $currentTags) ? 'checked="checked"' : '';
		$html .= '<tr>
                <td valign="middle"><input type="checkbox" value="' . $row->name . '"  ' . $checked . ' /></td>
                <td width="100%" valign="middle"><label class="catitem" style="vertical-align:middle" title="' . $row->useCount . ' blog entries">' . $row->name . '</label></td>
            </tr>';
	}
	$html .= '</tbody></table>';
	return $html;
}

function jbGetCategoryHtml($contentid = 0)
{
	global $_JB_CONFIGURATION;

	$db = JFactory::getDBO();
	$sections = implode(',', jbGetCategoryArray($_JB_CONFIGURATION->get('managedSections')));

	$strSQL = "SELECT `catid` FROM #__joomblog_posts WHERE `id`='{$contentid}'";
	$db->setQuery($strSQL);
	$selCat = $db->loadResult();

	//$strSQL = 'SELECT * FROM #__categories WHERE `section` IN (' . $sections . ') AND `published`=\'1\'';
	$strSQL = 'SELECT * FROM #__categories WHERE extension=\'com_joomblog\' AND `published`=\'1\'';
	$db->setQuery($strSQL);

	$categories = $db->loadObjectList();

	$html = '<select id="catid" name="catid" size="1" class="text">';

	foreach ($categories as $row) {
		$rowName = $row->title;

		if ( $selCat && $selCat == $row->id ) {
			$html .= '<option value="' . $row->id . '" selected="selected">' . $rowName . '</option>';
		} else {
			$html .= '<option value="' . $row->id . '">' . $rowName . '</option>';
		}
	}
	$html .= '</select>';

	return $html;
}


function jbGetJoomlaCategoryName($id)
{
	global $_JB_CONFIGURATION;

	$db = JFactory::getDBO();

	$query = "SELECT `title` FROM #__categories WHERE `id`='$id' ";
	$db->setQuery($query);
	return $db->loadResult();
}

function getPoweredByLink()
{
	$powered_by_link = '<div align="center" style="text-align:center; font-size:90%"></div>';
	return '';
}


function jbAddPathway($title, $link = '')
{
	$mainframe = JFactory::getApplication();
	$pathway = $mainframe->getPathway();

	$pathway->addItem($title, $link);
}

function jbAddPageTitle($title)
{
	$config = JFactory::getConfig();
	$conf_titles = $config->get('sitename_pagetitles');
	$document = JFactory::getDocument();

	if ( $conf_titles == 1 ) {
		$pagetitle = $document->getTitle();
		$pagetitle .= " " . $title;
		$document->setTitle($pagetitle);
	} elseif ( $conf_titles == 2 ) {
		$parentpagetitle = $document->getTitle();
		$pagetitle = $title . " " . $parentpagetitle;
		$document->setTitle($pagetitle);
	} else $document->setTitle($title);
}

function jbAddEditorHeader()
{
	static $added = false;

	if ( !$added ) {
		$document = JFactory::getDocument();

		$script = "\r\n";
		$script .= '<!--[if lte IE 6]>' . "\r\n";
		$script .= '<link href="' . JURI::base() . 'components/com_joomblog/css/style.IE6.css" rel="stylesheet" type="text/css" />' . "\r\n";
		$script .= '</style>' . "\r\n";
		$script .= '<![endif]-->' . "\r\n";
		$script .= '<!--[if IE 7]>' . "\r\n";
		$script .= '<link href="' . JURI::base() . 'components/com_joomblog/css/style.IE7.css" rel="stylesheet" type="text/css" />' . "\r\n";
		$script .= '<![endif]-->' . "\r\n";

		if ( method_exists($document, 'addCustomTag') )
			$document->addCustomTag($script);

		$added = true;
	}
}

function jbGetDisqusComments($row)
{
	global $_JB_CONFIGURATION;

	// JoomlaWorks reference parameters
	$plg_name = "jw_disqus";

	// API
	$mainframe = JFactory::getApplication();
	$document = JFactory::getDocument();
	$db = JFactory::getDBO();
	$user = JFactory::getUser();
	$aid = $user->get('aid', 0);

	// Assign paths
	$sitePath = JPATH_SITE;
	$siteUrl = substr(JURI::root(), 0, -1);

	// Requests
	$jinput = JFactory::getApplication()->input;
	$option = $jinput->get('option');
	$view = $jinput->get('view');
	$buser = $jinput->get('user');
	$archive = $jinput->get('archive');
	$task = $jinput->get('task');
	$show = $jinput->get('show', 0);
	$Itemid = $jinput->get('Itemid');
	if ( !$Itemid ) $Itemid = 999999;

	$disqusSubDomain = trim($_JB_CONFIGURATION->get('disqusSubDomain'));
	$usedisqus = $_JB_CONFIGURATION->get('useComment') == 2 ? TRUE : FALSE;
	$usecomment = $_JB_CONFIGURATION->get('useComment') == 1 ? TRUE : FALSE;
	if ( $usecomment )
		if ( !$disqusSubDomain && $usedisqus ) {

			global $raiseDisqusNotice;
			if ( !$raiseDisqusNotice ) {
				$raiseDisqusNotice = 1;
				JError::raiseNotice('', JText::_('COM_JOOMBLOG_DISQUS_ERROR'));
			}
			return;
		}
	// Perform some cleanups
	if ( $disqusSubDomain ) $disqusSubDomain = str_replace(array('http://', '.disqus.com/', '.disqus.com'), array('', '', ''), $disqusSubDomain);

	require_once($sitePath . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomblog' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . $plg_name . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'helper.php');

	$check_url = JURI::base();
	if ( strpos($check_url, 'joomplace.com') ) {
		//fix for disquss after content table change
		$query = "SELECT `id` FROM `#__content` WHERE `title` LIKE ('" . $row->title . "') AND `alias` LIKE ('" . $row->alias . "') AND `state` = '-2' LIMIT 1";
		$db->setQuery($query);
		$old_content_id = $db->loadResult();
		if ( $old_content_id > 0 ) {
			$row->id = $old_content_id;
		}
		//end fix
	}

	// Output object
	$output = new JObject;

	// Post URLs (raw, browser, system)
	$itemURLraw = $siteUrl . '/index.php?option=com_joomblog&show=' . $row->id . '&Itemid=' . $Itemid;

	$websiteURL = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ? "https://" . $_SERVER['HTTP_HOST'] : "http://" . $_SERVER['HTTP_HOST'];
	$itemURLbrowser = $websiteURL . $_SERVER['REQUEST_URI'];
	$itemURLbrowser = explode("#", $itemURLbrowser);
	$itemURLbrowser = $itemURLbrowser[0];
	$itemURL = JRoute::_('index.php?option=com_joomblog&show=' . $row->id . '&Itemid=' . $Itemid);

	// Post URL assignments
	$output->itemURL = $websiteURL . $itemURL;
	$output->itemURLrelative = $itemURL;
	$output->itemURLbrowser = $itemURLbrowser;
	$output->itemURLraw = $itemURLraw;

	// Comments
	$showID = JFactory::getApplication()->input->get('show', 0);

	if ( $usedisqus || ( JFactory::getApplication()->input->get('task', '') == 'show' && !empty($showID) ) )
	{
		JHtml::_('behavior.framework');
		$output->disqus_identifier = substr(md5($disqusSubDomain), 0, 10) . '_id' . $row->id;
		$disqConfig = '';

		if ( $disqusSubDomain == 'stemax' ) {
			$disqConfig .= 'var disqus_developer = "1";';
		}

		// it's are post
		if ( JFactory::getApplication()->input->get('task', 0) == 'show' && !empty($showID) )
		{
			$disqConfig .= 'var disqus_url= "' . $output->itemURL . '";';
			$disqConfig .= 'var disqus_title = "' . str_replace('"', '\"', $row->title) . '";';
			$disqConfig .= 'var disqus_identifier = "' . $output->disqus_identifier . '";';
		}

		$disqConfig .= 'var disqusSubDomain = "'. $disqusSubDomain .'";';
		$disqConfig .= 'var disqus_shortname = "'. $disqusSubDomain .'";';

		// include Once
		if ( isset(JFactory::getDocument()->_script["text/javascript"]) && strpos(JFactory::getDocument()->_script["text/javascript"], $disqConfig) === false)
			JFactory::getDocument()->addScriptDeclaration($disqConfig);

		//JFactory::getDocument()->addScript($siteUrl.'/components/com_joomblog/libraries/'.$plg_name.'/includes/js/behaviour.js');
		JFactory::getDocument()->addStyleSheet( JbDisqusHelper::getTemplatePath($plg_name, 'css/template.css')->http );

		if ( $usedisqus &&
			( JFactory::getApplication()->input->get('task', '') != 'show') && !JFactory::getApplication()->input->get('useDisqusCommentsCode',0) )
		{
			JFactory::getDocument()->addScriptDeclaration("
					var disqus_shortname = '{$disqusSubDomain}'; // required: replace example with your forum shortname
					(function () {
					var s = document.createElement('script'); s.async = true;
					s.type = 'text/javascript';
					s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
					(document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
					}());");

			JFactory::getApplication()->input->set('useDisqusCommentsCode', 1);
		}


		$output->comments = '
				<div id="disqus_thread"></div>
				<script type="text/javascript" src="http://disqus.com/forums/' . $disqusSubDomain . '/embed.js"></script>
				<noscript>
					<a href="http://' . $disqusSubDomain . '.disqus.com/?url=ref">' . JText::_('COM_JOOMBLOG_DISQUS_THREAD') . '</a>
				</noscript>
				';
	}
	// Comments END

	// rezerford ----------------------------------- Render the output -----------------------------------
	// Output head includes
//	echo "Debug: <pre>"; print_r($output); echo "</pre>"; die;
//	JbDisqusHelper::loadHeadIncludes($output);
	// Fetch the template

	if ( $show != 0 ) {
		ob_start();
		$dsqArticlePath = JbDisqusHelper::getTemplatePath($plg_name, 'post.php');
		$dsqArticlePath = $dsqArticlePath->file;
		include($dsqArticlePath);
		$getArticleTemplate = ob_get_contents();
		ob_end_clean();
	} else {
		ob_start();
		$dsqListingPath = JbDisqusHelper::getTemplatePath($plg_name, 'listing.php');
		$dsqListingPath = $dsqListingPath->file;
		include($dsqListingPath);
		$getArticleTemplate = ob_get_contents();
		ob_end_clean();
	}
	return $getArticleTemplate;
}


function jbGetAddThis()
{
	global $_JB_CONFIGURATION;

	// JoomlaWorks reference parameters
	$plg_name = "jw_disqus";

	// API
	$mainframe = JFactory::getApplication();
	$document = JFactory::getDocument();
	$db = JFactory::getDBO();
	$user = JFactory::getUser();

	// Requests
	$jinput = JFactory::getApplication()->input;
	$option = $jinput->get('option');
	$show = $jinput->get('show', 0);
	$Itemid = $jinput->get('Itemid');
	if ( !$Itemid ) $Itemid = 999999;

	$addThisName = trim($_JB_CONFIGURATION->get('addThisName'));

	$replace = '';
	if ( ($option = 'com_joomblog' && $show != 0) ) {
		$replace .= '<!-- AddThis Button BEGIN -->
						<div class="addthis_toolbox addthis_default_style ">
						<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
						<a class="addthis_button_tweet"></a>
						<a class="addthis_counter addthis_pill_style"></a>
						</div>
						<script type="text/javascript">var addthis_config = {"data_track_clickback":true};</script>
						<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=' . $addThisName . '"></script>
					<!-- AddThis Button END -->';
	}

	return $replace;
}

function jbGetComments($id)
{

	global $_JB_CONFIGURATION;

	$mainframe = JFactory::getApplication();
	$db = JFactory::getDBO();
	$user = JFactory::getUser();

	if ( $_JB_CONFIGURATION->get('useComment') ) {

		if ( $_JB_CONFIGURATION->get('viewComments') == 2 && !$user->get('id') ) {
			return '';
		}

		$e = array('name' => '', 'email' => '');

		if ( empty($userGroups) ) $userGroups = array();
		foreach ($user->groups as $groupName => $groupId) {
			$userGroups[] = $groupName;
		}

		$canEditGroups = array_intersect((array)$userGroups, array('Manager', 'Administrator', 'Super Users'));

		$query = "SELECT DISTINCT user_id FROM #__joomblog_comment WHERE contentid = " . $id ." LIMIT 1";
		$db->setQuery($query);
		$comment_creators = $db->loadColumn();

		if ( !empty($canEditGroups) || in_array($user->get('id'), $comment_creators) AND $user->get('id') !== 0 ) {
			$published = ' c.published IN (0,1) AND  ';
		} else {
			$published = ' c.published = 1 AND  ';
		}

		$query = "SELECT COUNT(*) FROM #__joomblog_comment AS c WHERE $published c.contentid = " . $id . "  ";
		$db->setQuery($query);
		$total = $db->loadResult();

		$e['total'] = $total;
		$jinput = JFactory::getApplication()->input;
		$limitstart = $jinput->get('limitstart', 0);

		$pageNav = new JBPagination($total, $limitstart, $_JB_CONFIGURATION->get('limitComment'));
		$e['page'] = $pageNav->getPagesLinks();

		$orderBy = 'c.created DESC';
		if ( $_JB_CONFIGURATION->get('firstlatestComments', 1) == 0 )
			$orderBy = 'c.created ASC';


		$query = "SELECT c.*, u.username FROM #__joomblog_comment as c LEFT JOIN #__users as u ON u.id = c.user_id WHERE $published c.contentid = " . $id . " ORDER BY $orderBy ";
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$comment = $db->loadObjectList();

		$e['comment'] = $comment;

		$template = new JoomblogTemplate();

		$avatar = 'Jb' . ucfirst($_JB_CONFIGURATION->get('avatar')) . 'Avatar';

		if ( $user->get('id') ) {
			$e['name'] = $user->get('name');
			$e['email'] = $user->get('email');
		} else {
			$e['name'] = 'guest';
		}

		foreach ($e['comment'] as $value) {
			$value->avatar = '';
			$value->isedit = 0;
			$value->ispublished = 0;

			$date = new JDate($value->created);
			$value->createdFormatted = $date->format($_JB_CONFIGURATION->get('dateFormat', 'Y-m-d'));
			$value->created = $date->format('Y-m-d H:i:s');

			if ( $value->user_id ) {

				$value->name = jbGetAuthorName($value->user_id, $_JB_CONFIGURATION->get('useFullName'));

				if ( $user->get('id') == $value->user_id ) {
					$value->isedit = 1;
				}
				$avatar = new $avatar($value->user_id);
				$avatar->_width = 36;
				$avatar->_height = 36;
				$value->avatar = $avatar->get();
			}

			if ( !empty($canEditGroups) || $value->user_id == $user->get('id') ) {
				$value->ispublished = 1;
			}
			if ( empty($value->avatar) ) {
				$value->avatar = '<img width="36" height="36" border="0" alt="" src="' . JURI::base() . 'components/com_joomblog/images/user_thumb.png">';

				if ( $_JB_CONFIGURATION->get('linkAvatar') && $user->get('id') ) {
					$value->avatar = "<a href='" . JRoute::_("index.php?option=com_joomblog&task=profile") . "'>" . $value->avatar . "</a>";
				}
			}

		}

		$e['id'] = $id;

		if ( $_JB_CONFIGURATION->get('useCommentCaptcha') ) {
			$e['captcha'] = 1;
		} else {
			$e['captcha'] = 0;
		}

		if ( !$user->get('id') && $_JB_CONFIGURATION->get('useCommentOnlyRegisteredUsers') ) {
			$e['onlyregisteredusers'] = $_JB_CONFIGURATION->get('useCommentOnlyRegisteredUsers');
		} else {
			$e['onlyregisteredusers'] = 0;
		}
//		JFormHelper::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
//		$jEditor = JFormHelper::loadFieldType('Html5editor', false);
//		$jEditor->setName('comment');
//		$jEditor->setLabel(JText::_('COM_JOOMBLOG_COMMENT_LEAVE_COMMENT'));
//		$template->set('jEditor', $jEditor, true);

		$template->set('e', $e);
		$content = $template->fetch(JbblogBaseController::_getTemplateName('comments'));

		return $content;
	}


}

/*** SMT NEW FUNCTIONAL 1.0.2 15.09.2011 ***/

function jbGetMultiCats($aid = 0)
{
	$mainframe = JFactory::getApplication();
	$db = JFactory::getDBO();
	$user = JFactory::getUser();

	$query = $db->getQuery(true);
	$query->select('cid');
	$query->from('#__joomblog_multicats');
	$query->where('aid=' . (int)$aid);
	$db->setQuery($query);
	$list = $db->loadColumn();
	return $list;
}

function jbShowSocialButtons($e, $position = 'bottom')
{
	global $_JB_CONFIGURATION;

	if ( $e['gpposition'] == $position && isset($e['gp_button']) ||
		$e['twposition'] == $position && isset($e['twitter_button']) ||
		$e['lnposition'] == $position && isset($e['ln_button']) ||
		$e['fbposition'] == $position && isset($e['fb_button']) ||
		$e['atposition'] == $position && isset($e['at_button']) ||
		$e['suposition'] == $position && isset($e['su_button']) ||
		$e['piposition'] == $position && isset($e['pi_button'])
	) {
		echo '<div class="jb_social_buttons_' . $position . '">';
		if ( $e['gpposition'] == $position && isset($e['gp_button']) ) {
			echo '<div class="jb_gpsocial_button">';
			echo $e['gp_button'];
			echo '</div>';
		}
		if ( $e['suposition'] == $position && isset($e['su_button']) ) {
			echo '<div class="jb_gpsocial_button">';
			echo $e['su_button'];
			echo '</div>';
		}
		if ( $e['twposition'] == $position && isset($e['twitter_button']) ) {
			echo '<div class="jb_twsocial_button">';
			echo $e['twitter_button'];
			echo '</div>';
		}
		if ( $e['lnposition'] == $position && isset($e['ln_button']) ) {
			echo '<div class="jb_lnsocial_button">';
			echo $e['ln_button'];
			echo '</div>';
		}
		if ( $e['fbposition'] == $position && isset($e['fb_button']) ) {
			echo '<div class="jb_fbsocial_button">';
			echo $e['fb_button'];
			echo '</div>';
		}
		if ( $e['atposition'] == $position && isset($e['at_button']) ) {
			echo '<div class="jb_atsocial_button">';
			echo $e['at_button'];
			echo '</div></div>';
		}
		if ( $e['piposition'] == $position && isset($e['pi_button']) ) {
			echo '<div class="pi_atsocial_button">';
			echo $e['pi_button'];
			echo '</div>';
		}


		echo '<div class="clr" style="clear:both"></div>';
		echo '</div>';
	}
}

function jbInJSgroup($id = 0, $gid = 0)
{
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	$query->select('groupid');
	$query->from('#__community_groups_members');
	$query->where('groupid=' . (int)$gid);
	$query->where('memberid=' . (int)$id);
	$db->setQuery($query);
	if ( $db->loadResult() ) return true; else return false;
}

function jbBlogNotifyAdmin($row, $isNew)
{
	global $_JB_CONFIGURATION;

	$mainframe = JFactory::getApplication();
	$status = false;
	$db = JFactory::getDBO();

	if ( $_JB_CONFIGURATION->get('allowNotification') ) {
		$author = jbGetAuthorName($row->user_id, $_JB_CONFIGURATION->get('useFullName'));
		$title = $row->title;
		$text = $row->introtext . $row->fulltext;
		$emails = $_JB_CONFIGURATION->get('adminEmail');
		$blog_link = jbGetExternalLink('index.php?option=com_joomblog&blogid=' . $row->id . '&view=blogger', true);

		if ( !class_exists('JBMailer') ) include_once(JB_LIBRARY_PATH . '/mail.php');

		$sid = jbGetSid();
		$date = strftime("%Y-%m-%d %H:%M:%S", time() + ($mainframe->getCfg('offset') * 60 * 60));

		$template = new JoomblogTemplate();

		$text = strip_tags($text);

		if ( $isNew ) {
			$content = $template->fetch(JB_TEMPLATE_PATH . "/default/newblog.notify.tmpl.html");
			$content = str_replace('%AUTHOR%', $author, $content);
			$content = str_replace('%TITLE%', $title, $content);
			$content = str_replace('%BLOG%', $blog_link, $content);
			$content = str_replace('%DATE%', $date, $content);

			$title = JText::_('COM_JOOMBLOG_NEW_BLOG_OK');
		} else {
			$content = $template->fetch(JB_TEMPLATE_PATH . "/default/updateblog.notify.tmpl.html");
			$content = str_replace('%AUTHOR%', $author, $content);
			$content = str_replace('%TITLE%', $title, $content);
			$content = str_replace('%BLOG%', $blog_link, $content);
			$content = str_replace('%DATE%', $date, $content);

			$title = JText::_('COM_JOOMBLOG_UPD_BLOG_OK');
		}

		if ( $emails ) {
			$emails = explode(',', $emails);
			if ( sizeof($emails) ) {
				foreach ($emails as $mailaddress) {
					$mail = new JBMailer();
					if ( $mail->send($mainframe->getCfg('mailfrom'), trim($mailaddress), $title, $content) ) $status = true;
					unset($mail);
				}
			}

		}
	}
	return $status;
}

function jbCacheChecker($jbFunction, $sql, $Result = false, $cacheTime = 30)
{
	$config = JFactory::getConfig();	
	if ($config->get('caching') != 0)
	{
	// cacheTime in seconds
	$cacheTime = $config->get('cachetime')*60;
	// prepare Dir
	$cacheDir = JPATH_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'joomblog' . DIRECTORY_SEPARATOR;
	
	if ( !file_exists($cacheDir) ) {
		@mkdir($cacheDir, 0757);
		if ( !file_exists($cacheDir) )
			$cacheDir = str_replace('joomblog' . DIRECTORY_SEPARATOR, 'page' . DIRECTORY_SEPARATOR, $cacheDir);
	}

	$db = & JFactory::getDBO();
	$user =& JFactory::getUser();

	$sqlCount = $db->loadResult($db->setQuery('SELECT COUNT(*) FROM #__joomblog_posts'));

	$cacheFile = $cacheDir . 'joomblog_' . $jbFunction . '_' . md5($sql . (int)$user->id);

	// if not Save action
	if ( !$Result ) {
		// if created time more that cacheTime var
		if ( !file_exists($cacheFile) || (time() - @filemtime($cacheFile) > $cacheTime) || !JFactory::getUser()->get('guest') ) {
			@unlink($cacheFile);
			return false;
		} else {
			$fileContent = unserialize(file_get_contents($cacheFile));

			// if last posts count is not like saved
			if ( $fileContent['sqlCount'] != $sqlCount ) {
				@unlink($cacheFile);
				return false;
			} else {
				return $fileContent['Result'];
			}
		}
	} else {
		$fileContent = @serialize(array('sqlCount' => $sqlCount, 'Result' => $Result));
		if ( $fileContent )
			file_put_contents($cacheFile, $fileContent);
	}
	}
}
