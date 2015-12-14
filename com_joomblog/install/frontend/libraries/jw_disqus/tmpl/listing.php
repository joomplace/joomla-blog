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

//$fix_url = JURI::base().$content_check->alias.".html" ;
?>


<!-- Disqus comments counter and anchor link -->
<a href="<?php echo $output->itemURL; ?>#disqus_thread" title="<?php echo JText::_('COM_JOOMBLOG_ADDACOMMENT'); ?>" data-disqus-identifier="<?php echo $output->disqus_identifier;?>"></a>
