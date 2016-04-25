<?php 
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.modal');

if(empty($imagesDir)) $imagesDir = NULL;
if(empty($extensions)) $extensions = NULL;

function regexDefaultInput($field) {
	$match = substr($field, 0, 6);
	if ($match == '<input') {
		$pattern = '/(<input.+?value=["|\'])(.+?)(["|\'].+?>)/i';
		$result = preg_replace_callback(
			$pattern,
			function ($matches) {
				return $matches[1].JText::_($matches[2]).$matches[3];
			},
			$field);
		return $result;
	}
	elseif ($match == '<texta') {
		$pattern = '/(<textarea.+?>)(.+?)([<].+?>)/i';
		$result = preg_replace_callback(
			$pattern,
			function ($matches) {
				return $matches[1].JText::_($matches[2]).$matches[3];
			},
			$field);
		return $result;
	}
	else return $field;

}

?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>administrator/components/com_joomblog/assets/js/settings.js"></script>
<style type="text/css">
	.hide {
		display: none;
	}
	.spacer {
		background-color: #EDEDED !important;
	}
</style>

<?php echo JoomBlogHelper::getMenuPanel(); ?>

<form action="<?php echo JRoute::_('index.php?option=com_joomblog&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
<?php if (!empty($this->sidebar)) { ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
<?php } ?>
	<div id="j-main-container" class="<?php echo (empty($this->sidebar) ? 'span12' : 'span10'); ?> form-horizontal ">
		<ul class="nav nav-tabs" id="viewTabs">
			<li><a href="#tab_global" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_GLOBAL");?></a></li>
			<li><a href="#tab_details" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_LAYOUT");?></a></li>
			<li><a href="#tab_dashboard-details" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_DASHBOARD");?></a></li>
			<li><a href="#tab_menu" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_MENU");?></a></li>
			<li><a href="#tab_social-details" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SOCIAL_INTEGRATIONS");?></a></li>
			<li><a href="#tab_permissions" data-toggle="tab"><?php echo  JText::_("JCONFIG_PERMISSIONS_LABEL");?></a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane" id="tab_global">
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
							<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
							<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->form->getFieldset('main') as $field):?>
							<?php if ($field->type == 'Spacer'):?>
								<tr>
									<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
								</tr>
							<?php elseif ($field->name == 'jform[adminEmail]'):?>
								<tr>
									<td>
										<?php echo $field->label; ?>
									</td>
									<td>
										<?php echo $field->input; ?>
									</td>
									<td>
										<?php echo JText::_($field->description);?>
									</td>
								</tr>
								<tr>
									<td>
										<?php echo JText::_('COM_JOOMBLOG_EDIT_MAIL_TEMPLATES'); ?>
									</td>
									<td>
										<?php
											$file_path = JPATH_SITE . '/components/com_joomblog/templates/default/newblog.notify.tmpl.html';
											if (file_exists($file_path) && is_writable($file_path)):
											?>
												<a class="btn btn-small modal" href="index.php?option=com_joomblog&amp;task=settings.editempl&amp;tmpl=component&amp;t=newblog.notify" rel="{handler: 'iframe', size: {x: 600, y: 450}, onClose: function() {}}">
													<?php echo JText::_('COM_JOOMBLOG_EDIT_NEWBLOG_TEMPLATE'); ?>
												</a>
										<?php endif;?>

										<?php
											$file_path = JPATH_SITE.'/components/com_joomblog/templates/default/new.notify.tmpl.html';
											if (file_exists($file_path) && is_writable($file_path)):
											?>
												<a class="btn btn-small modal" href="index.php?option=com_joomblog&amp;task=settings.editempl&amp;tmpl=component&amp;t=new.notify" rel="{handler: 'iframe', size: {x: 600, y: 450}, onClose: function() {}}">
													<?php echo JText::_('COM_JOOMBLOG_EDIT_NEWPOST_TEMPLATE'); ?>
												</a>
										<?php endif;?>

										<?php
											$file_path = JPATH_SITE.'/components/com_joomblog/templates/default/update.notify.tmpl.html';
											if (file_exists($file_path) && is_writable($file_path)):
											?>
												<a class="btn btn-small modal" href="index.php?option=com_joomblog&amp;task=settings.editempl&amp;tmpl=component&amp;t=update.notify" rel="{handler: 'iframe', size: {x: 600, y: 450}, onClose: function() {}}">
													<?php echo JText::_('COM_JOOMBLOG_EDIT_UPDPOST_TEMPLATE'); ?>
												</a>
										<?php endif;?>

										<?php
										/*in future
											$file_path = JPATH_SITE.'/components/com_joomblog/templates/default/updateblog.notify.tmpl.html';
											if (file_exists($file_path) && is_writable($file_path)):
											?>
														<a class="btn btn-small" href="index.php?option=com_joomblog&amp;task=settings.editempl&amp;tmpl=component&amp;t=updateblog.notify" rel="{handler: 'iframe', size: {x: 600, y: 450}, onClose: function() {}}" class="modal">
															<?php echo JText::_('COM_JOOMBLOG_EDIT_UPDBLOG_TEMPLATE'); ?>
														</a>
										<?php endif; */?>
									</td>
									<td></td>
								</tr>
							<?php else: ?>
								<tr>
									<td>
										<?php echo $field->label; ?>
									</td>
									<td>
										<?php echo regexDefaultInput($field->input) ?>
									</td>
									<td>
										<?php echo JText::_($field->description);?>
									</td>
								</tr>
							<?php endif;?>
						<?php endforeach;?>
					</tbody>
				</table>
				<script type="text/javascript">
					if (jQuery('#jform_useRSSFeed1').is(':checked') || jQuery('#jform_useRSSFeed0').is(':checked')) {
						jQuery('#jform_rssFeedBurnerLabel').val('<?php echo JURI::root(); ?>index.php?option=com_joomblog&task=rss');
					}

					if (jQuery('#jform_showFeed0').is(':checked') || jQuery('#jform_showFeed1').is(':checked')){
						jQuery('#jform_countOfChars').attr('disabled', 'disabled');
					}

                    if (jQuery('#jform_useRSSFeed0').is(':checked')) {
                        jQuery('#jform_rssFeedLimit, #jform_titleFeed').attr("disabled", "disabled");
                    }

                    if (jQuery('#jform_useComment0').is(':checked') || jQuery('#jform_useComment1').is(':checked')) {
                        jQuery('#jform_disqusSubDomain').attr('disabled', 'disabled');
                    }

                    if (jQuery("#jform_useCommentCaptcha0").is(":checked") || jQuery("#jform_useCommentCaptcha1").is(":checked")) {
                        jQuery("#jform_recaptcha_publickey, #jform_recaptcha_privatekey").attr("disabled", "disabled");
                    }

                    if (jQuery("#jform_useFeedBurnerIntegration0").is(':checked')) {
                        jQuery("#jform_rssFeedBurner").attr("disabled", "disabled");
                    }

                    if (jQuery("#jform_allowNotification0").is(':checked')) {
                        jQuery("#jform_adminEmail").attr("disabled", "disabled");
                    }
				</script>
			</div>
			<div class="tab-pane" id="tab_details">
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
							<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
							<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->form->getFieldset('layout') as $field):?>
						<?php
							if ($field->name == 'jform[template]'):
								echo $field->input;
								continue;
							?>
						<?php elseif ($field->type == 'Spacer'): ?>
							<tr>
								<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
							</tr>
						<?php else:?>
							<tr>
								<td>
									<?php echo $field->label; ?>
								</td>
								<td>
									<?php echo regexDefaultInput($field->input) ?>
									<?php if ($field->name == 'jform[dateFormat]'):?>
										<a class="modal" href="http://www.joomplace.com/media/dateformat.html" rel="{'handler': 'iframe', 'size': {x: 700, y: 600}}" style="font-weight: bold; text-decoration: none;">?</a>
									<?php endif;?>
								</td>
								<td>
									<?php echo JText::_($field->description);?>
								</td>
							</tr>
						<?php endif;?>
					<?php endforeach;?>
					</tbody>
				</table>
			</div>
			<div class="tab-pane" id="tab_dashboard-details">
				<table class="table table-striped">
					<thead>
					<tr>
						<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
						<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
						<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->form->getFieldset('dashboard') as $field):?>
						<?php if ($field->type == 'Spacer'): ?>
							<tr>
								<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
							</tr>
						<?php else:?>
							<tr>
								<td>
									<?php echo $field->label; ?>
								</td>
								<td>
									<?php echo $field->input; ?>
								</td>
								<td>
									<?php echo JText::_($field->description);?>
								</td>
							</tr>
						<?php endif;?>
					<?php endforeach;?>
					</tbody>
				</table>
			</div>
			<div class="tab-pane" id="tab_menu">
				<table class="table table-striped">
					<thead>
					<tr>
						<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
						<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
						<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->form->getFieldset('joomblogmenu') as $field):?>
						<?php if ($field->type == 'Spacer'): ?>
							<tr>
								<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
							</tr>
						<?php else:?>
							<tr>
								<td>
									<?php echo $field->label; ?>
								</td>
								<td>
									<?php echo regexDefaultInput($field->input) ?>
								</td>
								<td>
									<?php echo JText::_($field->description);?>
								</td>
							</tr>
						<?php endif;?>
					<?php endforeach;?>
					</tbody>
				</table>
			</div>
			<div class="tab-pane" id="tab_social-details">
				<ul class="nav nav-tabs" id="viewSocialTabs">
					<li><a href="#tab_twitter" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_SOCIAL_INT_TWITTER");?></a></li>
					<li><a href="#tab_facebook" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_SOCIAL_INT_FACEBOOK");?></a></li>
					<li><a href="#tab_google-plus" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_SOCIAL_INT_GOOGLE");?></a></li>
					<li><a href="#tab_pinterest" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_SOCIAL_INT_PINTEREST");?></a></li>
					<li><a href="#tab_linkedin" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_SOCIAL_INT_LINKEDIN");?></a></li>
					<li><a href="#tab_stumbleupon" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_SOCIAL_INT_STUMBLEUPON");?></a></li>
					<li><a href="#tab_addthis" data-toggle="tab"><?php echo  JText::_("COM_JOOMBLOG_SETTINGS_SOCIAL_INT_ADDTHIS");?></a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane" id="tab_twitter">
						<table class="table table-striped">
							<thead>
							<tr>
								<th style="width: 20%;"><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($this->form->getFieldset('tw') as $field):?>
								<?php if ($field->type == 'Spacer'): ?>
									<tr>
										<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
									</tr>
								<?php else:?>
									<tr>
										<td>
											<?php echo $field->label; ?>
										</td>
										<td>
											<?php echo $field->input; ?>
										</td>
										<td>
											<?php echo JText::_($field->description);?>
										</td>
									</tr>
								<?php endif;?>
							<?php endforeach;?>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="tab_facebook">
						<table class="table table-striped">
							<thead>
							<tr>
								<th style="width: 20%;"><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($this->form->getFieldset('fb') as $field):?>
								<?php if ($field->type == 'Spacer'): ?>
									<tr>
										<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
									</tr>
								<?php else:?>
									<tr>
										<td>
											<?php echo $field->label; ?>
										</td>
										<td>
											<?php echo $field->input; ?>
										</td>
										<td>
											<?php echo JText::_($field->description);?>
										</td>
									</tr>
								<?php endif;?>
							<?php endforeach;?>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="tab_google-plus">
						<table class="table table-striped">
							<thead>
							<tr>
								<th style="width: 20%;"><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($this->form->getFieldset('gp') as $field):?>
								<?php if ($field->type == 'Spacer'): ?>
									<tr>
										<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
									</tr>
								<?php else:?>
									<tr>
										<td>
											<?php echo $field->label; ?>
										</td>
										<td>
											<?php echo $field->input; ?>
										</td>
										<td>
											<?php echo JText::_($field->description);?>
										</td>
									</tr>
								<?php endif;?>
							<?php endforeach;?>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="tab_pinterest">
						<table class="table table-striped">
							<thead>
							<tr>
								<th style="width: 20%;"><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($this->form->getFieldset('pi') as $field):?>
								<?php if ($field->type == 'Spacer'): ?>
									<tr>
										<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
									</tr>
								<?php else:?>
									<tr>
										<td>
											<?php echo $field->label; ?>
										</td>
										<td>
											<?php echo $field->input; ?>
										</td>
										<td>
											<?php echo JText::_($field->description);?>
										</td>
									</tr>
								<?php endif;?>
							<?php endforeach;?>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="tab_linkedin">
						<table class="table table-striped">
							<thead>
								<tr>
									<th style="width: 20%;"><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
									<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
									<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($this->form->getFieldset('li') as $field):?>
								<?php if ($field->type == 'Spacer'): ?>
									<tr>
										<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
									</tr>
								<?php else:?>
									<tr>
										<td>
											<?php echo $field->label; ?>
										</td>
										<td>
											<?php echo $field->input; ?>
										</td>
										<td>
											<?php echo JText::_($field->description);?>
										</td>
									</tr>
								<?php endif;?>
							<?php endforeach;?>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="tab_stumbleupon">
						<table class="table table-striped">
							<thead>
							<tr>
								<th style="width: 20%;"><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($this->form->getFieldset('su') as $field):?>
								<?php if ($field->type == 'Spacer'): ?>
									<tr>
										<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
									</tr>
								<?php else:?>
									<tr>
										<td>
											<?php echo $field->label; ?>
										</td>
										<td>
											<?php echo $field->input; ?>
										</td>
										<td>
											<?php echo JText::_($field->description);?>
										</td>
									</tr>
								<?php endif;?>
							<?php endforeach;?>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="tab_addthis">
						<table class="table table-striped">
							<thead>
							<tr>
								<th style="width: 20%;"><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
								<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($this->form->getFieldset('addThis') as $field):?>
								<?php if ($field->type == 'Spacer'): ?>
									<tr>
										<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
									</tr>
								<?php else:?>
									<tr>
										<td>
											<?php echo $field->label; ?>
										</td>
										<td>
											<?php echo $field->input; ?>
										</td>
										<td>
											<?php echo JText::_($field->description);?>
										</td>
									</tr>
								<?php endif;?>
							<?php endforeach;?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="tab_permissions">
				<table class="table table-striped">
					<thead>
					<tr>
						<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_TITLE');?></th>
						<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_OPTION');?></th>
						<th><?php echo JText::_('COM_JOOMBLOG_SETTINGS_DESCRIPTION');?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->form->getFieldset('rules') as $field):?>
						<?php if ($field->type == 'Spacer'): ?>
							<tr>
								<td colspan="3" class="spacer"><strong><?php echo JText::_($field->description);?></strong></td>
							</tr>
						<?php elseif ($field->type == 'Rules'):?>
							<tr>
								<td>
									<?php echo $field->label; ?>
								</td>
								<td colspan="2">
									<?php echo $field->input; ?>
								</td>
							</tr>
						<?php else:?>
							<tr>
								<td>
									<?php echo $field->label; ?>
								</td>
								<td>
									<?php echo $field->input; ?>
								</td>
								<td>
									<?php echo JText::_($field->description);?>
								</td>
							</tr>
						<?php endif;?>
					<?php endforeach;?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div>
		<input type="hidden" name="id" value="<?php echo (isset($this->item->id)?$this->item->id:0)?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
<?php if ($this->messageTrigger): ?>
	<div id="notification" class="jb-survey-wrap clearfix" style="clear: both">
		<div class="jb-survey">
            <span>
	            <?php echo JText::_("COM_JOOMBLOG_NOTIFICMES1"); ?><a onclick="jb_dateAjaxRef()" style="cursor: pointer" rel="nofollow" target="_blank">
		            <?php echo JText::_("COM_JOOMBLOG_NOTIFICMES2"); ?>
	            </a>
	            <?php echo JText::_("COM_JOOMBLOG_NOTIFICMES3"); ?><i id="close-icon" class="icon-remove" onclick="jb_dateAjaxIcon()"></i>
            </span>
		</div>
	</div>
<?php endif; ?>
</form>