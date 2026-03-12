<?php

namespace Elastica\Test\Bulk\Action;

use Elastica\ApiVersion;
use Elastica\Bulk\Action\AbstractDocument;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use stdClass;

/**
 * @internal
 */
class AbstractDocumentTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testCreateAbstractDocumentWithInvalidParameter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The data needs to be a Document or a Script.');

        AbstractDocument::create(
            new stdClass(),
            null,
            ApiVersion::API_VERSION_9,
            static fn () => Type::DOC
        );
    }

    /**
     * @group unit
     */
    public function testCreateAbstractDocumentWithScript(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Scripts can only be used with the update operation type.');

        AbstractDocument::create(
            new Script('foobar'),
            AbstractDocument::OP_TYPE_CREATE,
            ApiVersion::API_VERSION_9,
            static fn () => Type::DOC
        );
    }
}
