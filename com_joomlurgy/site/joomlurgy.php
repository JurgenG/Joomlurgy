<?php
/**
 * @version     1.0.0
 * @package     com_joomlurgy
 * @copyright   
 * @license     
 * @author      nidhi <nidhi.gupta@daffodilsw.com> - http://
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . '/helpers/joomlurgy.php';
// Execute the task.
$controller	= JController::getInstance('Joomlurgy');
if(JFactory::getApplication()->input->get('view') == 'single'){
    //die('www');
    JFactory::getApplication()->input->set('view', 'joomlurgyevent');
    
}
//echo JFactory::getApplication()->input->get('view'); die;
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
