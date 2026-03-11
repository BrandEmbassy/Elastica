<?php

namespace Elastica\Index;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Request;
use Elastica\Response;

/**
 * Elastica index stats object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-stats.html
 */
class Stats
{
    /**
     * Response.
     *
     * @var Response Response object
     */
    protected $_response;

    /**
     * Stats info.
     *
     * @var array Stats info
     */
    protected $_data = [];

    /**
     * Index.
     *
     * @var Index
     */
    protected $_index;

    /**
     * Construct.
     */
    public function __construct(Index $index)
    {
        $this->_index = $index;
        $this->refresh();
    }

    /**
     * Returns the raw stats info.
     *
     * @return array Stats info
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * Returns the entry in the data array based on the params.
     * Various params possible.
     *
     * @return mixed Data array entry or null if not found
     */
    public function get(...$args)
    {
        $data = $this->getData();

        foreach ($args as $arg) {
            if (isset($data[$arg])) {
                $data = $data[$arg];
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * Returns the index object.
     */
    public function getIndex(): Index
    {
        return $this->_index;
    }

    /**
     * Returns response object.
     *
     * @return Response Response object
     */
    public function getResponse(): Response
    {
        return $this->_response;
    }

    /**
     * Reloads all status data of this object.
     */
    public function refresh(): void
    {
        try {
            $esResponse = $this->getIndex()->getClient()->getConnection()->getClient()->indices()->stats([
                'index' => $this->getIndex()->getName(),
            ]);
        } catch (ClientResponseException | ServerResponseException $e) {
            // ES9 throws ClientResponseException (e.g. 400 for closed index) instead of returning
            // an error response. Convert to ResponseException so callers can catch it.
            $psrResponse = $e->getResponse();
            $bodyStream = $psrResponse->getBody();
            if ($bodyStream->isSeekable()) {
                $bodyStream->rewind();
            }
            $elasticaResponse = new Response((string) $bodyStream, $psrResponse->getStatusCode());
            throw new ResponseException(
                new Request($this->getIndex()->getName() . '/_stats'),
                $elasticaResponse,
            );
        }
        $this->_response = new Response($esResponse->asArray(), $esResponse->getStatusCode());
        $this->_data = $this->getResponse()->getData();
    }
}
