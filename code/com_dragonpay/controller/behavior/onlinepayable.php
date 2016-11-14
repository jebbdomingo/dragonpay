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
class ComDragonpayControllerBehaviorOnlinepayable extends KControllerBehaviorAbstract
{
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

        $this->_actions = KObjectConfig::unbox($config->actions);
        $this->_columns = KObjectConfig::unbox($config->columns);
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
            'columns'    => array(
                'txnid'  => 'id',
                'amount' => 'amount',
                'mode'   => 'mode',
            )
        ));

        // Append the default action if none is set.
        if (!count($config->actions)) {
            $config->append(array('actions' => array('after.add')));
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
            $env = getenv('APP_ENV');

            // @todo move dragonpay config to its own table
            $config = $this->getObject('com://admin/nucleonplus.model.configs')->item('dragonpay')->fetch();

            $dragonpay = $config->getJsonValue();
            $entity    = $this->getEntity($command);
            $data      = $this->getData($entity);

            // $parameters = array(
            //     'merchantid'  => $dragonpay->merchant_id,
            //     'txnid'       => $data['txnid'],
            //     'amount'      => number_format($data['amount'], 2, '.', ''),
            //     'ccy'         => 'PHP',
            //     'description' => 'Order description.',
            //     'email'       => $this->getObject('user')->getEmail(),
            // );




            $parameters = array(
                'merchantId'    => $dragonpay->merchant_id,
                'password'      => $dragonpay->password,
                'merchantTxnId' => $data['txnid'],
                'amount'        => number_format($data['amount'], 2, '.', ''),
                'ccy'           => 'PHP',
                'description'   => 'Order description.',
                'email'         => $this->getObject('user')->getEmail(),
            );

            $url      = $env == 'production' ? "{$dragonpay->merchant_service_prod}" : "{$dragonpay->merchant_service_test}";
            $client   = new SoapClient("{$url}?wsdl");
            $resource = $client->GetTxnToken($parameters);
            $token    = $resource->GetTxnTokenResult;

            $url = $env == 'production' ? "{$dragonpay->url_prod}?" : "{$dragonpay->url_test}?";
            $query['token'] = $token;
            $query['mode']  = $data['mode'];
            $url .= http_build_query($query, '', '&');
            $this->getContext()->response->setRedirect(JRoute::_($url, false));





            // $parameters['key'] = $dragonpay->password;
            // $digest_string     = implode(':', $parameters);

            // unset($parameters['key']);

            // $parameters['digest'] = sha1($digest_string);
            // $parameters['mode']   = $data['mode'];

            // $url = $env == 'production' ? "{$dragonpay->url_prod}?" : "{$dragonpay->url_test}?";
            // $url .= http_build_query($parameters, '', '&');

            // $this->getContext()->response->setRedirect(JRoute::_($url, false));
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
     * @return array Activity data.
     */
    public function getData(KModelEntityInterface $entity)
    {
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
        return 'onlinepayable';
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
