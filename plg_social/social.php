<?php
/**
* JoomBlog Image Plugin for Joomla
* @version $Id: social.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage social.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/ 

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Editor Social buton
 *
 * @package		Joomla.Plugin
 * @subpackage	Editors-xtd.social
 * @since 1.5
 */
class plgButtonSocial extends JPlugin
{
	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	
	public function onDisplay($name)
	{
		$app = JFactory::getApplication();

		$doc		= JFactory::getDocument();
		$template	= $app->getTemplate();

		$js = "
			function insertSocial(editor) {
					jInsertEditorText('{social}', editor);
			}
			";

		$doc->addScriptDeclaration($js);

		$button = new JObject;
		$button->set('modal', false);
		$button->set('onclick', 'insertSocial(\''.$name.'\');return false;');
		$button->set('text', JText::_('Social'));
		$button->set('class', 'btn btn-small');
		$button->set('name', 'blank');
		$button->set('link', '#');

		return $button;
	}
}
