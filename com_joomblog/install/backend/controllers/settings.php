<?php
/**
 * JoomBlog component for Joomla 3.x
 * @package   JoomBlog
 * @author    JoomPlace Team
 * @Copyright Copyright (C) JoomPlace, www.joomplace.com
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controllerform');

class JoomBlogControllerSettings extends JControllerForm
{
	protected function allowEdit($data = array(), $key = 'id')
	{
		$user = JFactory::getUser();
		if ($user->authorise('core.manage', 'com_joomblog'))
		{
			return true;
		}
		return false;
	}

	public function cancel($key = NULL)
	{
		$this->setRedirect('index.php?option=com_joomblog');
	}

	public function reset()
	{
		require_once (JPATH_ADMINISTRATOR . '/components/com_joomblog/helpers/jbdefaultsettings.class.php');

		$oClass = new ReflectionClass('JoomBlogDefaultSettings');
		$settings = $oClass->getConstants();
		$settings['curr_date'] = date("Y-m-d", strtotime("-2 months"));

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update('`#__extensions`')
			->set('`params` = "' . addslashes(json_encode($settings)) . '"')
			->where('`name` = "com_joomblog" AND `element` = "com_joomblog"');
		$db->setQuery($query);
		try
		{
			$db->execute();
			$this->setMessage(JText::_('Default settings have been restored successfully!'));
		}
		catch (RuntimeException $e)
		{
			$this->setMessage($e->getMessage());
		}

		$this->setRedirect('index.php?option=com_joomblog&view=settings');
	}

	function editempl()
	{
		jimport('joomla.filesystem.file');
		$jinput = JFactory::getApplication()->input;
		$t = $jinput->get('t');
		if ($t)
		{
			$file_path = JPATH_SITE . '/components/com_joomblog/templates/default/' . $t . '.tmpl.html';
			if (file_exists($file_path))
				if (is_writeable($file_path))
				{
					$editor = JFactory::getEditor('codemirror');
					$params = array('smilies' => '0',
						'style' => '0',
						'layer' => '0',
						'table' => '0',
						'buttons' => 'no',
						'class' => 'template-editor',
						'clear_entities' => '0',
						' editor=>' => 'codemirror',
						'filter' => 'raw'
					);
					ob_start();
					?>    Joomla.submitbutton = function(task)
					{
					<?php echo $editor->save('Array'); ?>
					Joomla.submitform(task, document.getElementById('adminForm'));
					}
					<?php
					$js = ob_get_contents();
					ob_get_clean();
					$document = JFactory::getDocument();
					$document->addScriptDeclaration($js);
					?>
					<form action="<?php echo JRoute::_('index.php?option=com_joomblog&task=settings.editempl'); ?>"
					      method="post" name="adminForm" id="adminForm" class="form-validate">
						<?php
						echo '<div>' . JText::_('COM_JOOMBLOG_TEMPL_PATH') . ': ' . $file_path . '</div>';
						echo '<div style="border:1px solid #c3c3c3">';
						echo $editor->display('notify', file_get_contents($file_path), '550', '350', '60', '20', false, $params);
						echo '</div>';
						?>
						<div>
							<input type="button" class="button" name="save" value="<?php echo JText::_('Save'); ?>"
							       onclick="javascript:Joomla.submitbutton('settings.tmplsave');"/>
							<input type="hidden" name="task" value=""/>
							<input type="hidden" name="t" value="<?php echo $t; ?>"/>
							<?php echo JHtml::_('form.token'); ?>
						</div>
					</form>
				<?php
				}
				else echo 'unwriteable file';
		}

	}

	function tmplsave()
	{
		jimport('joomla.filesystem.file');
		$jinput = JFactory::getApplication()->input;
		$t = $jinput->get('t');
		if ($t)
		{
			$file_path = JPATH_SITE . '/components/com_joomblog/templates/default/' . $t . '.tmpl.html';
			if (is_writeable($file_path))
			{
				$content = $jinput->get('notify');
				if (JFile::write($file_path, $content))
				{
					$this->setMessage(JText::_('COM_JOOMBLOG_SAVESUCCESS'));
				}
				else
				{
					$this->setMessage('Failed to open file for writing!');
				}
			}
			else
			{
				$this->setMessage(JText::_('COM_JOOMPORTFOLIO_UNWRITEABLE'));
			}
		}
		$this->setRedirect(JRoute::_($_SERVER['HTTP_REFERER'], false));
	}
}
