<?php
namespace Elastica2\Exception;

use Elastica2\Request;
use Elastica2\Response;

/**
 * Connection exception.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ConnectionException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var \Elastica2\Request Request object
     */
    protected $_request;

    /**
     * @var \Elastica2\Response Response object
     */
    protected $_response;

    /**
     * Construct Exception.
     *
     * @param string             $message  Message
     * @param \Elastica2\Request  $request
     * @param \Elastica2\Response $response
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
     * @return \Elastica2\Request Request object
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns response object.
     *
     * @return \Elastica2\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
