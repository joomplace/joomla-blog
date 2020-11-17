<?php
/**
* JoomBlog Latest Post Module for Joomla
* @version $Id: default.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage default.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); 
?>
<ul class="blog-latest<?php echo $params->get('moduleclass_sfx'); ?>">
<?php
	//Get current timezone
	$userTz = JFactory::getUser()->getParam('timezone');
	$timeZone = JFactory::getConfig()->get('offset');
	if ($userTz)
	{
		$timeZone = $userTz;
	}
	$limit = $params->get('numLatestEntries');

	$i = 0;

	foreach ($list as $row ):
		if ($i < $limit ){
		$i++;
		$row->permalinkURL = jbGetPermalinkUrl($row->id);
	    $row->titleLink	= $row->permalinkURL;
	    $row->author = jbGetAuthorName($row->created_by, ($postedByDisplay=="1" ? "0" : "1"));

	    $row->authorLink 	= JRoute::_("index.php?option=com_joomblog&user=".urlencode($row->author)."&Itemid=".$jbItemid."");
	    $row->title 		= htmlspecialchars($row->title);

	    $date = new JDate( $row->created, $timeZone );

		$row->createdFormatted	= $date->format( $params->get( 'dateFormat','d M Y, H.m' ), true );
		$row->created = $date->format($params->get( 'dateFormat','d M Y, H.m' ), true);
	?>
		<li>
			<div>
				<span class="jb_mod_latest_title">
					<a title="<?php echo $row->title; ?>" href="<?php echo $row->titleLink; ?>">
					<?php
						$titlelength = JString::strlen($row->title);

						if ($titlelength>$titleMaxLength)
					    {
					        $row->title = JString::substr($row->title,0,$titleMaxLength);
					    }

						if ($titlelength>$titleMaxLength) $row->title.=' ...';
					    echo $row->title;
					   ?>
					</a>
				</span>
			</div>

			<?php if ($showDate): ?>
				<span class="jb_mod_latest_date"><?php echo $row->createdFormatted; ?></span>
			<?php endif;?>

			<?php if ($showIntro): ?>
				<div class="jb_mod_latest_introtext">
					<?php
						$row->introtext= strip_tags($row->introtext);
					    $titlelength = JString::strlen($row->introtext);

					    if ($titlelength>$titleMaxLength)
					    {
					        $row->introtext = JString::substr($row->introtext,0,$titleMaxLength*2);
						}

						echo $row->introtext;

						if ($titlelength>$titleMaxLength)
						{
						    echo " ...";
						}
						?>
				</div>
			<?php endif;?>

			<?php if ($showAuthor != "0"): ?>
				<span class="created"> <?php echo JText::_('MOD_JB_LATESTPOSTS_CREATEBY');?> <a href="<?php echo $row->authorLink; ?>"><?php echo $row->author; ?></a></span>
			<?php endif;?>

			<?php if ($showReadmore): ?>
				<div>
					<span class="jb_mod_latest_readmore" >
					    <a title="<?php echo JText::_('MOD_JB_LATESTPOSTS_READMORE');?>: <?php echo $row->title; ?>" href="<?php echo $row->titleLink; ?>"><?php echo JText::_('MOD_JB_LATESTPOSTS_READMORE');?></a>
					 </span>
				</div>
			<?php endif; ?>

			<div style="clear:both;"><hr size="1"></div>
		</li>
		<?php }endforeach; ?>
</ul>
