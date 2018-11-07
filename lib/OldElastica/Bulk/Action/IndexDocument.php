<?php
namespace OldElastica\Bulk\Action;

use OldElastica\AbstractUpdateAction;
use OldElastica\Document;

class IndexDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_INDEX;

    /**
     * @param \OldElastica\Document $document
     *
     * @return $this
     */
    public function setDocument(Document $document)
    {
        parent::setDocument($document);

        $this->setSource($document->getData());

        return $this;
    }

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
            'percolate',
            'parent',
            'ttl',
            'timestamp',
            'retry_on_conflict',
        );

        $metadata = $action->getOptions($params, true);

        return $metadata;
    }
}
