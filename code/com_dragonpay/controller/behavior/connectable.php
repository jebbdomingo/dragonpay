<?php
/**
 * Nucleon Plus
 *
 * @package     Nucleon Plus
 * @copyright   Copyright (C) 2015 - 2020 Nucleon Plus Co. (http://www.nucleonplus.com)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/jebbdomingo/nucleonplus for the canonical source repository
 */

/**
 * Dragonpay Connectable Controller Behavior.
 *
 * @author  Jebb Domingo <https://github.com/jebbdomingo>
 * @package Dragonpay\Component\Payout
 */
class ComDragonpayControllerBehaviorConnectable extends KControllerBehaviorAbstract
{
    /**
     * List of actions
     *
     * @var array
     */
    protected $_actions;

    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_actions = KObjectConfig::unbox($config->actions);
    }

    /**
     * Initializes the options for the object.
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param KObjectConfig $config Configuration options.
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority'   => self::PRIORITY_LOWEST,
            'actions'    => array(),
        ));

        // Append the default action if none is set.
        if (!count($config->actions)) {
            $config->append(array(
                'actions' => array(
                    'before.add',
                    'before.edit'
            )));
        }

        parent::_initialize($config);
    }

    /**
     * Command handler.
     *
     * @param KCommandInterface      $command The command.
     * @param KCommandChainInterface $chain   The chain executing the command.
     * 
     * @return mixed If a handler breaks, returns the break condition. Returns the result of the handler otherwise.
     */
    final public function execute(KCommandInterface $command, KCommandChainInterface $chain)
    {
        $action = $command->getName();

        if (in_array($action, $this->_actions))
        {
            $result = false;

            try
            {
                $env = getenv('APP_ENV');

                // @todo move dragonpay config to its own table
                $config = $this->getObject('com://admin/nucleonplus.model.configs')->item('dragonpay')->fetch();

                // Test payout api connection
                $dragonpay = $config->getJsonValue();
                $url       = $env == 'production' ? "{$dragonpay->payout_url_prod}" : "{$dragonpay->payout_url_test}";
                $client    = new SoapClient("{$url}?wsdl", array('exceptions' => 1));

                $result = true;
            }
            catch(Exception $e)
            {
                if ($e instanceof SoapFault) {
                    $error = "SOAP Fault: (faultcode: {$e->faultcode}, faultstring: {$e->faultstring})";
                }
                else $error = $this->getMessage();

                $this->getContext()->response->addMessage($error, 'exception');
            }
        
            return $result;
        }
    }

    /**
     * Get the behavior name.
     *
     * Hardcode the name to 'loggable'.
     *
     * @return string
     */
    final public function getName()
    {
        return 'connectable';
    }

    /**
     * Get an object handle.
     *
     * Force the object to be enqueued in the command chain.
     *
     * @see execute()
     *
     * @return string A string that is unique, or NULL.
     */
    final public function getHandle()
    {
        return KObjectMixinAbstract::getHandle();
    }
}
