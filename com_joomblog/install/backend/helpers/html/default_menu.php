<?php

/**
* JoomBlog component for Joomla 3.x
* @package  JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;
JHtml::_('behavior.modal');
$viewName = JRequest::getCmd('view', 'about');
switch ( $viewName ) 
{
	case 'posts':
	case 'post':
	case 'blogs':
	case 'blog':
	case 'categories':
	case 'category':
	case 'users':
	case 'user':
	case 'comments':
	case 'comment':
	case 'tags':
	case 'tag':
	case 'export':
	case 'import':
		$show = 1;
		break;
	case 'plugins':
	case 'plugin':
	case 'settings':
		$show = 2;
		break;
		
	case 'sampledata':
		$show = 3;
		break;

	case 'help':
		$show = 4;
		break;
	
	default:
		$show = 0;
		break;
}
?>
<div id="jpMenu" >
	<div class="topbg" >
		<div class="botbg" >
			<div class="mainbg" >
				<div class="logo" >
					<img src="components/com_joomblog/assets/images/EF_logo.jpg" alt="JoomPlace.com" />
					<h3>
						<?php echo JText::_('COM_JOOMBLOG').' '.JoomBlogHelper::getVersion(); ?>
					</h3>
				</div>
				<?php echo JHtml::_('sliders.start','content-sliders-leftmenu', array('useCookie'=>0, 'startOffset'=>$show)); ?>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_JOOMBLOG_MENU_ABOUT'), 'blog-about'); ?>
				<table class="adminlist" >
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/about.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=about" >
								<?php echo JText::_('COM_JOOMBLOG_MENU_ITEM_ABOUT');?>
							</a>
						</td>
					</tr>
				</table>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_JOOMBLOG_MENU_MANAGEMENT'), 'blog-manage'); ?>
				<table class="adminlist" >
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/categories.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_categories&view=categories&extension=com_joomblog" >
								<?php echo JText::_('COM_JOOMBLOG_SUBMENU_CATEGORIES');?>
							</a>
						</td>
					</tr>
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/blogs.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=blogs" >
								<?php echo JText::_('COM_JOOMBLOG_SUBMENU_BLOGS');?>
							</a>
						</td>
					</tr>                    
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/posts.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=posts" >
								<?php echo JText::_('COM_JOOMBLOG_SUBMENU_POSTS');?>
							</a>
						</td>
					</tr>                   
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/comments.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=comments" >
								<?php echo JText::_('COM_JOOMBLOG_SUBMENU_COMMENTS');?>
							</a>
						</td>
					</tr>
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/tags.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=tags" >
								<?php echo JText::_('COM_JOOMBLOG_SUBMENU_TAGS');?>
							</a>
						</td>
					</tr>
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/users.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=users" >
								<?php echo JText::_('COM_JOOMBLOG_SUBMENU_USERS');?>
							</a>
						</td>
					</tr>
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/sample.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=export" >
								<?php echo JText::_('COM_JOOMBLOG_SUBMENU_EXPORT_IMPORT');?>
							</a>
						</td>
					</tr>
				</table>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_JOOMBLOG_MENU_SETTINGS'), 'blog-setting'); ?>
				<table class="adminlist">
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/settings.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=settings" >
								<?php echo JText::_('COM_JOOMBLOG_MENU_ITEM_SETTINGS');?>
							</a>
						</td>
					</tr>
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/plugins.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=plugins" >
								<?php echo JText::_('COM_JOOMBLOG_MENU_ITEM_PLUGINS');?>
							</a>
						</td>
					</tr>
				</table>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_JOOMBLOG_MENU_SAMPLEDATA'), 'blog-sampledata'); ?>
				<table class="adminlist">
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/sample.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=sampledata" >
								<?php echo JText::_('COM_JOOMBLOG_MENU_ITEM_SAMPLEDATA');?>
							</a>
						</td>
					</tr>
				</table>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_JOOMBLOG_SUBMENU_HELP'), 'blog-help'); ?>
				<table class="adminlist" >
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/help.png" />
						</td>
						<td class="title">
							<a class="menu_link" href="index.php?option=com_joomblog&view=help"><?php echo JText::_('COM_JOOMBLOG_SUBMENU_HELP');?></a>
						</td>
					</tr>
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/support.png" />
						</td>
						<td class="title">
							<a target="_blank"  class="menu_link" href="http://www.joomplace.com/forum/joomla-components/joomblog-component.html">
								<?php echo JText::_('COM_JOOMBLOG_ADMINISTRATION_SUPPORT_FORUM');?>
							</a>
						</td>
					</tr>
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/support.png" />
						</td>
						<td class="title">
							<a target="_blank"  class="menu_link" href="http://www.joomplace.com/helpdesk/index.php">
								<?php echo JText::_('COM_JOOMBLOG_ADMINISTRATION_SUPPORT_DESC');?>
							</a>
						</td>
					</tr>
					<tr>
						<td width="16px">
							<img src="<?php echo JURI::root();?>/administrator/components/com_joomblog/assets/images/support.png" />
						</td>
						<td class="title">
							<a target="_blank" class="menu_link" href="http://www.joomplace.com/helpdesk/ticket_submit.php">
								<?php echo JText::_('COM_JOOMBLOG_ADMINISTRATION_SUPPORT_REQUEST');?>
							</a>
						</td>
					</tr>
				</table>
				<?php echo JHtml::_('sliders.end'); ?>
			</div>
		</div>
	</div>
</div>
<table>
	<tr>
		<td>
			<div align="center">
				<a href="http://extensions.joomla.org/extensions/news-production/blog/16108" target="_blank">
					<img src="http://www.joomplace.com/components/com_jparea/assets/images/rate-2.png"/>
				</a>
			</div>
		</td>
	</tr>
</table>