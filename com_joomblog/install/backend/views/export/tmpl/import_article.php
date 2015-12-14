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

JHtml::_('behavior.multiselect');

if(empty($imagesDir)) $imagesDir = NULL;
if(empty($extensions)) $extensions = NULL;
?>

<style type="text/css">
.hide {
	display: none;
}
</style>

<?php echo JoomBlogHelper::getMenuPanel(); ?>

<form action="<?php echo JRoute::_('index.php?option=com_joomblog&view=export'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<?php if (!empty($this->sidebar)) { ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php } ?>

	<div id="j-main-container" class="<?php echo (empty($this->sidebar) ? '' : 'span10'); ?> form-horizontal ">
		<table id="importArtList" class="table table-striped">
			<thead>
				<tr>
					<th width="5">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%" class="center"><?php echo JText::_('JGRID_HEADING_ID');?></th>
					<th><?php echo JText::_('JGLOBAL_TITLE'); ?></th>
					<th width="30%"><?php echo JText::_('JCATEGORY'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($this->importData)): ?>
					<?php foreach($this->importData as $k => $article) : ?>
						<tr class="row<?php echo ($k%2); ?>">
							<td style="width:25px;">
								<?php echo JHtml::_('grid.id', $k, $article->id); ?>
							</td>
							<td class="center"><?php echo $article->id; ?></td>
							<td><?php echo $article->title;?></td>
							<td><?php echo $article->category_title;?></td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr class="row0"><td colspan="4"><?php echo JText::_('COM_JOOMBLOG_TAB_IMPORT_NOTHING_TO_IMPORT'); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>

		<div class="form-actions">
			<a class="btn btn-primary" onclick="Joomla.submitbutton('export.cancel')" href="#"><?php echo JText::_('COM_JOOMBLOG_BACK_BUTTON'); ?></a>
			<a class="btn btn-success" onclick="Joomla.submitbutton('export.importArticleSave')" href="#"><?php echo JText::_('COM_JOOMBLOG_TAB_IMPORT'); ?></a>
		</div>

		<div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>

