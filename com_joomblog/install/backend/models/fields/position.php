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

class JFormFieldPosition extends JFormField
{
	function getInput() {
		$input = '';
		
		$arr = array(
			JHTML::_('select.option', 1, 'Posts' ),
			JHTML::_('select.option', 2, 'Blogs' ),
			JHTML::_('select.option', 3, 'Search' ),
			JHTML::_('select.option', 4, 'Add Blog' ),
			JHTML::_('select.option', 5, 'Profile' ),
			JHTML::_('select.option', 6, 'Add Post' ),
			JHTML::_('select.option', 7, 'Bloggers' ),
			JHTML::_('select.option', 8, 'Categories' ),
			JHTML::_('select.option', 9, 'Archive' ),
			JHTML::_('select.option', 10, 'Tags' )
		);
		$input .= JHTML::_('select.genericlist', $arr, $this->name, null, 'value', 'text', $this->value);

		return $input;
	}
}
