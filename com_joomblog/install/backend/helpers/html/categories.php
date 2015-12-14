<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;

JoomBlogHelper::getSideBarMenu($this);
JHtmlSidebar::setAction('index.php?option=com_joomblog&view='.$this->getName());
$this->blog_sidebar = JHtmlSidebar::render();

?>
<style type="text/css">
	.joomblog #j-sidebar-container {
		display: none;
	}
</style>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('.joomblog #j-main-container').removeClass('span10').addClass('span12');
	});
</script>

<?php echo JoomBlogHelper::getMenuPanel(); ?>

<?php if (!empty($this->blog_sidebar)) { ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->blog_sidebar; ?>
	</div>
<?php } ?>

<div id="j-main-container" class="<?php echo (empty($this->blog_sidebar) ? '' : 'span10'); ?> joomblog">
	<?php include('components/com_categories/views/categories/tmpl/default.php'); ?>
</div>