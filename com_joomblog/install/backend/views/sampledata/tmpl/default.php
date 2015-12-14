<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted Access');
 
JHtml::_('behavior.tooltip');
?>
<?php echo JoomBlogHelper::getMenuPanel(); ?>

<form action="<?php echo JRoute::_('index.php?option=com_joomblog&view=sampledata'); ?>" method="post" name="adminForm" id="adminForm" class="adminform">
	<div class="hero-unit" style="padding: 20px ! important;">
		<h1>Sample Data</h1>
		<br/>

		<p style="clear: both"></p>

		<?php if ($this->canDo->get('core.admin') or $this->canDo->get('core.create')): ?>
			<h2 class="text-center">Will be created 2 demonstration blogs and 6 demonstration blog posts. Do you want to install it for sure?</h2>
			<a onclick="Joomla.submitbutton('installSampleData')" class="btn btn-success btn-large" href="#">Yes</a>
			<a class="btn btn-danger btn-large" onclick="window.location.href = 'index.php?option=com_joomblog&amp;view=posts';" href="#">No</a>
			<br/>
		<?php else:?>
			<h2 class="text-center">You have no rights to perform this action!</h2>
		<?php endif;?>
		<p></p>
	</div>
	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>