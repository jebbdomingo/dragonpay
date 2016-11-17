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
 * Dragonpay Payment Controller Behavior.
 *
 * @author  Jebb Domingo <https://github.com/jebbdomingo>
 * @package Dragonpay\Component\Payout
 */
class ComDragonpayControllerBehaviorCancellable extends KControllerBehaviorAbstract
{
    /**
     * Controller
     *
     * @var array
     */
    protected $_controller;

    /**
     * List of actions
     *
     * @var array
     */
    protected $_actions;

    /**
     * List of columns
     *
     * @var array
     */
    protected $_columns;

    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_controller = $config->controller;
        $this->_actions    = KObjectConfig::unbox($config->actions);
        $this->_columns    = KObjectConfig::unbox($config->columns);
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
            'controller' => 'com:dragonpay.controller.payment',
            'actions'    => array(),
            'columns'    => array(
                'txnId' => 'id',
            )
        ));

        // Append the default action if none is set.
        if (!count($config->actions)) {
            $config->append(array('actions' => array('before.edit')));
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
        $controller = $this->getObject($this->_controller);
        $action     = $command->getName();

        if ($controller instanceof KControllerModellable && in_array($action, $this->_actions))
        {
            $result = false;

            try
            {
                $env = getenv('APP_ENV');

                // @todo move dragonpay config to its own table
                $config = $this->getObject('com://admin/nucleonplus.model.configs')->item('dragonpay')->fetch();

                $dragonpay = $config->getJsonValue();
                $entities  = $this->getEntity($command);

                foreach ($entities as $entity)
                {
                    $data = $this->getData($entity);

                    $parameters = array(
                        'merchantId'  => $dragonpay->merchant_id,
                        'merchantPwd' => $dragonpay->password,
                        'txnId'       => "Cancellation Request for Order #{$data['txnId']}",
                    );

                    $url      = $env == 'production' ? "{$dragonpay->merchant_service_prod}" : "{$dragonpay->merchant_service_test}";
                    $client   = new SoapClient("{$url}?wsdl");
                    $resource = $client->CancelTransaction($parameters);
                    $status   = $resource->CancelTransactionResult;

                    // Record dragonpay payment transaction
                    $data['result'] = $status;
                    $this->_recordPaymentStatus($data);

                    if ($status === 0) {
                        $result = true;
                    } else {
                        $this->getContext()->response->addMessage("Dragonpay cancel action on txnid {$data['txnId']} failed.", 'error');
                    }
                }
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

    protected function _recordPaymentStatus($data)
    {
        $controller = $this->getObject('com:dragonpay.controller.payment');
        $payment    = $controller->getModel()->id($data['txnId'])->fetch();

        if ($payment->isNew())
        {
            $data['id'] = $data['txnId'];
            $controller->add($data);
        }
        else
        {
            $controller
                ->id($data['txnId'])
                ->edit($data)
            ;
        }
    }

    /**
     * Get the entity.
     *
     * @param KCommandInterface $command The command.
     *
     * @return KModelEntityInterface
     */
    public function getEntity(KCommandInterface $command)
    {
        $parts = explode('.', $command->getName());

        // Properly fetch data for the event.
        if ($parts[0] == 'before') {
            $entity = $command->getSubject()->getModel()->fetch();
        } else {
            $entity = $command->result;
        }

        return $entity;
    }

    /**
     * Get the data.
     *
     * @param KModelEntityInterface $entity
     * 
     * @return array data.
     */
    public function getData(KModelEntityInterface $entity)
    {
        $data = array();

        foreach ($this->_columns as $name => $column)
        {
            if ($entity->{$column}) {
                $data[$name] = $entity->{$column};
            }
        }

        return $data;
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
        return 'cancellable';
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
