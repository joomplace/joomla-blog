<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;

// For security reasons use build in content model class

jimport('joomla.application.component.modeladmin');

class JoomBlogModelPost extends JModelAdmin
{
    protected $context = 'com_joomblog';

	protected function canDelete($data, $key = 'id') {
		return JFactory::getUser()->authorise('core.delete', 'com_joomblog.article.'.((int) isset($data->$key) ? $data->$key : 0));
		return true;
	}

	public function getTable($type = 'Posts', $prefix = 'Table', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ( !empty($item->custom_metatags) && is_string($item->custom_metatags) )
			$item->custom_metatags = unserialize( $item->custom_metatags );

		return $item;
	}

	public function getForm($data = array(), $loadData = false) 
	{
		$form = $this->loadForm('com_joomblog.post', 'post', array('control' => 'jform', 'load_data' => $loadData));
		$item = $this->getItem();

		$form->setFieldAttribute('catid', 'extension', 'com_joomblog');
		
		if (empty($item->id)) {
			$app = JFactory::getApplication();
			$item->set('catid', $app->getUserStateFromRequest('com_joomblog.filter.category_id', 'filter_author_id'));
			$item->set('blog_id', $app->getUserStateFromRequest('com_joomblog.filter.blog_id', 'filter_author_id'));
			$item->set('created_by', $app->getUserStateFromRequest('com_joomblog.filter.author_id', 'filter_author_id'));
		}
		if(JFactory::getApplication()->input->get('error', false)){
		    $form->bind(JFactory::getApplication()->getUserState('com_joomblog.write.form'));
		}else{
		    $form->bind($item);
		}

		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	public function getRandomItem($exclude = 0){

	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);
	    $query->select('DISTINCT(a.id)');
	    $query->from('#__joomblog_posts AS a');
	    if (!in_array('8', JFactory::getUser()->getAuthorisedGroups())) {
		$query->join('LEFT', '#__joomblog_blogs AS b ON b.content_id = a.id');
		$query->join('LEFT', '#__joomblog_list_blogs AS lb ON lb.id = b.blog_id');
		$query->join('LEFT', '#__joomblog_multicats AS mc ON mc.aid = a.id');
		$query->join('LEFT', '#__categories AS c ON c.id = mc.cid');

		$user	= JFactory::getUser();
		$user_id = (int)$user->id;
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		if(!JComponentHelper::getParams('com_joomblog')->get('integrJoomSoc', false)){
		    $query->where('(a.access IN ('.$groups.') OR a.created_by='.$user_id.')');
		    $query->where('(c.access IN ('.$groups.'))');
		    $query->where('(lb.access IN ('.$groups.') OR lb.user_id='.$user_id.')');
		}else{
		    $userJSGroups = JbblogBaseController::getJSGroups($user->id);
		    $userJSFriends = JbblogBaseController::getJSFriends($user->id);
		    if(count($userJSGroups)>0){
			$tmpQ1 = ' OR (a.access=-4 AND a.access_gr IN ('.implode(',', $userJSGroups).')) ';
			$tmpQ2 = ' OR (lb.access=-4 AND lb.access_gr IN ('.implode(',', $userJSGroups).')) ';
		    }else{
			$tmpQ1 = ' ';
			$tmpQ2 = '';
		    }
		    if(count($userJSFriends)>0){
			$tmpQ11 = ' OR (a.access=-2 AND a.created_by IN ('.implode(',', $userJSFriends).')) ';
			$tmpQ22 = ' OR (lb.access=-2 AND lb.user_id IN ('.implode(',', $userJSFriends).')) ';
		    }else{
			$tmpQ11 = ' ';
			$tmpQ22 = '';
		    }
		    $query->where('(a.access IN ('.$groups.') 
			    OR a.created_by='.$user_id.' '.$tmpQ1.' '.$tmpQ11.' )');
		    $query->where('(c.access IN ('.$groups.'))');
		    $query->where('(lb.access IN ('.$groups.') 
			    OR lb.user_id='.$user_id.' '.$tmpQ2.' '.$tmpQ22.')');
		}
	    }
	    $query->where('a.state = 1');
	    if($exclude > 0) $query->where('a.id != '.$exclude);
	    $query->order('RAND()');
	    $db->setQuery($query, 0, 1);
	    $result = $db->loadColumn();


	    if($result) return $result;
	    else return false;
	}
}
?>
