<?php
/**
 * @version     1.0.0
 * @package     com_joomlurgy
 * @copyright   
 * @license     
 * @author      nidhi <nidhi.gupta@daffodilsw.com> - http://
 */
// no direct access
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_joomlurgy/assets/joomlurgy.css', 'text/css');
$i=0;
?>

<div class="items">
<!--    <ul class="items_list">
<?php $show = false; ?>
        <?php foreach ($this->items as $item) : ?>

            
				<?php
//					if($item->state == 1 || ($item->state == 0 && JFactory::getUser()->authorise('core.edit.own',' com_joomlurgy.joomlurgyevent.'.$item->id))):
//						$show = true;
//						?>
							<li>
								<a href="//<?php echo JRoute::_('index.php?option=com_joomlurgy&view=joomlurgyevent&id=' . (int)$item->id); ?>"><?php echo $item->name; ?></a>
							</li>
						<?php $show = true; // endif; ?>
<li>
								<a href="<?php echo JRoute::_('index.php?option=com_joomlurgy&view=joomlurgyevent&id=' . (int)$item->id); ?>"><?php echo $item->name; ?></a>
							</li>
<?php endforeach; ?>
        <?php
        if (!$show):
            echo JText::_('COM_JOOMLURGY_NO_ITEMS');
        endif;
        ?>
    </ul>-->



<!--------------------------------->
<ul class="items_list">
<table>
<tr>
	<td class="sectiontableheader" width="4%"><?php echo JText::_('COM_JOOMLURGY_JOOMLURGYSAINTS_DAY');?></td> 
	<td class="sectiontableheader" width="4%"><?php echo JText::_('COM_JOOMLURGY_JOOMLURGYSAINTS_MONTH');?></td> 
	<td class="sectiontableheader" width="38%"><?php echo JText::_('COM_JOOMLURGY_JOOMLURGYSAINTS_NAME');?></td>
	<td class="sectiontableheader" width="18%"><?php echo JText::_('COM_JOOMLURGY_JOOMLURGYEVENTS_SCRIPTURE1');?></td>
	<td class="sectiontableheader" width="18%"><?php echo JText::_('COM_JOOMLURGY_JOOMLURGYEVENTS_SCRIPTURE2');?></td>
	<td class="sectiontableheader" width="18%"><?php echo JText::_('COM_JOOMLURGY_JOOMLURGYEVENTS_GOSPEL');?></td>
</tr>
<?php $show = false; ?>
<?php foreach ($this->items as $item) : 
    
     $show = true; ?>
<tr class="sectiontableentry<?php echo $i%2+1; ?>">
	<td><?php echo $item->day; ?></td>
	<td><?php echo $item->month; ?></td>
	<td><a href="<?php echo JRoute::_('index.php?option=com_joomlurgy&view=joomlurgysaint&id=' . (int)$item->id); ?>"><?php echo $item->name; ?></a></td>
	<td><?php echo $item->scripture1; ?></td>
	<td><?php echo $item->scripture2; ?></td>
	<td><?php echo $item->gospel; ?></td>
</tr>
<?php endforeach; ?>
        <?php
        if (!$show):
            echo JText::_('COM_JOOMLURGY_NO_ITEMS');
        endif;
        ?>
</table>
</ul>
</div>
<?php if ($show): ?>
    <div class="pagination">
        <p class="counter">
            <?php echo $this->pagination->getPagesCounter(); ?>
        </p>
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
<?php endif; ?>