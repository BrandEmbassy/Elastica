<?php

namespace Elastica;

use function array_merge;

/**
 * Represents elasticsearch task.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/tasks.html
 */
class Task extends Param
{
    public const WAIT_FOR_COMPLETION = 'wait_for_completion';
    public const WAIT_FOR_COMPLETION_FALSE = 'false';
    public const WAIT_FOR_COMPLETION_TRUE = 'true';

    /**
     * Task id, e.g. in form of nodeNumber:taskId.
     *
     * @var string
     */
    protected $_id;

    /**
     * @var Response
     */
    protected $_response;

    /**
     * @var array<string, mixed>
     */
    protected $_data;

    /**
     * @var Client
     */
    protected $_client;

    public function __construct(Client $client, string $id)
    {
        $this->_client = $client;
        $this->_id = $id;
    }

    /**
     * Returns task id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns task data.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        if (null === $this->_data) {
            $this->refresh();
        }

        return $this->_data;
    }

    /**
     * Returns response object.
     */
    public function getResponse(): Response
    {
        if (null === $this->_response) {
            $this->refresh();
        }

        return $this->_response;
    }

    /**
     * Refresh task status.
     *
     * @param array<string, mixed> $options
     */
    public function refresh(array $options = []): void
    {
        $params = array_merge($options, [
            'task_id' => $this->_id,
        ]);

        $esResponse = $this->_client->getConnection()->getClient()->tasks()->get($params);
        $this->_response = new Response($esResponse->asArray(), $esResponse->getStatusCode());
        $this->_data = $this->getResponse()->getData();
    }

    public function isCompleted(): bool
    {
        $data = $this->getData();

        return true === $data['completed'];
    }

    public function cancel(): Response
    {
        if ('' === $this->_id) {
            throw new \Exception('No task id given');
        }

        $esResponse = $this->_client->getConnection()->getClient()->tasks()->cancel([
            'task_id' => $this->_id,
        ]);

        return new Response($esResponse->asArray(), $esResponse->getStatusCode());
    }
}
