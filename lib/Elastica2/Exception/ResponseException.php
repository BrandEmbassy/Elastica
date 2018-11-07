<?php
namespace Elastica2\Exception;

use Elastica2\Request;
use Elastica2\Response;

/**
 * Response exception.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ResponseException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var \Elastica2\Request Request object
     */
    protected $_request = null;

    /**
     * @var \Elastica2\Response Response object
     */
    protected $_response = null;

    /**
     * Construct Exception.
     *
     * @param \Elastica2\Request  $request
     * @param \Elastica2\Response $response
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
