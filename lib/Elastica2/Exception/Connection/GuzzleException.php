<?php
namespace Elastica2\Exception\Connection;

use Elastica2\Exception\ConnectionException;
use Elastica2\Request;
use Elastica2\Response;
use GuzzleHttp\Exception\TransferException;

/**
 * Transport exception.
 *
 * @author Milan Magudia <milan@magudia.com>
 */
class GuzzleException extends ConnectionException
{
    /**
     * @var TransferException
     */
    protected $_guzzleException;

    /**
     * @param \GuzzleHttp\Exception\TransferException $guzzleException
     * @param \Elastica2\Request                       $request
     * @param \Elastica2\Response                      $response
     */
    public function __construct(TransferException $guzzleException, Request $request = null, Response $response = null)
    {
        $this->_guzzleException = $guzzleException;
        $message = $this->getErrorMessage($this->getGuzzleException());
        parent::__construct($message, $request, $response);
    }

    /**
     * @param \GuzzleHttp\Exception\TransferException $guzzleException
     *
     * @return string
     */
    public function getErrorMessage(TransferException $guzzleException)
    {
        return $guzzleException->getMessage();
    }

    /**
     * @return TransferException
     */
    public function getGuzzleException()
    {
        return $this->_guzzleException;
    }
}
