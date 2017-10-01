<?php
/**
 * Nucleon Plus - Dragonpay
 *
 * @package     Nucleon Plus
 * @copyright   Copyright (C) 2015 - 2020 Nucleon Plus Co. (http://www.nucleonplus.com)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/jebbdomingo/nucleonplus for the canonical source repository
 */

/**
 * Dragonpay Mass Payout Controller Behavior.
 *
 * @author  Jebb Domingo <https://github.com/jebbdomingo>
 * @package Dragonpay\Component\Payout
 */
class ComDragonpayControllerBehaviorMasspayable extends KControllerBehaviorAbstract
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
     * On error callback
     *
     * @var array
     */
    protected $_on_error_callback;

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

        $this->_controller        = $config->controller;
        $this->_actions           = KObjectConfig::unbox($config->actions);
        $this->_on_error_callback = $config->onErrorCallack;
        $this->_columns           = KObjectConfig::unbox($config->columns);
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
            'controller'     => 'com://admin/dragonpay.controller.payout',
            'actions'        => array('after.processing'),
            'onErrorCallack' => 'save', // Entity method to call when dragonpay payout failed
            'columns'        => array(
                'merchantTxnId' => 'id',
                'userName'      => 'username',
                'amount'        => 'amount',
                'procDetail'    => 'account_number',
                'email'         => 'email',
                'mobileNo'      => 'mobile',
                'runDate'       => 'run_date',
            )
        ));

        // Append the default action if none is set.
        if (!count($config->actions)) {
            $config->append(array('actions' => array('after.edit')));
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
            $env            = getenv('APP_ENV');
            $error_callback = $this->_on_error_callback;

            // @todo move dragonpay config to its own table
            $config = $this->getObject('com://site/rewardlabs.model.configs')->item('dragonpay')->fetch();

            $dragonpay = $config->getJsonValue();
            $entities  = $this->getEntity($command);


            foreach ($entities as $entity)
            {
                $data = $this->getData($entity);

                $parameters = array(
                    'apiKey'      => $dragonpay->payout_api_key,
                    'currency'    => 'PHP',
                    'description' => "Payout Request for Payout #{$data['merchantTxnId']}",
                    'procId'      => 'BDO'
                );

                $parameters = array_merge($parameters, $data);

                try
                {
                    $wsdl     = $env == 'production' ? $dragonpay->payout_url_prod : $dragonpay->payout_url_test;
                    $client   = new SoapClient($wsdl . '?WSDL');
                    $resource = $client->RequestPayoutEx($parameters);
                    $result   = $resource->RequestPayoutExResult;

                    $controller->add(array(
                        'id'     => $data['merchantTxnId'],
                        'txnid'  => $data['merchantTxnId'],
                        'result' => (string) $result
                    ));

                    if ($result != 0) {
                        $entity->$error_callback();
                    }
                }
                catch(Exception $e)
                {
                    $entity->$error_callback();

                    if ($e instanceof SoapFault) {
                        $error = "SOAP Fault: (faultcode: {$e->faultcode}, faultstring: {$e->faultstring})";
                    }
                    else $error = $e->getMessage();

                    $parameters = '<pre>' . print_r($parameters, true) . '</pre>';

                    $this->getContext()->response->addMessage($error, 'exception');
                    $this->getContext()->response->addMessage($parameters, 'exception');
                }
            }
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
        return 'masspayable';
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
