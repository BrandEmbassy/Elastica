<?php
namespace Elastica2\Bulk;

use Elastica2\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @var \Elastica2\Bulk\Action
     */
    protected $_action;

    /**
     * @var string
     */
    protected $_opType;

    /**
     * @param array|string          $responseData
     * @param \Elastica2\Bulk\Action $action
     * @param string                $opType
     */
    public function __construct($responseData, Action $action, $opType)
    {
        parent::__construct($responseData);

        $this->_action = $action;
        $this->_opType = $opType;
    }

    /**
     * @return \Elastica2\Bulk\Action
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @return string
     */
    public function getOpType()
    {
        return $this->_opType;
    }
}
