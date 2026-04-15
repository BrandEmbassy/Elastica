<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Result;
use Elastica\Test\Base as BaseTest;
use GuzzleHttp\Exception\RequestException;

/**
 * @internal
 */
class ResultSetTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGetters(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['name' => 'elastica search']),
            new Document('2', ['name' => 'elastica library']),
            new Document('3', ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $index->search('elastica search');

        $this->assertEquals(3, $resultSet->getTotalHits());
        $this->assertEquals('eq', $resultSet->getTotalHitsRelation());
        $this->assertGreaterThan(0, $resultSet->getMaxScore());
        $this->assertNotTrue($resultSet->hasTimedOut());
        $this->assertNotTrue($resultSet->hasAggregations());
        $this->assertNotTrue($resultSet->hasSuggests());
        $this->assertIsArray($resultSet->getResults());
        $this->assertNull($resultSet->getPointInTimeId());
        $this->assertCount(3, $resultSet);
    }

    /**
     * @group functional
     */
    public function testArrayAccess(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['name' => 'elastica search']),
            new Document('2', ['name' => 'elastica library']),
            new Document('3', ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $index->search('elastica search');

        $this->assertContainsOnlyInstancesOf(Result::class, $resultSet);
        $this->assertArrayNotHasKey(3, $resultSet);
    }

    /**
     * @group functional
     */
    public function testDocumentsAccess(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['name' => 'elastica search']),
            new Document('2', ['name' => 'elastica library']),
            new Document('3', ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $index->search('elastica search');
        $documents = $resultSet->getDocuments();

        $this->assertIsArray($documents);
        $this->assertCount(3, $documents);
        $this->assertContainsOnlyInstancesOf(Document::class, $documents);
        $this->assertArrayNotHasKey(3, $documents);
        $this->assertEquals('elastica search', $documents[0]->get('name'));
    }

    /**
     * @group functional
     */
    public function testInvalidOffsetCreation(): void
    {
        $index = $this->_createIndex();

        try {
            $index->addDocument(new Document('1', ['name' => 'elastica search']));
            $index->refresh();
        } catch (RequestException $e) {
            $this->markTestSkipped('Elasticsearch connection failed: '.$e->getMessage());
        }

        $this->expectException(InvalidException::class);
        $resultSet = $index->search('elastica search');
        $resultSet[1] = new Result(['_id' => 'fakeresult']);
    }

    /**
     * @group functional
     */
    public function testInvalidOffsetGet(): void
    {
        $index = $this->_createIndex();

        try {
            $doc = new Document('1', ['name' => 'elastica search']);
            $index->addDocument($doc);
            $index->refresh();
        } catch (RequestException $e) {
            $this->markTestSkipped('Elasticsearch connection failed: '.$e->getMessage());
        }

        $this->expectException(InvalidException::class);
        $resultSet = $index->search('elastica search');
        $_ = $resultSet[1]; // triggers offsetGet() which throws InvalidException
    }
}
