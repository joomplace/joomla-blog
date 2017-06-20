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

class JbblogTagTask extends JbblogBrowseBase
{
	var $category;
	
	function __construct()
	{
		parent::__construct();
		$this->toolbar = JB_TOOLBAR_HOME;
	}
	
	
	function _header()
	{
		echo parent::_header();
		$jinput = JFactory::getApplication()->input;
        $category	= $jinput->get( 'tag' , '' );
        if(!$category){
            $category = $jinput->get( 'category' , '' );
        }
		
				
		if(is_numeric($category)){
			$this->category = intval(urldecode( $category ));
	
			jbAddPathway( JText::_('COM_JOOMBLOG_SHOW_CATEGORIES_TITLE') , JRoute::_('index.php?option=com_joomblog&task=categorylist&Itemid='.jbGetItemId()));
			
			jbAddPageTitle(htmlspecialchars(jbGetJoomlaCategoryName($this->category)));
			
			jbAddPathway(htmlspecialchars(jbGetJoomlaCategoryName($this->category)));
		}else{
			$this->category = strval(urldecode( $category ) );
			$this->category = str_replace("+", " ", $category);

			jbAddPathway( JText::_('COM_JOOMBLOG_SHOW_TAGS_TITLE') , JRoute::_('index.php?option=com_joomblog&task=tagslist&Itemid='.jbGetItemId()));
			
			jbAddPageTitle(htmlspecialchars(jbGetTagName($this->category)));

			jbAddPathway(htmlspecialchars(jbGetTagName($this->category)));
		}
	}
	
	function setData()
	{
		$searchby = array();
    	$jinput = JFactory::getApplication()->input;
		$category	= $jinput->get( 'category' , '' , 'REQUEST' );
		$view = $jinput->get( 'view' , '' , 'GET' );
		if (isset($view) && $view == 'category')
		{
            $menu = JFactory::getApplication()->getMenu();
			$item   = $menu->getActive();
			$params   = $menu->getParams($item->id);
			$category = intval($params->get('category'));
		}
		
		if( !empty( $category ) && is_numeric($category)){
			$searchby['jcategory'] = $category;
		}else{
			$searchby['category'] = $this->category;
			$searchby['category'] = str_replace('-and-', '&', $searchby['category']);
			$searchby['category'] = str_replace(' and ', '&', $searchby['category']);
		}
		
		$this->filters = $searchby;	
	}
	
}
