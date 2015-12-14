<?php
/**
* JoomBlog Archive Module for Joomla
* @version $Id: default.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage default.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); 

$show_posts = $params->get('show_posts');
if (!isset($show_posts)) $show_posts = 1;
$posts_type = $params->get('posts_type');
if (!isset($posts_type)) $posts_type = 1;

JHtml::_('behavior.framework');
$document = JFactory::getDocument();
$js = "jQuery(document).ready(function(){
    jQuery('.year_inner').hide();
    jQuery('.month_inner').hide();
    jQuery('.yearID .year').click(function() {
    if (jQuery(this).parent().attr('class') != 'yearID active') {
            var newVal = jQuery(this).parent().attr('id');
            jQuery(this).parent().find('dl.'+newVal).slideDown(400);
      jQuery(this).parent().addClass('active');
    } else {
      var newVal = jQuery(this).parent().attr('id');
            jQuery(this).parent().find('dl.'+newVal).slideUp(400);
      jQuery(this).parent().removeClass('active');
    }
    });
  
  jQuery('dt').click(function() {
    if (jQuery(this).attr('class') != 'active') {
            var newVal = jQuery(this).attr('id');
            jQuery(this).parent().find('dd.'+newVal).slideDown(400);
      jQuery(this).addClass('active');
    } else {
      var newVal = jQuery(this).attr('id');
            jQuery(this).parent().find('dd.'+newVal).slideUp(400);
      jQuery(this).removeClass('active');
    }
    });
  
  jQuery('.collapseAll').click(function() {
    jQuery('.yearID').removeClass('active');
    jQuery('dt').removeClass('active');
        jQuery('.month_inner').slideUp(300);
    jQuery('.year_inner').slideUp(300);
    });

});";

$document->addScriptDeclaration($js);
$document->addStyleSheet(JURI::root() . "modules/mod_jb_archive/css/styles.css");
?>
<div class="blog_archive<?php echo $params->get('moduleclass_sfx'); ?>">
<h3> <?php echo JText::_('MOD_JB_ARCHIVE_POSTS') ?></h3>
<ul class="archive_list">
<?php 
foreach ($list as $key => $items) :
  echo '<li class="yearID" id="year_'.$key.'"><span class="year"><strong>'.$key.'</strong> ('.$items['count'].')</span>';
  echo '<dl class="year_inner year_'.$key.'">';
    foreach ($items['month'] as $item) : 
      if($item->count_month > 0) {
      echo '<dt id="month_'.$item->monthname.'"><strong>'.JText::_('MOD_JB_ARCHIVE_'.strtoupper($item->monthname)).'</strong> ('.$item->count_month.')</dt>';
      if ($show_posts == 1)
      {
        echo '<dd class="month_inner month_'.$item->monthname.'">';
        foreach ($item->posts as $title)
        {
          $link = JRoute::_('index.php?option=com_joomblog&&show='.$title->id.($itemid?'&Itemid='.$itemid:''));  
          echo '<li class="post"><a href="'.$link.'">'.$title->title.'</a></li>';
        }
        $link = JRoute::_('index.php?option=com_joomblog&&archive='.urlencode($key."-".$item->created_month).($itemid?'&Itemid='.$itemid:''));      
          if ($item->count_month>5)
          {
            echo '<p><a href="'.$link.'">'. JText::_('MOD_JB_ARCHIVE_READMORE') .'</a></p>';
          } 
        }
      } 
    
    endforeach;
  echo '</dl></li>';
endforeach;
echo '</ul>';
?>
<a href="javascript:void(0)" class="collapseAll"> <?php echo JText::_('MOD_JB_ARCHIVE_COLAPSEALL') ?> </a>
</div>


