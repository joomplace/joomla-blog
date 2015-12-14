<?php

/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

define("SITE_ROOT_PATH", JPATH_ROOT);

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');

class com_joomblogInstallerScript
{
	protected $newVersion;

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent)
	{
		$db = JFactory::getDBO();
		$db->setQuery("DELETE FROM #__menu WHERE path = 'joomblog' AND type = 'component' AND client_id = 1 AND menutype = 'main' AND link LIKE 'index.php?option=com_joomblog%' ");
		$db->execute();
		echo '<p>Component JoomBlog successfully uninstalled</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent)
	{

	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		$pathes = $files = array();

		$pathes[] = SITE_ROOT_PATH . "/administrator/components/com_joomblog/controllers";
		$pathes[] = SITE_ROOT_PATH . "/administrator/components/com_joomblog/helpers";
		$pathes[] = SITE_ROOT_PATH . "/administrator/components/com_joomblog/images";
		$pathes[] = SITE_ROOT_PATH . "/administrator/components/com_joomblog/install";
		$pathes[] = SITE_ROOT_PATH . "/administrator/components/com_joomblog/models";
		$pathes[] = SITE_ROOT_PATH . "/administrator/components/com_joomblog/views";
		$pathes[] = SITE_ROOT_PATH . "/components/com_joomblog/css";
		$pathes[] = SITE_ROOT_PATH . "/components/com_joomblog/js";
		$pathes[] = SITE_ROOT_PATH . "/components/com_joomblog/libraries";
		$pathes[] = SITE_ROOT_PATH . "/components/com_joomblog/tables";
		$pathes[] = SITE_ROOT_PATH . "/components/com_joomblog/task";
		$pathes[] = SITE_ROOT_PATH . "/components/com_joomblog/templates";
		$pathes[] = SITE_ROOT_PATH . "/components/com_joomblog/views";

		if (sizeof($pathes))
			foreach ($pathes as $path)
			{
				if (JFolder::exists($path)) JFolder::delete($path);
			}


		$files[] = SITE_ROOT_PATH . "/administrator/components/com_joomblog/admin.joomblog.html.php";
		$files[] = SITE_ROOT_PATH . "/administrator/components/com_joomblog/admin.joomblog.php";
		$files[] = SITE_ROOT_PATH . "/administrator/components/com_joomblog/toolbar.joomblog.php";

		$files[] = SITE_ROOT_PATH . "/components/com_joomblog/defines.joomblog.php";
		$files[] = SITE_ROOT_PATH . "/components/com_joomblog/functions.joomblog.php";
		$files[] = SITE_ROOT_PATH . "/components/com_joomblog/joomblog.php";
		$files[] = SITE_ROOT_PATH . "/components/com_joomblog/template.php";

		if (sizeof($files))
			foreach ($files as $file)
			{
				if (is_file($file)) JFile::delete($file);
			}

	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		$mainframe = JFactory::getApplication();

		require_once(JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomblog' . DIRECTORY_SEPARATOR . 'install.sql.php');

		$this->_createAvatarFolder();
		$this->_FixMenuLinks();
		$this->_ExtractFiles();
		$this->_UpdateAssetCategories();
		$this->_UpdateDBkeys();
		//Component settings fixing
		if ($type == 'update')
		{
			$database = JFactory::getDBO();
			$query = "SELECT `params` FROM `#__extensions` WHERE `name`='com_joomblog' AND `element`='com_joomblog'";
			$database->setQuery($query);
			$old_extension_params = $database->loadResult();
			if ($old_extension_params)
			{
				$old_params_array = json_decode($old_extension_params, true);
			}
			$new_params['mainBlogTitle'] = (isset($old_params_array['mainBlogTitle'])) ? $old_params_array['mainBlogTitle'] : 'JoomBlog';
			$new_params['mainBlogDesc'] = (isset($old_params_array['mainBlogDesc'])) ? $old_params_array['mainBlogDesc'] : 'My first blog';
			$new_params['BloggerRecentPosts'] = (isset($old_params_array['BloggerRecentPosts'])) ? $old_params_array['BloggerRecentPosts'] : '5';
			$new_params['CategoriesRecentPosts'] = (isset($old_params_array['CategoriesRecentPosts'])) ? $old_params_array['CategoriesRecentPosts'] : '5';
			$new_params['allowDefaultTags'] = (isset($old_params_array['allowDefaultTags'])) ? $old_params_array['allowDefaultTags'] : '0';
			$new_params['enableUserCreateTags'] = (isset($old_params_array['enableUserCreateTags'])) ? $old_params_array['enableUserCreateTags'] : '0';
			$new_params['ShowUntaggedWithoutTags'] = (isset($old_params_array['ShowUntaggedWithoutTags'])) ? $old_params_array['ShowUntaggedWithoutTags'] : '1';
			$new_params['useComment'] = (isset($old_params_array['useComment'])) ? $old_params_array['useComment'] : '1';
			$new_params['allowModerateComment'] = (isset($old_params_array['allowModerateComment'])) ? $old_params_array['allowModerateComment'] : '0';
			$new_params['recaptcha_publickey'] = (isset($old_params_array['recaptcha_publickey'])) ? $old_params_array['recaptcha_publickey'] : '';
			$new_params['recaptcha_privatekey'] = (isset($old_params_array['recaptcha_privatekey'])) ? $old_params_array['recaptcha_privatekey'] : '';
			$new_params['useCommentCaptcha'] = (isset($old_params_array['useCommentCaptcha'])) ? $old_params_array['useCommentCaptcha'] : '1';
			$new_params['notifyCommentAdmin'] = (isset($old_params_array['notifyCommentAdmin'])) ? $old_params_array['notifyCommentAdmin'] : '0';
			$new_params['notifyCommentAuthor'] = (isset($old_params_array['notifyCommentAuthor'])) ? $old_params_array['notifyCommentAuthor'] : '0';
			$new_params['useCommentCaptchaRegisteredUsers'] = (isset($old_params_array['useCommentCaptchaRegisteredUsers'])) ? $old_params_array['useCommentCaptchaRegisteredUsers'] : '1';
			$new_params['limitComment'] = (isset($old_params_array['limitComment'])) ? $old_params_array['limitComment'] : '10';
			$new_params['disqusSubDomain'] = (isset($old_params_array['disqusSubDomain'])) ? $old_params_array['disqusSubDomain'] : '';
			$new_params['useRSSFeed'] = (isset($old_params_array['useRSSFeed'])) ? $old_params_array['useRSSFeed'] : '0';
			$new_params['rssFeedLimit'] = (isset($old_params_array['rssFeedLimit'])) ? $old_params_array['rssFeedLimit'] : '10';
			$new_params['titleFeed'] = (isset($old_params_array['titleFeed'])) ? $old_params_array['titleFeed'] : 'Subscribe to feed';
			$new_params['useFeedBurnerIntegration'] = (isset($old_params_array['useFeedBurnerIntegration'])) ? $old_params_array['useFeedBurnerIntegration'] : '0';
			$new_params['rssFeedBurnerLabel'] = (isset($old_params_array['rssFeedBurnerLabel'])) ? $old_params_array['rssFeedBurnerLabel'] : JUri::root() . 'index.php?option=com_joomblog&task=rss';
			$new_params['rssFeedBurner'] = (isset($old_params_array['rssFeedBurner'])) ? $old_params_array['rssFeedBurner'] : 'Paste link to your FeedBurner here...';
			$new_params['allowHTMLinRSS'] = (isset($old_params_array['allowHTMLinRSS'])) ? $old_params_array['allowHTMLinRSS'] : '0';
			$new_params['allowNotification'] = (isset($old_params_array['allowNotification'])) ? $old_params_array['allowNotification'] : '0';
			$new_params['adminEmail'] = (isset($old_params_array['adminEmail'])) ? $old_params_array['adminEmail'] : '';
			$new_params['integrJoomSoc'] = (isset($old_params_array['integrJoomSoc'])) ? $old_params_array['integrJoomSoc'] : '0';
			$new_params['numEntry'] = (isset($old_params_array['numEntry'])) ? $old_params_array['numEntry'] : '10';
			$new_params['categoryDisplay'] = (isset($old_params_array['categoryDisplay'])) ? $old_params_array['categoryDisplay'] : '1';
			$new_params['frontpageToolbar'] = (isset($old_params_array['frontpageToolbar'])) ? $old_params_array['frontpageToolbar'] : '1';
			$new_params['showPrimaryTitles'] = (isset($old_params_array['showPrimaryTitles'])) ? $old_params_array['showPrimaryTitles'] : '1';
			$new_params['dateFormat'] = (isset($old_params_array['dateFormat'])) ? $old_params_array['dateFormat'] : 'Y-m-d H:i:s';
			$new_params['enableBackLink'] = (isset($old_params_array['enableBackLink'])) ? $old_params_array['enableBackLink'] : '1';
			$new_params['enablePrintLink'] = (isset($old_params_array['enablePrintLink'])) ? $old_params_array['enablePrintLink'] : '1';
			$new_params['modulesDisplay'] = (isset($old_params_array['modulesDisplay'])) ? $old_params_array['modulesDisplay'] : '0';
			$new_params['mambotFrontpage'] = (isset($old_params_array['mambotFrontpage'])) ? $old_params_array['mambotFrontpage'] : '0';
			$new_params['showBlogTitle'] = (isset($old_params_array['showBlogTitle'])) ? $old_params_array['showBlogTitle'] : '1';
			$new_params['showRandomPost'] = (isset($old_params_array['showRandomPost'])) ? $old_params_array['showRandomPost'] : '1';
			$new_params['avatar'] = (isset($old_params_array['avatar'])) ? $old_params_array['avatar'] : 'juser';
			$new_params['showUserLink'] = (isset($old_params_array['showUserLink'])) ? $old_params_array['showUserLink'] : '1';
			$new_params['useFullName'] = (isset($old_params_array['useFullName'])) ? $old_params_array['useFullName'] : '1';
			$new_params['avatarWidth'] = (isset($old_params_array['avatarWidth'])) ? $old_params_array['avatarWidth'] : '64';
			$new_params['avatarHeight'] = (isset($old_params_array['avatarHeight'])) ? $old_params_array['avatarHeight'] : '64';
			$new_params['maxFileSize'] = (isset($old_params_array['maxFileSize'])) ? $old_params_array['maxFileSize'] : '2';
			$new_params['linkAvatar'] = (isset($old_params_array['linkAvatar'])) ? $old_params_array['linkAvatar'] : '0';
			$new_params['showAsAuthors'] = (isset($old_params_array['showAsAuthors'])) ? $old_params_array['showAsAuthors'] : '1';
			$new_params['useIntrotext'] = (isset($old_params_array['useIntrotext'])) ? $old_params_array['useIntrotext'] : '1';
			$new_params['autoReadmorePCount'] = (isset($old_params_array['autoReadmorePCount'])) ? $old_params_array['autoReadmorePCount'] : '3';
			$new_params['readMoreLink'] = (isset($old_params_array['readMoreLink'])) ? $old_params_array['readMoreLink'] : 'read more ...';
			$new_params['disableReadMoreTag'] = (isset($old_params_array['disableReadMoreTag'])) ? $old_params_array['disableReadMoreTag'] : '0';
			$new_params['necessaryReadmore'] = (isset($old_params_array['necessaryReadmore'])) ? $old_params_array['necessaryReadmore'] : '1';
			$new_params['anchorReadmore'] = (isset($old_params_array['anchorReadmore'])) ? $old_params_array['anchorReadmore'] : '1';
			$new_params['autoapproveblogs'] = (isset($old_params_array['autoapproveblogs'])) ? $old_params_array['autoapproveblogs'] : '1';
			$new_params['defaultPublishStatus'] = (isset($old_params_array['defaultPublishStatus'])) ? $old_params_array['defaultPublishStatus'] : '1';
			$new_params['useMCEeditor'] = (isset($old_params_array['useMCEeditor'])) ? $old_params_array['useMCEeditor'] : '1';
			$new_params['showIcons'] = (isset($old_params_array['showIcons'])) ? $old_params_array['showIcons'] : '1';
			$new_params['showTabs'] = (isset($old_params_array['showTabs'])) ? $old_params_array['showTabs'] : '1';
			$new_params['showPanel'] = (isset($old_params_array['showPanel'])) ? $old_params_array['showPanel'] : '1';
			$new_params['firstPositionT'] = (isset($old_params_array['firstPositionT'])) ? $old_params_array['firstPositionT'] : '1';
			$new_params['secondPositionT'] = (isset($old_params_array['secondPositionT'])) ? $old_params_array['secondPositionT'] : '2';
			$new_params['thirdPositionT'] = (isset($old_params_array['thirdPositionT'])) ? $old_params_array['thirdPositionT'] : '3';
			$new_params['fourthPositionT'] = (isset($old_params_array['fourthPositionT'])) ? $old_params_array['fourthPositionT'] : '4';
			$new_params['showPosts'] = (isset($old_params_array['showPosts'])) ? $old_params_array['showPosts'] : '1';
			$new_params['showBlogs'] = (isset($old_params_array['showBlogs'])) ? $old_params_array['showBlogs'] : '1';
			$new_params['showBloggers'] = (isset($old_params_array['showBloggers'])) ? $old_params_array['showBloggers'] : '1';
			$new_params['showCategories'] = (isset($old_params_array['showCategories'])) ? $old_params_array['showCategories'] : '1';
			$new_params['firstPositionI'] = (isset($old_params_array['firstPositionI'])) ? $old_params_array['firstPositionI'] : '1';
			$new_params['secondPositionI'] = (isset($old_params_array['secondPositionI'])) ? $old_params_array['secondPositionI'] : '2';
			$new_params['thirdPositionI'] = (isset($old_params_array['thirdPositionI'])) ? $old_params_array['thirdPositionI'] : '3';
			$new_params['showSearch'] = (isset($old_params_array['showSearch'])) ? $old_params_array['showSearch'] : '1';
			$new_params['showArchive'] = (isset($old_params_array['showArchive'])) ? $old_params_array['showArchive'] : '1';
			$new_params['showTags'] = (isset($old_params_array['showTags'])) ? $old_params_array['showTags'] : '1';
			$new_params['titlePosts'] = (isset($old_params_array['titlePosts'])) ? $old_params_array['titlePosts'] : 'Posts';
			$new_params['titleBlogs'] = (isset($old_params_array['titleBlogs'])) ? $old_params_array['titleBlogs'] : 'Blogs';
			$new_params['titleSearch'] = (isset($old_params_array['titleSearch'])) ? $old_params_array['titleSearch'] : 'Search';
			$new_params['titleBloggers'] = (isset($old_params_array['titleBloggers'])) ? $old_params_array['titleBloggers'] : 'Bloggers';
			$new_params['titleCategories'] = (isset($old_params_array['titleCategories'])) ? $old_params_array['titleCategories'] : 'Categories';
			$new_params['titleArchive'] = (isset($old_params_array['titleArchive'])) ? $old_params_array['titleArchive'] : 'Archive';
			$new_params['titleTags'] = (isset($old_params_array['titleTags'])) ? $old_params_array['titleTags'] : 'Tags';
			$new_params['usetwitter'] = (isset($old_params_array['usetwitter'])) ? $old_params_array['usetwitter'] : '0';
			$new_params['twitterlang'] = (isset($old_params_array['twitterlang'])) ? $old_params_array['twitterlang'] : 'en';
			$new_params['twitterName'] = (isset($old_params_array['twitterName'])) ? $old_params_array['twitterName'] : '';
			$new_params['showtwitterInList'] = (isset($old_params_array['showtwitterInList'])) ? $old_params_array['showtwitterInList'] : '0';
			$new_params['twitterliststyle'] = (isset($old_params_array['twitterliststyle'])) ? $old_params_array['twitterliststyle'] : 'none';
			$new_params['twitterfollowliststyle'] = (isset($old_params_array['twitterfollowliststyle'])) ? $old_params_array['twitterfollowliststyle'] : 'none';
			$new_params['positiontwitterInList'] = (isset($old_params_array['positiontwitterInList'])) ? $old_params_array['positiontwitterInList'] : 'bottom';
			$new_params['showtwitterInPost'] = (isset($old_params_array['showtwitterInPost'])) ? $old_params_array['showtwitterInPost'] : '0';
			$new_params['twitterpoststyle'] = (isset($old_params_array['twitterpoststyle'])) ? $old_params_array['twitterpoststyle'] : 'none';
			$new_params['twitterfollowpoststyle'] = (isset($old_params_array['twitterfollowpoststyle'])) ? $old_params_array['twitterfollowpoststyle'] : 'none';
			$new_params['positiontwitterInPost'] = (isset($old_params_array['positiontwitterInPost'])) ? $old_params_array['positiontwitterInPost'] : 'top';
			$new_params['usefacebook'] = (isset($old_params_array['usefacebook'])) ? $old_params_array['usefacebook'] : '0';
			$new_params['fb_sendbutton'] = (isset($old_params_array['fb_sendbutton'])) ? $old_params_array['fb_sendbutton'] : '0';
			$new_params['fbwidth'] = (isset($old_params_array['fbwidth'])) ? $old_params_array['fbwidth'] : '50';
			$new_params['fbadmin'] = (isset($old_params_array['fbadmin'])) ? $old_params_array['fbadmin'] : '';
			$new_params['fbappid'] = (isset($old_params_array['fbappid'])) ? $old_params_array['fbappid'] : '';
			$new_params['page_image'] = (isset($old_params_array['page_image'])) ? $old_params_array['page_image'] : '';
			$new_params['usefacebookrecommendbar'] = (isset($old_params_array['usefacebookrecommendbar'])) ? $old_params_array['usefacebookrecommendbar'] : '0';
			$new_params['facebookrecommendbar_trigger'] = (isset($old_params_array['facebookrecommendbar_trigger'])) ? $old_params_array['facebookrecommendbar_trigger'] : '0';
			$new_params['facebookrecommendbar_xtrigger'] = (isset($old_params_array['facebookrecommendbar_xtrigger'])) ? $old_params_array['facebookrecommendbar_xtrigger'] : '30';
			$new_params['facebookrecommendbar_readtime'] = (isset($old_params_array['facebookrecommendbar_readtime'])) ? $old_params_array['facebookrecommendbar_readtime'] : '30';
			$new_params['facebookrecommendbar_num'] = (isset($old_params_array['facebookrecommendbar_num'])) ? $old_params_array['facebookrecommendbar_num'] : '3';
			$new_params['showfbInList'] = (isset($old_params_array['showfbInList'])) ? $old_params_array['showfbInList'] : '0';
			$new_params['fb_style_list'] = (isset($old_params_array['fb_style_list'])) ? $old_params_array['fb_style_list'] : 'none';
			$new_params['positionfbInList'] = (isset($old_params_array['positionfbInList'])) ? $old_params_array['positionfbInList'] : 'bottom';
			$new_params['showfbInPost'] = (isset($old_params_array['showfbInPost'])) ? $old_params_array['showfbInPost'] : '1';
			$new_params['fb_style_post'] = (isset($old_params_array['fb_style_post'])) ? $old_params_array['fb_style_post'] : 'none';
			$new_params['positionfbInPost'] = (isset($old_params_array['positionfbInPost'])) ? $old_params_array['positionfbInPost'] : 'top';
			$new_params['usegp'] = (isset($old_params_array['usegp'])) ? $old_params_array['usegp'] : '0';
			$new_params['gp_language'] = (isset($old_params_array['gp_language'])) ? $old_params_array['gp_language'] : 'en';
			$new_params['showgpInList'] = (isset($old_params_array['showgpInList'])) ? $old_params_array['showgpInList'] : '0';
			$new_params['gp_style_list'] = (isset($old_params_array['gp_style_list'])) ? $old_params_array['gp_style_list'] : 'none';
			$new_params['positiongpInList'] = (isset($old_params_array['positiongpInList'])) ? $old_params_array['positiongpInList'] : 'bottom';
			$new_params['showgpInPost'] = (isset($old_params_array['showgpInPost'])) ? $old_params_array['showgpInPost'] : '0';
			$new_params['gp_style_post'] = (isset($old_params_array['gp_style_post'])) ? $old_params_array['gp_style_post'] : 'none';
			$new_params['positiongpInPost'] = (isset($old_params_array['positiongpInPost'])) ? $old_params_array['positiongpInPost'] : 'top';
			$new_params['usepi'] = (isset($old_params_array['usepi'])) ? $old_params_array['usepi'] : '0';
			$new_params['pi_language'] = (isset($old_params_array['pi_language'])) ? $old_params_array['pi_language'] : 'en';
			$new_params['showpiInList'] = (isset($old_params_array['showpiInList'])) ? $old_params_array['showpiInList'] : '0';
			$new_params['pi_style_list'] = (isset($old_params_array['pi_style_list'])) ? $old_params_array['pi_style_list'] : 'none';
			$new_params['positionpiInList'] = (isset($old_params_array['positionpiInList'])) ? $old_params_array['positionpiInList'] : 'bottom';
			$new_params['showpiInPost'] = (isset($old_params_array['showpiInPost'])) ? $old_params_array['showpiInPost'] : '0';
			$new_params['pi_style_post'] = (isset($old_params_array['pi_style_post'])) ? $old_params_array['pi_style_post'] : 'none';
			$new_params['positionpiInPost'] = (isset($old_params_array['positionpiInPost'])) ? $old_params_array['positionpiInPost'] : 'top';
			$new_params['useln'] = (isset($old_params_array['useln'])) ? $old_params_array['useln'] : '0';
			$new_params['showlnInList'] = (isset($old_params_array['showlnInList'])) ? $old_params_array['showlnInList'] : '0';
			$new_params['ln_style_list'] = (isset($old_params_array['ln_style_list'])) ? $old_params_array['ln_style_list'] : 'none';
			$new_params['positionlnInList'] = (isset($old_params_array['positionlnInList'])) ? $old_params_array['positionlnInList'] : 'bottom';
			$new_params['showlnInPost'] = (isset($old_params_array['showlnInPost'])) ? $old_params_array['showlnInPost'] : '0';
			$new_params['ln_style_post'] = (isset($old_params_array['ln_style_post'])) ? $old_params_array['ln_style_post'] : 'none';
			$new_params['positionlnInPost'] = (isset($old_params_array['positionlnInPost'])) ? $old_params_array['positionlnInPost'] : 'top';
			$new_params['useAddThis'] = (isset($old_params_array['useAddThis'])) ? $old_params_array['useAddThis'] : '0';
			$new_params['addThisName'] = (isset($old_params_array['addThisName'])) ? $old_params_array['addThisName'] : '';
			$new_params['addthis_language'] = (isset($old_params_array['addthis_language'])) ? $old_params_array['addthis_language'] : 'en';
			$new_params['showAddThisInList'] = (isset($old_params_array['showAddThisInList'])) ? $old_params_array['showAddThisInList'] : '0';
			$new_params['addthis_list_button_style'] = (isset($old_params_array['addthis_list_button_style'])) ? $old_params_array['addthis_list_button_style'] : 'style1';
			$new_params['addThisListPosition'] = (isset($old_params_array['addThisListPosition'])) ? $old_params_array['addThisListPosition'] : 'bottom';
			$new_params['showAddThisInPost'] = (isset($old_params_array['showAddThisInPost'])) ? $old_params_array['showAddThisInPost'] : '0';
			$new_params['addthis_post_button_style'] = (isset($old_params_array['addthis_post_button_style'])) ? $old_params_array['addthis_post_button_style'] : 'style1';
			$new_params['addThisPostPosition'] = (isset($old_params_array['addThisPostPosition'])) ? $old_params_array['addThisPostPosition'] : 'bottom';
			$new_params['disallowedPosters'] = (isset($old_params_array['disallowedPosters'])) ? $old_params_array['disallowedPosters'] : '';
			$new_params['allowedPosters'] = (isset($old_params_array['allowedPosters'])) ? $old_params_array['allowedPosters'] : '';
			$new_params['allowedPublishers'] = (isset($old_params_array['allowedPublishers'])) ? $old_params_array['allowedPublishers'] : '';
			$new_params['viewIntro'] = (isset($old_params_array['viewIntro'])) ? $old_params_array['viewIntro'] : '1';
			$new_params['viewEntry'] = (isset($old_params_array['viewEntry'])) ? $old_params_array['viewEntry'] : '1';
			$new_params['viewComments'] = (isset($old_params_array['viewComments'])) ? $old_params_array['viewComments'] : '1';
			$new_params_json = json_encode($new_params);
			$query = "UPDATE `#__extensions` SET `params`='" . $new_params_json . "' WHERE `name`='com_joomblog' AND `element`='com_joomblog'";
			$database->setQuery($query);
			$database->execute();

		}

		if ($type == 'install')
		{
			$database = JFactory::getDBO();
			$uri = JUri::root();
			$curr_date = date("Y-m-d", strtotime("-2 months"));
			$extension_params = '{"mainBlogTitle":"JoomBlog","mainBlogDesc":"My first blog","BloggerRecentPosts":"5","CategoriesRecentPosts":"5","allowDefaultTags":"0","enableUserCreateTags":"0","ShowUntaggedWithoutTags":"1","useComment":"1","allowModerateComment":"0","recaptcha_publickey":"","recaptcha_privatekey":"","useCommentCaptcha":"1","notifyCommentAdmin":"0","notifyCommentAuthor":"0","useCommentCaptchaRegisteredUsers":"1","useCommentOnlyRegisteredUsers":"0","limitComment":"10","disqusSubDomain":"","useRSSFeed":"0","rssFeedLimit":"10","titleFeed":"Subscribe to feed","useFeedBurnerIntegration":"0","rssFeedBurnerLabel":"'.$uri.'index.php?option=com_joomblog&task=rss","rssFeedBurner":"Paste link to your FeedBurner here...","allowHTMLinRSS":"0","allowNotification":"0","adminEmail":"","integrJoomSoc":"0","numEntry":"10","categoryDisplay":"1","frontpageToolbar":"1","showPrimaryTitles":"1","dateFormat":"Y-m-d H:i:s","enableBackLink":"1","enablePrintLink":"1","modulesDisplay":"0","mambotFrontpage":"0","showBlogTitle":"1","showRandomPost":"1","avatar":"juser","showUserLink":"1","useFullName":"1","avatarWidth":"64","avatarHeight":"64","maxFileSize":"2","linkAvatar":"0","showAsAuthors":"1","useIntrotext":"1","autoReadmorePCount":"3","readMoreLink":"read more ...","disableReadMoreTag":"0","necessaryReadmore":"1","anchorReadmore":"1","autoapproveblogs":"1","defaultPublishStatus":"1","useMCEeditor":"1","showIcons":"1","showTabs":"1","showPanel":"1","firstPositionT":"1","secondPositionT":"2","thirdPositionT":"3","fourthPositionT":"4","showPosts":"1","showBlogs":"1","showBloggers":"1","showCategories":"1","firstPositionI":"1","secondPositionI":"2","thirdPositionI":"3","showSearch":"1","showArchive":"1","showTags":"1","titlePosts":"Posts","titleBlogs":"Blogs","titleSearch":"Search","titleBloggers":"Bloggers","titleCategories":"Categories","titleArchive":"Archive","titleTags":"Tags","usetwitter":"0","twitterlang":"en","twitterName":"joomplace","showtwitterInList":"0","twitterliststyle":"none","twitterfollowliststyle":"none","positiontwitterInList":"bottom","showtwitterInPost":"0","twitterpoststyle":"none","twitterfollowpoststyle":"none","positiontwitterInPost":"top","usefacebook":"0","fb_sendbutton":"0","fbwidth":"50","fbadmin":"","fbappid":"","page_image":"","usefacebookrecommendbar":"0","facebookrecommendbar_trigger":"0","facebookrecommendbar_xtrigger":"30","facebookrecommendbar_readtime":"30","facebookrecommendbar_num":"3","showfbInList":"0","fb_style_list":"none","positionfbInList":"bottom","showfbInPost":"1","fb_style_post":"none","positionfbInPost":"top","usegp":"0","gp_language":"en","showgpInList":"0","gp_style_list":"none","positiongpInList":"bottom","showgpInPost":"0","gp_style_post":"none","positiongpInPost":"top","usepi":"0","pi_language":"en","showpiInList":"0","pi_style_list":"none","positionpiInList":"bottom","showpiInPost":"0","pi_style_post":"none","positionpiInPost":"top","useln":"0","showlnInList":"0","ln_style_list":"none","positionlnInList":"bottom","showlnInPost":"0","ln_style_post":"none","positionlnInPost":"top","useAddThis":"0","addThisName":"","addthis_language":"en","showAddThisInList":"0","addthis_list_button_style":"style1","addThisListPosition":"bottom","showAddThisInPost":"0","addthis_post_button_style":"style1","addThisPostPosition":"bottom","disallowedPosters":"","allowedPosters":"","allowedPublishers":"","viewIntro":"1","viewEntry":"1","viewComments":"1","curr_date":"' . $curr_date . '"}';
			$query = "UPDATE `#__extensions` SET `params`='" . $extension_params . "' WHERE `name`='com_joomblog' AND `element`='com_joomblog'";
			$database->setQuery($query);
			$database->execute();
		}
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		$this->newVersion = '1.3.0 (build 001)';
?>
		<div class="well">
			<style type="text/css">
				.nav-pills li:hover a {
					background-color: #0088CC;
					color: #fff;
				}
			</style>

			<div style="clear:both; font-size:1.8em; color:#55AA55;">JoomBlog component successfully installed.</div>
			<div style="margin:6px 0 0 0; clear:both; font-size:1.0em; color:#000000;">Version:<?php echo ' ' . $this->newVersion; ?></div>

			<br/>

			<div style="background-color:#ffffff; text-align:left; font-size:16px; font-weight:400; line-height:18px;border-radius:5px; padding:7px;">
				<img style="" src="<?php echo JURI::root(); ?>administrator/components/com_joomblog/assets/images/tick.png">
				<span style="margin:0 0 0 8px; font-weight:bold;">Helpfull links:</span>
			</div>

			<div style="font-size:1.2em;padding-left: 20px; padding-top: 10px;">
				<ul class="nav nav-pills nav-stacked">
					<li><a href="<?php echo JURI::root(); ?>administrator/index.php?option=com_joomblog&view=sampledata">Install Sample Data</a></li>
					<li><a target="_blank" href="http://www.joomplace.com/video-tutorials-and-documentation/joomblog/">Component's help</a></li>
					<li><a href="http://www.joomplace.com/forum/joomla-components/joomblog.html" target="_blank">Support forum</a></li>
					<li><a href="http://www.joomplace.com/helpdesk/ticket_submit.php" target="_blank">Submit request to our technicians</a></li>
				</ul>
			</div>

			<div style="background-color:#ffffff; text-align:left; font-size:16px; font-weight:400; line-height:18px;border-radius:5px; padding:7px;">
				<img src="<?php echo JURI::root(); ?>administrator/components/com_joomblog/assets/images/tick.png">
				<div style="display: inline-block; margin:0 0 0 8px; font-weight:bold;">Say your "Thank you" to Joomla community</div>
			</div>

			<div style="font-size:1.0em; padding: 10px">
				<strong>Say your "Thank you" to Joomla community</strong> for WonderFull Joomla CMS and <strong>help it</strong> by sharing your experience with this component. It will only take 1 min for registration on 
				<a href="http://extensions.joomla.org" target="_blank">http://extensions.joomla.org</a> and 3 minutes to write a useful review! A lot of people will thank you!<br/>
				<a href="http://extensions.joomla.org/extensions/authoring-a-content/blog/16108" target="_blank">
					<img src="<?php echo JURI::root(); ?>administrator/components/com_joomblog/assets/images/rate_us.png" title="Rate us!" style="margin:10px 0 0 0;" />
				</a>
			</div>
		</div>
<?php
	}

	function _UpdateAssetCategories()
	{
		$db = JFactory::getDBO();

		$query = "SELECT * FROM #__assets  WHERE name = 'com_joomblog' ";
		$db->setQuery($query);
		$com = $db->loadObject();

		$cat1 = array();
		if (is_object($com))
		{
			$query = "SELECT c.* FROM #__categories AS c, #__assets AS a WHERE c.asset_id = a.id AND c.extension = 'com_joomblog' AND  a.parent_id <> " . $com->id;
			$db->setQuery($query);
			$cat1 = $db->loadObjectList();
		}

		if (!empty($cat1))
		{
			foreach ($cat1 as $value)
			{
				$db->setQuery("UPDATE #__assets SET parent_id = " . $com->id . " WHERE name = 'com_joomblog.category." . $value->id . "' ");
				$db->execute();
			}
		}


	}

	function _UpdateDBkeys()
	{

		$keysSQL = array(
			'ALTER TABLE `#__joomblog_multicats` ADD INDEX ( `aid` )',
			'ALTER TABLE `#__joomblog_multicats` ADD INDEX ( `cid` )',
			'ALTER TABLE `#__joomblog_blogs` ADD INDEX ( `content_id` )',
			'ALTER TABLE `#__joomblog_blogs` ADD INDEX ( `blog_id` )',

			'ALTER TABLE `#__joomblog_privacy` ADD INDEX ( `postid` )',
			'ALTER TABLE `#__joomblog_privacy` ADD INDEX ( `isblog` )',
			'ALTER TABLE `#__joomblog_privacy` ADD INDEX ( `posts` )',
			'ALTER TABLE `#__joomblog_content_tags` ADD INDEX ( `tag` )',
		);

		$db = JFactory::getDBO();

		foreach ($keysSQL as $sql)
		{
			$db->setQuery($sql);
			$db->execute();
		}

	}

	function _ExtractFiles()
	{
		JArchive::extract(SITE_ROOT_PATH . "/administrator/components/com_joomblog/install/frontend.zip", SITE_ROOT_PATH . "/components/com_joomblog/");
		JArchive::extract(SITE_ROOT_PATH . "/administrator/components/com_joomblog/install/backend.zip", SITE_ROOT_PATH . "/administrator/components/com_joomblog/");
	}

	function _FixMenuLinks()
	{
		$db = JFactory::getDBO();

		$query = "SELECT `extension_id` FROM #__extensions WHERE `type` = 'component' AND `element`='com_joomblog'";
		$db->setQuery($query);

		$comid = $db->loadResult();
		$query = "UPDATE #__menu SET `component_id`='$comid' WHERE `link` LIKE 'index.php?option=com_joomblog%'";
		$db->setQuery($query);
		$db->execute();
	}

	function _createAvatarFolder()
	{


		$joomblog_folder = JPATH_ROOT . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'joomblog';
		if (!JFolder::exists($joomblog_folder))
		{
			if (!JFolder::create($joomblog_folder, 0755))
			{
				echo JString::str_ireplace("'", "\\'", JText::_('Can not create images/joomblog folder'));
				jexit();
			}
		}

		$avatar_folder = $joomblog_folder . DIRECTORY_SEPARATOR . 'avatar';
		if (!JFolder::exists($avatar_folder))
		{
			if (!JFolder::create($avatar_folder, 0755))
			{
				echo JString::str_ireplace("'", "\\'", JText::_('Can not create images/joomblog folder'));
				jexit();
			}
		}

	}
}

?>