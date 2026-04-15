<?php

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastica\Bulk\ResponseSet;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotFoundException;
use Elastica\Exception\ResponseException;
use Elastica\Index\Recovery as IndexRecovery;
use Elastica\Index\Settings as IndexSettings;
use Elastica\Index\Stats as IndexStats;
use Elastica\Query\AbstractQuery;
use Elastica\ResultSet\BuilderInterface;
use Elastica\Script\AbstractScript;

/**
 * Elastica index object.
 *
 * Handles reads, deletes and configurations of an index
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Index implements SearchableInterface
{
    /**
     * Index name.
     *
     * @var string Index name
     */
    protected $_name;

    /**
     * Client object.
     *
     * @var Client Client object
     */
    protected $_client;

    /**
     * Creates a new index object.
     *
     * All the communication to and from an index goes of this object
     *
     * @param Client $client Client object
     * @param string $name   Index name
     */
    public function __construct(Client $client, string $name)
    {
        $this->_client = $client;
        $this->_name = $name;
    }

    /**
     * Return Index Stats.
     *
     * @return IndexStats
     */
    public function getStats()
    {
        return new IndexStats($this);
    }

    /**
     * Return Index Recovery.
     *
     * @return IndexRecovery
     */
    public function getRecovery()
    {
        return new IndexRecovery($this);
    }

    /**
     * Sets the mappings for the current index.
     *
     * @param Mapping $mapping MappingType object
     * @param array   $query   querystring when put mapping (for example update_all_types)
     */
    public function setMapping(Mapping $mapping, array $query = []): Response
    {
        return $mapping->send($this, $query);
    }

    /**
     * Gets all mappings for the current index.
     */
    public function getMapping(): array
    {
        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->getMapping([
            'index' => $this->getName(),
        ]);
        $response = new Response($esResponse->asArray(), $esResponse->getStatusCode());
        $data = $response->getData();

        // Get first entry as if index is an Alias, the name of the mapping is the real name and not alias name
        $mapping = \array_shift($data);

        return $mapping['mappings'] ?? [];
    }

    /**
     * Returns the index settings object.
     *
     * @return IndexSettings
     */
    public function getSettings()
    {
        return new IndexSettings($this);
    }

    /**
     * @param array|string $data
     *
     * @return Document
     */
    public function createDocument(string $id = '', $data = [])
    {
        return new Document($id, $data, $this);
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * @param Document[] $docs    Array of Elastica\Document
     * @param array      $options Array of query params to use for query. For possible options check es api
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     */
    public function updateDocuments(array $docs, array $options = []): ResponseSet
    {
        foreach ($docs as $doc) {
            $doc->setIndex($this->getName());
        }

        return $this->getClient()->updateDocuments($docs, $options);
    }

    /**
     * Update entries in the db based on a query.
     *
     * @param AbstractQuery|array|Collapse|Query|string|Suggest $query   Query object or array
     * @param AbstractScript                                    $script  Script
     * @param array                                             $options Optional params
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update-by-query.html
     */
    public function updateByQuery($query, AbstractScript $script, array $options = []): Response
    {
        $q = Query::create($query)->getQuery();
        $body = [
            'query' => \is_array($q) ? $q : $q->toArray(),
            'script' => $script->toArray()['script'],
        ];

        $params = \array_merge($options, [
            'index' => $this->getName(),
            'body' => $body,
        ]);

        $esResponse = $this->getClient()->getConnection()->getClient()->updateByQuery($params);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Adds the given document to the search index.
     */
    public function addDocument(Document $doc): Response
    {
        $params = [
            'index' => $this->getName(),
            'body' => $doc->getData(),
        ];

        if (null !== $doc->getId() && '' !== $doc->getId()) {
            $params['id'] = $doc->getId();
        }

        $options = $doc->getOptions(
            [
                'consistency',
                'op_type',
                'parent',
                'percolate',
                'pipeline',
                'refresh',
                'replication',
                'retry_on_conflict',
                'routing',
                'timeout',
            ]
        );

        $params = \array_merge($params, $options);

        try {
            $esResponse = $this->getClient()->getConnection()->getClient()->index($params);
        } catch (ClientResponseException|ServerResponseException $e) {
            // ES9 throws ClientResponseException (4xx) / ServerResponseException (5xx) instead of
            // returning an error response. Wrap into Elastica's ResponseException so callers
            // that catch ResponseException (e.g. for op_type:create conflict handling) still work.
            $psrResponse = $e->getResponse();
            $bodyStream = $psrResponse->getBody();
            if ($bodyStream->isSeekable()) {
                $bodyStream->rewind();
            }
            $elasticaResponse = new Response((string) $bodyStream, $psrResponse->getStatusCode());
            throw new ResponseException(new Request($this->getName().'/_doc'), $elasticaResponse);
        }
        $response = new Response($esResponse->asArray(), $esResponse->getStatusCode());

        $data = $response->getData();
        // set autogenerated id to document
        if ($response->isOk() && (
            $doc->isAutoPopulate() || $this->getClient()->getConfigValue(['document', 'autoPopulate'], false)
        )) {
            if (isset($data['_id']) && !$doc->hasId()) {
                $doc->setId($data['_id']);
            }
            $doc->setVersionParams($data);
        }

        return $response;
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * @param array|Document[] $docs    Array of Elastica\Document
     * @param array            $options Array of query params to use for query. For possible options check es api
     *
     * @return ResponseSet
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     */
    public function addDocuments(array $docs, array $options = [])
    {
        foreach ($docs as $doc) {
            $doc->setIndex($this->getName());
        }

        return $this->getClient()->addDocuments($docs, $options);
    }

    /**
     * Get the document from search index.
     *
     * @param int|string $id      Document id
     * @param array      $options options for the get request
     *
     * @throws ResponseException
     * @throws NotFoundException
     */
    public function getDocument($id, array $options = []): Document
    {
        unset($options[CustomOptions::REQUEST_TAGS]);

        $params = \array_merge($options, [
            'index' => $this->getName(),
            'id' => $id,
        ]);

        try {
            $esResponse = $this->getClient()->getConnection()->getClient()->get($params);
        } catch (ClientResponseException $e) {
            if (404 === $e->getResponse()->getStatusCode()) {
                throw new NotFoundException('doc id '.$id.' not found');
            }
            throw $e;
        }
        $response = new Response($esResponse->asArray(), $esResponse->getStatusCode());
        $result = $response->getData();

        if (!isset($result['found']) || false === $result['found']) {
            throw new NotFoundException('doc id '.$id.' not found');
        }

        if (isset($result['fields'])) {
            $data = $result['fields'];
        } elseif (isset($result['_source'])) {
            $data = $result['_source'];
        } else {
            $data = [];
        }

        $doc = new Document($id, $data, $this->getName());
        $doc->setVersionParams($result);

        return $doc;
    }

    /**
     * Get the document from search index.
     *
     * @param string[] $ids     Document ids
     * @param array    $options options for the get request
     *
     * @throws ResponseException
     * @throws NotFoundException
     *
     * @return array<array-key, Document>
     */
    public function getDocuments(array $ids, array $options = [], bool $throwOnNotFound = true): array
    {
        $client = $this->getClient();
        $isApiV6 = $client->getApiVersion() === ApiVersion::API_VERSION_6;
        $documentType = ($client->getDocumentTypeResolver())($this->getName());

        $docs = [];
        foreach ($ids as $id) {
            $identifiers = [
                '_id' => $id,
            ];

            if ($isApiV6) {
                $identifiers['_type'] = $documentType;
            }

            $docs[] = $identifiers;
        }

        $body = [
            'docs' => $docs,
        ];

        $params = \array_merge($options, [
            'index' => $this->getName(),
            'body' => $body,
        ]);

        $esResponse = $client->getConnection()->getClient()->mget($params);
        $response = new Response($esResponse->asArray(), $esResponse->getStatusCode());
        $results = $response->getData();

        $documents = [];
        $notFoundIds = [];
        foreach ($results['docs'] as $result) {
            $id = $result['_id'] ?? '???';
            if (!isset($result['found']) || false === $result['found']) {
                $notFoundIds[] = $id;
                $documents[$id] = null;
                continue;
            }

            if (isset($result['fields'])) {
                $data = $result['fields'];
            } elseif (isset($result['_source'])) {
                $data = $result['_source'];
            } else {
                $data = [];
            }

            $doc = new Document($id, $data, $this->getName());
            $doc->setVersionParams($result);

            $documents[$id] = $doc;
        }

        if ($notFoundIds !== [] && $throwOnNotFound) {
            throw new NotFoundException(\sprintf('doc ids %s not found', \implode(', ', $notFoundIds)), 0, null, $notFoundIds);
        }

        return $documents;
    }

    /**
     * Deletes a document by its unique identifier.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-delete.html
     */
    public function deleteById(string $id, array $options = []): Response
    {
        if (!\trim($id)) {
            throw new NotFoundException('Doc id "'.$id.'" not found and can not be deleted');
        }

        $params = \array_merge($options, [
            'index' => $this->getName(),
            'id' => \trim($id),
        ]);

        $esResponse = $this->getClient()->getConnection()->getClient()->delete($params);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Deletes documents matching the given query.
     *
     * @param AbstractQuery|array|Collapse|Query|string|Suggest $query   Query object or array
     * @param array                                             $options Optional params
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-delete-by-query.html
     */
    public function deleteByQuery($query, array $options = []): Response
    {
        $query = Query::create($query)->getQuery();

        $params = \array_merge($options, [
            'index' => $this->getName(),
            'body' => ['query' => \is_array($query) ? $query : $query->toArray()],
        ]);

        $esResponse = $this->getClient()->getConnection()->getClient()->deleteByQuery($params);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Opens a Point-in-Time on the index.
     *
     * @see: https://www.elastic.co/guide/en/elasticsearch/reference/current/point-in-time-api.html
     */
    public function openPointInTime(string $keepAlive): Response
    {
        $esResponse = $this->getClient()->getConnection()->getClient()->openPointInTime([
            'index' => $this->getName(),
            'keep_alive' => $keepAlive,
        ]);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Deletes the index.
     */
    public function delete(): Response
    {
        try {
            $esResponse = $this->getClient()->getConnection()->getClient()->indices()->delete([
                'index' => $this->getName(),
            ]);
        } catch (ClientResponseException|ServerResponseException $e) {
            $psrResponse = $e->getResponse();
            $bodyStream = $psrResponse->getBody();
            if ($bodyStream->isSeekable()) {
                $bodyStream->rewind();
            }
            $elasticaResponse = new Response((string) $bodyStream, $psrResponse->getStatusCode());
            throw new ResponseException(new Request($this->getName()), $elasticaResponse);
        }

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Uses the "_bulk" endpoint to delete documents from the server.
     *
     * @param Document[] $docs Array of documents
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     */
    public function deleteDocuments(array $docs): ResponseSet
    {
        foreach ($docs as $doc) {
            $doc->setIndex($this->getName());
        }

        return $this->getClient()->deleteDocuments($docs);
    }

    /**
     * Force merges index.
     *
     * Detailed arguments can be found here in the ES documentation.
     *
     * @param array $args Additional arguments
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-forcemerge.html
     */
    public function forcemerge($args = []): Response
    {
        $params = \array_merge($args, [
            'index' => $this->getName(),
        ]);

        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->forcemerge($params);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Refreshes the index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html
     */
    public function refresh(): Response
    {
        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->refresh([
            'index' => $this->getName(),
        ]);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Creates a new index with the given arguments.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html
     *
     * @param array      $args    Additional arguments to pass to the Create endpoint
     * @param array|bool $options OPTIONAL
     *                            bool=> Deletes index first if already exists (default = false).
     *                            array => Associative array of options (option=>value)
     *
     * @throws InvalidException
     * @throws ResponseException
     *
     * @return Response Server response
     */
    public function create(array $args = [], $options = null): Response
    {
        if (null === $options) {
            if (\func_num_args() >= 2) {
                \trigger_deprecation('ruflin/elastica', '7.1.0', 'Passing null as 2nd argument to "%s()" is deprecated, avoid passing this argument or pass an array instead. It will be removed in 8.0.', __METHOD__);
            }
            $options = [];
        } elseif (\is_bool($options)) {
            \trigger_deprecation('ruflin/elastica', '7.1.0', 'Passing a bool as 2nd argument to "%s()" is deprecated, pass an array with the key "recreate" instead. It will be removed in 8.0.', __METHOD__);
            $options = ['recreate' => $options];
        } elseif (!\is_array($options)) {
            throw new \TypeError(\sprintf('Argument 2 passed to "%s()" must be of type array|bool|null, %s given.', __METHOD__, \is_object($options) ? \get_class($options) : \gettype($options)));
        }

        unset($options[CustomOptions::REQUEST_TAGS]);

        $allowedOptions = [
            'master_timeout',
            'timeout',
            'wait_for_active_shards',
            'recreate',
        ];
        $invalidOptions = \array_diff(\array_keys($options), $allowedOptions);

        if (1 === $invalidOptionCount = \count($invalidOptions)) {
            throw new InvalidException(\sprintf('"%s" is not a valid option. Allowed options are "%s".', \implode('", "', $invalidOptions), \implode('", "', $allowedOptions)));
        }

        if ($invalidOptionCount > 1) {
            throw new InvalidException(\sprintf('"%s" are not valid options. Allowed options are "%s".', \implode('", "', $invalidOptions), \implode('", "', $allowedOptions)));
        }

        if ($options['recreate'] ?? false) {
            try {
                $this->delete();
            } catch (ResponseException $e) {
                // Index can't be deleted, because it doesn't exist
            } catch (ClientResponseException $e) {
                // Only ignore 404 (index does not exist); rethrow permission errors and other failures
                if (404 !== $e->getResponse()->getStatusCode()) {
                    throw $e;
                }
            }
        }

        unset($options['recreate']);

        $params = \array_merge($options, [
            'index' => $this->getName(),
            'body' => $args,
        ]);

        try {
            $esResponse = $this->getClient()->getConnection()->getClient()->indices()->create($params);
        } catch (ClientResponseException|ServerResponseException $e) {
            $psrResponse = $e->getResponse();
            $bodyStream = $psrResponse->getBody();
            if ($bodyStream->isSeekable()) {
                $bodyStream->rewind();
            }
            $elasticaResponse = new Response((string) $bodyStream, $psrResponse->getStatusCode());
            throw new ResponseException(new Request($this->getName()), $elasticaResponse);
        }

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Checks if the given index exists ans is created.
     */
    public function exists(): bool
    {
        try {
            $esResponse = $this->getClient()->getConnection()->getClient()->indices()->exists([
                'index' => $this->getName(),
            ]);

            return 200 === $esResponse->getStatusCode();
        } catch (ClientResponseException $e) {
            if (404 === $e->getResponse()->getStatusCode()) {
                return false;
            }

            throw $e;
        }
    }

    public function createSearch($query = '', $options = null, ?BuilderInterface $builder = null): Search
    {
        $search = new Search($this->getClient(), $builder);
        $search->addIndex($this);
        $search->setOptionsAndQuery($options, $query);

        return $search;
    }

    public function search($query = '', $options = [], string $method = Request::POST): ResultSet
    {
        $search = $this->createSearch($query, $options);

        return $search->search('', $options, $method);
    }

    public function count($query = '', string $method = Request::POST): int
    {
        $search = $this->createSearch($query);

        return $search->count('', false, $method);
    }

    /**
     * Opens an index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-open-close.html
     */
    public function open(): Response
    {
        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->open([
            'index' => $this->getName(),
        ]);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Closes the index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-open-close.html
     */
    public function close(): Response
    {
        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->close([
            'index' => $this->getName(),
        ]);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Returns the index name.
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Returns index client.
     */
    public function getClient(): Client
    {
        return $this->_client;
    }

    /**
     * Adds an alias to the current index.
     *
     * @param bool $replace If set, an existing alias will be replaced
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-aliases.html
     */
    public function addAlias(string $name, bool $replace = false): Response
    {
        $data = ['actions' => []];

        if ($replace) {
            $status = new Status($this->getClient());
            foreach ($status->getIndicesWithAlias($name) as $index) {
                $data['actions'][] = ['remove' => ['index' => $index->getName(), 'alias' => $name]];
            }
        }

        $data['actions'][] = ['add' => ['index' => $this->getName(), 'alias' => $name]];

        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->updateAliases([
            'body' => $data,
        ]);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Removes an alias pointing to the current index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-aliases.html
     */
    public function removeAlias(string $name): Response
    {
        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->deleteAlias([
            'index' => $this->getName(),
            'name' => $name,
        ]);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Returns all index aliases.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->getAlias([
            'index' => $this->getName(),
            'name' => '*',
        ]);
        $response = new Response($esResponse->asArray(), $esResponse->getStatusCode());
        $responseData = $response->getData();

        if (!isset($responseData[$this->getName()])) {
            return [];
        }

        $data = $responseData[$this->getName()];
        if (!empty($data['aliases'])) {
            return \array_keys($data['aliases']);
        }

        return [];
    }

    /**
     * Checks if the index has the given alias.
     */
    public function hasAlias(string $name): bool
    {
        return \in_array($name, $this->getAliases(), true);
    }

    /**
     * Clears the cache of an index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-clearcache.html
     */
    public function clearCache(): Response
    {
        // TODO: add additional cache clean arguments
        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->clearCache([
            'index' => $this->getName(),
        ]);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Flushes the index to storage.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-flush.html
     */
    public function flush(array $options = []): Response
    {
        $params = \array_merge($options, [
            'index' => $this->getName(),
        ]);

        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->flush($params);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Can be used to change settings during runtime. One example is to use it for bulk updating.
     *
     * @param array $data Data array
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html
     */
    public function setSettings(array $data): Response
    {
        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->putSettings([
            'index' => $this->getName(),
            'body' => $data,
        ]);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }

    /**
     * Makes calls to the elasticsearch server based on this index.
     *
     * @param string       $path   Path to call
     * @param string       $method Rest method to use (GET, POST, DELETE, PUT)
     * @param array|string $data   Arguments as array or encoded string
     */
    public function request(string $path, string $method, $data = [], array $queryParameters = []): Response
    {
        $path = $this->getName().'/'.$path;

        return $this->getClient()->request($path, $method, $data, $queryParameters);
    }

    /**
     * Makes calls to the elasticsearch server with usage official client Endpoint based on this index.
     *
     * @param string[] $tags
     * @param mixed    $endpoint
     *
     * @deprecated This method is deprecated in Elasticsearch v9
     */
    public function requestEndpoint($endpoint, array $tags = []): Response
    {
        throw new \RuntimeException('requestEndpoint() is deprecated in Elasticsearch v9. AbstractEndpoint class no longer exists. Use direct client methods like $client->indices()->refresh() instead.');
    }

    /**
     * Run the analysis on the index.
     *
     * @param array $body request body for the `_analyze` API, see API documentation for the required properties
     * @param array $args Additional arguments
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-analyze.html
     */
    public function analyze(array $body, $args = []): array
    {
        $params = \array_merge($args, [
            'index' => $this->getName(),
            'body' => $body,
        ]);

        $esResponse = $this->getClient()->getConnection()->getClient()->indices()->analyze($params);
        $response = new Response($esResponse->asArray(), $esResponse->getStatusCode());
        $data = $response->getData();

        // Support for "Explain" parameter, that returns a different response structure from Elastic
        // @see: https://www.elastic.co/guide/en/elasticsearch/reference/current/_explain_analyze.html
        if (isset($body['explain']) && $body['explain']) {
            return $data['detail'];
        }

        return $data['tokens'];
    }

    /**
     * Update document, using update script.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update.html
     *
     * @param AbstractScript|Document $data    Document or Script with update data
     * @param array                   $options array of query params to use for query
     */
    public function updateDocument($data, array $options = []): Response
    {
        if (!($data instanceof Document) && !($data instanceof AbstractScript)) {
            throw new \InvalidArgumentException('Data should be a Document or Script');
        }

        if (!$data->hasId()) {
            throw new InvalidException('Document or Script id is not set');
        }

        return $this->getClient()->updateDocument($data->getId(), $data, $this->getName(), $options);
    }
}
