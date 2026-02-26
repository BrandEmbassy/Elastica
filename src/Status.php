<?php

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Exception\ResponseException;

/**
 * Elastica general status.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-status.html
 */
class Status
{
    /**
     * Contains all status infos.
     *
     * @var Response
     */
    protected $_response;

    /**
     * Data.
     *
     * @var array<string, mixed> Data
     */
    protected $_data;

    /**
     * @var Client
     */
    protected $_client;

    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * Returns status data.
     *
     * @return array<string, mixed> Status data
     */
    public function getData()
    {
        if (null === $this->_data) {
            $this->refresh();
        }

        return $this->_data;
    }

    /**
     * Returns a list of the existing index names.
     *
     * @return string[]
     */
    public function getIndexNames()
    {
        $data = $this->getData();

        return \array_map(static function ($name): string {
            return (string) $name;
        }, \array_keys($data['indices']));
    }

    /**
     * Checks if the given index exists.
     *
     * @return bool True if index exists
     */
    public function indexExists(string $name)
    {
        return \in_array($name, $this->getIndexNames(), true);
    }

    /**
     * Checks if the given alias exists.
     *
     * @return bool True if alias exists
     */
    public function aliasExists(string $name)
    {
        return \count($this->getIndicesWithAlias($name)) > 0;
    }

    /**
     * Returns an array with all indices that the given alias name points to.
     *
     * @return Index[]
     */
    public function getIndicesWithAlias(string $alias)
    {
        try {
            $esResponse = $this->_client->getConnection()->getClient()->indices()->getAlias([
                'name' => $alias,
            ]);
            $response = new Response($esResponse->asArray(), $esResponse->getStatusCode());
        } catch (ResponseException $e) {
            // 404 means the index alias doesn't exist which means no indexes have it.
            if (404 === $e->getResponse()->getStatus()) {
                return [];
            }
            // If we don't have a 404 then this is still unexpected so rethrow the exception.
            throw $e;
        } catch (ClientResponseException $e) {
            if (404 === $e->getCode()) {
                return [];
            }
            throw $e;
        }

        $indices = [];
        foreach ($response->getData() as $name => $unused) {
            $indices[] = new Index($this->_client, $name);
        }

        return $indices;
    }

    /**
     * Returns response object.
     *
     * @return Response Response object
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            $this->refresh();
        }

        return $this->_response;
    }

    /**
     * Return shards info.
     *
     * @return array<string, mixed> Shards info
     */
    public function getShards()
    {
        $data = $this->getData();

        return $data['shards'];
    }

    /**
     * Refresh status object.
     */
    public function refresh(): void
    {
        $esResponse = $this->_client->getConnection()->getClient()->indices()->stats();
        $this->_response = new Response($esResponse->asArray(), $esResponse->getStatusCode());
        $this->_data = $this->getResponse()->getData();
    }
}
