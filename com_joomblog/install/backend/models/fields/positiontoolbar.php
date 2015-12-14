<?php

/**
* JoomPortfolio component for Joomla 3.0
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldPositiontoolbar extends JFormField
{
	function getInput() {
		$input = '';
		
		$arr = array(
			JHTML::_('select.option', 1, 'Posts' ),
			JHTML::_('select.option', 2, 'Blogs' ),
			JHTML::_('select.option', 3, 'Bloggers' ),
			JHTML::_('select.option', 4, 'Categories' )
		);
		$input .= JHTML::_('select.genericlist', $arr, $this->name, null, 'value', 'text', $this->value);

		return $input;
	}
}
