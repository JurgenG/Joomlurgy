<?php
/**
 * @version     1.0.0
 * @package     com_joomlurgy
 * @copyright   
 * @license     
 * @author      nidhi <nidhi.gupta@daffodilsw.com> - http://
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Joomlurgyevent controller class.
 */
class JoomlurgyControllerJoomlurgyevent extends JControllerForm
{

    function __construct() {
        $this->view_list = 'joomlurgyevents';
        parent::__construct();
    }

}