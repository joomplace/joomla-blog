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

class JoomBlogTableBlog extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__joomblog_list_blogs', 'id', $db);
	}
	
	protected function _getAssetName()
        {
                $k = $this->_tbl_key;
                return 'com_joomblog.blog.'.(int) $this->$k;
        }

        protected function _getAssetTitle()
        {
                return $this->title;
        }

        protected function _getAssetParentId( JTable $table = null, $id = null)
        {
               $assetId = null;
               $asset = JTable::getInstance('Asset');
               $asset->loadByName('com_joomblog');
                return $asset->id; 
        }
       


        public function bind($array, $ignore = '')
        {
                if (isset($array['params']) && is_array($array['params'])) {
                        $registry = new JRegistry();
                        $registry->loadArray($array['params']);
                        $array['params'] = (string)$registry;
                }

                if (isset($array['metadata']) && is_array($array['metadata'])) {
                        $registry = new JRegistry();
                        $registry->loadArray($array['metadata']);
                        $array['metadata'] = (string)$registry;
                }
               
                if (isset($array['rules']) && is_array($array['rules'])) {
                        $rules = new JAccessRules($array['rules']);
                        $this->setRules($rules);
                }
                return parent::bind($array, $ignore);
        }

	public function delete($pk = null)
	{
		$db = $this->getDBO();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__joomblog_blogs');
		$query->where('blog_id='.$pk);
		
		$db->setQuery($query);
		$ids = $db->loadObjectList();

		if ($ids) {
			$content = JTable::getInstance('Post', 'JoomBlogTable', array());
			foreach ($ids as $id) {
				$content->delete($id->content_id);
			}
		}
		
		return parent::delete($pk);
	}
	
	public function store($updateNulls = false)
	{
		$jform = JFactory::getApplication()->input->get('jform','','array');
			$id = $jform['id'];
			$db = $this->getDBO();
		if ($id>0)
		{	
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__joomblog_list_blogs');
			$query->where('id='.$id);
			$db->setQuery($query);
			$th = $db->loadObject();
				if ($th->approved==0 && $jform['approved']==1)
				{
					$conf = JoomBlogHelper::getParams();
					if ($conf->allowNotification)
					{
						
					
					
					$author = JFactory::getUser($th->user_id);
							$usrmail = $author->email;
								$config	= JFactory::getConfig();
								$fromname	= $config->get('fromname');
								$mailfrom	= $config->get('mailfrom');
								$sitename	= $config->get('sitename');
			
								$subject = stripslashes(JText::_('COM_JOOMBLOG_MAIL_SUBJECT_APPROVED'));
								$message = nl2br(sprintf(stripslashes(JText::_('COM_JOOMBLOG_MAIL_NEW_MESSAGE_APPROVED')), JURI::root(), $th->title, $th->description));
								JUtility::sendMail($mailfrom, $fromname, $usrmail, $subject, $message, 1);

								
					}		
						
				}
		}
		$this->alias = JFilterOutput::stringURLSafe($this->title);
		if (!$this->create_date) $this->create_date = date('Y-m-d H:i:s');
		if (!$this->create_date=='0000-00-00 00:00:00') $this->create_date = date('Y-m-d H:i:s');
		if (!$this->user_id) $this->user_id = JFactory::getUser()->id;
		
		if (parent::store($updateNulls)) 
		{
		if ($this->id)
			{
				$db	= JFactory::getDBO();
				$jform = JRequest::getVar('jform');
				if($jform['access'] == -4){
				    $js_group = (int)$jform['access_gr'];
				    $query	= "UPDATE `#__joomblog_list_blogs` SET access_gr=$js_group WHERE `id`='".$this->id."' ";
				    $db->setQuery( $query );
				    $db->execute();
				}
				if($jform['waccess'] == -4){
				    $js_group = (int)$jform['waccess_gr'];
				    $query	= "UPDATE `#__joomblog_list_blogs` SET waccess_gr=$js_group WHERE `id`='".$this->id."' ";
				    $db->setQuery( $query );
				    $db->execute();
				}
			}
			$this->reorder();

			return true;
		}
		return false;
	}
	
	
	public function publish_approve($pks = null, $state = 1, $userId = 0)
		{
			// Initialise variables.
			$k = $this->_tbl_key;
	
			// Sanitize input.
			JArrayHelper::toInteger($pks);
			$userId = (int) $userId;
			$state  = (int) $state;
	
			// If there are no primary keys set check to see if the instance key is set.
			if (empty($pks)) {
				if ($this->$k) {
					$pks = array($this->$k);
				}
				// Nothing to set publishing state on, return false.
				else {
					$e = new JException(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
					$this->setError($e);
	
					return false;
				}
			}
	
			if (sizeof($pks) && $state==1)
			{
				foreach ( $pks as $idblog ) 
				{
					if ($this->approved==0)
					{
						$author = JFactory::getUser($this->user_id);
						$usrmail = $author->email;
							$config	= JFactory::getConfig();
							$fromname	= $config->get('fromname');
							$mailfrom	= $config->get('mailfrom');
							$sitename	= $config->get('sitename');
		
							$subject = stripslashes(JText::_('COM_JOOMBLOG_MAIL_SUBJECT_APPROVED'));
							$message = nl2br(sprintf(stripslashes(JText::_('COM_JOOMBLOG_MAIL_NEW_MESSAGE_APPROVED')), JURI::root(), $this->title, $this->description));
							JUtility::sendMail($mailfrom, $fromname, $usrmail, $subject, $message, 1);

					}
				}
			}
			
			// Update the publishing state for rows with the given primary keys.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('approved = '.(int) $state);
	
			// Determine if there is checkin support for the table.
			if (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time')) {
				$query->where('(checked_out = 0 OR checked_out = '.(int) $userId.')');
				$checkin = true;
			}
			else {
				$checkin = false;
			}
	
			// Build the WHERE clause for the primary keys.
			$query->where($k.' = '.implode(' OR '.$k.' = ', $pks));
	
			$this->_db->setQuery($query);
	
			// Check for a database error.
			if (!$this->_db->execute()) {
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_PUBLISH_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
	
				return false;
			}
	
			// If checkin is supported and all rows were adjusted, check them in.
			if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
				// Checkin the rows.
				foreach($pks as $pk)
				{
					$this->checkin($pk);
				}
			}
	
			// If the JTable instance value is in the list of primary keys that were set, set the instance.
			if (in_array($this->$k, $pks)) {
				$this->approved = $state;
			}
	
			$this->setError('');
			return true;
		}

}
