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

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Joomlurgysaint controller class.Used for the routing class must exists here.because it search for the class existance
 */
class JoomlurgyControllerJoomlurgysaint extends JoomlurgyController
{

  function cancel() {
		$menu = & JSite::getMenu();
        $item = $menu->getActive();
        $this->setRedirect(JRoute::_($item->link, false));
    }
    
    
    
}
