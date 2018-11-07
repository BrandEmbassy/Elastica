<?php
namespace OldElastica\Exception;

use OldElastica\JSON;
use OldElastica\Request;
use OldElastica\Response;

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
     * @param \OldElastica\Request  $request
     * @param \OldElastica\Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $shardsStatistics = $response->getShardsStatistics();
        $this->message = JSON::stringify($shardsStatistics['failed']);
    }
}
