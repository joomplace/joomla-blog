<?php
/**
* JoomBlog Popular Bloggers Module for Joomla
* @version $Id: default.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage default.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); 

if( $showAvatar && $rows)
{
  ?>
    <table class="blog-bloggers<?php echo $params->get('moduleclass_sfx'); ?>" width="100%" cellpadding="3" cellspacing="0">
  <?php
}
elseif($rows)
{
  ?>
    <ul class="blog-bloggers<?php echo $params->get('moduleclass_sfx'); ?>">
  <?php
}

if($rows)
{
  foreach($rows as $row)
  {
      $count = modJbPopularbloggersHelper::getCountPosts($row->created_by);
      $authorname = jbGetAuthorName( $row->created_by , $_JB_CONFIGURATION->get('useFullName') );
      $link = JRoute::_("index.php?option=com_joomblog&user={$authorname}&Itemid={$jbItemid}");
      
      if($showAvatar)
      {
?>
    <tr>
      <td>
          <a href="<?php echo $link;?>">
            <?php echo jbUserGetName($row->created_by, ($postedByDisplay == '1' ? '0' : 1 )); ?>
          </a>
          (<?php echo $count; ?>)
      </td>
      <td align="right">
        <?php
          $avatar	= 'Jb'.ucfirst($_JB_CONFIGURATION->get('avatar')).'Avatar';
          $avatar	= new $avatar($row->created_by);
          $avatar	= $avatar->get();
          
          echo ($showAvatar) ? $avatar : ''; 
        ?>
      </td>
    </tr>
<?php				}
    else
    {
?>
    <li>
      <a href="<?php echo $link;?>">
        <?php echo jbUserGetName($row->created_by, ($postedByDisplay == '1' ? '0' : 1 )); ?>
      </a>
      (<?php echo $count; ?>)
    </li>
<?php
    }
  }
  
  if( $showAvatar )
  {
?>
    </table>
<?php
  }
  else
  {
?>
    </ul>
<?php
  }
}
