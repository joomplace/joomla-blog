<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

if (!defined('JOOMBLOG_CONFIG_CLASS'))
{
	define('JOOMBLOG_CONFIG_CLASS', 1);

	class JB_Configuration
	{
		var $_configString = "";
		var $_tableName = "#__joomblog_config";
		var $useBBCode = "1";
		var $useFeed = "1";
		var $notifyAdmin = "0";
		var $notifyEmail = "";
		var $dateFormat = "Y-m-d H:i:s";
		var $numEntry = "10";
		var $avatar = "none";
		var $textWrap = "75";
		var $processMambots = "0";
		var $useComment = "1";
		var $useJomComment = "0";
		var $useDraganddrop = "0";
		var $sectionid = "1";
		var $template = "default";
		var $overrideTemplate = "0";
		var $frontOrderby = "ordering";
		var $postGroup = "Manager,Administrator,Registered,Author,Editor,Publisher,Shop Suppliers,Customer Group,Super Users";
		var $adminPostGroup = "";
		var $adminPublishControlGroup = "";
		var $publishControlGroup = "Manager,Administrator,Registered,Author,Editor,Publisher,Shop Suppliers,Customer Group,Super Users";
		var $postUsers = "";
		var $frontpageToolbar = "1";
		var $allowedUser = "";
		var $useMCEeditor = "1";
		var $catid = "0";
		var $managedSections = "0";
		var $postSection = "0";
		var $showCBProfile = "";
		var $avatarWidth = "160";
		var $avatarHeight = "160";
		var $maxFileSize = "2";
		var $smfPath = "";
		var $linkAvatar = "0";
		var $imgFolderRestrict = "0";
		var $useRSSFeed = "1";
		var $rssFeedLimit = '20';
		var $pingTechnorati = "0";
		var $useImageUpload = "1";
		var $useFullName = "0";
		var $introLength = "400";
		var $useIntrotext = "1";
		var $defaultPublishStatus = "1";
		var $mambotFrontpage = "0";
		var $necessaryReadmore = "1";
		var $disableReadMoreTag = "0";
		var $readMoreLink = "read more...";
		var $replacements_array = array();
		var $language = "english.php";
		var $allowedPosters = "";
		var $allowedPublishers = "";
		var $extraPostGroups = "";
		var $extraPublishGroups = "";
		var $limitComment = "20";
		var $allowModerateComment = "0";
		var $useCommentCaptcha = "1";
		var $useCommentCaptchaRegisteredUsers = "0";
		var $useCommentOnlyRegisteredUsers = "0";
		var $allowCategorySelection = "1";
		var $enableBackLink = "1";
		var $enablePdfLink = "1";
		var $enablePrintLink = "1";
		var $enableJCDashboard = "0";
		var $enableUserCreateTags = "1";
		var $enableAllVideoBot = "0";
		var $mainBlogTitle = "JoomBlog";
		var $mainBlogDesc = "Description of joomblog";
		var $useFeedBurner = '0';
		var $useFeedBurnerURL = "";
		var $userUseFeedBurner = '0';
		var $adminEmail = '';
		var $allowNotification = '';
		var $viewEntry = '1';
		var $viewIntro = '1';
		var $viewComments = '1';
		var $anchorReadmore = '0';
		var $allowDefaultTags = '0';
		var $autoReadmorePCount = '3';
		var $disallowedPosters = '';
		var $categoryDisplay = '1';
		var $modulesDisplay = '0';
		var $notifyCommentAdmin = '1';
		var $notifyCommentAuthor = '1';
		var $showIcons = '1';
		var $showTabs = '1';
		var $showPanel = '1';
		var $disqusSubDomain = '';
		var $useAddThis = '0';
		var $addThisName = '';
		var $showPosts = 1;
		var $showBlogs = 1;
		var $showSearch = 1;
		var $showFeed = 1;
		var $showProfile = 1;
		var $showAddPost = 1;
		var $showBloggers = 1;
		var $showCategories = 1;
		var $showArchive = 1;
		var $showTags = 1;
		var $titlePosts = 'Posts';
		var $titleBlogs = 'Blogs';
		var $titleSearch = 'Search';
		var $titleFeed = 'Feed';
		var $titleProfile = 'Profile';
		var $titleAddPost = 'Add Post';
		var $titleBloggers = 'Bloggers';
		var $titleCategories = 'Categories';
		var $titleArchive = 'Archive';
		var $titleTags = 'Tags';
		var $firstPosition = '1';
		var $secondPosition = '2';
		var $thirdPosition = '3';
		var $fourthPosition = '4';
		var $fifthPosition = '5';
		var $sixthPosition = '6';
		var $seventhPosition = '7';
		var $eighthPosition = '8';
		var $ninthPosition = '9';
		var $tenthPosition = '10';
		var $BloggerRecentPosts = 5;
		var $CategoriesRecentPosts = 5;
		var $showUserLink = '1';

		var $integrJoomSoc = '0';
		var $autoapproveblogs = '1';

		function __construct()
		{
			$mainframe = JFactory::getApplication();
			$db = JFactory::getDBO();

			$db->setQuery("SELECT params FROM #__extensions WHERE name='com_joomblog' AND element='com_joomblog'");
			$this->_configString = $db->loadResult();

			$cfg = json_decode($this->_configString);

			/**
			 * @todo: Correctly process case when cfg variable is empty. Temporary showing notice asking to save configuration
			 */
			if (sizeof($cfg))
			{
				foreach ($cfg as $key => $val)
				{
					$this->$key = $val;
				}
			}
			else $mainframe->enqueueMessage(JText::_('COM_JOOMBLOG_SETTINGS_EMPTY'), 'notice');
			/**

			 */

			if ($this->useJomComment)
			{
				$db->setQuery("SELECT count(*) FROM #__extensions WHERE `element`='com_jomcomment'");
				$this->useJomComment = strval($db->loadResult());
			}

			if ($this->useDraganddrop)
			{
				$db->setQuery("SELECT count(*) FROM #__extensions WHERE `element`='com_joomdragdrop'");
				$this->useDraganddrop = strval($db->loadResult());
			}

			$this->replacements_array = array();
			$replacements = "ï¿½?|A, Ã‚|A, Ä‚|A, Ã„|A, Ä†|C, Ã‡|C, ÄŒ|C, ÄŽ|D, ï¿½?|D, Ã‰|E, ï¿½?|E, Ã‹|E, Äš|E, Ã?|I, ÃŽ|I, Ä¹|L, ï¿½?|N, Å‡|N, Ã“|O, Ã”|O, ï¿½?|O, Ã–|O, Å”|R, ï¿½?|R, Å |S, Åš|O, Å¤|T, Å®|U, Ãš|U, Å°|U, Ãœ|U, Ã?|Y, Å½|Z, Å¹|Z, Ã¡|a, Ã¢|a, ï¿½?|a, Ã¤|a, Ä‡|c, Ã§|c, Ä?|c, Ä?|d, Ä‘|d, Ã©|e, Ä™|e, Ã«|e, Ä›|e, Ã­|i, Ã®|i, Äº|l, Å„|n, ï¿½?|n, Ã³|o, Ã´|o, Å‘|o, Ã¶|o, Å¡|s, Å›|s, Å™|r, Å•|r, Å¥|t, Å¯|u, Ãº|u, Å±|u, Ã¼|u, Ã½|y, Å¾|z, Åº|z, Ë™|-, ÃŸ|ss, Ä„|A, Âµ|u, Ã¥|a, Ã…|A, Ã¦|ae, Ã†|AE, Å“|ce, Å’|CE, А|A, а|a, Б|B, б|b, В|V, в|v, Г|G, г|g, Д|D, д|d, Е|E, е|e, Ж|Zh, ж|zh, З|Z, з|z, И|I, и|i, Й|Y, й|y, К|K, к|k, Л|L, л|l, М|M, м|m, Н|N, н|n, О|O, о|o, П|P, п|p, Р|R, р|r, С|S, с|s, Т|T, т|t, У|U, у|u, Ф|F, ф|f, Х|Ch, х|ch, Ц|Ts, ц|ts, Ч|Ch, ч|ch, Ш|Sh, ш|sh, Щ|Sch, щ|sch, Ы|I, ы|i, Э|E, э|e, Ю|U, ю|iu, Я|Ya, я|ya, Ъ| , ъ| , Ь| , ь|";
			$items = explode(',', $replacements);
			foreach ($items as $item)
			{
				@list($src, $dst) = explode('|', trim($item));
				$this->replacements_array[trim($src)] = trim($dst);
			}

			if (empty($this->dateFormat))
			{
				$this->dateFormat = 'Y-m-d';
			}

		}

		function get($varname, $default = "0")
		{
			if (isset ($this->$varname))
			{
				return str_replace('%%', '$', $this->$varname);
			}
			else
			{
				return $default;
			}
		}

		function saveUsingClassVars()
		{
			$db = JFactory::getDBO();

			$this->_configString = "";
			$default_vars = get_object_vars($this);
			foreach ($default_vars as $name => $value)
			{
				if (substr($name, 0, 1) != "_")
					$this->_configString .= "\$$name=\"" . strval($value) . "\";\n";
			}
			$db->setQuery("INSERT INTO $this->_tableName SET value='$this->_configString',name='all' ON DUPLICATE KEY UPDATE value='$this->_configString'");
			$db->execute();
		}

		function save()
		{
			$db = JFactory::getDBO();
			$config = "";
			$this->_configString = "";
			$objvars = get_object_vars($this);

			$config = json_encode($objvars);

			jbClearCache();
			//$config = addslashes($config);
			$db->setQuery("UPDATE #__extensions SET params=" . $db->Quote($config) . " WHERE element='com_joomblog'");
			$db->execute();
		}

		function getReplacements()
		{
		}
	}
}