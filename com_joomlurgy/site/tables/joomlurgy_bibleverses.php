<?php

/**
 * @version     1.0.0
 * @package     com_legalconfirm
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      yogendra <yogendra.singh@daffodilsw.com> - http://
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Joomlurgy_bibleverses Table class
 */
class JoomlurgyTableJoomlurgy_bibleverses extends JTable {
    /**
     * Constructor
     *
     * @param JDatabase A database connector object
     */
    public function __construct(&$db) {
        parent::__construct('#__joomlurgy_bibleverses', 'id', $db);
    }
	
}
