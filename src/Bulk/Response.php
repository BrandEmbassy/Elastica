<?php

namespace Elastica\Bulk;

use Elastica\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @var Action
     */
    protected $_action;

    /**
     * @var string
     */
    protected $_opType;

    private int $apiVersion;

    /**
     * @param array|string $responseData
     */
    public function __construct($responseData, Action $action, string $opType, int $apiVersion)
    {
        parent::__construct($responseData);

        $this->_action = $action;
        $this->_opType = $opType;
        $this->apiVersion = $apiVersion;
    }

    public function getAction(): Action
    {
        return $this->_action;
    }

    public function getOpType(): string
    {
        return $this->_opType;
    }

    public function getApiVersion(): int
    {
        return $this->apiVersion;
    }
}
