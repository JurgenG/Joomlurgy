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

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_joomlurgy', JPATH_ADMINISTRATOR);
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_joomlurgy/assets/joomlurgy.css', 'text/css')
?>
<?php if ($this->item) : ?>
    <div class="componentheading"><?php echo JText::_('COM_JOOMLURGY_TITLE') ;?> <?php echo $this->item['joomlurgyevent_detail']->name; ?></div>
    <div class="item_fields">
        <h2><?php echo JText::_('COM_JOOMLURGY_SERMONS') ?></h2>

        <table width="100%" class="items_list">
            <tr>
                <td class="sectiontableheader" width="40%"><?php echo JText::_('JGLOBAL_TITLE'); ?></td>

                <td class="sectiontableheader" width="20%"><?php echo JText::_('JAUTHOR'); ?></td>

            </tr>

            <?php
            $i = 0;
            if ($this->item['sermons']) {
                foreach ($this->item['sermons'] as $sermon) {

                    // echo $sermon->id;
                    $Authorname = ($sermon->created_by_alias == "") ? $sermon->author : $sermon->created_by_alias . " (" . $sermon->author . ")";
                    ?>


                    <?php $show = true; ?>
                    <tr class="sectiontableentry<?php echo $i % 2 + 1; ?>">

                        <td><a href="<?php echo $sermon->link; ?>"><?php echo $sermon->title; ?></a></td>
                        <td><?php echo $Authorname; ?></td>

                    </tr>

                    <?
                }
            } else {
                ?>
                <tr ><td colspan="2">
                        <?php
                        echo JText::_('COM_JOOMLURGY_ITEM_NOT_LOADED') . '</td></tr>';
                    }
                    ?>
        </table>

        <!------- Celebrations  --->
        <h2><?php echo JText::_('COM_JOOMLURGY_CELEBRATION') ?></h2>
        <table width="100%" class="items_list">
            <tr>
                <td class="sectiontableheader" width="40%"><?php echo JText::_('JGLOBAL_TITLE'); ?></td>

                <td class="sectiontableheader" width="20%"><?php echo JText::_('JAUTHOR'); ?></td>

            </tr>
            <?php
            if ($this->item['celebrations']) {
                foreach ($this->item['celebrations'] as $celeb) {


                    $Authorname = ($celeb->created_by_alias == "") ? $celeb->author : $celeb->created_by_alias . " (" . $celeb->author . ")";
                    ?>


            <?php $show = true; ?>
                    <tr class="sectiontableentry<?php echo $i % 2 + 1; ?>">

                        <td><a href="<?php echo $celeb->link; ?>"><?php echo $celeb->title; ?></a></td>
                        <td><?php echo $Authorname; ?></td>

                    </tr>

                    <?
                }
            } else {
                ?>
                <tr ><td colspan="2">
                        <?php
                        echo JText::_('COM_JOOMLURGY_ITEM_NOT_LOADED') . '</td></tr>';
                    }
                    ?>
        </table>


        <!----- Docman Document listing   ------>
        <h2><?php echo JText::_('COM_JOOMLURGY_DOCMAN') ?></h2>
        <table width="100%" class="items_list">
            <tr>
                <td class="sectiontableheader" width="40%"><?php echo JText::_('JGLOBAL_TITLE'); ?></td>

                <td class="sectiontableheader" width="20%"><?php echo JText::_('JAUTHOR'); ?></td>

            </tr>


            <?php
            if ($this->item['doc_cat']) {
                foreach ($this->item['doc_cat'] as $doc) {


                    $Authorname = $doc->author;
                    ?>


            <?php $show = true; ?>
                    <tr class="sectiontableentry<?php echo $i % 2 + 1; ?>">

                        <td><a href="<?php echo $doc->link; ?>"><?php echo $doc->dmname; ?></a></td>
                        <td><?php echo $Authorname; ?></td>

                    </tr>

                            <?
                        }
                    } else {
                        ?>
                <tr ><td colspan="2">
        <?php
        echo JText::_('COM_JOOMLURGY_ITEM_NOT_LOADED') . '</td></tr>';
    }
    ?>
        </table>


        <!---  Scripture1  -->
        <h2><?php echo $this->item['joomlurgyevent_detail']->scripture1; ?></h2>

        <ul class="item_fields">
    <?php
    if (!empty($this->item['scripture1'])) {
        foreach ($this->item['scripture1'] as $scripture) {
            ?>
                    <li><span class="versenumber"><?php echo '[' . $scripture->versus_number . ']' . '</span>&nbsp;&nbsp;' . $scripture->versus; ?></li>




                    <?php
                }
            } else {

                echo JText::_('COM_JOOMLURGY_ITEM_NOT_LOADED');
            }
            ?>
        </ul>
        <!---  Scripture2  -->
        <h2><?php echo $this->item['joomlurgyevent_detail']->scripture2; ?></h2>

        <ul class="item_fields">
    <?php
    if (!empty($this->item['scripture2'])) {
        foreach ($this->item['scripture2'] as $scripture) {
            ?>
                    <li><span class="versenumber"><?php echo '[' . $scripture->versus_number . ']' . '</span>&nbsp;&nbsp;' . $scripture->versus; ?></li>




                    <?php
                }
            } else {

                echo JText::_('COM_JOOMLURGY_ITEM_NOT_LOADED');
            }
            ?>
        </ul>

        <!---  Gospel  -->
        <h2><?php echo $this->item['joomlurgyevent_detail']->gospel; ?></h2>
        <ul class="item_fields">
    <?php
    if (!empty($this->item['gospel'])) {
        foreach ($this->item['gospel'] as $scripture) {
            ?>
                    <li><span class="versenumber"><?php echo '[' . $scripture->versus_number . ']' . '</span>&nbsp;&nbsp;' . $scripture->versus; ?></li>



                    <?php
                }
            } else {

                echo JText::_('COM_JOOMLURGY_ITEM_NOT_LOADED');
            }
            ?>
        </ul>






    </div>

    <?php
else:
    echo JText::_('COM_JOOMLURGY_ITEM_NOT_LOADED');
endif;
?>
<hr />
<p class="copyright"><?php echo $this->item['copyright']; ?></p>
<hr />
