<?php
/**
 * Nucleon Plus - Dragonpay
 *
 * @package     Nucleon Plus
 * @copyright   Copyright (C) 2015 - 2020 Nucleon Plus Co. (http://www.nucleonplus.com)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/jebbdomingo/nucleonplus for the canonical source repository
 */

class ComDragonpayDatabaseBehaviorOnlinepayable extends KDatabaseBehaviorAbstract
{
    public function getPaymentCharge()
    {
        $amount = 0;

        if ($this->hasProperty('payment_mode') && !empty($this->payment_mode))
        {
            $rate = $this->getObject('com:dragonpay.model.paymentrates')
                ->mode($this->payment_mode)
                ->fetch()
            ;

            $amount = (float) $rate->amount;
        }

        return $amount;
    }

    public function getPaymentDescription()
    {
        $desc = null;

        if ($this->hasProperty('payment_mode') && !empty($this->payment_mode))
        {
            $rate =  $this->getObject('com:dragonpay.model.paymentrates')
                ->mode($this->payment_mode)
                ->fetch()
            ;

            $desc = $rate->description;
        }

        return $desc;
    }
}
