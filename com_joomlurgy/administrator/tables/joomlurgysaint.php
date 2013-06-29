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

/**
 * joomlurgyevent Table class
 */
class JoomlurgyTablejoomlurgysaint extends JTable {

    /**
     * Constructor
     *
     * @param JDatabase A database connector object
     */
    public function __construct(&$db) {
        parent::__construct('#__joomlurgy_saints', 'id', $db);
    }

   
    
    

}
