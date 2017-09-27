<?php
/**
 * Dragonpay
 *
 * @package     Dragonpay
 * @copyright   Copyright (C) 2017 Nucleon Plus Co. (http://www.rewardlabs.com)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/jebbdomingo/dragonpay for the canonical source repository
 */

class ComDragonpayModelPayouts extends KModelDatabase
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('status', 'string')
            ->insert('txnid', 'int')
        ;
    }

    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if ($state->status) {
            $query->where('(tbl.status IN :status)')->bind(array('status' => (array) $state->status));
        }

        if ($state->txnid) {
            $query->where('(tbl.txnid IN :txnid)')->bind(array('txnid' => (array) $state->txnid));
        }
    }
}
