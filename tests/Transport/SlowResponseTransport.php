<?php

namespace Elastica\Test\Transport;

use Elastica\Request;
use Elastica\Response;
use Elastica\Transport\AbstractTransport;

class SlowResponseTransport extends AbstractTransport
{
    public function exec(Request $request, array $params): Response
    {
        $response = new Response(\json_encode(['hits' => 'ok']), 200);
        $response->setQueryTime(5.0);

        return $response;
    }
}
