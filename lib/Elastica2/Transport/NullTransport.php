<?php
namespace Elastica2\Transport;

use Elastica2\JSON;
use Elastica2\Request;
use Elastica2\Response;

/**
 * Elastica2 Null Transport object.
 *
 * This is used in case you just need a test transport that doesn't do any connection to an elasticsearch
 * host but still returns a valid response object
 *
 * @author James Boehmer <james.boehmer@jamesboehmer.com>
 */
class NullTransport extends AbstractTransport
{
    /**
     * Null transport.
     *
     * @param \Elastica2\Request $request
     * @param array             $params  Hostname, port, path, ...
     *
     * @return \Elastica2\Response Response empty object
     */
    public function exec(Request $request, array $params)
    {
        $response = array(
            'took' => 0,
            'timed_out' => false,
            '_shards' => array(
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
            ),
            'hits' => array(
                'total' => 0,
                'max_score' => null,
                'hits' => array(),
            ),
            'params' => $params,
        );

        return new Response(JSON::stringify($response));
    }
}
