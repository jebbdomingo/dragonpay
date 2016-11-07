<?php
/**
 * Nucleon Plus
 *
 * @package     Nucleon Plus
 * @copyright   Copyright (C) 2015 - 2020 Nucleon Plus Co. (http://www.nucleonplus.com)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/jebbdomingo/nucleonplus for the canonical source repository
 */

class ComDragonpayControllerPermissionAbstract extends ComKoowaControllerPermissionAbstract
{
    /**
     * Specialized permission check
     *
     * @return boolean
     */
    public function canAdd()
    {
        if ($this->_isAuthentic()) {
            return true;
        }
        else return parent::canAdd();
    }

    public function canEdit()
    {
        if ($this->_isAuthentic()) {
            return true;
        }
        else return parent::canEdit();
    }

    protected function _isAuthentic()
    {
        $result = false;

        // JFactory::getUser()->id is needed to support Joomla login/authentication programatically
        // Nooku's user object needs to reinitialize after calling JFactory::getApplication('site')->login() hence the need for JFactory::getUser()->id
        if (JFactory::getUser()->id || $this->getObject('user')->isAuthentic()) {
            $result = true;
        }

        return $result;
    }
}