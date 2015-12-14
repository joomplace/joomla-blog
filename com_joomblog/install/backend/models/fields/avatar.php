<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldAvatar extends JFormField
{
	function getInput() {
		$input = '';
		
		$input .= '<div style="float:left;" >';
		if ($this->value) {
			$input .= '<p style="float:none;" ><img src="'.JURI::root().'images/joomblog/avatar/thumb_'.$this->value.'" alt="avatar" /></p>';
		} else {
			$input .= '<p style="float:none;" ><img src="'.JURI::root().'administrator/components/com_joomblog/assets/images/user.png" alt="avatar" /></p>';
		}
		$input .= '<p style="float:none;" ><input type="file" name="avatarFile" /></p>';
		$input .= '<p style="float:none;" ><input style="float:left" type="checkbox" name="resetAvatar" id="resetAvatar" value="1" /><label style="width:100px; margin-left:5px; float:left" for="resetAvatar">Reset Avatar</label></p>';
		$input .= '</div>';
		
		return $input;
	}
}
