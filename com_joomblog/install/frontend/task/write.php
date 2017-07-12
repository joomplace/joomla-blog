<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JbblogWriteTask extends JbBlogBaseController
{

	function display()
	{
		global $_JB_CONFIGURATION, $Itemid;

		$mainframe = JFactory::getApplication();
		$my = JFactory::getUser();
		$doc = JFactory::getDocument();

		$pathway = $mainframe->getPathway();

		$tpl = new JoomblogTemplate();
		$jinput = JFactory::getApplication()->input;
		$id = $jinput->get('id', '', 'GET');
		$postid = $jinput->get('id', 0, 'POST');

		if (!empty($postid))
		{
			$id = $postid;
		}

		$row = JTable::getInstance('Posts', 'Table');
		$row->load($id);
		if (JFactory::getApplication()->input->get('error', false))
		{
			$row->bind(JFactory::getApplication()->getUserState('com_joomblog.write.form'));
		}

		$isNew = true;

		if ($id)
		{
			$isNew = false;
		}
		if ($isNew && !jbGetUserCanPost())
		{
			$mainframe->redirect($_SERVER['HTTP_REFERER'], JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_POST'));
			return;
		}

		if (!$isNew && (!$my->authorise('core.edit', 'com_joomblog.article.' . $id)))
		{
			if ($row->created_by == $my->get('id') && (!$my->authorise('core.edit.own', 'com_joomblog.article.' . $id)))
			{
				$mainframe->redirect($_SERVER['HTTP_REFERER'], JText::_('COM_JOOMBLOG_BLOG_ADMIN_NO_PERMISSIONS_TO_POST'));
				return;
			}
		}

		if ($isNew)
		{
			$pathway->addItem(JText::_('COM_JOOMBLOG_NEW_ENTRIES'), '');
			jbAddPageTitle(JText::_('COM_JOOMBLOG_NEW_ENTRIES'));
		}
		else
		{
			$pathway->addItem($row->title, JRoute::_('index.php?option=com_joomblog&show=' . $row->id . '&Itemid=' . $Itemid));
			$pathway->addItem(JText::_('COM_JOOMBLOG_EDIT_ENTRIES'), '');
			jbAddPageTitle(JText::_('COM_JOOMBLOG_EDIT_ENTRIES'));
		}

		if (!jbGetUserCanPublish())
		{
			$row->state = 0;
		}

		$userCreateTag = (boolean) $_JB_CONFIGURATION->get('enableUserCreateTags');

		$userImageUpload = (boolean) $_JB_CONFIGURATION->get('useImageUpload');

		$my = JFactory::getUser();

		if ($my->id == '42')
		{
			$userCreateTag = true;
		}

		$db = JFactory::getDBO();
		if ($row->id != 0)
		{
			$query = "SELECT b.name FROM #__joomblog_content_tags AS a, #__joomblog_tags AS b "
				. "WHERE b.id=a.tag AND a.contentid='{$row->id}' ";
			$db->setQuery($query);

			$arr = $db->loadColumn();

			if ($arr)
			{
				$tags = $arr;
//                $doc->addScriptDeclaration('jQuery(".tm-input").tagsManager({
//                      prefilled: ["'.implode('", "', $tags).'"]
//                    });
//                    jQuery(".tm-input").typeahead({
//                      name: "tags",
//                      prefetch: "/ajax/tags/json"
//                    }).on("typeahead:selected", function (e, d) {
//
//                      tagApi.tagsManager("pushTag", d.value);
//
//                    });
//                    ');
			}
			else
			{
				$tags = "";
			}
		}
		elseif ($_JB_CONFIGURATION->get('allowDefaultTags'))
		{
			$query = "SELECT t.name FROM #__joomblog_tags as t WHERE t.default = 1 ";
			$db->setQuery($query);

			$arr = $db->loadColumn();
			if ($arr)
			{
				$tags = $arr;
				$doc->addScriptDeclaration('jQuery(".tm-input").tagsManager({
                      prefilled: ["' . implode('", "', $tags) . '"]
                    });');
			}
			else
			{
				$tags = "";
			}
		}

		if (!empty($row->created))
		{
			$date = JFactory::getDate($row->created);
		}
		else
		{
			$date = JFactory::getDate();
		}

		$jcDashboard = false;
		$enableDashboard = $_JB_CONFIGURATION->get('enableJCDashboard');
		$enableJC = $_JB_CONFIGURATION->get('useComment');
		$jcFile = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jomcomment' . DIRECTORY_SEPARATOR . 'jomcomment.php';

		if ($enableDashboard && $enableJC && file_exists($jcFile))
			$jcDashboard = true;

		$validation_msg = array();
		$message = "";
		$jinput = JFactory::getApplication()->input;
		$saving = $jinput->get('saving', '', 'POST');

		$readmoreTag = '<hr id="system-readmore" />';
		$readmore = (!empty($row->introtext) && !empty($row->fulltext)) ? $readmoreTag : '';
		$row->fulltext = $row->introtext . $readmore . $row->fulltext;

		$jcState = 'disabled';
		if ($jcDashboard)
		{
			if (stristr($row->fulltext, '{jomcomment}') !== false)
			{
				$jcState = 'enabled';
				$row->fulltext = str_replace('{jomcomment}', '', $row->fulltext);
			}
			else if (stristr($row->fulltext, '{!jomcomment}') !== false)
			{
				$jcState = 'disabled';
				$row->fulltext = str_replace('{!jomcomment}', '', $row->fulltext);
			}
			else
			{
				$jcState = 'default';
			}
		}
		$query = "SELECT `blog_id` FROM #__joomblog_blogs WHERE content_id = '" . (int) $id . "' ";
		$db->setQuery($query);
		$blogid = $db->loadResult();

		$tpl->set('blogid', $blogid);
		$tpl->set('imageUpload', $userImageUpload);
		$tpl->set('userCreateTag', $userCreateTag);
		$tpl->set('jcState', $jcState);
		$tpl->set('jcDashboard', $jcDashboard);
		$tpl->set('validation_msg', $validation_msg);
		$tpl->set('date', $date->format('Y-m-d H:i:s'));
		$tpl->set('publishRights', jbGetUserCanPublish());
		$tpl->set('publishStatus', true);
		$tpl->set('disableReadMoreTag', $_JB_CONFIGURATION->get('disableReadMoreTag'));
		$db->setQuery('SELECT publish_up FROM #__joomblog_posts WHERE id=' . $id);
		$publish_up = $db->loadResult();
		if ($publish_up == '')
		{
			$date = JFactory::getDate();
			$publish_up = $date->Format('Y-m-d H:i:s');

		}

		$db->setQuery('SELECT publish_down FROM #__joomblog_posts WHERE id=' . $id);
		$publish_down = $db->loadResult();
		$tpl->set('publish_up', $publish_up);
		$tpl->set('publish_down', $publish_down);


		$row->id = intval($row->id);
		if ($row->id == 0)
			$row->state = $_JB_CONFIGURATION->get('defaultPublishStatus');
		$meta_array = json_decode($row->metadata);
		if (empty($meta_array))
		{
			$meta_array = new stdClass();
			$meta_array->metakey = '';
			$meta_array->metadesc = '';
			$meta_array->page_image = '';
			$meta_array->ogdesc = '';
		}
		($meta_array->metakey !== '') ? $tpl->set('metakey', $meta_array->metakey) : $tpl->set('metakey', $row->metakey);
		($meta_array->metadesc !== '') ? $tpl->set('metadesc', $meta_array->metadesc) : $tpl->set('metadesc', $row->metadesc);
		$tpl->set('page_image', $meta_array->page_image);
		$tpl->set('ogdesc', $meta_array->ogdesc);

		$tpl->set('state', JHTML::_('select.booleanlist', 'state', '', $row->state));
		// $tpl->set('categories', $contentcat);
		//$tpl->set('blogs', $lblogs);
		$tpl->set('videobot', $_JB_CONFIGURATION->get('enableAllVideosBot'));
		$tpl->set('tags', isset($tags) ? $tags : '');
		$tpl->set('fulltext', $row->fulltext);
		$tpl->set('id', $row->id);
		$tpl->set('title', $row->title);

		// twitter tags
		if (empty($row->custom_metatags['twitter:description']))
			$row->custom_metatags['twitter:description'] = '';

		if (empty($row->custom_metatags['twitter:title']))
			$row->custom_metatags['twitter:title'] = substr($row->title, 0, 70);

		$tpl->set('custom_metatags', $row->custom_metatags);

		$tpl->set('use_mce', $_JB_CONFIGURATION->get('useMCEeditor') ? "true" : "false");

		$registry = new JRegistry;
		$registry->loadString($row->attribs);
		$attribs = $registry->toArray();

		$tpl->set('alternative_readmore', @$attribs['alternative_readmore'] ? $attribs['alternative_readmore'] : $_JB_CONFIGURATION->get('readMoreLink'));

		require_once(JB_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'plugins.php');

		$plugins = new JBPlugins();
		$plugins->init('editors-xtd');
		$plugins_rows = $plugins->get(0, 999, 'editors-xtd');

		$not_use_editors_xtd = array();
		if ($plugins_rows)
		{
			foreach ($plugins_rows as $value)
			{
				if (!$value->published)
					$not_use_editors_xtd[] = $value->element;
			}
		}

		$tpl->set('editorsxtd', $not_use_editors_xtd);

		jimport('joomla.form.form');

		$form = new JForm('languageform');
		$form->loadFile(JB_ADMIN_COM_PATH . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'post.xml');
		$form->setValue("language", null, $row->language);
		$formlist = array();
		$formlist['input'] = $form->getInput("language");
		$formlist['label'] = $form->getLabel("language");
		$tpl->set('form', $formlist);
		$query = "SELECT id FROM #__assets WHERE name = 'com_joomblog' ";
		$db->setQuery($query);
		$assets = $db->loadResult();
		$tpl->set('assets', $assets);
		$tpl->set('author', $my->get('id'));

		$sections = $_JB_CONFIGURATION->get('managedSections');

		$query = "SELECT cid FROM #__joomblog_multicats WHERE aid = " . $row->id;
		$db->setQuery($query);
		$selCat = $db->loadColumn();

		$doc->addStyleSheet(rtrim(JURI::base(), '/') . "/components/com_joomblog/css/style.css");

		JFormHelper::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
		$jEditor = JFormHelper::loadFieldType('Html5editor', false);
		$jEditor->setName('fulltext');
		$jEditor->setValue($row->fulltext);
		$jEditor->setLabel(JText::_('COM_JOOMBLOG_POST_CONTENT'));
		$tpl->set('jEditor', $jEditor, true);

		$postModel = $this->getModel('Post', 'JoomblogModel');
		$postForm = $postModel->getForm();
		$tpl->set('postForm', $postForm, true);

		$html = $tpl->fetch(JB_TEMPLATE_PATH . "/admin/write.html");

		$html = str_replace("src=\"icons", "src=\"" . rtrim(JURI::base(), '/') . "/components/com_joomblog/templates/admin/icons", $html);

		return $html;
	}
}

