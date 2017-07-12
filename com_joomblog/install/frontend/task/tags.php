<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once( JB_COM_PATH . DIRECTORY_SEPARATOR . 'task' . DIRECTORY_SEPARATOR . 'browse.base.php' );

class JbblogTagsTask extends JbblogBrowseBase
{
	function JbblogTagsTask()
	{
		parent::__construct();
		$this->toolbar = JB_TOOLBAR_HOME;
	}
	
	function setData()
	{
		$searchby = array(); 
		$jinput = JFactory::getApplication()->input;
		$category	= $jinput->get( 'category' , '' , 'REQUEST' );

		if( !empty( $category ) )
		{
			$category	= strval( urldecode( $category ) );
			$category	= str_replace("+", " ", $category);

			$searchby['category'] = $category;
		}
		
		$this->filters = $searchby;	
	}
	
}
