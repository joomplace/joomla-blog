<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.utilities.date');
jimport('joomla.filesystem.file');
require_once(JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'pagination.php');


class JbblogBaseController
{
	var $toolbar = JB_TOOLBAR_HOME;
	var $pageTitle = "";
	var $category = "";

	var $socialCalled = false;

	function execute()
	{
		$content = $this->display();
		echo $this->_header();
		echo $content;
		echo $this->_footer();
	}

	function JbblogBaseController()
	{
		$db = JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;
		$this->category = $jinput->get('tag', '', 'REQUEST');
		if (!empty($this->category))
		{
			$this->category = strval(urldecode($this->category));
			$this->category = str_replace("+", " ", $this->category);
		}

		$db->setQuery("SELECT rules FROM #__assets WHERE name = 'com_joomblog' ");
		if ($db->loadResult() == "{}" || !$db->loadResult())
		{
			$db->setQuery('UPDATE #__assets SET rules=\'{"core.admin":{"1":0,"6":0,"7":0,"2":0,"3":0,"4":0,"5":0,"10":0,"12":0,"8":0},"core.manage":{"1":0,"6":0,"7":0,"2":0,"3":0,"4":0,"5":0,"10":0,"12":0,"8":0},"core.create":{"1":1,"6":0,"7":0,"2":1,"3":0,"4":1,"5":1,"10":1,"12":0,"8":0},"core.delete":{"1":1,"6":1,"7":1,"2":1,"3":1,"4":1,"5":1,"10":1,"12":1,"8":0},"core.edit":{"1":1,"6":1,"7":1,"2":1,"3":1,"4":1,"5":1,"10":1,"12":1,"8":0},"core.edit.state":{"1":1,"6":1,"7":1,"2":1,"3":1,"4":1,"5":1,"10":1,"12":1,"8":0},"core.edit.own":{"1":1,"6":1,"7":1,"2":1,"3":1,"4":1,"5":1,"10":1,"12":1,"8":0}}\' WHERE name = "com_joomblog" ');
			$db->execute();
		}
	}

