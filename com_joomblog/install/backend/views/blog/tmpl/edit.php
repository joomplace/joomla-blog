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
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#viewTabs a:first').tab('show');
	});
</script>

<?php echo JoomBlogHelper::getMenuPanel(); ?>

<form name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_joomblog&view=blog&layout=edit&id='.(int) $this->item->id); ?>" method="post" autocomplete="off">
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
						<div class="span8">
			<?php   foreach($this->form->getFieldset($fieldset->name) as $field): ?>
					<?php echo $field->getControlGroup();?>
						<?php if ($field->name == "jform[header]"):?>
						</div>
						<div class="span4">
						<?php endif;?>
			<?php   endforeach; ?>
						</div>
					</fieldset>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<div>
		<input type="hidden" name="task" value="item.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>