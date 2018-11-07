<?php
namespace OldElastica\Bulk\Action;

use OldElastica\AbstractUpdateAction;

class DeleteDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_DELETE;

    /**
     * @param \OldElastica\AbstractUpdateAction $action
     *
     * @return array
     */
    protected function _getMetadata(AbstractUpdateAction $action)
    {
        $params = array(
            'index',
            'type',
            'id',
            'version',
            'version_type',
            'routing',
            'parent',
        );
        $metadata = $action->getOptions($params, true);

        return $metadata;
    }
}
