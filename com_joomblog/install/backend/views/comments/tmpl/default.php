<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomPortfolio
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// load tooltip behavior
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$sortFields = array(
	'i.comment'   => JText::_('COM_JOOMBLOG_FIELD_HEADING_COMMENT'),
	'c.title'     => JText::_('COM_JOOMBLOG_FIELD_HEADING_POST'),
	'i.name'      => JText::_('COM_JOOMBLOG_FIELD_HEADING_AUTHOR'),
	'i.created'   => JText::_('COM_JOOMBLOG_FIELD_HEADING_DATE'),
	'i.published' => JText::_('JSTATUS'),
	'c.id'        => JText::_('JGRID_HEADING_ID')
);

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$search 	= $this->escape($this->state->get('filter.search'));
?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		table = document.getElementById('sortTable');
		direction = document.getElementById('directionTable');
		order = table.options[table.selectedIndex].value;

		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<?php echo JoomBlogHelper::getMenuPanel(); ?>

<form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_joomblog&view=comments'); ?>" method="post" autocomplete="off">
	<?php if (!empty($this->sidebar)) { ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php } ?>

	<div id="j-main-container" class="<?php echo (empty($this->sidebar) ? '' : 'span10'); ?>">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" value="<?php
				echo $search; ?>" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php
				echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right">
			</div>
			<div class="btn-group pull-right">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
				</select>
			</div>
		</div>

		<div class="clearfix"></div>

		<table id="blogsList" class="table table-striped">
			<thead>
				<tr>
					<th width="1%" class="center">
                        <input type="checkbox" name="checkall-toggle" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this);" />
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_HEADING_COMMENT', 'i.comment', $listDirn, $listOrder); ?>
					</th>
					<th class="center">
						<?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_HEADING_POST', 'c.title', $listDirn, $listOrder); ?>
					</th>
					<th class="center">
						<?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_HEADING_AUTHOR', 'i.name', $listDirn, $listOrder); ?>
					</th>
					<th class="center">
						<?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_HEADING_DATE', 'i.created', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'i.published', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'i.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			if ($this->comments):
				foreach($this->comments as $i => $item):
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center" >
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php if ($this->canDo->get('core.admin')): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_joomblog&task=comment.edit&id='.$item->id);?>" ><?php echo JText::_('COM_JOOMBLOG_FIELD_HEADING_COMMENT'); ?>:</a>
							<?php echo $this->crop($item->comment); ?>
						<?php else: ?>
							<?php echo $this->crop($item->comment); ?>
						<?php endif; ?>
					</td>
					<td class="center">
						<a href="<?php echo JRoute::_('index.php?option=com_joomblog&task=post.edit&id='.$item->contentid);?>">
							<?php echo $item->post_title; ?>
						</a>
					</td>
					<td class="center">
						<?php if ($item->user_id): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_joomblog&task=user.edit&id='.$item->user_id); ?>" >
								<?php echo $item->author; ?>
							</a>
						<?php else: ?>
							<?php echo ucfirst($item->name); ?>
						<?php endif; ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('date', $item->created); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'comments.', $this->canDo->get('core.admin'), 'cb'); ?>
					</td>
					<td class="center">
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php
				endforeach;
			else:
				?>
				<tr>
					<td colspan="7" class="center">
						<?php echo JText::sprintf('COM_JOOMBLOG_FIELD_NONE', 'comments'); ?>
						<a class="btn btn-small" href="<?php echo JRoute::_('index.php?option=com_joomblog&task=comment.add'); ?>" >
							<?php echo JText::_('COM_JOOMBLOG_FIELD_NONE_A'); ?>
						</a>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>

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
