<?php
namespace OldElastica\Transport;

use OldElastica\Connection;
use OldElastica\Exception\InvalidException;
use OldElastica\Param;
use OldElastica\Request;

/**
 * OldElastica Abstract Transport object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class AbstractTransport extends Param
{
    /**
     * @var \OldElastica\Connection
     */
    protected $_connection;

    /**
     * Construct transport.
     *
     * @param \OldElastica\Connection $connection Connection object
     */
    public function __construct(Connection $connection = null)
    {
        if ($connection) {
            $this->setConnection($connection);
        }
    }

    /**
     * @return \OldElastica\Connection Connection object
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @param \OldElastica\Connection $connection Connection object
     *
     * @return $this
     */
    public function setConnection(Connection $connection)
    {
        $this->_connection = $connection;

        return $this;
    }

    /**
     * Executes the transport request.
     *
     * @param \OldElastica\Request $request Request object
     * @param array             $params  Hostname, port, path, ...
     *
     * @return \OldElastica\Response Response object
     */
    abstract public function exec(Request $request, array $params);

    /**
     * Create a transport.
     *
     * The $transport parameter can be one of the following values:
     *
     * * string: The short name of a transport. For instance "Http", "Memcache" or "Thrift"
     * * object: An already instantiated instance of a transport
     * * array: An array with a "type" key which must be set to one of the two options. All other
     *          keys in the array will be set as parameters in the transport instance
     *
     * @param mixed                $transport  A transport definition
     * @param \OldElastica\Connection $connection A connection instance
     * @param array                $params     Parameters for the transport class
     *
     * @throws \OldElastica\Exception\InvalidException
     *
     * @return AbstractTransport
     */
    public static function create($transport, Connection $connection, array $params = array())
    {
        if (is_array($transport) && isset($transport['type'])) {
            $transportParams = $transport;
            unset($transportParams['type']);

            $params = array_replace($params, $transportParams);
            $transport = $transport['type'];
        }

        if (is_string($transport)) {
            $className = 'OldElastica\\Transport\\'.$transport;

            if (!class_exists($className)) {
                throw new InvalidException('Invalid transport');
            }

            $transport = new $className();
        }

        if ($transport instanceof self) {
            $transport->setConnection($connection);

            foreach ($params as $key => $value) {
                $transport->setParam($key, $value);
            }
        } else {
            throw new InvalidException('Invalid transport');
        }

        return $transport;
    }
}
