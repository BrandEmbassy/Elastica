<?php

namespace Elastica\Test\Transport;

use Elastica\Request;
use Elastica\Response;
use Elastica\Transport\AbstractTransport;

class LargeResponseTransport extends AbstractTransport
{
    public function exec(Request $request, array $params): Response
    {
        $response = new Response(\json_encode(['hits' => \str_repeat('x', 1024)]), 200);
        $response->setQueryTime(0.0);

        return $response;
    }
}
