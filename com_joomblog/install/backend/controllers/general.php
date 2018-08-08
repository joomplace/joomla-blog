<?php defined('_JEXEC') or die('Restricted access');
/*
* JoomBlog Component
* @package JoomBlog
* @author JoomPlace Team
* @copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

class JoomBlogControllerGeneral extends JControllerLegacy
{
	//----------------------------------------------------------------------------------------------------
	public function get_latest_news()
	{
		require_once(JPATH_COMPONENT_ADMINISTRATOR."/helpers/Snoopy.php");
		require_once(JPATH_COMPONENT_ADMINISTRATOR."/helpers/MethodsForXml.php");
		
		// Making request.
		
		$snoopy = new Snoopy();
		$snoopy->read_timeout = 10;
		$snoopy->referer = JURI::root();
		@$snoopy->fetch("http://www.joomplace.com/news_check/componentNewsCheck.php?component=joomblog");
		
		$error = $snoopy->error;
		$status = $snoopy->status;
		
		$content = $snoopy->results;
		
		// Returning data.
		
		@ob_clean();
		header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-Type: text/xml; charset=utf-8');
		
		$xml = array();
		
		$xml[] = "<\x3fxml version=\"1.0\" encoding=\"UTF-8\"\x3f>";
		$xml[] = '<root>';
		$xml[] = 	'<error>' . MethodsForXml::XmlEncode($error) . '</error>';
		$xml[] = 	'<status>' . MethodsForXml::XmlEncode($status) . '</status>';
		$xml[] = 	'<content>' . MethodsForXml::XmlEncode($content) . '</content>';
		$xml[] = '</root>';
		
		print(implode("", $xml));
		
		jexit();
	}
	//----------------------------------------------------------------------------------------------------
	public function show_changelog()
	{
		@ob_clean;
		header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-Type: text/html; charset=utf-8');
		
		jimport ('joomla.filesystem.file');
		
		echo '<h2>' . JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_CHANGELOG') . '</h2>';
		
		if (!JFile::exists(JPATH_COMPONENT_ADMINISTRATOR.'/changelog.txt'))
		{
			echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_CHANGELOG_NO_FILE');
		}
		else
		{
			echo '<pre style="font-size:12px;">';
			echo 	JFile::read(JPATH_COMPONENT_ADMINISTRATOR.'/changelog.txt');
			echo '</pre>';
		}
		
		jexit();
	}
}