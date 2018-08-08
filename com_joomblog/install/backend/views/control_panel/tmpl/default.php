<?php
defined('_JEXEC') or die('Restricted access');
/*
* JoomBlog Component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
?>
<style type="text/css">
	div.extend-buttons:hover {
		border: 1px solid blue;
	}
	.extend .btn {
		height: 40px;
		padding: 15px 0px;
		vertical-align: middle;
		width: 85px;
		margin: 0px 0px 5px 0px;
		display: inline-block;
		font-size: 12px;
	}
	.extend .btn img {
		width: 36px;
	}
	.popover-content {
		overflow: hidden;
		background-color: #ebebeb;
		outline: 0;
		background-image: none;
		-webkit-box-shadow: inset 0 3px 5px rgba(0,0,0,.125);
		box-shadow: inset 0 3px 5px rgba(0,0,0,.125);
	}
	.popover {
		max-width: 400px;
	}
</style>
<script type="text/javascript">
	
	jQuery(document).ready(function () {
	    getLatestNews();
	});
	
	function getLatestNews()
	{
		var url = '<?php echo JURI::root().'administrator/index.php?option=com_joomblog&task=general.get_latest_news'; ?>';
		var xmlData = "";
		var syncObject = {};
		var timeout = 5000;
		var dataCallback = function(request, syncObject, responseText) { onGetLatestNewsData(request, syncObject, responseText); };
		var timeoutCallback = function(request, syncObject) { onGetLatestNewsTimeout(request, syncObject); };
		
		MyAjax.makeRequest(url, xmlData, syncObject, timeout, dataCallback, timeoutCallback);
	}
	
	function onGetLatestNewsData(request, syncObject, responseText)
	{
		var resultDiv = document.getElementById('joomblogLatestNews');
		
		// Handling XML.
		
		var xmlDoc = MethodsForXml.getXmlDocFromString(responseText);
		var rootNode = xmlDoc.documentElement;
		
		var error = MethodsForXml.getNodeValue(rootNode.childNodes[0]);
		var status = MethodsForXml.getNodeValue(rootNode.childNodes[1]);
		var content = MethodsForXml.getNodeValue(rootNode.childNodes[2]);
		
		// Handling data.
		
		if (error == "" && status == 200)
		{
			resultDiv.innerHTML = content;
		}
		else
		{
			resultDiv.innerHTML = '<font color="red">' + '<?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_CONNECTION_FAILED'); ?>: ' +
				'<?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_TIMEOUT'); ?>' + '</font>';
		}
	}
	
	function onGetLatestNewsTimeout(request, syncObject)
	{
		var resultDiv = document.getElementById('joomblogLatestNews');
		
		resultDiv.innerHTML = '<font color="red">' + '<?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_CONNECTION_FAILED'); ?>: ' +
			'<?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_TIMEOUT'); ?>' + '</font>';
	}
	
	function onBtnShowChangelogClick(sender, event)
	{
		var link = '<?php echo 'index.php?option=com_joomblog&task=general.show_changelog&tmpl=component'; ?>';
		var width = 620;
		var height = 620;
		
		var linkElement = document.createElement('a');
		linkElement.href = link;
		
		SqueezeBox.fromElement(linkElement, { handler: 'iframe', size: { x: width, y: height }, url: link });
	}
	
</script>

<?php echo JoomBlogHelper::getMenuPanel(); ?>

<div id="j-sidebar-container" class="span6" style="margin-left: 0px;">
	<div class="joomblog_dashboard">
		<div class="btn" onclick="window.location = 'index.php?option=com_categories&view=categories&extension=com_joomblog';">
			<img src="<?php echo JUri::root();?>administrator/components/com_joomblog/assets/images/icon_48_categories.png">
			<div><?php echo $this->escape(JText::_('COM_JOOMBLOG_BE_SUBMENU_CATEGORIES')); ?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=blogs';">
			<img src="<?php echo JUri::root();?>administrator/components/com_joomblog/assets/images/icon_48_publications.png">
			<div><?php echo $this->escape(JText::_('COM_JOOMBLOG_BE_SUBMENU_BLOGS')); ?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=posts';">
			<img src="<?php echo JUri::root();?>administrator/components/com_joomblog/assets/images/icon_48_pages.png">
			<div><?php echo $this->escape(JText::_('COM_JOOMBLOG_BE_SUBMENU_POSTS')); ?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=comments';">
			<img src="<?php echo JUri::root();?>administrator/components/com_joomblog/assets/images/icon_48_comments.png">
			<div><?php echo $this->escape(JText::_('COM_JOOMBLOG_BE_SUBMENU_COMMENTS')); ?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=users';">
			<img src="<?php echo JUri::root();?>administrator/components/com_joomblog/assets/images/icon_48_bloggers.png">
			<div><?php echo $this->escape(JText::_('COM_JOOMBLOG_BE_MENU_BLOGGERS')); ?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=settings';">
			<img src="<?php echo JUri::root();?>administrator/components/com_joomblog/assets/images/icon_48_settings.png">
			<div><?php echo $this->escape(JText::_('COM_JOOMBLOG_BE_MENU_CONFIGURATION')); ?></div>
		</div>
		<div class="btn" onclick="window.location = 'http://www.joomplace.com/video-tutorials-and-documentation/joomblog/description.htm';">
			<img src="<?php echo JUri::root();?>administrator/components/com_joomblog/assets/images/icon_48_help.png">
			<div><?php echo $this->escape(JText::_('COM_JOOMBLOG_BE_SUBMENU_HELP')); ?></div>
		</div>
		<!--<div class="btn extend-buttons">
			<i class="icon-plus-2" style="margin-top: 20px;"></i>
		</div>-->
	</div>
</div>

<!--<div id="j-sidebar-container-popover" class="span6" style="margin-left: 0px; display: none;">
	<div class="joomblog_dashboard extend">
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=categories';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_categories.png">
			<div><?php /*echo $this->escape(JText::_('COM_JOOMBLOG_BE_SUBMENU_CATEGORIES')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=categories';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_categories.png">
			<div><?php /*echo $this->escape(JText::_('New category')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=publications';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_publications.png">
			<div><?php /*echo $this->escape(JText::_('Posts')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=publications';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_publications.png">
			<div><?php /*echo $this->escape(JText::_('New post')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=pages';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_pages.png">
			<div><?php /*echo $this->escape(JText::_('Blogs')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=pages';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_pages.png">
			<div><?php /*echo $this->escape(JText::_('Add blog')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=templates';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_templates.png">
			<div><?php /*echo $this->escape(JText::_('Bloggers')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=templates';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_templates.png">
			<div><?php /*echo $this->escape(JText::_('Comments')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=templates';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_templates.png">
			<div><?php /*echo $this->escape(JText::_('Add comment')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=resolutions';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_resolutions.png">
			<div><?php /*echo $this->escape(JText::_('Tags')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=resolutions';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_resolutions.png">
			<div><?php /*echo $this->escape(JText::_('New tag')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=configuration';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_settings.png">
			<div><?php /*echo $this->escape(JText::_('COM_JOOMBLOG_BE_MENU_CONFIGURATION')); */?></div>
		</div>
		<div class="btn" onclick="window.location = 'index.php?option=com_joomblog&view=help';">
			<img src="<?php /*echo JUri::root();*/?>administrator/components/com_joomblog/assets/images/icon_48_help.png">
			<div><?php /*echo $this->escape(JText::_('COM_JOOMBLOG_BE_SUBMENU_HELP')); */?></div>
		</div>
	</div>
