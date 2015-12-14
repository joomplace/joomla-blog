<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class TableComments extends JTable
{ 
	var $id =0;
	var $parentid =null;
	var $user_id =null;
	var $status =null;
	var $contentid =null;
	var $ip =null;
	var $name =null;
	var $title =null;
	var $comment =null;
	var $created =null;
	var $modified =null;
	var $modified_by =null;
	var $published =null;
	var $ordering =null;
	var $email=null;		
	
	function __construct(&$db)
	{
		parent::__construct('#__joomblog_comment','id', $db);
	}
} 
