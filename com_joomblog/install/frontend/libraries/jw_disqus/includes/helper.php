<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JbDisqusHelper
{
	// Load Includes
	public static function loadHeadIncludes($headIncludes)
	{
		global $loadDisqusPluginIncludes;
		$document = JFactory::getDocument();
		if (!$loadDisqusPluginIncludes)
		{
			$loadDisqusPluginIncludes = 1;
			$document->addCustomTag($headIncludes);
		}
	}

	// Path overrides
	public static function getTemplatePath($pluginName, $file)
	{
		$mainframe = JFactory::getApplication();
		$p = new JObject;
		if (file_exists(JPATH_SITE . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $mainframe->getTemplate() . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file)))
		{
			$p->file = JPATH_SITE . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $mainframe->getTemplate() . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . $file;
			$p->http = JURI::base() . "templates/" . $mainframe->getTemplate() . "/html/{$pluginName}/{$file}";
		}
		else
		{
			$p->file = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomblog' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $file;
			$p->http = JURI::base() . "components/com_joomblog/libraries/{$pluginName}/tmpl/{$file}";
		}
		return $p;
	}
} // end class
