<?php
namespace Elastica2\Bulk\Action;

use Elastica2\AbstractUpdateAction;
use Elastica2\Bulk\Action;
use Elastica2\Document;
use Elastica2\Script;

abstract class AbstractDocument extends Action
{
    /**
     * @var \Elastica2\Document|\Elastica2\Script
     */
    protected $_data;

    /**
     * @param \Elastica2\Document|\Elastica2\Script $document
     */
    public function __construct($document)
    {
        $this->setData($document);
    }

    /**
     * @param \Elastica2\Document $document
     *
     * @return $this
     */
    public function setDocument(Document $document)
    {
        $this->_data = $document;

        $metadata = $this->_getMetadata($document);

        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @param \Elastica2\Script $script
     *
     * @return $this
     */
    public function setScript(Script $script)
    {
        if (!($this instanceof UpdateDocument)) {
            throw new \BadMethodCallException('setScript() can only be used for UpdateDocument');
        }

        $this->_data = $script;

        $metadata = $this->_getMetadata($script);
        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @param \Elastica2\Script|\Elastica2\Document $data
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setData($data)
    {
        if ($data instanceof Script) {
            $this->setScript($data);
        } elseif ($data instanceof Document) {
            $this->setDocument($data);
        } else {
            throw new \InvalidArgumentException('Data should be a Document or a Script.');
        }

        return $this;
    }

    /**
     * Note: This is for backwards compatibility.
     *
     * @return \Elastica2\Document|null
     */
    public function getDocument()
    {
        if ($this->_data instanceof Document) {
            return $this->_data;
        }

        return;
    }

    /**
     * Note: This is for backwards compatibility.
     *
     * @return \Elastica2\Script|null
     */
    public function getScript()
    {
        if ($this->_data instanceof Script) {
            return $this->_data;
        }

        return;
    }

    /**
     * @return \Elastica2\Document|\Elastica2\Script
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param \Elastica2\AbstractUpdateAction $source
     *
     * @return array
     */
    abstract protected function _getMetadata(AbstractUpdateAction $source);

    /**
     * @param \Elastica2\Document|\Elastica2\Script $data
     * @param string                              $opType
     *
     * @return static
     */
    public static function create($data, $opType = null)
    {
        //Check type
        if (!($data instanceof Document) && !($data instanceof Script)) {
            throw new \InvalidArgumentException('The data needs to be a Document or a Script.');
        }

        if (null === $opType && $data->hasOpType()) {
            $opType = $data->getOpType();
        }

        //Check that scripts can only be used for updates
        if ($data instanceof Script) {
            if ($opType === null) {
                $opType = self::OP_TYPE_UPDATE;
            } elseif ($opType != self::OP_TYPE_UPDATE) {
                throw new \InvalidArgumentException('Scripts can only be used with the update operation type.');
            }
        }

        switch ($opType) {
            case self::OP_TYPE_DELETE:
                $action = new DeleteDocument($data);
                break;
            case self::OP_TYPE_CREATE:
                $action = new CreateDocument($data);
                break;
            case self::OP_TYPE_UPDATE:
                $action = new UpdateDocument($data);
                break;
            case self::OP_TYPE_INDEX:
            default:
                $action = new IndexDocument($data);
                break;
        }

        return $action;
    }
}
