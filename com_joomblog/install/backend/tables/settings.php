<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.database.table');

class JoomBlogTableSettings extends JTable
{	
	function __construct(&$db) {
		parent::__construct('#__extensions', 'extension_id', $db);

	}
	
	protected function _getAssetName()
	{
		return 'com_joomblog';
	}
	
	public function bind($array, $ignore = '')
        {
               
                if (isset($array['rules']) && is_array($array['rules'])) {
                        $rules = new JRules($array['rules']);
                        $this->setRules($rules);
                }
                return parent::bind($array, $ignore);
        }
	
	function store()
	{
		$options = array();
		$db     = JFactory::getDBO();
		$jform = JRequest::getVar('jform', array(), '', '', JREQUEST_ALLOWRAW);

		if (sizeof($jform))
		{
			foreach ( $jform as $key => $jv ) 
			{
				if ($key!='rules')
				{
					$options[$key]=$jv;
				}
			}
		}
		//check jomsocial installed
        if ($jform['integrJoomSoc'] == "1")
        {                       
               
            try
	        {
	        	$query = "SELECT * FROM #__community_fields LIMIT 1";
	            $db->setQuery($query);
	            $community = $db->loadObject();      
	        }
           catch (RuntimeException $e)
           { 
               $options['integrJoomSoc'] = "0";
           }
        }

		$config		= JComponentHelper::getParams('com_joomblog');
        $saved_date = $config->get('curr_date');
		if ($saved_date) $options['curr_date'] = $saved_date;

		//Prepare rules values
		$check_array = array("1" =>"","6" =>"","7" =>"","2" =>"","3" =>"","4" =>"","5" =>"","8" =>"");
		$array_core_admin = array_diff($jform['rules']['core.admin'],$check_array);
		$array_core_manage = array_diff($jform['rules']['core.manage'],$check_array);
		$array_core_create = array_diff($jform['rules']['core.create'],$check_array);
		$array_core_delete = array_diff($jform['rules']['core.delete'],$check_array);
		$array_core_edit = array_diff($jform['rules']['core.edit'],$check_array);
		$array_core_editstate = array_diff($jform['rules']['core.edit.state'],$check_array);
		$array_core_editown = array_diff($jform['rules']['core.edit.own'],$check_array);
		$array_post = array_diff($jform['rules']['post'],$check_array);
		$rules_array_no_nulls = array("core.admin" =>$array_core_admin,"core.manage" =>$array_core_manage,"core.create" =>$array_core_create,"core.delete" =>$array_core_delete,"core.edit" =>$array_core_edit,"core.edit.state" =>$array_core_editstate,"core.edit.own" =>$array_core_editown,"post" =>$array_post);
		$extension_rules = json_encode($rules_array_no_nulls);
			
		if (!empty($extension_rules))
		{
			$db	=& JFactory::getDBO();
			$query	= "UPDATE `#__assets` SET `rules`=".$db->quote($extension_rules)." WHERE `name`='com_joomblog' AND `title`='com_joomblog'";
			$db->setQuery( $query );
			$db->execute();
		}	
		$this->params = json_encode($options);
		$query	= "SELECT `extension_id` FROM `#__extensions` WHERE `name`='com_joomblog' AND `element`='com_joomblog'";
		$db->setQuery( $query );
		$ext_id = $db->loadResult();
		$this->extension_id = $ext_id;

	 return parent::store();
	}
}
