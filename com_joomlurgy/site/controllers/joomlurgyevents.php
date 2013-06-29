<?php
/**
 * @version     1.0.0
 * @package     com_joomlurgy
 * @copyright   
 * @license     
 * @author      nidhi <nidhi.gupta@daffodilsw.com> - http://
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Joomlurgyevents list controller class.
 */
class JoomlurgyControllerJoomlurgyevents extends JoomlurgyController
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'Joomlurgyevents', $prefix = 'JoomlurgyModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}