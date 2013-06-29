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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_joomlurgy/assets/css/joomlurgy.css');
?>
<script type="text/javascript">
    function getScript(url,success) {
        var script = document.createElement('script');
        script.src = url;
        var head = document.getElementsByTagName('head')[0],
        done = false;
        // Attach handlers for all browsers
        script.onload = script.onreadystatechange = function() {
            if (!done && (!this.readyState
                || this.readyState == 'loaded'
                || this.readyState == 'complete')) {
                done = true;
                success();
                script.onload = script.onreadystatechange = null;
                head.removeChild(script);
            }
        };
        head.appendChild(script);
    }
    getScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',function() {
        js = jQuery.noConflict();
        js(document).ready(function(){
            

            Joomla.submitbutton = function(task)
            {
                if (task == 'joomlurgyevent.cancel') {
                    Joomla.submitform(task, document.getElementById('joomlurgyevent-form'));
                }
                else{
                    
                    if (task != 'joomlurgyevent.cancel' && document.formvalidator.isValid(document.id('joomlurgyevent-form'))) {
                        
                        Joomla.submitform(task, document.getElementById('joomlurgyevent-form'));
                    }
                    else {
                        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
                    }
                }
            }
        });
    });
</script>

<form action="<?php echo JRoute::_('index.php?option=com_joomlurgy&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="joomlurgyevent-form" class="form-validate">
    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_JOOMLURGY_LEGEND_JOOMLURGYEVENT'); ?></legend>
            <ul class="adminformlist">

                				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
				<li><?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?></li>
				<li><?php echo $this->form->getLabel('created_by'); ?>
				<?php echo $this->form->getInput('created_by'); ?></li>
				<li><?php echo $this->form->getLabel('name'); ?>
				<?php echo $this->form->getInput('name'); ?></li>
				<li><?php echo $this->form->getLabel('cycle'); ?>
				<?php echo $this->form->getInput('cycle'); ?></li>
				<li><?php echo $this->form->getLabel('period'); ?>
				<?php echo $this->form->getInput('period'); ?></li>
				<li><?php echo $this->form->getLabel('detail'); ?>
				<?php echo $this->form->getInput('detail'); ?></li>
				<li><?php echo $this->form->getLabel('weight'); ?>
				<?php echo $this->form->getInput('weight'); ?></li>
				<li><?php echo $this->form->getLabel('scripture1'); ?>
				<?php echo $this->form->getInput('scripture1'); ?></li>
				<li><?php echo $this->form->getLabel('scripture2'); ?>
				<?php echo $this->form->getInput('scripture2'); ?></li>
				<li><?php echo $this->form->getLabel('gospel'); ?>
				<?php echo $this->form->getInput('gospel'); ?></li>
				<li><?php echo $this->form->getLabel('category'); ?>
				<?php echo $this->form->getInput('category'); ?></li>
				<li><?php echo $this->form->getLabel('cat_celeb'); ?>
				<?php echo $this->form->getInput('cat_celeb'); ?></li>
				<li><?php echo $this->form->getLabel('cat_doc'); ?>
				<?php echo $this->form->getInput('cat_doc'); ?></li>
				<li><?php echo $this->form->getLabel('created_date'); ?>
				<?php echo $this->form->getInput('created_date'); ?></li>
				<li><?php echo $this->form->getLabel('modified_date'); ?>
				<?php echo $this->form->getInput('modified_date'); ?></li>


            </ul>
        </fieldset>
    </div>

    <div class="clr"></div>

<?php if (JFactory::getUser()->authorise('core.admin','joomlurgy')): ?>
	<div class="width-100 fltlft">
		<?php echo JHtml::_('sliders.start', 'permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
		<?php echo JHtml::_('sliders.panel', JText::_('ACL Configuration'), 'access-rules'); ?>
		<fieldset class="panelform">
			<?php echo $this->form->getLabel('rules'); ?>
			<?php echo $this->form->getInput('rules'); ?>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>
<?php endif; ?>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
    <div class="clr"></div>

    <style type="text/css">
        /* Temporary fix for drifting editor fields */
        .adminformlist li {
            clear: both;
        }
    </style>
</form>