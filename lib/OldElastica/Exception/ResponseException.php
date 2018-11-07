<?php
namespace OldElastica\Exception;

use OldElastica\Request;
use OldElastica\Response;

/**
 * Response exception.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ResponseException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var \OldElastica\Request Request object
     */
    protected $_request = null;

    /**
     * @var \OldElastica\Response Response object
     */
    protected $_response = null;

    /**
     * Construct Exception.
     *
     * @param \OldElastica\Request  $request
     * @param \OldElastica\Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->_request = $request;
        $this->_response = $response;
        parent::__construct($response->getError());
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

    /**
     * Returns elasticsearch exception.
     *
     * @return ElasticsearchException
     */
    public function getElasticsearchException()
    {
        $response = $this->getResponse();
        $transfer = $response->getTransferInfo();
        $code = array_key_exists('http_code', $transfer) ? $transfer['http_code'] : 0;

        return new ElasticsearchException($code, $response->getError());
    }
}
