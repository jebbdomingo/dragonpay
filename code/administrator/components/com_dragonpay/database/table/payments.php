<?php
/**
 * Nucleon Plus - Dragonpay
 *
 * @package     Nucleon Plus
 * @copyright   Copyright (C) 2015 - 2020 Nucleon Plus Co. (http://www.nucleonplus.com)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/jebbdomingo/nucleonplus for the canonical source repository
 */

class ComDragonpayDatabaseTablePayments extends KDatabaseTableAbstract
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'behaviors' => array(
                'modifiable',
                'creatable'
            ),
            'filters' => array(
            )
        ));
        
        parent::_initialize($config);
    }
}
