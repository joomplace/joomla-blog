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

	<div id="j-main-container" class="<?php echo (empty($this->sidebar) ? 'span12' : 'span10'); ?> form-horizontal ">
		<div class="hero-unit" style="padding: 20px ! important;">
			<?php echo JText::_('COM_JOOMBLOG_TAB_IMPORT_IMPORT_COMPLETE'); ?>
		</div>
		<div class="form-actions">
			<a class="btn btn-primary" onclick="Joomla.submitbutton('export.cancel')" href="#"><?php echo JText::_('COM_JOOMBLOG_BACK_BUTTON'); ?></a>
		</div>
	</div>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="<?php echo (isset($this->item->id)?$this->item->id:0)?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
