<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @subpackage pagination.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.html.pagination' );

class JBPagination extends JPagination{

	private $params;
	
	public function __construct($total, $limitstart, $limit, $prefix = '') {
	    global $_JB_CONFIGURATION;
	    $this->params = $_JB_CONFIGURATION;
	    parent::__construct($total, $limitstart, $limit, $prefix = '');
	}
	
	protected function _list_render($list)
	{
	    JHtml::script(JURI::base().'components/com_joomblog/assets/paginator3000/paginator3000.js');
	    JHtml::stylesheet(JURI::base().'components/com_joomblog/assets/paginator3000/paginator3000.css');
	    $this->set('pages.start', 1);
	    $this->set('pages.stop', $this->get('pages.total'));
	    $data = $this->_buildDataObject();
	    $list['pages'] = array(); //make sure it exists
	    foreach ($data->pages as $i => $page)
	    {
		    if ($page->base !== null)
		    {
			    $list['pages'][$i]['active'] = true;
			    $list['pages'][$i]['data'] = $this->_item_active($page);
		    }
		    else
		    {
			    $list['pages'][$i]['active'] = false;
			    $list['pages'][$i]['data'] = $this->_item_inactive($page);
		    }
	    }
	    $js = '';
	    $i = 0;
	    foreach ($list['pages'] as $page)
	    {
		    $js .= "paginatorCustomPages.push('".$page['data']."'); \n";
		    $i++;
	    }
	    $script = '
		var paginatorCustomPages = [];
		'.$js;
	    JFactory::getDocument()->addScriptDeclaration($script);

	    $html = '';
	    
	    $html .= '	<div class="paginator" id="paginator"></div>
			<div class="paginator_pages">'.JText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $this->get('pages.current'), $this->get('pages.total')).'</div>
			<script type="text/javascript">
				pag = new Paginator("paginator", '.$this->get('pages.total').', 10, '.$this->get('pages.current').', "");
			</script>';
	    return $html;
	}
	
	public function _item_active(JPaginationObject $item) {
	    if(JFactory::getApplication()->get('searchPagination', false)){
		if ($item->base > 0)
		{
			return "<a class=\"pagenav\" href=\"#\" title=\"" . $item->text . "\" onclick=\"document.searchForm.limitstart.value=" . $item->base
				. "; document.searchForm.submit();return false;\">" . $item->text . "</a>";
		}
		else
		{
			return "<a class=\"pagenav\" href=\"#\" title=\"" . $item->text . "\" onclick=\"document.searchForm.limitstart.value=0; document.searchForm.submit();return false;\">" . $item->text . "</a>";
		}
	    }else{
		return parent::_item_active($item);
	    }
	}
}