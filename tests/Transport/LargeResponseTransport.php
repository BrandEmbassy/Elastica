<?php

namespace Elastica\Test\Transport;

use Elastica\Request;
use Elastica\Response;
use Elastica\Transport\AbstractTransport;

class LargeResponseTransport extends AbstractTransport
{
    /**
     * Exact byte size of the response body this transport returns. Boundary tests derive their
     * thresholds from this constant so they can't silently invert if the payload below changes.
     */
    public const RESPONSE_SIZE_IN_BYTES = 1035;

    public function exec(Request $request, array $params): Response
    {
        // Pad the payload so the encoded body is exactly RESPONSE_SIZE_IN_BYTES bytes.
        $envelopeSize = \strlen(\json_encode(['hits' => '']));
        $payload = \str_repeat('x', self::RESPONSE_SIZE_IN_BYTES - $envelopeSize);

        $response = new Response(\json_encode(['hits' => $payload]), 200);
        $response->setQueryTime(0.0);

        return $response;
    }
}
