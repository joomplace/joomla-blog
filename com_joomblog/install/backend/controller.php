<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die;

class JoomBlogController extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false)
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root() . 'administrator/components/com_joomblog/assets/css/joomblog.css');
		$document->addScriptDeclaration("
			if (typeof jQuery === 'undefined')
	         {
			    var script = document.createElement('script');
			    script.src = 'http://code.jquery.com/jquery-latest.min.js';
			    script.type = 'text/javascript';
			    document.getElementsByTagName('head')[0].appendChild(script);
			} ");

		$document->addScript(JURI::root() . 'administrator/components/com_joomblog/assets/js/base.js');

		$this->input->set('view', $this->input->getCmd('view', 'control_panel'));
		$this->input->set('layout', $this->input->getCmd('layout', 'default'));

		parent::display($cachable, $urlparams);
	}

	public function datedb()
	{
		$config = JComponentHelper::getParams('com_joomblog');
		$saved_date = $config->get('curr_date');
		$db = JFactory::getDbo();
		$curr_date = date("Y-m-d");
		if (empty($saved_date) OR $saved_date !== $curr_date)
		{
			$query = $db->getQuery(true)
				->select('`params`')
				->from('`#__extensions`')
				->where('`name` = "com_joomblog" AND `element` = "com_joomblog"');
			$db->setQuery($query);
			$extension_params = $db->loadResult();

			if ($extension_params)
			{
				$params_array = json_decode($extension_params, true);
				$params_array['curr_date'] = $curr_date;
				$new_params_json = json_encode($params_array);

				$query = $db->getQuery(true)
					->update('`#__extensions`')
					->set('`params` = "' . $new_params_json . '"')
					->where('`name` = "com_joomblog" AND `element` = "com_joomblog"');
				$db->setQuery($query);
				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					echo $e->getMessage();
				}
			}
		}
	}

	public function history()
	{
		echo '<h2>' . JText::_('COM_JOOMBLOG_VERSION_HISTORY') . '</h2><br/>';
		jimport('joomla.filesystem.file');
		if (!JFile::exists(JPATH_COMPONENT_ADMINISTRATOR . '/changelog.txt'))
		{
			echo 'History file not found.';
		}
		else
		{
			echo '<textarea class="editor" rows="30" cols="50" style="width:100%">';
			echo file_get_contents(JPATH_COMPONENT_ADMINISTRATOR . '/changelog.txt');
			echo '</textarea>';
		}
		exit();
	}

	function installSampleData()
	{
		$link = 'index.php?option=com_joomblog&view=posts';
		$model = $this->getModel('sampledata');

		if (!$model->makeInstall())
		{
			$this->setRedirect($link, implode(',', $model->getErrors()), 'error');
			return;
		}

		$this->setRedirect($link, JText::_('COM_JOOMBLOG_SAMPLEDATA_INSTALL_SUCCESS'));
	}
}
