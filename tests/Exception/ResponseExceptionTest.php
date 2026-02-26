<?php

namespace Elastica\Test\Exception;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Mapping;
use function json_decode;

/**
 * @internal
 */
class ResponseExceptionTest extends AbstractExceptionTest
{
    /**
     * @group functional
     */
    public function testCreateExistingIndex(): void
    {
        $this->_createIndex('woo', true);

        try {
            $this->_createIndex('woo', false);
            $this->fail('Index created when it should fail');
        } catch (ResponseException $ex) {
            $error = $ex->getResponse()->getFullError();

            $this->assertNotEquals('index_already_exists_exception', $error['type']);
            $this->assertEquals('resource_already_exists_exception', $error['type']);
            $this->assertEquals(400, $ex->getResponse()->getStatus());
        } catch (ClientResponseException $ex) {
            $body = json_decode((string) $ex->getResponse()->getBody(), true);
            $this->assertNotEquals('index_already_exists_exception', $body['error']['type']);
            $this->assertEquals('resource_already_exists_exception', $body['error']['type']);
            $this->assertEquals(400, $ex->getResponse()->getStatusCode());
        }
    }

    /**
     * @group functional
     */
    public function testBadType(): void
    {
        $index = $this->_createIndex();

        $index->setMapping(new Mapping([
            'num' => [
                'type' => 'long',
            ],
        ]));

        try {
            $index->addDocument(new Document('', [
                'num' => 'not number at all',
            ]));
            $this->fail('Indexing with wrong type should fail');
        } catch (ResponseException $ex) {
            $error = $ex->getResponse()->getFullError();
            $this->assertEquals('document_parsing_exception', $error['type']);
            $this->assertEquals(400, $ex->getResponse()->getStatus());
        } catch (ClientResponseException $ex) {
            $body = json_decode((string) $ex->getResponse()->getBody(), true);
            $this->assertEquals('document_parsing_exception', $body['error']['type']);
            $this->assertEquals(400, $ex->getResponse()->getStatusCode());
        }
    }

    /**
     * @group functional
     */
    public function testWhatever(): void
    {
        $index = $this->_createIndex();
        $index->delete();

        try {
            $index->search();
        } catch (ResponseException $ex) {
            $error = $ex->getResponse()->getFullError();
            $this->assertEquals('index_not_found_exception', $error['type']);
            $this->assertEquals(404, $ex->getResponse()->getStatus());
        } catch (ClientResponseException $ex) {
            $body = json_decode((string) $ex->getResponse()->getBody(), true);
            $this->assertEquals('index_not_found_exception', $body['error']['type']);
            $this->assertEquals(404, $ex->getResponse()->getStatusCode());
        }
    }
}
