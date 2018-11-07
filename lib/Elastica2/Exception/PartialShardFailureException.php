<?php
namespace Elastica2\Exception;

use Elastica2\JSON;
use Elastica2\Request;
use Elastica2\Response;

/**
 * Partial shard failure exception.
 *
 * @author Ian Babrou <ibobrik@gmail.com>
 */
class PartialShardFailureException extends ResponseException
{
    /**
     * Construct Exception.
     *
     * @param \Elastica2\Request  $request
     * @param \Elastica2\Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $shardsStatistics = $response->getShardsStatistics();
        $this->message = JSON::stringify($shardsStatistics['failed']);
    }
}
