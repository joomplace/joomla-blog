<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('text');

class JFormFieldDatepicker extends JFormFieldText
{
	public function getInput()
	{
		$doc = JFactory::getDocument();
		$doc->addStyleSheet('components/com_joomblog/assets/css/datepicker.css');
		$doc->addScript('components/com_joomblog/assets/js/datepicker.js');
		$doc->addScriptDeclaration('window.addEvent("domready", function() {
			new DatePicker("#'.(string)$this->id.'", {
				pickerClass: "datepicker_vista",
				positionOffset: {x: -10, y: -175},
				timePicker: true,
				format: "Y-m-d H:i:s",
				inputOutputFormat: "Y-m-d H:i:s"
			});
		});');
		
		return parent::getInput();
	}
	
	public function getValue()
	{
		if (!isset($this->value)) {
			$config = JFactory::getConfig();
			$user	= JFactory::getUser();
			$date = JFactory::getDate('NOW', 'UTC');
			$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));
			$this->value = $date->toSql(true);
		}
	}
}
