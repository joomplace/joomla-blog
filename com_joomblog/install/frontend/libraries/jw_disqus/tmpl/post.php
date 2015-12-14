<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<!-- Disqus Comments -->
<div class="jwDisqusForm">
	<?php echo $output->comments; ?>
</div>

<div class="jwDisqusBackToTop">
	<a href="<?php echo $output->itemURL; ?>#startOfPage"><?php echo JText::_("COM_JOOMBLOG_BACKTOTOP"); ?></a>
	<div class="clr"></div>
</div>
	
<div class="clr"></div>
