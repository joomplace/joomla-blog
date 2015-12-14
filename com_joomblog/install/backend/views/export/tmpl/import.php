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
<script type="text/javascript">
    function checkUncheck(el, className){
		var elements = document.getElementsByClassName(className);
		var n = elements.length;
		for (var i = 0; i < n; i++) {
		    var e = elements[i];
		    if(e.type.toLowerCase() === 'checkbox') {
				e.checked = el.checked;
		    }
		}
    }
</script>

<?php echo JoomBlogHelper::getMenuPanel(); ?>

<form action="<?php echo JRoute::_('index.php?option=com_joomblog&view=export'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<?php if (!empty($this->sidebar)) { ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php } ?>

	<div id="j-main-container" class="<?php echo (empty($this->sidebar) ? 'span12' : 'span10'); ?> form-horizontal ">
		<table id="importArtList" class="table table-striped">
			<?php if(!empty($this->importData) && !empty($this->importData->blogs)): ?>
				<thead>
					<tr>
						<th></th>
						<th>
							<?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_BLOG'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_AUTHOR'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_NEW_BLOG'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_POSTS_COUNT'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($this->importData->blogs as $blog_id=>$blog): ?>
					<?php if(!empty($blog->posts) && count($blog->posts) > 0 ): ?>
						<tr class="row<?php echo ($blog_id%2); ?>">
							<td style="width:25px;"><input type="checkbox" name="importBlogs[]" id="importBlogs_<?php echo $blog_id; ?>" value="<?php echo $blog_id; ?>" checked="checked" onchange="checkUncheck(this, 'importPosts_<?php echo $blog_id; ?>');" /></td>
							<td><?php echo $blog->title;?></td>
							<td><?php echo $blog->username;?></td>
							<td><?php echo (!empty($blog->id) && $blog->id > 0 ? JText::_('JNO') : JText::_('JYES'))?></td>
							<td>
								<table class="table table-condensed table-striped">
									<thead>
										<tr>
											<th></th>
											<th><?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_POST'); ?></th>
											<th><?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_AUTHOR'); ?></th>
											<th><?php echo JText::_('JCATEGORY'); ?></th>
											<th><?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_TAGS'); ?></th>
											<th><?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_NEW_POST'); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php foreach($blog->posts as $post_id=>$post) : ?>
										<tr>
											<td style="width:25px;"><input type="checkbox" name="importPosts[<?php echo $blog_id; ?>][]" id="importPosts_<?php echo $blog_id; ?>_<?php echo $post_id; ?>" class="importPosts_<?php echo $blog_id; ?>" value="<?php echo $post_id; ?>" checked="checked" /></td>
											<td><?php echo $post->title;?></td>
											<td><?php echo $post->username;?></td>
											<td>
												<?php
													if (sizeof($post->cats))
													{
														foreach ( $post->cats as $cat )
														{
															echo $cat->title . '<br />';
														}
													}
													?>
											</td>
											<td><?php echo $post->tags;?></td>
											<td><?php echo (!empty($post->id) && $post->id > 0 ? JText::_('JNO') : JText::_('JYES'))?></td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</td>
						</tr>
					<?php else: ?>
						<tr class="row<?php echo ($blog_id%2); ?>">
							<td style="width:25px;">
								<input type="checkbox" name="importBlogs[]" id="importBlogs_<?php echo $blog_id; ?>" value="<?php echo $blog_id; ?>" checked="checked" onchange="checkUncheck(this, 'importPosts_<?php echo $blog_id; ?>');" />
							</td>
							<td><?php echo $blog->title;?></td>
							<td><?php echo $blog->username;?></td>
							<td><?php echo (!empty($blog->id) && $blog->id > 0 ? JText::_('JNO') : JText::_('JYES'))?></td>
							<td><?php echo JText::_('COM_JOOMBLOG_TAB_IMPORT_NO_POSTS'); ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="row0"><td colspan="6"><?php echo JText::_('COM_JOOMBLOG_TAB_IMPORT_NOTHING_TO_IMPORT'); ?></td></tr>
			<?php endif; ?>
				</tbody>
		</table>

		<div class="form-actions">
			<a class="btn btn-primary" onclick="Joomla.submitbutton('export.cancel')" href="#"><?php echo JText::_('COM_JOOMBLOG_BACK_BUTTON'); ?></a>
			<a class="btn btn-success" onclick="Joomla.submitbutton('export.importSave')" href="#"><?php echo JText::_('COM_JOOMBLOG_TAB_IMPORT'); ?></a>
		</div>
	</div>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="<?php echo (isset($this->item->id)?$this->item->id:0)?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
