<?php
namespace OldElastica\Exception;

use OldElastica\Request;
use OldElastica\Response;

/**
 * Connection exception.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ConnectionException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var \OldElastica\Request Request object
     */
    protected $_request;

    /**
     * @var \OldElastica\Response Response object
     */
    protected $_response;

    /**
     * Construct Exception.
     *
     * @param string             $message  Message
     * @param \OldElastica\Request  $request
     * @param \OldElastica\Response $response
     */
    public function __construct($message, Request $request = null, Response $response = null)
    {
        $this->_request = $request;
        $this->_response = $response;

        parent::__construct($message);
    }

    /**
     * Returns request object.
     *
     * @return \OldElastica\Request Request object
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns response object.
     *
     * @return \OldElastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
