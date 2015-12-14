<?php
/**
* JoomBlog Categories Module for Joomla
* @version $Id: default.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage default.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); 

?>

<ul class="blog-categories<?php echo $params->get('moduleclass_sfx'); ?>">
<?php
  foreach( $categories as $category)
  {
    $link	= JRoute::_('index.php?option=com_joomblog&task=tag&category=' . $category->id . '&Itemid=' . $jbItemid );
?>
    <li>
      <span>
        <a href="<?php echo $link;?>"><?php echo $category->title; ?>(<?php echo $category->count; ?>)</a>
      </span>
    </li>
<?php
  }
?>
</ul>
