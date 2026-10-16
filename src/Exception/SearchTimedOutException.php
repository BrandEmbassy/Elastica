<?php

namespace Elastica\Exception;

use Elastica\Request;
use Elastica\Response;

/**
 * The search response reports "timed_out": true, so its hits may be incomplete.
 *
 * Only thrown by Client::assertCompleteSearchResponse().
 */
class SearchTimedOutException extends ResponseException
{
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $this->message = 'Search timed out';
    }
}
