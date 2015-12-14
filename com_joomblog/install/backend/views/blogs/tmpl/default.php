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

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$search 	= $this->escape($this->state->get('filter.search'));
$user		= JFactory::getUser();

$sortFields = array(
	'i.title'       => JText::_('JGLOBAL_TITLE'),
	'i.user_id'     => JText::_('COM_JOOMBLOG_FIELD_HEADING_AUTHOR'),
	'i.create_date' => JText::_('COM_JOOMBLOG_FIELD_HEADING_DATE'),
	'i.hits'        => JText::_('JGLOBAL_HITS'),
	'i.published'   => JText::_('JSTATUS'),
	'i.approved'    => JText::_('COM_JOOMBLOG_APPROVEBLOG'),
	'i.ordering'    => JText::_('JGRID_HEADING_ORDERING'),
	'i.id'          => JText::_('JGRID_HEADING_ID')
);
$sortedByOrder = ($listOrder == 'i.ordering');

if ($sortedByOrder)
{
	$saveOrderingUrl = 'index.php?option=com_joomblog&task=blogs.save_order_ajax';
	JHtml::_('sortablelist.sortable', 'blogsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
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

<form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_joomblog&view=blogs'); ?>" method="post" autocomplete="off">
	<?php if (!empty($this->sidebar)) { ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php } ?>

	<div id="j-main-container" class="<?php echo (empty($this->sidebar) ? '' : 'span10'); ?>">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" value="<?php echo $search; ?>" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
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
					<th width="1" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'i.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="5" class="center">
						<input type="checkbox" name="checkall-toggle" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this);" />
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'i.title', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JText::_('JGLOBAL_DESCRIPTION'); ?>
					</th>
					<th class="center">
						<?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_HEADING_AUTHOR', 'i.user_id', $listDirn, $listOrder); ?>
					</th>
					<th class="center">
						<?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_FIELD_HEADING_DATE', 'i.create_date', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'i.hits', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'i.published', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JHtml::_('grid.sort', 'COM_JOOMBLOG_APPROVEBLOG', 'i.approved', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="center">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'i.ordering', $listDirn, $listOrder); ?>
						<?php if ($listOrder == 'i.ordering') :?>
							<?php echo JHtml::_('grid.order', $this->blogs, 'filesave.png', 'pages.saveorder'); ?>
						<?php endif; ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'i.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
			<tbody>
			<?php
				if (count($this->blogs)) :
					foreach($this->blogs as $i => $item) :
						$ordering	= ($listOrder == 'ordering');
						$canCheckin	= $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canChange	= $user->authorise('core.edit.state',	'com_joomblog.blogs.'.$item->id) && $canCheckin;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="order nowrap center hidden-phone">
						<?php
						$disabledLabel = '';
						$disabledClassName = '';

						if (!$sortedByOrder)
						{
							$disabledLabel = JText::_('JORDERINGDISABLED');
							$disabledClassName = 'inactive tip-top';
						}
						?>
						<span class="sortable-handler hasTooltip <?php echo $disabledClassName; ?>" title="<?php echo $disabledLabel; ?>">
									<i class="icon-menu"></i>
								</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
					</td>
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php if ($this->canDo->get('core.edit') or ($this->canDo->get('core.edit.own') and $item->user_id == $this->user->get('id'))) { ?>
							<a href="<?php echo JRoute::_('index.php?option=com_joomblog&task=blog.edit&id='.$item->id);?>">
								<?php echo $this->escape($item->title); ?>
							</a>
						<?php } else { ?>
							<?php echo $this->escape($item->title); ?>
						<?php } ?>
					</td>
					<td>
						<?php echo $item->description; ?>
					</td>
					<td class="center">
						<a href="<?php echo JRoute::_('index.php?option=com_joomblog&task=user.edit&id='.$item->user_id); ?>" >
							<?php echo $item->author; ?>
						</a>
					</td>
					<td class="center">
						<?php echo JHtml::_('date', $item->create_date); ?>
					</td>
					<td class="center">
						<?php echo (int)$item->hits; ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'blogs.', $this->canDo->get('core.edit'), 'cb'); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.state', JoomBlogHelper::approveStates(), $item->approved, $i, 'dashboard.'); ?>
					</td>
					<?php if ($canChange) : ?>
						<td class="order center">
							<div class="input-prepend">
								<?php if ($listOrder == 'i.ordering') :?>
									<?php if ($listDirn == 'asc') : ?>
										<span class="add-on"><?php echo $this->pagination->orderUpIcon($i, true, 'blogs.orderup', 'JLIB_HTML_MOVE_UP', ($listOrder == 'i.ordering')); ?></span>
										<span class="add-on"><?php echo $this->pagination->orderDownIcon($i, count($this->blogs), true, 'blogs.orderdown', 'JLIB_HTML_MOVE_DOWN', ($listOrder == 'i.ordering')); ?></span>
									<?php elseif ($listDirn == 'desc') : ?>
										<span class="add-on"><?php echo $this->pagination->orderUpIcon($i, true, 'blogs.orderdown', 'JLIB_HTML_MOVE_UP', ($listOrder == 'i.ordering')); ?></span>
										<span class="add-on"><?php echo $this->pagination->orderDownIcon($i, count($this->blogs), true, 'blogs.orderup', 'JLIB_HTML_MOVE_DOWN', ($listOrder == 'i.ordering')); ?></span>
									<?php endif; ?>
								<?php endif; ?>
								<?php $disabled = ($listOrder == 'i.ordering') ? '' : 'disabled="disabled"'; ?>
								<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="width-20 text-area-order" />
							</div>
						</td>
					<?php else : ?>
						<td class="order">
							<?php echo $item->ordering; ?>
						</td>
					<?php endif; ?>
					<td>
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php
					endforeach;
				else : ?>
				<tr>
					<td colspan="10" align="center" >
						<?php echo JText::sprintf('COM_JOOMBLOG_FIELD_NONE', 'blogs'); ?>
						<a class="btn btn-small" href="<?php echo JRoute::_('index.php?option=com_joomblog&task=blog.add'); ?>" >
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
<?php if ($this->messageTrigger) : ?>
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