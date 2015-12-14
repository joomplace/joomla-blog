<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

class JFormFieldHtml5editor extends JFormField
{
	protected $type = 'Html5editor';
	public $postId = 0;
	
	public function getInput() {
	    if(empty($this->name)) $this->name = 'texteditor';
	    if(empty($this->id)) $this->id = str_replace('[', '_', str_replace(']', '_', $this->name));

	    ob_start();
		?>

			<div id="ckeditorLoading" style="text-align: center; position: absolute; width: 100%; padding-top: 150px;"><img style="float: none;" src="<?php echo JURI::root();?>components/com_joomblog/assets/file-upload/img/loading.gif" /></div>
			<textarea cols="80" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" rows="20" style="height: 400px;width: 100%" class="jb-textarea jb-comment-textarea" placeholder="<?php echo $this->label; ?>">
				<?php echo $this->value; ?>
			</textarea>

		<script src="<?php echo JURI::root();?>components/com_joomblog/assets/ckeditor/ckeditor.js" type="text/javascript"></script>
		<script>
			var editor;
			function createEditor() {
				if ( editor ) { editor.destroy(); }
				editor = CKEDITOR.replace( '<?php echo $this->id; ?>', {
					language: 'en',
					"imageBrowser_listUrl": '<?php echo JURI::root(); ?>index.php?option=com_joomblog&task=images&subaction=list&id=<?php echo (int)$this->postId; ?>&tmpl=component',
					"imageBrowser_uploadUrl": '<?php echo JURI::root(); ?>index.php?option=com_joomblog&task=images&subaction=upload&id=<?php echo (int)$this->postId; ?>&tmpl=component',
					on: { 'pluginsLoaded': function (ev) { document.getElementById('ckeditorLoading').style.display = 'none'; } }
				});
			}
			createEditor();
		</script>
	    <?php
		$input = ob_get_contents();
		ob_end_clean();
				
		return $input;
	}
	
	public function setName($name){
	    $this->name = $name;
	}
	
	public function setValue($value){
	    $this->value = $value;
	}
	
	public function setLabel($label){
	    $this->label = $label;
	}

	public function setId( $id )
	{
		$this->id = $id;
	}


	public function getButtons($editor = '')
	{
		JHtml::_('behavior.modal', 'a.modal-button');
		$editorz =& JEditor::getInstance('tinymce');

		/* Get plugins */
		$plugins = JPluginHelper::getPlugin('editors-xtd');

		foreach ($plugins as $plugin)
		{
			JPluginHelper::importPlugin('editors-xtd', $plugin->name, false);
			$className = 'plgButton' . $plugin->name;

			if (class_exists($className))
			{
				$plugin = new $className($editorz, (array) $plugin);
			}

			if ($temp = $plugin->onDisplay($editorz, '', ''))
			{
				$result[] = $temp;
			}

		}

		$return = "<div id=\"editor-xtd-buttons\">\n";

		foreach ($result as $button)
		{
			/*
			 * Results should be an object
			 */

			if ( $button->get('name') ) {
				$modal		= ($button->get('modal')) ? ' class="btn btn-small modal-button"' : null;
				$class      = ($button->get('class')) ? ' class="' . $button->get('class') . '"' : null;
				$href		= ($button->get('link')) ? ' href="'.JURI::base().$button->get('link').'"' : null;
				$onclick	= ($button->get('onclick')) ? ' onclick="'.$button->get('onclick').'"' : 'onclick="IeCursorFix(); return false;"';
				$title      = ($button->get('title')) ? $button->get('title') : $button->get('text');
				$return .= '<div class="inline ' . $button->get('name')
					. '"><a' . $modal . ' ' . $class . ' title="' . $title . '"' . $href . $onclick . ' rel="' . $button->get('options')
					. '">' . $button->get('text') . "</a></div>\n";
			}
		}

		$return .= "</div>\n";

		$return.='<script type="text/javascript">
		function IeCursorFix() {}
		function insertReadmore() { btn_insertReadmore(); }
		function jInsertEditorText( text ) { CKEDITOR.instances[\''.$this->id.'\'].insertHtml(text);}
		</script>';

		return $return;
	}
}
