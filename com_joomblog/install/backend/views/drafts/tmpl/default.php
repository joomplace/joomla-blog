<?php

/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted Access');
JHtml::_('behavior.tooltip');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$search 	= $this->escape($this->state->get('filter.search'));
$saveOrder	= ($listOrder == 'c.ordering');
?>
<table class="admin">
	<tbody>
		<tr>
			<td valign="top" class="lefmenutd" >
				<?php //echo $this->loadTemplate('menu');?>
			</td>
			<td valign="top" width="100%" >
				<form action="<?php echo JRoute::_('index.php?option=com_joomblog&view=drafts'); ?>" method="post" name="adminForm" >
					<?php if ($this->drafts or $this->categories) { ?>
					<fieldset id="filter-bar">
						<?php if ($this->drafts or $search) { ?>
						<div class="filter-search fltlft">
							<label class="filter-search-lbl" for="filter_search">
								<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
							</label>
							<input type="text" name="filter_search" id="filter_search" value="<?php echo $search; ?>" title="" />
							<button type="submit">
								<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
							</button>
							<button type="button" onclick="document.id('filter_search').value='';this.form.submit();">
								<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
							</button>
						</div>
						<?php } ?>
						<?php if ($this->categories) { ?>
						<div class="filter-select fltrt">
							<?php
								$addcaption	= array(JHtml::_('select.option', '', JText::_('JOPTION_SELECT_CATEGORY'), 'id', 'title'));
								$this->categories ? $categories = array_merge($addcaption, $this->categories) : $categories = $addcaption;
								echo JHTML::_('select.genericlist',  $categories, 'filter_category_id', 'class="inputbox" onchange="this.form.submit()" ', 'id', 'title', $this->state->get('filter.category_id'));
							?>
						</div>
						<?php } ?>
						<?php if ($this->blogs) { ?>
						<div class="filter-select fltrt">
							<?php
								$addcaption	= array(JHtml::_('select.option', '', JText::_('COM_JOOMBLOG_OPTION_SELECT_BLOG'), 'id', 'title'));
								$this->blogs ? $blogs = array_merge($addcaption, $this->blogs) : $blogs = $addcaption;
								echo JHTML::_('select.genericlist',  $blogs, 'filter_blog_id', 'class="inputbox" onchange="this.form.submit()" ', 'id', 'title', $this->state->get('filter.blog_id'));
							?>
						</div>
						<?php } ?>
						<?php if ($this->users) { ?>
						<div class="filter-select fltrt">
							<?php
								$addcaption	= array(JHtml::_('select.option', '', JText::_('COM_JOOMBLOG_OPTION_SELECT_AUTHOR'), 'id', 'name'));
								$this->users ? $users = array_merge($addcaption, $this->users) : $users = $addcaption;
								echo JHTML::_('select.genericlist',  $users, 'filter_author_id', 'class="inputbox" onchange="this.form.submit()" ', 'id', 'name', $this->state->get('filter.author_id'));
							?>
						</div>
						<?php } ?>
					</fieldset>
					<?php } ?>
					<table class="adminlist">
						<thead>
							<tr>
								<th width="1%">
									<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
								</th>
								<th>
									<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'c.title', $listDirn, $listOrder); ?>
								</th>
								<th width="10%" >
									<?php echo JHtml::_('grid.sort', 'JCATEGORY', 'cc.title', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_TAGS'); ?>
								</th>
								<th>
									<?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_HEADING_AUTHOR', 'u.name', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_HEADING_DATE', 'c.created', $listDirn, $listOrder); ?>
								</th>
                                <th>
                                    <?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_PUBLISH_DATE', 'c.publish_up', $listDirn, $listOrder); ?>
                                </th>
                                <th>
                                    <?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_EXPIRE_DATE', 'c.publish_down', $listDirn, $listOrder); ?>
                                </th>
								<th width="1%" class="nowrap">
									<?php echo JHtml::_('grid.sort',  'JGLOBAL_HITS', 'c.hits', $listDirn, $listOrder); ?>
								</th>
								<th width="5%">
									<?php echo JHtml::_('grid.sort', 'JSTATUS', 'c.state', $listDirn, $listOrder); ?>
								</th>
								<th width="1%" class="nowrap">
									<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
							</tr>
						</tfoot>
						<tbody>
						<?php if ($this->drafts) {foreach($this->drafts as $i => $item) {
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="center">
									<?php echo JHtml::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
								<?php if ($this->canDo->get('core.edit') or ($this->canDo->get('core.edit.own') and $item->created_by == $this->user->id)) { ?>
									<a href="<?php echo JRoute::_('index.php?option=com_joomblog&task=post.edit&id='.$item->id);?>">
										<?php echo $this->escape($item->title); ?>
									</a>
								<?php } else { ?>
									<?php echo $this->escape($item->title); ?>
								<?php } ?>
									<p class="smallsub">
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
									</p>
								</td>
								<td>
									<?php
									
										if (sizeof($item->cats))
										{
											foreach ( $item->cats as $cat ) 
											{
												?>
												<a href="<?php echo JRoute::_('index.php?option=com_categories&task=category.edit&id='.$cat->cid.'&extension=com_joomblog'); ?>" >
													<img src="<?php echo JURI::root()?>administrator/components/com_joomblog/assets/images/categories.png" border="0"/>
													<?php echo $cat->title; ?>
												</a><br />
												<?php
											}
										} 
									?>									
								</td>
								<td>
									<?php echo $item->tags; ?>
								</td>
								<td>
									<a href="<?php echo JRoute::_('index.php?option=com_joomblog&task=user.edit&id='.$item->created_by); ?>" >
										<?php echo $item->author; ?>
									</a>
								</td>
								<td class="center">
									<?php echo JHtml::_('date', $item->created); ?>
								</td>
                                <td>
                                    <?php echo JHtml::_('date', $item->publish_up); ?>
                                </td>
                                <td>
                                    <?php echo JHtml::_('date', $item->publish_down); ?>
                                </td>
								<td class="center">
									<?php echo (int) $item->hits; ?>
								</td>
								<td class="center">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'posts.', $this->canDo->get('core.edit'), 'cb'); ?>
								</td>
								<td>
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php }} else { ?>
							<tr>
								<td colspan="11" align="center" >
									<?php if ($this->categories) { ?>
									<?php echo JText::sprintf('COM_JOOMBLOG_FIELD_NONE', 'drafts'); ?>
									<a href="<?php echo JRoute::_('index.php?option=com_joomblog&task=post.edit'); ?>" >
										<?php echo JText::_('COM_JOOMBLOG_FIELD_NONE_A'); ?>
									</a>
									<?php } else { ?>
									<?php echo JText::sprintf('COM_JOOMBLOG_FIELD_NONE', 'categories'); ?>
									<a href="<?php echo JRoute::_('index.php?option=com_categories&task=category.add&extension=com_joomblog'); ?>" >
										<?php echo JText::_('COM_JOOMBLOG_FIELD_NONE_A'); ?>
									</a>
									<?php } ?>
								</td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
					<div>
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="boxchecked" value="0" />
						<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
						<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
						<?php echo JHtml::_('form.token'); ?>
					</div>
				</form>
			</td>
		</tr>
	</tbody>
</table>
