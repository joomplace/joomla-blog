<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('JPATH_BASE') or die;

jimport('joomla.html.html.select');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');


class JFormFieldjbprivacy extends JFormFieldList
{

	protected $type = 'jbprivacy';


	protected function getInput()
	{
		$jinput = JFactory::getApplication()->input;
		$option = $jinput->get('option');
		// Initialize variables.
		$html = array();
		$attr = '';
		$this->multiple = true;

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}

		$jbtype = $this->element['jbtype']?$this->element['jbtype']:'posts';
		$isblog = $this->element['isblog']?$this->element['isblog']:0;
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		
		$config = JComponentHelper::getParams('com_joomblog');
		if ($config->get('integrJoomSoc') || $this->value==4)	
		{
			$attr .= ' onchange="checkselperm_'.$jbtype.'(this);"';
		}
		// Get the field options.
		
		$options = (array) $this->getOptions($isblog);
		$selected = (array)$this->getSelected($jbtype,$isblog);
		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true') {
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
		}
		// Create a regular list.
		else {
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $selected, $this->id);
		}
		
		if ($config->get('integrJoomSoc') || $this->value==4)	
		{
			$doc = JFactory::getDocument();
			ob_start();
			?>
				function checkselperm_<?php echo $jbtype;?>(ob)
				{
					if (ob.value==4)
					document.getElementById('jsgroups_<?php echo $jbtype;?>').style.display='block';
					else document.getElementById('jsgroups_<?php echo $jbtype;?>').style.display='none';
				}
			<?php
			$js = ob_get_contents();
			ob_end_clean();
			$groups = $this->getJSGroups($jbtype);
			if ($this->value==4) $st=''; else $st="display:none;";
			if ($groups)
			{
				$html[] = '<div id="jsgroups_'.$jbtype.'" style="'.$st.'float: left;">'.$groups.'</div>';	
			}else $html[] = '<div id="jsgroups_'.$jbtype.'" style="'.$st.'float: left;">'.JText::_('COM_JOOMBLOG_PRIVACY_NOMEMBER').'</div>';			
			$doc->addScriptDeclaration($js);
		}
		return implode($html);
	}
	
	protected function getSelected($type='posts',$isblog=0)
	{
		$jinput = JFactory::getApplication()->input;
		$id = $jinput->get('id');
		$selected =  $menuselected = array();
		
		if (sizeof($this->value)>0 && is_array($this->value))
		{
			foreach ( $this->value as $val ) 
			{
				$menuselected[] = $val;
			}
		}

		if ($id)
		{
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);

			$query->select($type);
			$query->from('#__joomblog_privacy');
			$query->where('postid='.(int)$id);
			$query->where('isblog='.$isblog);
			// Get the options.
			$db->setQuery($query);
			$selected = $db->loadColumn();
		}
		if (isset($selected[0]))$this->value = $selected[0];
		if (sizeof($selected)) $selected = array_merge($menuselected, $selected);
		return $selected;
	}
	
	protected function getJSGroups($jbtype)
	{
		$uid = JFactory::getUser()->id;
		if (!$uid) return null;
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('g.name as `text`, g.id as `value`');
		$query->from('#__community_groups AS `g`');
		$query->order('g.name','ASC');
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		$sel =0;		
		if ($this->value==4)
		{ if ($jbtype=='posts') $fld='jsviewgroup'; else $fld='jspostgroup'; 
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get('id');
			$query	= $db->getQuery(true);
			$query->select($fld);
			$query->from('#__joomblog_privacy');
			$query->where('`postid`='.(int)$id);
			$query->where('`isblog`=1');
			$db->setQuery($query);
			$sel = $db->loadResult();
		}
		
		if (sizeof($groups))
		 return JHtml::_('select.genericlist', $groups, 'jsgroups_'.$jbtype, '', 'value', 'text',$sel);
		else return null;
	}
	
	
	protected function getOptions($isblog)
	{
		$app = JFactory::getApplication();
  		$config = JComponentHelper::getParams('com_joomblog');
		$state = array();
		$state[] = JHTML::_('select.option', '0',  JText::_('COM_JOOMBLOG_PRIVACY_ALL') );
		$state[] = JHTML::_('select.option', '1',  JText::_('COM_JOOMBLOG_PRIVACY_MEMBERS'));
		if ($config->get('integrJoomSoc'))	
		{
			$state[] = JHTML::_('select.option', '2',  JText::_('COM_JOOMBLOG_PRIVACY_FRIENDS'));
			if ($isblog)$state[] = JHTML::_('select.option', '4',  JText::_('COM_JOOMBLOG_PRIVACY_GMEMBERS'));
		}
		$state[] = JHTML::_('select.option', '3', JText::_('COM_JOOMBLOG_PRIVACY_ONLYME'));
		return $state;
	}
}