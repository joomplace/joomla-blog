<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldAccessLevelJS extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'AccessLevelJS';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string   The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize variables.
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get the field options.
		$options = $this->getOptions();
		if ($this->value =='' AND $this->name!='jform[waccess]') $this->value =1;
		if ($this->value =='' AND $this->name =='jform[waccess]') $this->value =0;
		return $this->level($this->name, $this->value, $attr, $options, $this->id);
	}
	
	public function level($name, $selected, $attribs = '', $params = true, $id = false)
	{
		$db = JFactory::getDbo();
		
		$subhtml = '';

		$query = $db->getQuery(true);
		$query->select('a.id AS value, a.title AS text');
		$query->from('#__viewlevels AS a');
		$query->group('a.id, a.title, a.ordering');
		$query->order('a.ordering ASC');
		$query->order($query->qn('title') . ' ASC');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}
		// If params is an array, push these options to the array
		if (is_array($params))
		{
			$options = array_merge($params, $options);
		}
		// If all levels is allowed, push it into the array.
		elseif ($params)
		{
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOPTION_ACCESS_SHOW_ALL_LEVELS')));
		}
		array_push($options, JHtml::_('select.option', '0', JText::_('COM_JOOMBLOG_PRIVACY_ONLYME')));
		$config = JComponentHelper::getParams('com_joomblog');
		if ($config->get('integrJoomSoc') || $selected==-4)	
		{
			$attribs .= ' onchange="accesscheckselperm'.$this->element['name'].'(this);"';
			$doc = JFactory::getDocument();
			ob_start();
			?>
				function accesscheckselperm<?php echo $this->element['name'];?>(ob)
				{
					if (ob.value==-4)
					document.getElementById('jsgroups_<?php echo $this->element['name'];?>').style.display='block';
					else document.getElementById('jsgroups_<?php echo $this->element['name'];?>').style.display='none';
				}
			<?php
			$js = ob_get_contents();
			ob_end_clean();
			$groups = $this->getJSGroups();
			if ($this->value==-4) $st=''; else $st="display:none;";
			if ($groups)
			{
				$subhtml = '<div id="jsgroups_'.$this->element['name'].'" style="'.$st.'float: left;">'.$groups.'</div><br style="clear:both;" />';	
			}else $subhtml = '<div id="jsgroups_'.$this->element['name'].'" style="'.$st.'float: left;">'.JText::_('COM_JOOMBLOG_PRIVACY_NOMEMBER').'</div><br style="clear:both;" />';			
			$doc->addScriptDeclaration($js);
			array_push($options,  JHTML::_('select.option', '-2',  JText::_('COM_JOOMBLOG_PRIVACY_FRIENDS')));
			array_push($options,  JHTML::_('select.option', '-4',  JText::_('COM_JOOMBLOG_PRIVACY_GMEMBERS')));
			//$state[] = JHTML::_('select.option', '2',  JText::_('COM_JOOMBLOG_PRIVACY_FRIENDS'));
			//if ($isblog)$state[] = JHTML::_('select.option', '4',  JText::_('COM_JOOMBLOG_PRIVACY_GMEMBERS'));
		}

		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array(
				'list.attr' => $attribs,
				'list.select' => $selected,
				'id' => $id
			)
		).$subhtml;
	}
	
	protected function getJSGroups()
	{
		$uid = JFactory::getUser()->id;
		if (!$uid) return null;
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('g.name as `text`, g.id as `value`');
		$query->from('#__community_groups AS `g`');
		//$query->join('INNER','#__community_groups_members AS `m` ON m.groupid=g.id');
		//$query->where('m.memberid='.(int)$uid);
		$query->order('g.name','ASC');
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		$sel =0;	
		if($this->value==-4){
		    $dbField = $this->element['name'];
		    $dbField = $dbField.'_gr';
		    $id = JRequest::getInt('id');
		    $query	= $db->getQuery(true);
		    $query->select($db->quoteName($dbField));
		    switch ($this->element['jbtype']){
			case 'blog':
			    $query->from('#__joomblog_list_blogs');
			    break;
			case 'post':
			default:
			    $query->from('#__joomblog_posts');
			    break;
		    }
		    $query->where('`id`='.(int)$id);
		    $db->setQuery($query);
		    $sel = $db->loadResult();
		}
		
		$name = str_replace(']', '_gr]', $this->name);
		if(strpos($name, '_gr') === false) $name .= '_gr';

		if (sizeof($groups))

		return JHtml::_('select.genericlist', $groups, $name, array('list.select' => $sel, 'list.attr' => ' class="jb-selectlist jb-small-select"'));
		else return null;
	}
}
