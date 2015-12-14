<?php
/**
* JoomBlog List of blogs Module for Joomla
* @version $Id: default.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage default.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); 
global $_JB_CONFIGURATION;
?>

<ul class="list-blogs<?php echo $params->get('moduleclass_sfx'); ?>">
<?php
  foreach( $blogs as $blog)
  {
	$user = JFactory::getUser($blog->user_id);
    $link	= JRoute::_('index.php?option=com_joomblog&blogid='.$blog->id.'&view=blogger&Itemid='.$jbItemid);
	$user = JFactory::getUser($blog->user_id);
	$blog->title = ($blog->title)?$blog->title:$user->username."'s blog";
?>
    <li>
      <span>
        <a href="<?php echo $link;?>"><?php echo $blog->title; ?>(<?php echo $blog->count; ?>)</a>
      </span>
    </li>
<?php
  }
?>
</ul>
