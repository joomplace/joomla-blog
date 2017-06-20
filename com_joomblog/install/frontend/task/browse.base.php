<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

require_once(JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'base.php');
require_once(JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'avatar.php');
require_once(JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'plugins.php');
require_once(JB_COM_PATH . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR . 'posts.php');


class JbblogBrowseBase extends JbblogBaseController
{
	var $entries;
	var $totalEntries;
	var $filters;
	var $html;
	var $limit;
	var $limitstart;
	var $_plugins = null;
	var $_headerHTML = '';

	function JbblogBrowseBase()
	{
		global $_JB_CONFIGURATION;

		parent::__construct();

		$this->_plugins = new JBPlugins();
		$this->toolbar = JB_TOOLBAR_HOME;
		$jinput = JFactory::getApplication()->input;
		$this->limit = $jinput->get('limit', $_JB_CONFIGURATION->get('numEntry'), 'GET');
		$this->limitstart = $jinput->get('limitstart', 0, 'GET');
	}

	function _header()
	{
		return parent::_header();
	}

	function display()
	{
		global $_JB_CONFIGURATION, $JBBLOG_LANG, $Itemid;

		$db = JFactory::getDBO();
		$lang = JFactory::getLanguage();

		if (!jbAllowedGuestView('intro'))
		{
			$template = new JoomblogTemplate();
			$content = $template->fetch($this->_getTemplateName('permissions'));
			return $content;
		}
		$blogger = JFactory::getApplication()->input->get('user', '', 'GET');

		if (!empty($blogger))
		{
			$title = jbGetAuthorTitle($blogger);
			if (!$title)
			{
				$title = $blogger . "'s blog";
			}

			jbAddPathway(JText::_('COM_JOOMBLOG_ALL_BLOGS_TITLE'), JURI::base() . JRoute::_('index.php?option=com_joomblog&user=' . $blogger));
			jbAddPathway($title);
		}

		$jb = JFactory::getUser();

		$this->setData();
		$this->_getEntries($this->filters);
		$tpl = new JoomblogCachedTemplate(serialize($this->entries) . $_JB_CONFIGURATION->get('template'));
		$html = '';

		if (!is_array($this->entries))
		{
			$this->entries = array();
		}

		array_walk($this->entries, array($this, '_prepareData'));

		$content_modules = array();
		if ($_JB_CONFIGURATION->get('modulesDisplay'))
		{
			$query = "SELECT m.module, m.params FROM #__modules AS m WHERE m.module LIKE ('mod_jb_%') GROUP BY (m.module) ";
			$db->setQuery($query);
			$modules = $db->loadObjectList('module');

			$query = "SELECT m.* FROM #__joomblog_modules AS m ";
			$db->setQuery($query);
			$joomblog_modules = $db->loadObjectList('name');

			$query = "SELECT MAX(m.ordering) FROM #__joomblog_modules AS m ";
			$db->setQuery($query);
			$next_ordering = $db->loadResult();

			$next_ordering++;

			foreach ($modules as $key => $value)
			{
				if (!isset($joomblog_modules[$key]))
				{
					$db->setQuery("INSERT INTO #__joomblog_modules SET published='1',	name = '$key', params='" . $value->params . "', ordering = " . $next_ordering . " ");
					$db->execute();
					$next_ordering++;
				}
			}

			$query = "SELECT m.* FROM #__joomblog_modules AS jm, #__modules AS m WHERE jm.name = m.module AND jm.published = 1 GROUP BY (m.module)  ";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$document = JFactory::getDocument();
			$renderer = $document->loadRenderer('module');


			foreach ($rows as $module)
			{
				$module->user = null;
				$modules = JModuleHelper::getModule($module->module);
				$params = array();
				ob_start();
				echo $renderer->render($module, $params); //ob_get_contents()
				$content_modules[] = array('text' => ob_get_clean(), 'name' => $module->module, 'title' => $module->title);
			}
		}

		$entryArray = $tpl->object_to_array($this->entries);

		$entrySession = array();
		$_SESSION['entrySession'] = array();
		foreach ($entryArray as $entry)
		{
			array_push($entrySession, $entry['id']);
		}

		$_SESSION['entrySession'] = (!empty($entrySession)) ? $entrySession : array();

		$tpl->set('entry', $entryArray);
		$tpl->set('categoryDisplay', $_JB_CONFIGURATION->get('categoryDisplay'));
		$tpl->set('showAnchor', $_JB_CONFIGURATION->get('anchorReadmore') ? '#readmore' : '');
		$tpl->set('useDraganddrop', $_JB_CONFIGURATION->get('useDraganddrop'));
		$tpl->set('modulesDisplay', $_JB_CONFIGURATION->get('modulesDisplay'));
		$tpl->set('modules', $content_modules);
		$tpl->set('headerHTML', $this->_headerHTML);

		$get = JFactory::getApplication()->input;
		$get_option = $get->get('option','','STR');
		$get_itemid = $get->get('Itemid','','INT');
		$get_view = $get->get('view','','STR');
		$get_language = $get->get('language','','STR');
		$get_lang = $get->get('lang','','STR');

		if (
			(isset($get_option) && isset($get_itemid) && $get->count() == 2) ||
			(isset($get_option) && $get->count() == 1) ||
			(isset($get_option) && isset($get_itemid) && isset($get_view) && $get_view == 'default' && $get->count() == 3) ||
			(isset($get_option) && isset($get_view) && $get_view == 'default' && $get->count() == 2) ||
			(isset($get_option) && isset($get_itemid) && isset($get_view) && isset($get_language) && isset($get_lang) && $get_view == 'default' && $get->count() == 5) ||
			(isset($get_option) && isset($get_itemid) && isset($get_language) && isset($get_lang) && $get->count() == 4)
		)
		{
			$isHome = 1;
		}
		else
		{
			$isHome = 0;
		}

		$tpl->set('isHome', $isHome);

		unset($entryArray);

		$template = $this->_getTemplateName('index');
		$html = $tpl->fetch_cache($template);

		if (!isset($_SERVER['REQUEST_URI']))
		{

			$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

			if (isset($_SERVER['QUERY_STRING']))
			{
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}

		if (!isset($_SERVER['QUERY_STRING']))
		{
			$_SERVER['QUERY_STRING'] = '';

			foreach ($_GET as $key => $val)
			{
				$_SERVER['QUERY_STRING'] .= $key . '=' . $val . '&';
			}
			$_SERVER['QUERY_STRING'] = rtrim($_SERVER['QUERY_STRING'], '&');
		}


		$queryString = $_SERVER['QUERY_STRING'];
		$queryString = preg_replace("/\&limit=[0-9]*/i", "", $queryString);
		$queryString = preg_replace("/\&limitstart=[0-9]*/i", "", $queryString);

		$pageNavLink = $_SERVER['REQUEST_URI'];
		$pageNavLink = preg_replace("/\&limit=[0-9]*/i", "", $pageNavLink);
		$pageNavLink = preg_replace("/\&limitstart=[0-9]*/i", "", $pageNavLink);

		if ($this->totalEntries > $this->limit)
		{
			$pageNav = new JBPagination($this->totalEntries, $this->limitstart, $this->limit);
			$html .= '<div class="jb-pagenav">' . $pageNav->getPagesLinks() . '</div>';
		}

		return $html;
	}

	function setData()
	{
		$searchby = array();
		$jinput = JFactory::getApplication()->input;
		$category = $jinput->get('category', JFactory::getApplication()->getParams()->get('category',0), 'REQUEST');

		if (!empty($category))
		{
			if (is_numeric($category))
			{
				$category = strval(urldecode($category));
				$category = str_replace("+", " ", $category);
				$searchby['jcategory'] = $category;
			}
			else
			{
				$category = strval(urldecode($category));
				$category = str_replace("+", " ", $category);
				$searchby['category'] = $category;
			}
		}

		$archive = $jinput->get('archive', '', 'REQUEST');
		if (!empty($archive))
		{
			$archive = urldecode($archive);
			$archive = str_replace(':', '-', $archive);
			$archive = date("Y-m-d 00:00:00", strtotime($archive . "-01"));
			$searchby['archive'] = $archive;
		}

		$this->filters = $searchby;
	}


	function _prepareData(&$row, $key)
	{
		global $_JB_CONFIGURATION, $Itemid, $JBBLOG_LANG;

		$mainframe = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;
		$tmpl = $jinput->get('tmpl');
		if ($tmpl == 'component') $tmpl = '&tmpl=component';
		else $tmpl = '';

		$this->_plugins->load();


		$blogger = $jinput->get('user', '', 'GET');


		if (!empty($blogger))
		{
			$title = jbGetAuthorTitle($blogger);
			if (!$title)
			{
				$title = $blogger . "'s blog";
			}

			jbAddPageTitle($title);
		}


		$row->permalink = jbGetPermalinkUrl($row->id);

		$row->introtext = str_replace('src="images', 'src="' . rtrim(JURI::base(), '/') . '/images', $row->introtext);
		$row->fulltext = str_replace('src="images', 'src="' . rtrim(JURI::base(), '/') . '/images', $row->fulltext);
		$row->introtext = str_replace('{social}', '', $row->introtext);
		$row->fulltext = str_replace('{social}', '', $row->fulltext);
		$row->author = jbGetAuthorName($row->created_by, $_JB_CONFIGURATION->get('useFullName'));
		if ($_JB_CONFIGURATION->get('integrJoomSoc') && file_exists(JPATH_ROOT . '/components/com_community/libraries/core.php'))
		{
			include_once JPATH_ROOT . '/components/com_community/libraries/core.php';
			// Get CUser object
			$row->authorLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $row->created_by);
		}
		else
		{
			$row->authorLink = JRoute::_("index.php?option=com_joomblog&task=profile&id=" . $row->created_by . "&Itemid=$Itemid");
		}
		$row->blogsLink = JRoute::_("index.php?option=com_joomblog&task=blogs&Itemid=" . $Itemid . $tmpl);
		$row->categories = jbCategoriesURLGet($row->id, true);

		$row->jcategory = null;
		$row->multicats = false;
		$cats = jbGetMultiCats($row->id);
		if (sizeof($cats))
		{
			$jcategories = array();
			foreach ($cats as $cat)
			{
				$catlink = JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $cat . '&Itemid=' . $Itemid . $tmpl);
				$jcategories [] = ' <a class="category" href="' . $catlink . '">' . jbGetJoomlaCategoryName($cat) . '</a> ';
			}
			if (sizeof($jcategories) > 1) $row->multicats = true;
			if (sizeof($jcategories)) $row->jcategory = implode(',', $jcategories);

		}
		else $row->jcategory = '<a class="category" href="' . JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $row->catid . '&Itemid=' . $Itemid . $tmpl) . '">' . jbGetJoomlaCategoryName($row->catid) . '</a>';

		$date = new JDate($row->created);

		$row->createdFormatted = $date->format($_JB_CONFIGURATION->get('dateFormat', 'Y-m-d'));
		$row->created = $date->format('Y-m-d H:i:s');

		$row->readmore = ($_JB_CONFIGURATION->get('useIntrotext') == '1') ? '1' : '0';

		$registry = new JRegistry;
		$registry->loadString($row->attribs);
		$attribs = $registry->toArray();

		$row->readmorelink = @$attribs['alternative_readmore'] ? $attribs['alternative_readmore'] : $_JB_CONFIGURATION->get('readMoreLink');

		//SMT social int

		/*** Twitter ***/
		$usetwitter = $_JB_CONFIGURATION->get('usetwitter');
		$row->twitter_button = null;
		$row->twposition = $_JB_CONFIGURATION->get('positiontwitterInList');

		if ($usetwitter)
		{
			$showTwInList = $_JB_CONFIGURATION->get('showtwitterInList');


			if ($showTwInList)
			{
				$twitStyle = $_JB_CONFIGURATION->get('twitterliststyle');
				$twitFlSt = $_JB_CONFIGURATION->get('twitterfollowliststyle');
				$twitLang = $_JB_CONFIGURATION->get('twitterlang');
				$twitName = $_JB_CONFIGURATION->get('twitterName');
				$twitflName = $twitName;
				$twitUrl = $row->permalink;

				if (isset($twitLang)) $twitLang = 'data-lang="' . $twitLang . '"';
				else $twitLang = '';
				if (isset($twitName)) $twitName = 'data-via="' . $twitName . '"';
				else $twitName = '';
				if (isset($twitUrl)) $twitUrl = 'data-url="' . $twitUrl . '"';
				else $twitUrl = '';
				if (isset($row->title)) $twitText = 'data-text="' . $row->title . '"';
				else $twitText = '';
				switch ($twitStyle)
				{
					case 'none':
						$row->twitter_button = '<a href="http://twitter.com/share" class="twitter-share-button" ' . $twitUrl . ' data-count="none" ' . $twitText . ' ' . $twitName . ' ' . $twitLang . '>Tweet</a>';
						break;
					case 'horizontal':
						$row->twitter_button = '<a href="http://twitter.com/share" class="twitter-share-button" ' . $twitUrl . ' data-count="horizontal" ' . $twitText . ' ' . $twitName . ' ' . $twitLang . '>Tweet</a>';
						break;
					case 'vertical':
						$row->twitter_button = '<a href="http://twitter.com/share" class="twitter-share-button" ' . $twitUrl . ' data-count="vertical" ' . $twitText . ' ' . $twitName . ' ' . $twitLang . '>Tweet</a>';
						break;
				}

				switch ($twitFlSt)
				{
					case 'twf1':
						$row->twitter_button .= '<a href="http://twitter.com/' . $twitflName . '" class="twitter-follow-button" data-show-count="false" ' . $twitLang . ' >@' . $twitflName . '</a>';
						break;
					case 'twf2':
						$row->twitter_button .= '<a href="http://twitter.com/' . $twitflName . '" class="twitter-follow-button" data-show-count="true" ' . $twitLang . ' >' . $twitflName . '</a>';
						break;
				}


			}

		}

		/*** Facebook ***/
		$usefacebook = $_JB_CONFIGURATION->get('usefacebook');
		$row->fb_button = null;
		$row->fbposition = $_JB_CONFIGURATION->get('positionfbInList');

		if ($usefacebook)
		{
			$showFbInList = $_JB_CONFIGURATION->get('showfbInList');

			if ($showFbInList)
			{
				$fbStyle = $_JB_CONFIGURATION->get('fb_style_list');
				$fbSendButton = $_JB_CONFIGURATION->get('fb`_sendbutton');
				$fbwidth = (int) $_JB_CONFIGURATION->get('fbwidth', 400);
				$fbUrl = $row->permalink;

				$row->fb_button = '';

				switch ($fbStyle)

				{

					case 'none':
						$row->fb_button .= '<div id="fb-root"></div><fb:like href="' . $fbUrl . '" send="' . ($fbSendButton ? true : false) . '" width="' . $fbwidth . '" show_faces="false" font=""></fb:like>';
						break;
					case 'horizontal':
						$row->fb_button .= '<div id="fb-root"></div><fb:like layout="button_count" href="' . $fbUrl . '" send="' . ($fbSendButton ? true : false) . '" width="' . $fbwidth . '" show_faces="false" font=""></fb:like>';
						break;
					case 'vertical':
						$row->fb_button .= '<div id="fb-root"></div><fb:like layout="box_count" href="' . $fbUrl . '" send="' . ($fbSendButton ? true : false) . '" width="' . $fbwidth . '" show_faces="false" font=""></fb:like>';
						break;

				}

			}

		}

		/*** Google + ***/

		$usegp = $_JB_CONFIGURATION->get('usegp');
		$row->gp_button = null;
		$row->gpposition = $_JB_CONFIGURATION->get('positiongpInList');

		if ($usegp)
		{
			$showGpInList = $_JB_CONFIGURATION->get('showgpInList');
			$gpLang = $_JB_CONFIGURATION->get('gp_language');
			if (isset($gpLang)) $gpLang = "{lang: '" . $gpLang . "'}";
			else $gpLang = '';
			if ($showGpInList)
			{
				$gpStyle = $_JB_CONFIGURATION->get('gp_style_list');
				$gpUrl = $row->permalink;
				switch ($gpStyle)
				{

					case 'horizontal':
						$row->gp_button .= '<div class="g-plusone" data-size="medium" data-annotation="none" data-href="' . $gpUrl . '"></div>';
						break;
					case 'none':
						$row->gp_button .= '<div class="g-plusone" data-size="medium" data-href="' . $gpUrl . '"></div>';
						break;
					case 'vertical':
						$row->gp_button .= '<div class="g-plusone" data-size="tall" data-href="' . $gpUrl . '"></div>';
						break;

				}

			}

		}

		/*** StumbleUpon ***/

		$usesu = $_JB_CONFIGURATION->get('usesu');
		$row->su_button = null;
		$row->suposition = $_JB_CONFIGURATION->get('positionsuInList');

		if ($usesu)
		{
			$showSuInList = $_JB_CONFIGURATION->get('showsuInList');
			if ($showSuInList)
			{
				$suStyle = preg_replace('/[^\d]+/i', '', $_JB_CONFIGURATION->get('su_style_list'));
				$row->su_button .= '<su:badge layout="' . $suStyle . '" location="' . $row->permalink . '"></su:badge>';
			}

		}

		/*** Linkedin ***/
		$useln = $_JB_CONFIGURATION->get('useln');
		$row->ln_button = null;
		$row->lnposition = $_JB_CONFIGURATION->get('positionlnInList');

		if ($useln)
		{
			$showLnInList = $_JB_CONFIGURATION->get('showlnInList');

			if ($showLnInList)
			{
				$lnStyle = $_JB_CONFIGURATION->get('ln_style_list');
				$lnUrl = $row->permalink;


				switch ($lnStyle)

				{

					case 'none':
						$row->ln_button .= '<script type="IN/Share" data-url="' . $lnUrl . '"></script>';
						break;
					case 'horizontal':
						$row->ln_button .= '<script type="IN/Share" data-url="' . $lnUrl . '" data-counter="right"></script>';
						break;
					case 'vertical':
						$row->ln_button .= '<script type="IN/Share" data-url="' . $lnUrl . '" data-counter="top"></script>';
						break;

				}

			}

		}
		/*** Pinterest ***/

		$usepi = $_JB_CONFIGURATION->get('usepi');
		$row->pi_button = null;
		$row->piposition = $_JB_CONFIGURATION->get('positionpiInList');

		if ($usepi)
		{
			$showPiInList = $_JB_CONFIGURATION->get('showpiInList');
			$piLang = $_JB_CONFIGURATION->get('pi_language');
			$piLang = (isset($piLang)) ? "{lang: '" . $piLang . "'}" : '';

			if ($showPiInList)

			{

				$piStyle = $_JB_CONFIGURATION->get('pi_style_list');
				$piUrl = urlencode($row->permalink);
				$piDescription = urlencode($row->blogtitle);
				preg_match_all('#<img.*?src=["\']*([\S]+)["\'].*?>#', $row->introtext . $row->fulltext, $piImageTemp);
				$piImage = ($piImageTemp == '') ? 'none' : '&media=' . @$piImageTemp[1][0];
				$piImage = urlencode($piImage);
				$pi_url_text = '<a target="_blank" href="//pinterest.com/pin/create/button/?url=' . $piUrl . $piImage . '&description=' . $piDescription . '" data-pin-do="buttonPin" data-pin-config="beside"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" title="Pin It" /></a>';
				$row->pi_button .= $pi_url_text;


			}
		}


		/*** AddThis ***/
		$useat = $_JB_CONFIGURATION->get('useAddThis');
		$row->at_button = null;
		$row->atposition = $_JB_CONFIGURATION->get('addThisListPosition');

		if ($useat)
			if ($_JB_CONFIGURATION->get('showAddThisInList') == 1)
			{
				$sefUrl = $row->permalink;
				$host = $_JB_CONFIGURATION->get('addThisName');
				$button_style = $_JB_CONFIGURATION->get('addthis_list_button_style');

				$services = array();

				if ($button_style == 'style2')
				{
					$add = '<!-- AddThis Button BEGIN --><div class="addthis_toolbox addthis_default_style addthis_32x32_style">';
				}
				else if ($button_style == 'style9' || $button_style == 'style10')
				{
					$add = '';
				}
				else
				{
					$add = '<!-- AddThis Button BEGIN --><div class="addthis_toolbox addthis_default_style ">';
				}

				$add .= socialDefaultAdd($button_style, $sefUrl, $host);
				$row->at_button = $add;
			}


		if ($_JB_CONFIGURATION->get('necessaryReadmore') == '1' && $row->readmore == '1')
		{
			if ($row->introtext && empty($row->fulltext))
			{

				$count = TablePosts::getParagraphCount($row->introtext);
				if ($count <= $_JB_CONFIGURATION->get('autoReadmorePCount'))
				{
					$row->readmore = '0';
				}
			}
			else if (empty($row->introtext) && $row->fulltext)
			{

				$count = TablePosts::getParagraphCount($row->fulltext);
				if ($count <= $_JB_CONFIGURATION->get('autoReadmorePCount'))
				{
					$row->readmore = '0';
				}
			}
		}

		TablePosts::getBrowseText($row);
		$row->comments = ($_JB_CONFIGURATION->get('useComment') == "1") ? jbCommentsURLGet($row->id, true) : jbGetDisqusComments($row);


		$avatar = 'Jb' . ucfirst($_JB_CONFIGURATION->get('avatar')) . 'Avatar';
		$avatar = new $avatar($row->created_by);

		$row->avatar = $avatar->get();
		$params = $this->_buildParams();

		if ($_JB_CONFIGURATION->get('mambotFrontpage') == "1")
		{
			/*$row->beforeContent = $this->_plugins->trigger('onBeforeDisplayContent', $row, $this->_buildParams(), 0);
			$this->_plugins->trigger('onPrepareContent', $row, $params, 0);			
			$row->afterContent	= $this->_plugins->trigger('onAfterDisplayContent', $row, $this->_buildParams(), 0);
			if ($row->afterContent != "") $row->afterContent = "<br/>" . $row->afterContent;*/
			$row->beforeContent = '';
			$row->afterContent = '';
		}
		$row->text = $this->_plugins->onContentPrepare($row->text);


		$row->text = str_replace(array('{mosimage}',
			'{mospagebreak}',
			'{readmore}',
			'{jomcomment}',
			'{!jomcomment}'), '', $row->text);

	}


	function _getEntries(&$searchby)
	{
		global $_JB_CONFIGURATION, $Itemid;

		$doc = JFactory::getDocument();
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$model = $this->getModel('Posts', 'JoomblogModel');
		$state = $model->getState();
		$rows = $model->getItems();
		$this->totalEntries = $model->getTotal();
		$this->entries = $rows;

		$limit = isset($searchby['limit']) ? intval($searchby['limit']) : $this->limit;
		$limitstart = isset($searchby['limitstart']) ? intval($searchby['limitstart']) : $this->limitstart;
		$jcategory = isset($searchby['jcategory']) ? intval($searchby['jcategory']) : 0;

		$authorid = isset($searchby['authorid']) ? $db->escape($searchby['authorid']) : "";
		$tag = isset($searchby['category']) ? $db->escape($searchby['category']) : "";
		$search = isset($searchby['search']) ? $db->escape($searchby['search']) : "";
		$archive = isset($searchby['archive']) ? $db->escape($searchby['archive']) : "";
		$tmpl = JFactory::getApplication()->input->get('tmpl');
		if ($tmpl == 'component') $tmpl = '&tmpl=component';
		else $tmpl = '';
		if (!empty ($jcategory) && $jcategory > 0)
		{
			//acccess check
			$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());
			$query = "SELECT c.* , COUNT(mc.cid) as count  
			FROM #__categories AS c 
			LEFT JOIN `#__joomblog_multicats` AS `mc` ON `mc`.`cid`=`c`.`id` 
			WHERE c.published = 1 AND c.id='$jcategory' AND c.access IN ($groups)";
			$db->setQuery($query);
			$categories = $db->loadObjectList();
			if (empty($categories[0]->id)) $jcategory = 0;
			else
			{
				$category = $categories[0];
				$category->link = JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $category->id . '&Itemid=' . $Itemid . $tmpl);
				$cat = array();
			}
		}
		$view = JFactory::getApplication()->input->get('view', '', 'GET');
		if ($view == 'blogger')
		{

			$blogid = JFactory::getApplication()->input->get('blogid', 0);

			if ($blogid)
			{
				$query = $db->getQuery(true);
				$query->select('lb.*,u.username, u.name, ub.avatar');
				$query->from('#__joomblog_list_blogs lb');
				$query->join('LEFT', '#__users u ON u.id=lb.user_id');
				$query->join('LEFT', '#__joomblog_user ub ON ub.user_id=lb.user_id');
				$query->where("lb.id=" . $db->quote($blogid));

				//acccess check
				if (!in_array('8', JFactory::getUser()->getAuthorisedGroups()))
				{
					$user = JFactory::getUser();
					$user_id = (int) $user->id;
					$groups = implode(',', $user->getAuthorisedViewLevels());
					if (!JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false))
					{
						$query->where('(lb.access IN (' . $groups . ') OR lb.user_id=' . $user_id . ')');
					}
					else
					{
						$userJSGroups = JbblogBaseController::getJSGroups($user->id);
						$userJSFriends = JbblogBaseController::getJSFriends($user->id);
						if (count($userJSGroups) > 0)
						{
							$tmpQ2 = ' OR (lb.access=-4 AND lb.access_gr IN (' . implode(',', $userJSGroups) . ')) ';
						}
						else
						{
							$tmpQ1 = ' ';
							$tmpQ2 = '';
						}
						if (count($userJSFriends) > 0)
						{
							$tmpQ22 = ' OR (lb.access=-2 AND lb.user_id IN (' . implode(',', $userJSFriends) . ')) ';
						}
						else
						{
							$tmpQ11 = ' ';
							$tmpQ22 = '';
						}
						$query->where('(lb.access IN (' . $groups . ')
							    OR lb.user_id=' . $user_id . ' ' . $tmpQ2 . ' ' . $tmpQ22 . ' )');
					}
				}
				$db->setQuery($query);

				$blog = $db->loadObject();
				if ($blog)
				{
					if ($doc->getDescription() == '' || $blog->metadata != '')
					{
						if (isset($blog->metadesc)) $doc->setDescription($blog->metadesc);
					}

					$keywords = '';
					if (!empty($blog->metakey))
					{
						$keywords .= ' ' . $blog->metakey;
					}

					$doc->setMetaData('keywords', $doc->getMetaData('keywords') . ' ' . $keywords);
					$blog->title = $blog->title ? $blog->title : $blog->username . "'s blog";
					$blog->blogLink = JRoute::_("index.php?option=com_joomblog&blogid=" . $blog->id . "&view=blogger&Itemid=" . $Itemid . $tmpl);

					$avatar = 'Jb' . ucfirst($_JB_CONFIGURATION->get('avatar')) . 'Avatar';
					$avatar = new $avatar($blog->user_id, 0);
					$blog->src = $avatar->get();

					if ($_JB_CONFIGURATION->get('integrJoomSoc') && file_exists(JPATH_ROOT . '/components/com_community/libraries/core.php'))
					{
						include_once JPATH_ROOT . '/components/com_community/libraries/core.php';
						// Get CUser object
						$blog->authorLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $blog->user_id);
					}
					else
					{
						$blog->authorLink = JRoute::_("index.php?option=com_joomblog&task=profile&id=" . $blog->user_id . "&Itemid=" . $Itemid . $tmpl);
					}


				}
				else $blogid = 0;
			}
			if ($blogid)
			{

			}
			else
			{
				JError::raiseError(404, JText::_('COM_JOOMBLOG_BLOG_NOT_FOUND'));
				return false;

			}

			$blogs = array();
			$blogs[0] = $blog;
			$template = new JoomblogTemplate();
			$template->set('blogs', $blogs);
			$template->set('Itemid', $Itemid);

			$this->_headerHTML = $template->fetch($this->_getTemplateName('blog_header'));

		}
		if ($view == 'jjcategory' && empty ($jcategory))
		{
			JError::raiseError(404, JText::_('COM_JOOMBLOG_CATEGORY_NOT_FOUND'));
			return false;
		}
		if (!empty ($authorid) or $authorid == "0")
		{
			$db->setQuery("SELECT u.username, u.name, ub.* FROM #__joomblog_user as ub LEFT JOIN #__users as u ON u.id = ub.user_id WHERE u.block = 0 AND ub.user_id = " . $authorid);
			$users = $db->loadObjectList();
			$user = $users[0];

			$avatar = 'Jb' . ucfirst($_JB_CONFIGURATION->get('avatar')) . 'Avatar';
			$avatar = new $avatar($user->user_id, 0);
			$user->src = $avatar->get();

			$user->link = JRoute::_("index.php?option=com_joomblog&task=profile&id={$user->user_id}&Itemid=$Itemid.$tmpl");

			$man = array();
			$man[0] = $user;

			$template = new JoomblogTemplate();
			$template->set('man', $man);
			$this->_headerHTML = $template->fetch($this->_getTemplateName('users_header'));
		}
		if (!empty ($jcategory) && $jcategory > 0)
		{

			$category->count = $this->totalEntries;
			$cat[0] = $category;
			$template = new JoomblogTemplate();
			$template->set('category', $cat);
			$this->_headerHTML = $template->fetch($this->_getTemplateName('category_header'));
		}

		if (!empty($tag) && $this->totalEntries > 0)
		{
			$template = new JoomblogTemplate();
			$template->set('tag_name', $tag);
			$this->_headerHTML = $template->fetch($this->_getTemplateName('tag_header'));
		}
		return null;
	}
}