	function _header()
	{
		global $_JB_CONFIGURATION, $JOOMBLOG_LANG;;

		$mainframe = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$db = JFactory::getDBO();
		JHtml::_('jquery.framework');
//		$doc->addScript(rtrim( JURI::base() , '/' ).'/components/com_joomblog/js/jquery-1.8.3.min.js');
		$doc->addScript(rtrim(JURI::base(), '/') . '/components/com_joomblog/js/joomblog.js');
		$doc->addScript(JURI::root() . 'components/com_joomblog/assets/tagmanager-master/tagmanager.js');
		$doc->addStyleSheet(JURI::root() . 'components/com_joomblog/assets/tagmanager-master/tagmanager.css');

		$doc->addScriptDeclaration("var baseurl = '" . JURI::base() . "';");

		if (!$this->socialCalled)
		{
			/*** SMT SOCIAL ADDED ***/
			$usetwitter = $_JB_CONFIGURATION->get('usetwitter');
			$usefacebook = $_JB_CONFIGURATION->get('usefacebook');
			$usegp = $_JB_CONFIGURATION->get('usegp');
			$useln = $_JB_CONFIGURATION->get('useln');
			$useat = $_JB_CONFIGURATION->get('useAddThis');
			$usepi = $_JB_CONFIGURATION->get('usepi');
			$usesu = $_JB_CONFIGURATION->get('usesu');

			if ($usetwitter)
			{
				$doc->addScriptDeclaration("
						var nodeTwitter = document.createElement('script');
						nodeTwitter.type = 'text/javascript';
						nodeTwitter.async = true;
						nodeTwitter.src = '//platform.twitter.com/widgets.js';
						var s = document.getElementsByTagName('script')[0];
						s.parentNode.insertBefore(nodeTwitter, s);
					");
				//$doc->addScript('//platform.twitter.com/widgets.js');
			}
			if ($usegp)
			{
				$doc->addScript('//apis.google.com/js/plusone.js');
				/*$doc->addScriptDeclaration("
						var po = document.createElement('script');
						po.type = 'text/javascript';
						po.async = true;
						po.src = '//apis.google.com/js/plusone.js';
						var s = document.getElementsByTagName('script')[0];
						s.parentNode.insertBefore(po, s);");
				*/
				$gpLang = $_JB_CONFIGURATION->get('gp_language');
				if (isset($gpLang)) $doc->addScriptDeclaration("{lang: '" . $gpLang . "'}");
			}
			if ($usesu)
			{
				$doc->addScript('//platform.stumbleupon.com/1/widgets.js');
			}
			if ($useln)
			{
				$doc->addScriptDeclaration("
						var nodelinkedin = document.createElement('script');
						nodelinkedin.type = 'text/javascript';
						nodelinkedin.async = true;
						nodelinkedin.src = '//platform.linkedin.com/in.js';
						var s = document.getElementsByTagName('script')[0];
						s.parentNode.insertBefore(nodelinkedin, s);
					");
				//$doc->addScript('//platform.linkedin.com/in.js');
			}
			if ($useat)
			{
				$lang = $_JB_CONFIGURATION->get('addthis_language') ? $_JB_CONFIGURATION->get('addthis_language') : 'en';
				$doc->addScriptDeclaration('var addthis_config = {"ui_language":\'' . $lang . '\'};');
				$doc->addScript('http://s7.addthis.com/js/250/addthis_widget.js#pubid=' . $_JB_CONFIGURATION->get('addThisName'));
			}
			if ($usepi)
			{
				$doc->addScriptDeclaration("
				(function(d){
				  var f = d.getElementsByTagName('SCRIPT')[0], p = d.createElement('SCRIPT');
				  p.type = 'text/javascript';
				  p.async = true;
				  p.src = '//assets.pinterest.com/js/pinit.js';
				  f.parentNode.insertBefore(p, f);
				}(document)); ");
			}

			if ($usefacebook)
			{
				$doc->addScriptDeclaration('
				(function(d, s, id) {
				  var js, fjs = d.getElementsByTagName(s)[0];
				  if (d.getElementById(id)) return;
				  js = d.createElement(s); js.id = id;
				  js.src = "//connect.facebook.net/' . JFactory::getLanguage()->getTag() . '/all.js#xfbml=1&appId=476797492360205";
				  fjs.parentNode.insertBefore(js, fjs);
				}(document, "script", "facebook-jssdk"));');
			}

			$this->socialCalled = true;
		}
		/***/

		if ($_JB_CONFIGURATION->get('overrideTemplate'))
		{
			$jbCustomTplStyle = JPATH_base . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $mainframe->getTemplate() . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'com_joomblog' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'template_style.css';

			if (JFile::exists($jbCustomTplStyle))
			{
				$style = '<link rel="stylesheet" type="text/css" href="' . rtrim(JURI::base(), '/') . '/templates/' . $mainframe->getTemplate() . '/html/com_joomblog/css/template_style.css?d=' . filemtime($jbCustomTplStyle) . '" />';
				$doc->addStyleSheet(rtrim(JURI::base(), '/') . '/templates/' . $mainframe->getTemplate() . "/html/com_joomblog/css/template_style.css?d='.filemtime($jbCustomTplStyle).'");
			}
			else
			{
				if (JFile::exists(JB_TEMPLATE_PATH . "/" . $_JB_CONFIGURATION->get('template') . "/css/template_style.css"))
				{
					$style = '<link rel="stylesheet" type="text/css" href="' . rtrim(JURI::base(), '/') . '/components/com_joomblog/templates/' . $_JB_CONFIGURATION->get('template') . "/css/template_style.css?d='.filemtime($jbCustomTplStyle).'" . '"/>';
					$doc->addStyleSheet(rtrim(JURI::base(), '/') . '/components/com_joomblog/templates/' . $_JB_CONFIGURATION->get('template') . "/css/template_style.css?d='.filemtime($jbCustomTplStyle).'");
				}
			}
		}
		else
		{
			$templateStyleFile = JB_TEMPLATE_PATH . "/" . $_JB_CONFIGURATION->get('template') . "/css/template_style.css";
			if (JFile::exists($templateStyleFile))
			{
				$doc->addStyleSheet(rtrim(JURI::base(), '/') . '/components/com_joomblog/templates/' . $_JB_CONFIGURATION->get('template') . "/css/template_style.css?d=" . filemtime($templateStyleFile));
			}
		}

		if (JFile::exists(JB_TEMPLATE_PATH . "/" . $_JB_CONFIGURATION->get('template') . "/css/IE.css"))
		{
			$doc = JFactory::getDocument();
			$style = "\r\n";
			$style .= '<!--[if gte IE 6]>' . "\r\n";
			$style .= '<link rel="stylesheet" type="text/css" href="' . rtrim(JURI::base(), '/') . '/components/com_joomblog/templates/' . $_JB_CONFIGURATION->get('template') . "/css/IE.css" . '"/>' . "\r\n";
			$style .= '<![endif]-->' . "\r\n";
			if (method_exists($doc, 'addCustomTag'))
				$doc->addCustomTag($style);
		}

		if (JFile::exists(JB_TEMPLATE_PATH . "/" . $_JB_CONFIGURATION->get('template') . "/css/IE6.css"))
		{
			$style = "\r\n";
			$style .= '<!--[if lte IE 6]>' . "\r\n";
			$style .= '<link rel="stylesheet" type="text/css" href="' . rtrim(JURI::base(), '/') . '/components/com_joomblog/templates/' . $_JB_CONFIGURATION->get('template') . "/css/IE6.css" . '"/>' . "\r\n";
			$style .= '<![endif]-->' . "\r\n";
			if (method_exists($doc, 'addCustomTag'))
				$doc->addCustomTag($style);
		}

		if (JFile::exists(JB_TEMPLATE_PATH . "/" . $_JB_CONFIGURATION->get('template') . "/css/IE7.css"))
		{
			$style = "\r\n";
			$style .= '<!--[if IE 7]>' . "\r\n";
			$style .= '<link rel="stylesheet" type="text/css" href="' . rtrim(JURI::base(), '/') . '/components/com_joomblog/templates/' . $_JB_CONFIGURATION->get('template') . "/css/IE7.css" . '"/>' . "\r\n";
			$style .= '<![endif]-->' . "\r\n";
			if (method_exists($doc, 'addCustomTag'))
				$doc->addCustomTag($style);
		}

		if (JFile::exists(JB_TEMPLATE_PATH . "/" . $_JB_CONFIGURATION->get('template') . "/css/IE8.css"))
		{
			$style = "\r\n";
			$style .= '<!--[if IE 8]>' . "\r\n";
			$style .= '<link rel="stylesheet" type="text/css" href="' . rtrim(JURI::base(), '/') . '/components/com_joomblog/templates/' . $_JB_CONFIGURATION->get('template') . "/css/IE8.css" . '"/>' . "\r\n";
			$style .= '<![endif]-->' . "\r\n";
			if (method_exists($doc, 'addCustomTag'))
				$doc->addCustomTag($style);
		}

		$html = '';

		if ($_JB_CONFIGURATION->get('useRSSFeed'))
		{

			$rssLink = "index.php?option=com_joomblog";
			$tag = JFactory::getApplication()->input->get('tag', '', 'REQUEST');
			if (!empty($tag))
			{
				$rssLink .= "&tag=" . htmlspecialchars($tag);
			}
			$category = JFactory::getApplication()->input->get('category', '', 'REQUEST');

			if (!empty($category))
			{
				$rssLink .= "&category=" . htmlspecialchars($category);
			}
			$authorId = JFactory::getApplication()->input->get('user', '', 'REQUEST');
			if (!empty($authorId))
			{
				$rssLink .= "&user=" . htmlspecialchars($authorId);
			}
			$blogid = JFactory::getApplication()->input->get('blogid', 0);
			if (!empty($blogid))
			{
				$rssLink .= "&blogid=" . htmlspecialchars($blogid);
			}


			if (isset ($_REQUEST['Itemid']) and $_REQUEST['Itemid'] != "" and $_REQUEST['Itemid'] != "0")
				$rssLink .= "&Itemid=" . intval($_REQUEST['Itemid']);

			else
			{

				$query = "SELECT id FROM #__menu  WHERE type='components' "
					. "AND link='index.php?option=com_joomblog' "
					. "AND published='1'";

				$db->setQuery($query);

				$jbItemid = $db->loadResult();
				if (!$jbItemid)
					$jbItemid = 1;
				$Itemid = $jbItemid;
			}
			$rssLink .= "&task=rss";
			if (JFactory::getUser()->id > 0)
			{
				$rssLink .= '&viewer=' . JFactory::getUser()->id . '&hash=' . md5('(n&(#$m2l()UBN34' . JFactory::getUser()->id . '35rf&#$()gFS#E6^@#23');
			}

			if ($_JB_CONFIGURATION->get('useFeedBurnerIntegration') == "0")
			{
				$rssLink = JRoute::_($rssLink);
			}
			else
			{
				$rssLink = $_JB_CONFIGURATION->get('rssFeedBurner');
			}
			$jinput = JFactory::getApplication()->input;
			$blogger = $jinput->get('user', '', 'GET');
			if (isset($blogger) && !empty($blogger))
			{

				if ($_JB_CONFIGURATION->get('userUseFeedBurner'))
				{

					$user = JTable::getInstance('BlogUsers', 'Table');
					$user->load(JbGetAuthorId($blogger));

					if ($user->feedburner == '' && $_JB_CONFIGURATION->get('useFeedBurner'))
					{
						$rssLink = $_JB_CONFIGURATION->get('useFeedBurnerURL');
					}
					else
					{
						$rssLink = $user->feedburner;
					}
				}
			}


			if ($_JB_CONFIGURATION->get('useFeedBurner') && empty($blogger))
			{
				$rssLink = $_JB_CONFIGURATION->get('useFeedBurnerURL');
			}

			$rss = '<div class="topFeed">' .
				'<a href="' . $rssLink . '">' .
				'<span>' . $_JB_CONFIGURATION->get('titleFeed') . '</span>' .
				'</a>' .
				'</div>';

		}

		if (!class_exists('JoomblogTemplate')) include_once(JB_COM_PATH . DIRECTORY_SEPARATOR . 'template.php');

		$tpl = new JoomblogTemplate();
		$Itemid = jbGetDefaultItemId();
		$tpl->set('_JB_CONFIGURATION', $_JB_CONFIGURATION, true);
		$tpl->set('Itemid', $Itemid);
		if (!empty($rssLink)) $tpl->set('rssLink', $rssLink);
		else $tpl->set('rssLink', '');
		$templateFile = $this->_getTemplateName('header');
		$html .= $tpl->fetch($templateFile);


		$tmpl = JFactory::getApplication()->input->get('tmpl');

		if (!$tmpl || $tmpl != 'component')
		{
			$html .= $this->_showToolbar($this->toolbar);
		}

		return $html;
	}

	function _footer()
	{
		$html = getPoweredByLink();
		$html .= '</div>';
		return $html;
	}

	function _buildParams()
	{
		$mosParams = new JRegistry();
		$mosParams->def('show_title', "");
		$mosParams->def('link_titles', "");
		$mosParams->def('show_intro', "");
		$mosParams->def('show_category', "");
		$mosParams->def('link_category', "");
		$mosParams->def('show_parent_category', "");
		$mosParams->def('link_parent_category', "");
		$mosParams->def('show_author', "");
		$mosParams->def('link_author', "");
		$mosParams->def('show_create_date', "");
		$mosParams->def('show_modify_date', "");
		$mosParams->def('show_publish_date', "");
		$mosParams->def('show_item_navigation', "");
		$mosParams->def('show_icons', "");
		$mosParams->def('show_print_icon', "");
		$mosParams->def('show_email_icon', "");
		$mosParams->def('show_vote', "");
		$mosParams->def('show_hits', "");
		$mosParams->def('show_noauth', "");
		$mosParams->def('alternative_readmore', "");
		$mosParams->def('article_layout', "");

		return $mosParams;
	}

	function _showToolbar($op = "")
	{
		global $JOOMBLOG_LANG, $Itemid, $_JB_CONFIGURATION;
		$my = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$show = array();
		$jinput = JFactory::getApplication()->input;
		$category = $jinput->get('category', '', 'GET');
		$search = $jinput->get('search', '', 'GET');
		$db = JFactory::getDBO();
		$document = JFactory::getDocument();

		$blogger = $jinput->get('user', '', 'GET');

		$isBlogger = jbGetUserCanPost();

		if ($isBlogger)
		{
			jbAddEditorHeader();
		}

		$show['feed'] = $_JB_CONFIGURATION->get('useRSSFeed');

		$rssLink = '';
		if ($show['feed'])
		{
			$rssLink = "index.php?option=com_joomblog";
			if (isset ($_REQUEST['blogger']) and $_REQUEST['blogger'] != "")
				$rssLink .= "&user=" . htmlspecialchars($_REQUEST['blogger']);

			if (isset ($_REQUEST['tag']) and isset($_REQUEST['category']) && $_REQUEST['category'] != "")
				$rssLink .= "&tag=" . htmlspecialchars($_REQUEST['category']);

			if (isset ($_REQUEST['keyword']) and $_REQUEST['keyword'] != "")
				$rssLink .= "&keyword=" . htmlspecialchars($_REQUEST['keyword']);

			if (isset ($_REQUEST['archive']) and $_REQUEST['archive'] != "")
				$rssLink .= "&archive=" . htmlspecialchars($_REQUEST['archive']);

			if (isset ($_REQUEST['Itemid']) and $_REQUEST['Itemid'] != "" and $_REQUEST['Itemid'] != "0")
				$rssLink .= "&Itemid=" . intval($_REQUEST['Itemid']);
			else
			{

				$query = "SELECT id FROM #__menu  WHERE type='components' "
					. "AND link='index.php?option=com_joomblog' "
					. "AND published='1'";

				$db->setQuery($query);

				$jbItemid = $db->loadResult();
				if (!$jbItemid)
					$jbItemid = 1;
				$Itemid = $jbItemid;
			}
			$rssLink .= "&task=rss";
			$rssLink = JRoute::_($rssLink);

			if (isset($blogger) && !empty($blogger))
			{

				if ($_JB_CONFIGURATION->get('userUseFeedBurner'))
				{

					$user = JTable::getInstance('BlogUsers', 'Table');
					$user->load(JbGetAuthorId($blogger));

					if ($user->feedburner == '' && $_JB_CONFIGURATION->get('useFeedBurner'))
					{
						$rssLink = $_JB_CONFIGURATION->get('useFeedBurnerURL');
					}
					else
					{
						$rssLink = $user->feedburner;
					}
				}
			}


			if ($_JB_CONFIGURATION->get('useFeedBurner') && empty($blogger))
			{
				$rssLink = $_JB_CONFIGURATION->get('useFeedBurnerURL');
			}

			$rssTitle = $JOOMBLOG_LANG['_JB_RSS_BLOG_ENTRIES'];

			if ($blogger && $blogger != "")
			{
				$rssTitle .= $JOOMBLOG_LANG['_JB_RSS_BLOG_FOR'] . ' ' . $blogger;
			}


			if ($category && $category != "")
			{
				$rssTitle .= ' ' . $JOOMBLOG_LANG['_JB_RSS_BLOG_TAGGED'] . ' \'' . htmlspecialchars($category) . "'";
			}


			if ($search && $search != "")
			{
				$rssTitle .= "," . $JOOMBLOG_LANG['_JB_RSS_BLOG_KEYWORD'] . "'" . htmlspecialchars($search) . "'";
			}

			$rssLinkHeader = "\r\n" . '<link rel="alternate" type="application/rss+xml" title="' . $rssTitle . '" href="' . $rssLink . '" />' . "\r\n";
			if (method_exists($document, 'addCustomTag'))
				$document->addCustomTag($rssLinkHeader);
		}


		if ($_JB_CONFIGURATION->get('frontpageToolbar'))
		{
			$Itemid = jbGetDefaultItemId();
			$dashboardItemid = jbGetAdminItemId();
			$jinput = JFactory::getApplication()->input;
			$task = $jinput->get('task');
			$view = $jinput->get('view', '', 'GET');
			$user = $jinput->get('user', '', 'GET');

			$homeLink = JRoute::_("index.php?option=com_joomblog&view=default&Itemid=$Itemid");
			$blogsLink = JRoute::_("index.php?option=com_joomblog&task=blogs&Itemid=$Itemid");
			$tagsLink = JRoute::_("index.php?option=com_joomblog&task=viewtags&Itemid=$Itemid");
			$searchLink = JRoute::_("index.php?option=com_joomblog&task=search&Itemid=$Itemid");
			$bloggersLink = JRoute::_("index.php?option=com_joomblog&task=users&Itemid=$Itemid");
			$accountLink = JRoute::_('index.php?option=com_joomblog&task=bloggerpref&Itemid=' . jbGetAdminItemId());
			$categoriesLink = JRoute::_("index.php?option=com_joomblog&task=categories&Itemid=$Itemid");
			$archiveLink = JRoute::_("index.php?option=com_joomblog&task=archive&Itemid=$Itemid");
			$addBlogLink = JRoute::_("index.php?option=com_joomblog&Itemid=$Itemid&task=newblog");
			$entriesLink = JRoute::_('index.php?option=com_joomblog&task=adminhome&Itemid=' . jbGetAdminItemId());
			$statsLink = JRoute::_('index.php?option=com_joomblog&task=bloggerstats&Itemid=' . jbGetAdminItemId());
			$commentsLink = JRoute::_('index.php?option=com_joomblog&task=showcomments&Itemid=' . jbGetAdminItemId());

			$dashboardClass = "thickbox";
			$thickboxScript = "";

			if (!class_exists('JoomblogTemplate'))
				include_once(JB_COM_PATH . DIRECTORY_SEPARATOR . 'template.php');

			$tpl = new JoomblogTemplate();
			$toolbar = array();
			$active = array();

			$toolbar['op'] = $op;
			$toolbar['homeLink'] = $homeLink;
			$toolbar['blogsLink'] = $blogsLink;
			$toolbar['bloggersLink'] = $bloggersLink;
			$toolbar['tagsLink'] = $tagsLink;
			$toolbar['searchLink'] = $searchLink;
			$toolbar['accountLink'] = $accountLink;
			$toolbar['categoriesLink'] = $categoriesLink;
			$toolbar['archiveLink'] = $archiveLink;
			$toolbar['write'] = JRoute::_('index.php?option=com_joomblog&task=write&id=0&Itemid=' . jbGetItemId());
			$toolbar['addBlogLink'] = $addBlogLink;
			$toolbar['entriesLink'] = $entriesLink;
			$toolbar['statsLink'] = $statsLink;
			$toolbar['commentsLink'] = $commentsLink;
			$active['home'] = '';
			$active['category'] = '';
			$active['search'] = '';
			$active['blogger'] = '';
			$active['account'] = '';
			$active['tags'] = '';
			$active['add'] = '';
			$active[$op] = ' blogActive';


			if ($task == "categories")
			{
				$active['blogger'] = '';
				$active['category'] = ' blogActive';
			}

			if ($task == "archive" || $view == 'archives')
			{
				$active['blogger'] = '';
				$active['archive'] = ' blogActive';
			}

			if ($task == "viewtags" || $view == 'tags')
			{
				$active['blogger'] = '';
				$active['tags'] = ' blogActive';
			}

			if ($task == "dashboard" OR $task == "adminhome" OR $task == "showcomments" OR $task == "bloggerpref" OR $task == "bloggerstats" OR $task == "newblog" OR $task == "editblog" OR $task == "editblog" OR $task == "write")
			{
				$active['blogger'] = '';
				$active['home'] = '';
			}

			if ($view == 'user' || $user != '')
			{
				$active['blogger'] = '';
				$active['home'] = ' blogActive';
			}

			if ($task == "bloggerpref")
			{
				$active['account'] = ' blogActive';
			}
			elseif ($op == "blogger" && $view != 'user' && $user == '')
			{
				$active['home'] = '';
			}

			if ($op == "userblog")
			{
				$homeLink = JRoute::_("index.php?option=com_joomblog&Itemid=$Itemid&task=userblog");
				$manageBlogLink = JRoute::_("index2.php?option=com_joomblog&admin=1&task=adminhome&Itemid=$Itemid&keepThis=true&TB_iframe=true&height=600&width=850");
				$toolbar['homeLink'] = $homeLink;
				$toolbar['manageBlogLink'] = $manageBlogLink;
			}
			else
			{
				$toolbar['rssFeedLink'] = $rssLink;
			}

			$title = '';
			$desc = '';

			if ($task == 'userblog')
			{
				$jb = JFactory::getUser();
				$blogger = $jb->username;
			}

			if (!empty($blogger))
			{
				$title = stripslashes(JbGetAuthorTitle(JbGetAuthorId($blogger)));
				$desc = stripslashes(JbGetAuthorDescription(JbGetAuthorId($blogger)));
			}

			if ($_JB_CONFIGURATION->get('showPrimaryTitles'))
			{
				$title = empty($title) ? stripslashes($_JB_CONFIGURATION->get('mainBlogTitle')) : $title;
				$desc = empty($desc) ? stripslashes($_JB_CONFIGURATION->get('mainBlogDesc')) : $desc;
			}

			$tpl->set('toolbar', $toolbar);
			$tpl->set('show', $show);
			$tpl->set('active', $active);
			$tpl->set('title', $title);
			$tpl->set('summary', $desc);

			$templateFile = $this->_getTemplateName('toolbar');

			$toolbar_output = $tpl->fetch($templateFile);
			return $toolbar_output;
		}
		else
			if ($_JB_CONFIGURATION->get('showPrimaryTitles'))
			{
				$jinput = JFactory::getApplication()->input;
				$task = $jinput->get('task');
				$blogger = $jinput->get('user', '', 'GET');

				if (!class_exists('JoomblogTemplate'))
					include_once(JB_COM_PATH . DIRECTORY_SEPARATOR . 'template.php');

				$tpl = new JoomblogTemplate();

				$title = '';
				$desc = '';

				if ($task == 'userblog')
				{
					$jb = JFactory::getUser();
					$blogger = $jb->username;
				}
				if (!empty($blogger))
				{
					$title = stripslashes(JbGetAuthorTitle(JbGetAuthorId($blogger)));
					$desc = stripslashes(JbGetAuthorDescription(JbGetAuthorId($blogger)));
				}

				$title = empty($title) ? stripslashes($_JB_CONFIGURATION->get('mainBlogTitle')) : $title;
				$desc = empty($desc) ? stripslashes($_JB_CONFIGURATION->get('mainBlogDesc')) : $desc;

				$tpl->set('title', $title);
				$tpl->set('summary', $desc);

				$templateFile = $this->_getTemplateName('onlyprimarybar');

				$head_output = $tpl->fetch($templateFile);
				return $head_output;
			}
	}

	function _getTemplateName($templateType)
	{
		global $_JB_CONFIGURATION;

		$mainframe = JFactory::getApplication();

		$template = JB_TEMPLATE_PATH . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $templateType . '.tmpl.html';

		if ($_JB_CONFIGURATION->get('overrideTemplate'))
		{
			$path = JPATH_base . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $mainframe->getTemplate() . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'com_joomblog' . DIRECTORY_SEPARATOR . $templateType . '.tmpl.html';

			$template = JFile::exists($path) ? $path : $template;
		}
		else
		{
			$path = JB_TEMPLATE_PATH . DIRECTORY_SEPARATOR . $_JB_CONFIGURATION->get('template') . DIRECTORY_SEPARATOR . $templateType . '.tmpl.html';
			$template = JFile::exists($path) ? $path : $template;
		}

		return $template;
	}

	function _checkViewPermissions($context)
	{

	}

	public static function getModel($name = '', $prefix = 'JoomblogModel', $init_new = false, $ignore_request = false)
	{
		static $modelInstances = null;

		if (!isset($modelInstances))
		{
			$modelInstances = array();
		}

		if (!isset($modelInstances[$prefix . $name]) || $init_new)
		{
			$modelFile = JPATH_ROOT . '/components/com_joomblog/models/' . strtolower($name) . '.php';
			if (!JFile::exists($modelFile))
			{
				$modelInstances[$prefix . $name] = false;
			}
			else
			{
				include_once($modelFile);
				$classname = $prefix . $name;
				$modelInstances[$prefix . $name] = new $classname(array('ignore_request' => $ignore_request));
			}
		}

		return $modelInstances[$prefix . $name];
	}

	public static function getJSGroups($user_id)
	{
		static $groups = null;
		$user_id = (int) $user_id;
		if (!isset($groups))
		{
			$groups = array();
		}

		if (!isset($groups[$user_id]) && $user_id > 0)
		{
			$groups[$user_id] = array();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('groupid');
			$query->from('#__community_groups_members');
			$query->where('memberid=' . (int) $user_id);
			$db->setQuery($query);
			$result = $db->loadObjectList();
			if ($result)
			{
				foreach ($result as $res) $groups[$user_id][] = $res->groupid;
			}
		}
		if (empty($groups[$user_id])) $groups[$user_id] = array();
		return $groups[$user_id];
	}

	public static function getJSFriends($user_id)
	{
		static $friends = null;

		if (!isset($friends))
		{
			$friends = array();
		}

		if (!isset($friends[$user_id]) && $user_id > 0)
		{
			$friends[$user_id] = array();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('connect_to');
			$query->from('#__community_connection');
			$query->where('connect_from=' . (int) $user_id);
			$query->where('`status`=1');
			$db->setQuery($query);
			$result = $db->loadObjectList();
			if ($result)
			{
				foreach ($result as $res) $friends[$user_id][] = $res->connect_to;
			}
		}
		if (empty($friends[$user_id])) $friends[$user_id] = array();
		return $friends[$user_id];
	}
}