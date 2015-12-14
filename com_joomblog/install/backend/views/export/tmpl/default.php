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
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');
?>

<style type="text/css">
	.hide {
		display: none;
	}
	.controls > .radio:first-child, .controls > .checkbox:first-child {
		padding-top: 0;
	}
	input[type="radio"], input[type="checkbox"] {
		margin: 0;
	}
</style>
<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('#viewTabs a:first').tab('show');
	});

	/* Override joomla.javascript, as form-validation not work with ToolBar */
	function submitbutton(pressbutton) {
	    if (pressbutton == 'export.import') {
	        var f = document.adminForm;
	        if (document.formvalidator.isValid(f)) {
	            submitform(pressbutton);
	        }
	        else {
	            alert ('<?php echo JText::_('COM_JOOMBLOG_IMPORT_EMPTY_FIELDS'); ?>');
	        }
	    }
	    else{
			submitform(pressbutton);
	    }
	}
</script>

<?php echo JoomBlogHelper::getMenuPanel(); ?>

<form action="<?php echo JRoute::_('index.php?option=com_joomblog&view=export'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
	<?php if (!empty($this->sidebar)) { ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php } ?>

	<div id="j-main-container" class="<?php echo (empty($this->sidebar) ? 'span12' : 'span10'); ?> form-horizontal ">
		<ul class="nav nav-tabs" id="viewTabs">
			<li><a href="#tab_export" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_TAB_EXPORT");?></a></li>
			<li><a href="#tab_import" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_TAB_IMPORT");?></a></li>
			<li><a href="#tab_import_articles" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_TAB_IMPORT_ARTICLES");?></a></li>
		</ul>

		<div class="tab-content">
			<div class="tab-pane" id="tab_export">
				<div class="control-group">
					<div class="control-label">
						<label for=""><?php echo JText::_('COM_JOOMBLOG_TAB_EXPORT_SELECT_BLOGS'); ?></label>
					</div>
					<div class="controls">
						<?php
							if(count($this->blogs) > 0):
								foreach($this->blogs as $i=>$blog): ?>
						<label for="exportBlogs_<?php echo $blog->id; ?>">
							<input type="checkbox" name="exportBlogs[]" id="exportBlogs_<?php echo $blog->id; ?>" value="<?php echo $blog->id; ?>" checked="checked" />
							<?php echo $blog->title; ?> (<?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_POSTS_COUNT'); ?> : <?php echo $blog->totalPosts; ?>)
						</label>
						<?php
								endforeach;
							else:
							?>
								<?php echo JText::_('COM_JOOMBLOG_TAB_EXPORT_NOTHING_TO_EXPORT'); ?>
						<?php endif;?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<label for=""><?php echo JText::_('COM_JOOMBLOG_TAB_EXPORT_SELECT_OPTIONS'); ?></label>
					</div>
					<div class="controls">
						<label for="exportUserIds">
							<input type="checkbox" name="exportUserIds" id="exportUserIds" value="1" checked="checked" />
							<?php echo JText::_('COM_JOOMBLOG_TAB_EXPORT_EXPORT_USER_IDS'); ?>
						</label>
						<label for="exportCatIds">
							<input type="checkbox" name="exportCatIds" id="exportCatIds" value="1" checked="checked" />
							<?php echo JText::_('COM_JOOMBLOG_TAB_EXPORT_EXPORT_CATEGORY_IDS'); ?>
						</label>
					</div>
				</div>
				<div class="form-actions">
					<a onclick="submitbutton('export.export')" href="javascript: void(0);" class="btn btn-primary"><?php echo JText::_('COM_JOOMBLOG_TAB_EXPORT'); ?></a>
				</div>
			</div>
			<div class="tab-pane" id="tab_import">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('importFile'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('importFile'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('published'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('published'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('user_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('user_id'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('catid'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('catid'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('updateExisting'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('updateExisting'); ?>
					</div>
				</div>

				<div class="form-actions">
					<a onclick="submitbutton('export.import')" href="javascript: void(0);" class="btn btn-primary"><?php echo JText::_('COM_JOOMBLOG_TAB_IMPORT'); ?></a>
				</div>
			</div>
			<div class="tab-pane" id="tab_import_articles">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('article_catid'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('article_catid'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('article_published'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('article_published'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('article_user_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('article_user_id'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('article_blog_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('article_blog_id'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->importForm->getLabel('article_default_catid'); ?>
					</div>
					<div class="controls">
						<?php echo $this->importForm->getInput('article_default_catid'); ?>
					</div>
				</div>

				<div class="form-actions">
					<a onclick="submitbutton('export.importarticle')" href="javascript: void(0);" class="btn btn-primary"><?php echo JText::_('COM_JOOMBLOG_TAB_IMPORT'); ?></a>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
