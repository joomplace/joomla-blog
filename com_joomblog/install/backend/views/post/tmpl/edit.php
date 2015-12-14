<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<style type="text/css">
	.jb-preview {
		width:auto;
	}
	#editor-xtd-buttons {
		margin-top: 10px;
	}

	#editor-xtd-buttons div.inline {
		display: inline;
	}
	.custom_metatags {
		max-width: 700px;
		font-size: 1.2em;
	}
	.custom_metatags table {
		background-color: rgba(0, 0, 0, 0);
		border-collapse: collapse;
		border-spacing: 0;
		max-width: 100%;
	}
	.custom_metatags .table {
		margin-bottom: 18px;
		width: 100%;
	}
	.custom_metatags .table th, .custom_metatags .table td {
		border-top: 1px solid #DDDDDD;
		line-height: 18px;
		padding: 8px;
		text-align: left;
		vertical-align: top;
	}
	.custom_metatags .table th {
		font-weight: bold;
	}
	.custom_metatags .table thead th {
		vertical-align: bottom;
	}
	.custom_metatags .table caption + thead tr:first-child th, .custom_metatags .table caption + thead tr:first-child td,
	.custom_metatags .table colgroup + thead tr:first-child th, .custom_metatags .table colgroup + thead tr:first-child td,
	.custom_metatags .table thead:first-child tr:first-child th, .custom_metatags .table thead:first-child tr:first-child td {
		border-top: 0 none;
	}
	.custom_metatags .table tbody + tbody {
		border-top: 2px solid #DDDDDD;
	}
	.custom_metatags .table .table {
		background-color: #FFFFFF;
	}

	.custom_metatags .table-striped tbody > tr:nth-child(2n+1) > td, .custom_metatags .table-striped tbody > tr:nth-child(2n+1) > th {
		background-color: #F9F9F9;
	}
	.custom_metatags .table-hover tbody tr:hover > td, .custom_metatags .table-hover tbody tr:hover > th {
		background-color: #F5F5F5;
	}

	.well {
		background-color: #F5F5F5;
		border: 1px solid #E3E3E3;
		border-radius: 4px;
		box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05) inset;
		margin-bottom: 5px;
		padding: 7px;
	}

	.well input[type="text"], .wellinput {
		border-radius: 3px;
		color: #555555;
		display: inline-block;
		font-size: 13px;
		height: 18px;
		line-height: 18px;
		padding: 4px 6px;
		vertical-align: middle;
	}

	.custom_metatags .btn {
		-moz-border-bottom-colors: none;
		-moz-border-left-colors: none;
		-moz-border-right-colors: none;
		-moz-border-top-colors: none;
		background-color: #F5F5F5;
		background-image: linear-gradient(to bottom, #FFFFFF, #E6E6E6);
		background-repeat: repeat-x;
		border-color: #BBBBBB #BBBBBB #A2A2A2;
		border-image: none;
		border-radius: 4px;
		border-style: solid;
		border-width: 1px;
		box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
		color: #333333;
		cursor: pointer;
		display: inline-block;
		font-size: 13px;
		line-height: 18px;
		margin-bottom: 0;
		padding: 4px 12px;
		text-align: center;
		text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
		vertical-align: middle;
		margin-left: 5px;
		margin-top: 3px;
	}

	.custom_metatags .btn:hover, .custom_metatags .btn:focus, .custom_metatags .btn:active, .custom_metatags .btn.active, .custom_metatags .btn.disabled, .custom_metatags .btn[disabled]
	{
		background-position: 0 -15px;
		transition: background-position 0.1s linear 0s;
	}
	
	.custom_metatags .btn-danger {
		background-color: #DA4F49;
		background-image: linear-gradient(to bottom, #EE5F5B, #BD362F);
		background-repeat: repeat-x;
		border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
		color: #FFFFFF;
		text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
		font-size: 1.2em;
	}
	.custom_metatags .btn-danger:hover, .custom_metatags .btn-danger:focus, .custom_metatags .btn-danger:active, .custom_metatags .btn-danger.active, .custom_metatags .btn-danger.disabled, .custom_metatags .btn-danger[disabled] {
		background-color: #BD362F;
		color: #FFFFFF;
	}

	.custom_metatags .btn-success {
		background-color: #5BB75B;
		background-image: linear-gradient(to bottom, #62C462, #51A351);
		background-repeat: repeat-x;
		border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
		color: #FFFFFF;
		text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
		font-size: 1.6em;
	}

	.custom_metatags .btn-success:hover,.custom_metatags .btn-success:focus,.custom_metatags .btn-success:active,.custom_metatags .btn-success.active,.custom_metatags .btn-success.disabled,.custom_metatags .btn-success[disabled] {
		background-color: #51A351;
		color: #FFFFFF;
	}

</style>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#viewTabs a:first').tab('show');
		jQuery('.arrow-down a').addClass('btn btn-small');
	});

	Joomla.submitbutton = function (task) {
		if (task == 'post.cancel') {
			Joomla.submitform(task, document.getElementById('adminForm'));
			return;
		}
		if (document.formvalidator.isValid(document.id('adminForm'))) {
			if (document.getElementById('jform_catid').value == 0) {
				document.getElementById('jform_catid').focus();
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
				return false;
			}
			if (document.getElementById('jform_blog_id').value == 0) {
				document.getElementById('jform_blog_id').focus();
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
				return false;
			}

			<?php echo ($this->_JB_CONFIGURATION->get('useMCEeditor') == 2 ? $this->form->getField('articletext')->save() : ''); ?>
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}

	function btn_insertReadmore() {
		if (typeof CKEDITOR != "undefined") {
			var content = CKEDITOR.instances.jform_articletext.getData();
			if (content.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i)) {
				alert('<?php echo JText::_('COM_JOOMBLOG_READMORE_EXISTS'); ?>');
				return false;
			} else {
				CKEDITOR.instances.jform_articletext.execCommand('pagebreak');
			}
		}
		else {
			if (typeof jInsertEditorText != "undefined") {
				var content = tinyMCE.get('jform_articletext').getContent();
				if (content.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i)) {
					alert('<?php echo JText::_('COM_JOOMBLOG_READMORE_EXISTS'); ?>');
					return false;
				} else {
					jInsertEditorText('<hr id="system-readmore" />', editor);
				}
			}
		}
	}

	function cmtRemove(element) {
		var oldNodesCount = jQuery('.custom_metatags > table > tbody').children().length;
		element.parentNode.parentNode.parentNode.removeChild(element.parentNode.parentNode);
		if (oldNodesCount == 1)
			jQuery('.custom_metatags > table > tbody').append(
				'<tr id="ct_notags"><td colspan="3"><?php echo JText::_('COM_JOOMBLOG_BE_PUBLICATIONS_METADATA_CUSTOM_TAGS_NOTAGS'); ?></td>'
			);
	}

	function cmtAdd() {
		document.getElementById('jcustom_name').value = document.getElementById('jcustom_name').value.replace(/\"/g, '&quote;');
		document.getElementById('jcustom_value').value = document.getElementById('jcustom_value').value.replace(/\"/g, '&quote;');

		if (document.getElementById('jcustom_name').value != '' && document.getElementById('jcustom_value').value != '') {
			if (document.getElementById('ct_notags'))
				document.getElementById('ct_notags').parentNode.removeChild(document.getElementById('ct_notags'));

			jQuery('.custom_metatags > table > tbody').append(
				'<tr><td>' + document.getElementById('jcustom_name').value + '</td>'
					+ '<td>' + document.getElementById('jcustom_value').value + '</td>'
					+ '<td><span class="btn-small btn btn-danger" onclick="cmtRemove(this);"> X </span>'
					+ '<input type="hidden" name="cm_names[]" value="' + document.getElementById('jcustom_name').value + '" />'
					+ '<input type="hidden" name="cm_values[]" value="' + document.getElementById('jcustom_value').value + '" />'
					+ '</td>'
					+ '</tr>'
			);

			document.getElementById('jcustom_name').value = '';
			document.getElementById('jcustom_value').value = '';
		}
	}
</script>

<?php echo JoomBlogHelper::getMenuPanel(); ?>

<form action="<?php echo JRoute::_('index.php?option=com_joomblog&view=post&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data" >
	<?php if (!empty($this->sidebar)) { ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php } ?>

	<div id="j-main-container" class="<?php echo (empty($this->sidebar) ? '' : 'span10'); ?>">
		<ul class="nav nav-tabs" id="viewTabs">
			<?php
			foreach ($this->form->getFieldsets() as $fieldset):
				$fields = $this->form->getFieldset($fieldset->name);
				if (count($fields) > 0):
					?>
					<li><a href="#tab_<?php echo $fieldset->name;?>" data-toggle="tab"><?php echo JText::_($fieldset->label);?></a></li>
				<?php
				endif;
			endforeach;
			?>
		</ul>
		<div class="tab-content">
			<?php foreach ($this->form->getFieldsets() as $fieldset): ?>
				<div class="tab-pane" id="tab_<?php echo $fieldset->name;?>">
					<fieldset class="form-horizontal">
						<?php if ($fieldset->name == 'details'):?>
						<div class="span12">
						<?php endif;?>
						<?php foreach($this->form->getFieldset($fieldset->name) as $field): ?>
							<?php if ($field->type != 'Hidden'):?>
							<div class="control-group">
							<?php else:?>
								<?php echo $field->input;?>
							<?php endif;?>
								<?php
								if ( $field->type == 'Editor' )
								{
									echo '<div style="max-width: 1000px;">';
									echo '<div class="control-label">' . $field->label . '</div>';
									switch ($this->_JB_CONFIGURATION->get('useMCEeditor')){
										case '1':
											echo '<div class="controls">';
											echo $this->jEditor->getInput();
											echo $this->jEditor->getButtons();
											echo '</div>';
											break;
										case '2':
											echo '<div class="controls">' . $field->input . '<div>';
											break;
										default:
											echo '<div class="controls"><textarea name="jform[articletext]" id="jform_articletext" cols="0" rows="0" style="width: 100%; height: 400px;">' . $field->value . '</textarea></div>';
											break;
									}
									echo '</div>';
								}
								else
								{
									if ( $field->fieldname == 'alternative_readmore' || $field->fieldname == 'hits')
									{
										if ( $field->fieldname == 'alternative_readmore' )
										{
											//Alternative readmore
											echo '<div class="control-label">' . JText::_('COM_JOOMBLOG_READMORE_LABEL') . '</div>';
											echo '<div class="controls"><input type="text" size="50" placeholder="'.JText::_('COM_JOOMBLOG_READMORE').'" class="inputbox" value="" id="jform_alternative_readmore" name="jform[alternative_readmore]">';
											if ( $this->_JB_CONFIGURATION->get('useMCEeditor') )
											{
												echo '<a class="btn btn-small" style="margin-left: 15px;" title="'.JText::_('COM_JOOMBLOG_SETTINGS_LAYOUT_READMORE_INSERT').'" href=javascript: void(0);" onclick="btn_insertReadmore(\'jform_articletext\');return false;" rel="">' . JText::_('COM_JOOMBLOG_SETTINGS_LAYOUT_READMORE_INSERT') . '</a>';
											}
											echo '</div>'; //close <div class="controls">
											echo '</div>'; //close <div class="control-group">

											//Hits field
											echo '<div class="control-group">';
											$hitsField = $this->form->getField('hits');
											echo '<div class="control-label">' . $hitsField->label . '</div>';
											echo '<div class="controls">' . $hitsField->input . '</div>';
										}
									}
									else
									{
										if ($field->type != 'Hidden')
										{
											echo '<div class="control-label">' . $field->label . '</div>';
											echo '<div class="controls">' . $field->input . '</div>';
										}
										//if ($field->type=='jbprivacy' || $field->type=='AccessLevelJS'){ echo "<div class='clr'></div>";}
									}
								}
								?>
							<?php if ($field->type != 'Hidden'):?>
								</div>
								<?php if ($field->name == 'jform[publish_down]'):?>
									</div>
									<div class="span12">
								<?php endif;?>
							<?php endif;?>
						<?php endforeach; ?>
						<?php if ($fieldset->name == 'details'):?>
						</div>
						<?php endif;?>
					</fieldset>
					<?php if ( $fieldset->name == 'metadata' ) { ?>
					<!-- custom_metatags -->
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_JOOMBLOG_BE_PUBLICATIONS_METADATA_CUSTOM_TAGS_TITLE'); ?></legend>
						<div class="custom_metatags">
							<table border="0" width="100%" class="table table-striped">
								<thead>
									<tr>
										<th width="200"><?php echo JText::_('COM_JOOMBLOG_BE_PUBLICATIONS_METADATA_CUSTOM_TAGS_NAME'); ?></th>
										<th><?php echo JText::_('COM_JOOMBLOG_BE_PUBLICATIONS_METADATA_CUSTOM_TAGS_CONTENT'); ?></th>
										<th width="20"></th>
									</tr>
								</thead>
								<tbody>
								<?php if ( empty($this->item->custom_metatags) )
										echo '<tr id="ct_notags"><td colspan="3">'.JText::_('COM_JOOMBLOG_BE_PUBLICATIONS_METADATA_CUSTOM_TAGS_NOTAGS').'</td>';
									else
									{
										foreach ( $this->item->custom_metatags as $ctag_name => $ctag_value )
											echo '<tr>'
												.'<td>'.$ctag_name.'</td>'
												.'<td><input type="text" class="wellinput" style="width:100%" name="cm_values[]" value="'.$ctag_value.'" /></td>'
												.'<td><span class="btn-small btn btn-danger" onclick="cmtRemove(this);"> X </span>'
												.'<input type="hidden" name="cm_names[]" value="'.$ctag_name.'" />'
												.'</td>'
												.'</tr>';
									} ?>
								</tbody>
							</table>
							<div class="well">
								<table border="0" width="100%">
									<tr>
										<td width="170" style="padding-right: 25px;">
											<input type="text" style="width:100%" class="inputbox" value="" id="jcustom_name" placeholder="<?php echo JText::_('COM_JOOMBLOG_BE_PUBLICATIONS_METADATA_CUSTOM_TAGS_NAME'); ?>">
										</td>
										<td style="padding-right: 25px;">
											<input type="text" style="width: 100%" class="inputbox" value="" id="jcustom_value" placeholder="<?php echo JText::_('COM_JOOMBLOG_BE_PUBLICATIONS_METADATA_CUSTOM_TAGS_CONTENT'); ?>">
										</td>
										<td width="20">
											<span class="btn btn-success" onclick="cmtAdd();"> + </span>
										</td>
									</tr>
								</table>
							</div>
						</div>
					<?php } ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>