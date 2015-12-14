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
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of categories
 *
 */
class JFormFieldCategoryPost extends JFormFieldList
{
	/**
	 * @var		string	The form field type.
	 */
	public $type = 'CategoryPost';

	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();
		$selected = (array)$this->getSelected();
		if(is_array($this->value)){
		    foreach($this->value as $sel){
			if(!in_array($sel, $selected)) $selected[] = $sel;
		    }
		}

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true') {
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $selected, $this->id);
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
		}
		// Create a regular list.
		else {
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $selected, $this->id);
		}

		return implode($html);
	}
	
	protected function getSelected()
	{
		$jinput = JFactory::getApplication()->input;
		$id = $jinput->get('id');
		$selected = array();
		if ($id)
		{
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);

			$query->select('cid');
			$query->from('#__joomblog_multicats');
			$query->where('aid='.(int)$id);
			$db->setQuery($query);
			
			$selected = $db->loadColumn();
		}
		return $selected;
	}
	
	/**
	 * Method to get the field options.
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$options	= array();
		$extension	= $this->element['extension'] ? (string) $this->element['extension'] : (string) $this->element['scope'];
		$published	= (string) $this->element['published'];

		// Load the category options for a given extension.
		if (!empty($extension)) {

			// Filter over published state or not depending upon if it is present.
			if ($published) {
				$options = JHtml::_('category.options', $extension, array('filter.published' => explode(',', $published)));
			}
			else {
				$options = JHtml::_('category.options', $extension);
			}

			// Verify permissions.  If the action attribute is set, then we scan the options.
			if ($action	= (string) $this->element['action']) {
				// Get the current user object.
				$user = JFactory::getUser();
			
				foreach($options as $i => $option)
				{
					if ($user->authorise('core.create', $extension.'.category.'.$option->value) != true ) {
						unset($options[$i]);
					}
				}
				
			}

			if (isset($this->element['show_root'])) {
				array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
			}
		}
		else {
			JError::raiseWarning(500, JText::_('JLIB_FORM_ERROR_FIELDS_CATEGORY_ERROR_EXTENSION_EMPTY'));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}