<?php

namespace Elastica\Test;

use Elastica\Response;
use Elastica\Test\Base as BaseTest;

/**
 * @group unit
 *
 * @internal
 */
class ResponseTest extends BaseTest
{
    public function testIsOkBulkWithErrorsField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'errors' => false,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));

        $this->assertTrue($response->isOk());
    }

    public function testIsNotOkBulkWithErrorsField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'errors' => true,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));

        $this->assertFalse($response->isOk());
    }

    public function testIsOkBulkItemsWithOkField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'ok' => true]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'ok' => true]],
            ],
        ]));

        $this->assertTrue($response->isOk());
    }

    public function testStringErrorMessage(): void
    {
        $response = new Response(\json_encode([
            'error' => 'a',
        ]));

        $this->assertEquals('a', $response->getErrorMessage());
    }

    public function testArrayErrorMessage(): void
    {
        $response = new Response(\json_encode([
            'error' => ['a', 'b'],
        ]));

        $this->assertEquals(['a', 'b'], $response->getFullError());
    }

    public function testIsNotOkBulkItemsWithOkField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'ok' => true]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'ok' => false]],
            ],
        ]));

        $this->assertFalse($response->isOk());
    }

    public function testIsOkBulkItemsWithStatusField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));

        $this->assertTrue($response->isOk());
    }

    public function testIsNotOkBulkItemsWithStatusField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 301]],
            ],
        ]));

        $this->assertFalse($response->isOk());
    }

    public function testDecodeResponseWithBigIntSetToTrue(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));
        $response->setJsonBigintConversion(true);

        $this->assertIsArray($response->getData());
    }

    public function testResponseSizeInBytesFromStringBody(): void
    {
        $body = \json_encode(['took' => 5, 'hits' => ['total' => 1]]);
        $response = new Response($body);

        $this->assertSame(\strlen($body), $response->getResponseSizeInBytes());
    }

    public function testResponseSizeInBytesIsPreservedAfterDataIsDecoded(): void
    {
        $body = \json_encode(['took' => 5, 'hits' => ['total' => 1]]);
        $response = new Response($body);

        // getData() decodes and clears the raw string; the captured size must survive.
        $response->getData();

        $this->assertSame(\strlen($body), $response->getResponseSizeInBytes());
    }

    public function testResponseSizeInBytesFromArrayBody(): void
    {
        $data = ['took' => 5, 'hits' => ['total' => 1]];
        $response = new Response($data);

        $this->assertSame(\strlen((string) \json_encode($data)), $response->getResponseSizeInBytes());
    }

    public function testResponseSizeInBytesIsZeroWhenArrayBodyCannotBeEncoded(): void
    {
        $response = new Response(['invalid' => "\xB1\x31"]);

        $this->assertSame(0, $response->getResponseSizeInBytes());
    }
}
