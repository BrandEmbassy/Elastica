<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\PointInTimeLostException;
use Elastica\Exception\ResponseException;
use Elastica\Exception\SearchTimedOutException;
use Elastica\PointInTime;
use Elastica\Query;
use Elastica\Request;
use Elastica\Response;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class PointInTimeTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testOpenClosePointInTime(): void
    {
        $index = $this->_createIndex();
        $pitOpenResponse = $index->openPointInTime('10s');
        $this->assertTrue($pitOpenResponse->isOk());

        $pitId = $pitOpenResponse->getData()['id'];

        $client = $index->getClient();
        $pitCloseResponse = $client->closePointInTime($pitId);
        $this->assertTrue($pitCloseResponse->isOk());

        $this->assertArrayHasKey('num_freed', $pitCloseResponse->getData());
    }

    /**
     * @group functional
     */
    public function testSearchWithPointInTimePagesWithSearchAfter(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['name' => 'a']),
            new Document('2', ['name' => 'b']),
            new Document('3', ['name' => 'c']),
        ]);
        $index->refresh();

        $client = $index->getClient();
        $pitId = $index->openPointInTime('1m')->getData()['id'];

        // Change the live index after the PIT was opened: remove one document, add another.
        $index->deleteById('1');
        $index->addDocument(new Document('4', ['name' => 'd']));
        $index->refresh();

        $query = new Query();
        $query->setPointInTime(new PointInTime($pitId, '1m'));

        // Test the PIT functionality by comparing a query with a PIT query
        $liveIds = \array_map(static fn ($result) => $result->getId(), $index->search(new Query(), 10)->getResults());
        \sort($liveIds);
        $this->assertSame(['2', '3', '4'], $liveIds);

        $pitIds = \array_map(static fn ($result) => $result->getId(), $client->searchWithPointInTime($query)->getResults());
        \sort($pitIds);
        $this->assertSame(['1', '2', '3'], $pitIds);

        // Query the first 2 records, sort by ascending to help with the next test on setSearchAfter
        $query->setSize(2);
        $query->setSort(['_shard_doc' => 'asc']);

        $firstPage = $client->searchWithPointInTime($query);
        $firstPageResults = $firstPage->getResults();

        $this->assertCount(2, $firstPageResults);
        $this->assertNotNull($firstPage->getPointInTimeId());

        $query->setSearchAfter(\end($firstPageResults)->getSort());

        $secondPageResults = $client->searchWithPointInTime($query)->getResults();
        $this->assertCount(1, $secondPageResults);

        $client->closePointInTime($pitId);
    }

    /**
     * @group functional
     */
    public function testSearchWithClosedPointInTimeThrowsPointInTimeLostException(): void
    {
        $index = $this->_createIndex();
        $pitId = $index->openPointInTime('1m')->getData()['id'];
        $client = $index->getClient();
        $client->closePointInTime($pitId);

        $query = new Query();
        $query->setPointInTime(new PointInTime($pitId, '1m'));

        $this->expectException(PointInTimeLostException::class);

        $client->searchWithPointInTime($query);
    }

    /**
     * @group unit
     */
    public function testMapFromConvertsSearchContextMissingExceptionByRootCause(): void
    {
        $exception = $this->createResponseException([
            'error' => [
                'root_cause' => [['type' => 'search_context_missing_exception', 'reason' => 'No search context found for id [1]']],
                'type' => 'search_phase_execution_exception',
                'reason' => 'all shards failed',
            ],
            'status' => 404,
        ]);

        $mapped = PointInTimeLostException::mapFrom($exception);

        $this->assertInstanceOf(PointInTimeLostException::class, $mapped);
        $this->assertSame($exception->getRequest(), $mapped->getRequest());
        $this->assertSame($exception->getResponse(), $mapped->getResponse());
    }

    /**
     * @group unit
     */
    public function testMapFromConvertsSearchContextMissingExceptionByType(): void
    {
        $exception = $this->createResponseException([
            'error' => ['type' => 'search_context_missing_exception', 'reason' => 'No search context found for id [1]'],
            'status' => 404,
        ]);

        $this->assertInstanceOf(PointInTimeLostException::class, PointInTimeLostException::mapFrom($exception));
    }

    /**
     * @group unit
     *
     * @dataProvider provideOtherErrors
     */
    public function testMapFromReturnsOtherExceptionsUnchanged(array $responseData): void
    {
        $exception = $this->createResponseException($responseData);

        $this->assertSame($exception, PointInTimeLostException::mapFrom($exception));
    }

    public function provideOtherErrors(): iterable
    {
        yield 'other error type' => [[
            'error' => [
                'root_cause' => [['type' => 'index_not_found_exception', 'reason' => 'no such index [missing]']],
                'type' => 'index_not_found_exception',
                'reason' => 'no such index [missing]',
            ],
            'status' => 404,
        ]];
        yield 'string error' => [['error' => 'something broke', 'status' => 500]];
        yield 'no error' => [['status' => 500]];
    }

    /**
     * @group unit
     *
     * @dataProvider provideCompleteResponses
     */
    public function testAssertCompleteSearchResponseAcceptsCompleteResponse(array $responseData): void
    {
        $this->_getClient()->assertCompleteSearchResponse(new Request('_search'), new Response(\json_encode($responseData), 200));

        $this->addToAssertionCount(1);
    }

    public function provideCompleteResponses(): iterable
    {
        yield 'all shards ok' => [['timed_out' => false, '_shards' => ['total' => 2, 'successful' => 2, 'skipped' => 0, 'failed' => 0]]];
        yield 'no timed_out and no _shards' => [['hits' => []]];
    }

    /**
     * @group unit
     */
    public function testAssertCompleteSearchResponseThrowsOnTimeout(): void
    {
        $this->expectException(SearchTimedOutException::class);

        $this->_getClient()->assertCompleteSearchResponse(
            new Request('_search'),
            new Response(\json_encode(['timed_out' => true, '_shards' => ['total' => 1, 'successful' => 1, 'failed' => 0]]), 200)
        );
    }

    /**
     * @group unit
     */
    public function testAssertCompleteSearchResponseThrowsOnFailedShardsWithoutFailuresArray(): void
    {
        $this->expectException(PartialShardFailureException::class);

        $this->_getClient()->assertCompleteSearchResponse(
            new Request('_search'),
            new Response(\json_encode(['timed_out' => false, '_shards' => ['total' => 2, 'successful' => 1, 'failed' => 1]]), 200)
        );
    }

    /**
     * @group unit
     */
    public function testAssertCompleteSearchResponseThrowsOnFailuresArray(): void
    {
        $this->expectException(PartialShardFailureException::class);

        $this->_getClient()->assertCompleteSearchResponse(
            new Request('_search'),
            new Response(\json_encode(['_shards' => ['total' => 2, 'successful' => 2, 'failed' => 0, 'failures' => [['reason' => 'x']]]]), 200)
        );
    }

    /**
     * @group unit
     */
    public function testSearchWithPointInTimeRejectsQueryWithoutPointInTime(): void
    {
        $this->expectException(InvalidException::class);

        $this->_getClient()->searchWithPointInTime(new Query());
    }

    private function createResponseException(array $responseData): ResponseException
    {
        return new ResponseException(new Request('_search'), new Response(\json_encode($responseData), $responseData['status'] ?? 200));
    }
}
