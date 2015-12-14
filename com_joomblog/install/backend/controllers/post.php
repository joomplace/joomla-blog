<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controllerform');

class JoomBlogControllerPost extends JControllerForm
{
    protected function allowEdit($data = array(), $key = 'id')
    {
        $id = (int)isset($data[$key]) ? $data[$key] : 0;
        $user = JFactory::getUser();

        if ($user->authorise('core.edit', 'com_joomblog.article.' . $id)) {
            return true;
        }

        if ($user->authorise('core.edit.own', 'com_joomblog.article.' . $id)) {
            $oid = (int)isset($data['created_by']) ? $data['created_by'] : 0;
            if (empty($oid) && $id) {
                $post = $this->getModel()->getItem($id);

                if (empty($post)) {
                    return false;
                }

                $oid = $post->created_by;
            }

            if ($oid == $user->get('id')) {
                return true;
            }
        }

        return parent::allowEdit($data, $key);
    }
}
