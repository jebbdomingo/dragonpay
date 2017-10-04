<?php
/**
 * Nucleon Plus
 *
 * @package     Nucleon Plus
 * @copyright   Copyright (C) 2017 Nucleon Plus Co. (http://www.nucleonplus.com)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/jebbdomingo/nucleonplus for the canonical source repository
 */

class ComDragonpayModelEntityPayout extends KModelEntityRow
{
    const STATUS_PENDING    = 'P';
    const STATUS_SUCCESSFUL = 'S';

    const REQUEST_RESULT_0 = 0;
    const REQUEST_RESULT_1 = -1;
    const REQUEST_RESULT_2 = -2;
    const REQUEST_RESULT_3 = -3;
    const REQUEST_RESULT_4 = -4;
    const REQUEST_RESULT_5 = -5;
    const REQUEST_RESULT_6 = -6;
    const REQUEST_RESULT_7 = -7;
    const REQUEST_RESULT_8 = -8;

    public static $state_messages = array(
        self::REQUEST_RESULT_0 => 'Successfully created payout request',
        self::REQUEST_RESULT_1 => 'Invalid credentials or apiKey',
        self::REQUEST_RESULT_2 => '(reserved) -2 no specific error',
        self::REQUEST_RESULT_3 => '(reserved) -3 no specific error',
        self::REQUEST_RESULT_4 => 'Unable to create payout transaction (internal error)',
        self::REQUEST_RESULT_5 => 'Invalid account no / details',
        self::REQUEST_RESULT_6 => 'Invalid pre-dated run date',
        self::REQUEST_RESULT_7 => 'Amount exceeds limit for payout channel',
        self::REQUEST_RESULT_8 => 'A payout has been previously requested for the same merchant txn id',
    );
}
