<?php

namespace Elastica\Test\Transport;

use Elastica\Connection;
use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;
use Elastica\Transport\Http;
use Psr\Log\NullLogger;

/**
 * @internal
 */
class HttpCurlExecutionTest extends BaseTest
{
    private const PORT_REFUSING_CONNECTIONS = 1;

    /**
     * @group unit
     */
    public function testResponseIsCreatedFromBodyReturnedByOverriddenExecution(): void
    {
        $connection = new Connection(['persistent' => false, 'config' => ['bigintConversion' => true]]);
        $transport = new OverriddenCurlExecutionHttp($connection);
        $transport->setResponseBody('{"count":12345678901234567890}');

        $response = $transport->exec(new Request('_count', Request::GET, [], [], $connection), []);

        $this->assertSame(['count' => '12345678901234567890'], $response->getData());
        $this->assertSame(1, $transport->executions);
    }

    /**
     * @group unit
     */
    public function testErrorInBodyReturnedByOverriddenExecutionIsThrown(): void
    {
        $connection = new Connection(['persistent' => false]);
        $transport = new OverriddenCurlExecutionHttp($connection);
        $transport->setResponseBody('{"error":{"type":"index_not_found_exception","reason":"no such index"},"status":404}');

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('no such index');

        $transport->exec(new Request('posts/_refresh', Request::POST, [], [], $connection), []);
    }

    /**
     * @group unit
     */
    public function testFailedShardsInBodyReturnedByOverriddenExecutionAreThrown(): void
    {
        $connection = new Connection(['persistent' => false]);
        $transport = new OverriddenCurlExecutionHttp($connection);
        $transport->setResponseBody('{"_shards":{"total":2,"successful":1,"failed":1,"failures":[{"shard":0}]}}');

        $this->expectException(PartialShardFailureException::class);

        $transport->exec(new Request('posts/_search', Request::GET, [], [], $connection), []);
    }

    /**
     * @group unit
     *
     * @dataProvider provideCurlErrorCases
     */
    public function testCurlErrorOfOverriddenExecutionIsHandledAsTheOneOfHttp(
        string $path,
        string $method,
        bool $isRetryFeatureEnabled,
        int $expectedExecutions
    ): void {
        $connection = new Connection(['host' => '127.0.0.1', 'port' => self::PORT_REFUSING_CONNECTIONS, 'persistent' => false]);
        $transport = new OverriddenCurlExecutionHttp($connection, new NullLogger(), $isRetryFeatureEnabled);
        $request = new Request($path, $method, [], [], $connection);

        $exceptionOfHttp = $this->execExpectingHttpException(new Http($connection, new NullLogger(), $isRetryFeatureEnabled), $request);
        $exception = $this->execExpectingHttpException($transport, $request);

        $this->assertSame($exceptionOfHttp->getError(), $exception->getError());
        $this->assertSame($exceptionOfHttp->getMessage(), $exception->getMessage());
        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($expectedExecutions, $transport->executions);
    }

    public function provideCurlErrorCases(): iterable
    {
        yield 'GET request is retried' => ['_msearch', Request::GET, true, 4];
        yield 'search request is retried' => ['posts/_search', Request::POST, true, 4];
        yield 'POST request is not retried' => ['posts/_refresh', Request::POST, true, 1];
        yield 'GET request is not retried with the retry feature disabled' => ['_msearch', Request::GET, false, 1];
    }

    private function execExpectingHttpException(Http $transport, Request $request): HttpException
    {
        try {
            $transport->exec($request, []);
        } catch (HttpException $e) {
            return $e;
        }

        $this->fail('The refused connection did not fail the request');
    }
}
