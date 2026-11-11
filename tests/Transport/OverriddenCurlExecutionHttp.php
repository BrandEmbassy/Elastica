<?php

namespace Elastica\Test\Transport;

use Elastica\Transport\Http;

class OverriddenCurlExecutionHttp extends Http
{
    /**
     * @var int
     */
    public $executions = 0;

    /**
     * @var string|null Response body returned instead of performing the transfer
     */
    private $responseBody;

    public function setResponseBody(string $responseBody): void
    {
        $this->responseBody = $responseBody;
    }

    protected function _executeCurl($curlConnection)
    {
        ++$this->executions;

        return $this->responseBody ?? parent::_executeCurl($curlConnection);
    }
}
