<?php
namespace OldElastica\Bulk\Action;

class CreateDocument extends IndexDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_CREATE;
}