</div>-->

<div id="j-main-container" class="span6 form-horizontal joomblog_control_panel_container well" style="margin-right: 0px;">
	
	<table class="table">
		<tr>
			<th colspan="100%" class="joomblog_control_panel_title">
				<?php echo JText::_('COM_JOOMBLOG'); ?>&nbsp;<?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_COMPONENT_DESC') .
					" 3.X " . JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_DEVELOPED_BY'); ?> <a href="http://www.joomplace.com/" target="_blank">JoomPlace</a>.
			</th>
		</tr>
		<tr>
			<td width="120" style="border-top: none;"><?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_INSTALLED_VERSION') . ':'; ?></td>
			<td class="joomblog_control_panel_current_version" style="border-top: none;"><?php echo $this->config->version; ?></td>
		</tr>
		 <tr>
			<td><?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_ABOUT') . ':'; ?></td>
			<td>
				<?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_ABOUT_DESC'); ?>
			</td>
		</tr>
		<tr>
			<td><?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_FORUM') . ':'; ?></td>
			<td>
				<a target="_blank" href="http://www.joomplace.com/forum/joomla-components/joomblog.html">
					http://www.joomplace.com/forum/joomla-components/joomblog.html
				</a>
			</td>
		</tr>
		<tr>
			<td><?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_CHANGELOG') . ':'; ?></td>
			<td>
				<div class="button2-left"><div class="blank">
					<button class="btn btn-small" onclick="onBtnShowChangelogClick(this, event);">
						<i class="icon-file"></i>
						<?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_CHANGELOG_VIEW'); ?>
					</button>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table cellpadding="5" class="joomblog_control_panel_news_table">
					<!--<tr>
						<td section="">
							<img src="<?php //echo JUri::root();?>components/com_joomblog/assets/images/.'tick.png'; ?>"><?php //echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_SAY_THANKS_TITLE'); ?>
						</td>
					</tr>
					<tr>
						<td class="joomblog_control_panel_thanks_cell">
							<div class="joomblog_control_panel_thanks">
								<?php //echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_SAY_THANKS_1'); ?>
								<a href="http://extensions.joomla.org/extensions/directory-a-documentation/portfolio/11307" target="_blank">http://extensions.joomla.org</a>
								<?php //echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_SAY_THANKS_2'); ?>
							</div>
							<div class="joomblog_control_panel_rate_us">
								<a href="http://extensions.joomla.org/extensions/directory-a-documentation/portfolio/11307" target="_blank">
									<img src="<?php //echo JUri::root();?>components/com_joomblog/assets/images/.'rate_us.png'; ?>" />
								</a>
							</div>
						</td>
					</tr>-->
					<tr>
						<td section="">
							<img src="<?php echo JUri::root();?>administrator/components/com_joomblog/assets/images/tick.png"><?php echo JText::_('COM_JOOMBLOG_BE_CONTROL_PANEL_NEWS_TITLE'); ?>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="joomblog_control_panel_news_cell" style="background-image: linear-gradient(to bottom, #FFFFFF, #EEEEEE);">
							<div id="joomblogLatestNews" class="joomblog_control_panel_news">
								<img src="<?php echo JUri::root();?>administrator/components/com_joomblog/assets/images/ajax_loader_16x11.gif" />
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
</div>