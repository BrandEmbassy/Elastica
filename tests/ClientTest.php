<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Exception\InvalidException;
use Elastica\Test\Base as BaseTest;
use Elastica\Test\Transport\DummyTransport;
use Elastica\Test\Transport\LargeResponseTransport;
use Elastica\Test\Transport\SlowResponseTransport;
use Psr\Log\LoggerInterface;

/**
 * @group unit
 *
 * @internal
 */
class ClientTest extends BaseTest
{
    public function testConstruct(): void
    {
        $client = $this->_getClient();
        $this->assertCount(1, $client->getConnections());
    }

    public function testLargeResponseIsLoggedWhenSlowRequestLoggingEnabled(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('Large Elastica Response'),
                $this->logicalAnd($this->arrayHasKey('responseSizeInBytes'), $this->arrayHasKey('responseSizeInMb')),
            )
        ;

        $client = $this->createClientWithTransportAndLogger(
            LargeResponseTransport::class,
            $logger,
            10,
        );
        $client->setLoggingMode(Client::LOG_SLOW_REQUESTS);

        $client->request('/_search');
    }

    public function testSlowResponseIsLoggedAsSlowRequest(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Slow Elastica Request'))
        ;

        $client = $this->createClientWithTransportAndLogger(
            SlowResponseTransport::class,
            $logger,
            Client::DEFAULT_LARGE_RESPONSE_THRESHOLD_IN_BYTES,
        );
        $client->setLoggingMode(Client::LOG_SLOW_REQUESTS);

        $client->request('/_search');
    }

    public function testLargeResponseIsNotLoggedWhenSlowRequestLoggingDisabled(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('warning')
        ;

        $client = $this->createClientWithTransportAndLogger(
            LargeResponseTransport::class,
            $logger,
            10,
        );
        $client->setLoggingMode(Client::LOG_DISABLED);

        $client->request('/_search');
    }

    public function testSmallFastResponseIsNotLoggedAsSlowOrLarge(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('warning')
        ;

        $client = $this->createClientWithTransportAndLogger(
            DummyTransport::class,
            $logger,
            Client::DEFAULT_LARGE_RESPONSE_THRESHOLD_IN_BYTES,
        );
        $client->setLoggingMode(Client::LOG_SLOW_REQUESTS);

        $client->request('/_search');
    }

    public function testSlowAndLargeResponseAreLoggedAsTwoWarnings(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))
            ->method('warning')
        ;

        $client = $this->createClientWithTransportAndLogger(
            SlowResponseTransport::class,
            $logger,
            10,
        );
        $client->setLoggingMode(Client::LOG_SLOW_REQUESTS);

        $client->request('/_search');
    }

    public function testLargeResponseLogOmitsRequestAndResponseBody(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('Large Elastica Response'),
                $this->logicalAnd(
                    $this->arrayHasKey('requestPath'),
                    $this->logicalNot($this->arrayHasKey('request')),
                    $this->logicalNot($this->arrayHasKey('response')),
                ),
            )
        ;

        $client = $this->createClientWithTransportAndLogger(
            LargeResponseTransport::class,
            $logger,
            10,
        );
        $client->setLoggingMode(Client::LOG_SLOW_REQUESTS | Client::LOG_RESPONSE_BODY);

        $client->request('/_search');
    }

    public function testResponseExactlyAtThresholdIsNotLoggedAsLarge(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('warning')
        ;

        $client = $this->createClientWithTransportAndLogger(
            LargeResponseTransport::class,
            $logger,
            1035,
        );
        $client->setLoggingMode(Client::LOG_SLOW_REQUESTS);

        $client->request('/_search');
    }

    public function testResponseJustAboveThresholdIsLoggedAsLarge(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Large Elastica Response'))
        ;

        $client = $this->createClientWithTransportAndLogger(
            LargeResponseTransport::class,
            $logger,
            1034,
        );
        $client->setLoggingMode(Client::LOG_SLOW_REQUESTS);

        $client->request('/_search');
    }

    private function createClientWithTransportAndLogger(
        string $transportClass,
        LoggerInterface $logger,
        int $largeResponseThresholdBytes
    ): Client {
        return new Client(
            [
                'host' => $this->_getHost(),
                'port' => $this->_getPort(),
                'transport' => $transportClass,
            ],
            null,
            $logger,
            null,
            false,
            Client::DEFAULT_SLOW_REQUEST_THRESHOLD_IN_MS,
            $largeResponseThresholdBytes,
        );
    }

    public function testConstructWithDsn(): void
    {
        $client = new Client('https://user:p4ss@foo.com:9200?persistent=false&retryOnConflict=2');
        $this->assertCount(1, $client->getConnections());

        $expected = [
            'host' => 'foo.com',
            'port' => 9200,
            'path' => null,
            'url' => null,
            'proxy' => null,
            'transport' => 'https',
            'persistent' => false,
            'timeout' => null,
            'connections' => [],
            'roundRobin' => false,
            'retryOnConflict' => 2,
            'bigintConversion' => false,
            'username' => 'user',
            'password' => 'p4ss',
            'auth_type' => 'basic',
            'connectionStrategy' => 'Simple',
        ];

        $this->assertEquals($expected, $client->getConfig());
    }

    public function testConnectionParamsArePreparedForConnectionsOption(): void
    {
        $url = 'https://'.$this->_getHost().':9200';
        $client = $this->_getClient(['connections' => [['url' => $url]]]);
        $connection = $client->getConnection();

        $this->assertEquals($url, $connection->getConfig('url'));
    }

    public function testConnectionParamsArePreparedForServersOption(): void
    {
        $url = 'https://'.$this->_getHost().':9200';
        $client = $this->_getClient(['servers' => [['url' => $url]]]);
        $connection = $client->getConnection();

        $this->assertEquals($url, $connection->getConfig('url'));
    }

    public function testConnectionParamsArePreparedForDefaultOptions(): void
    {
        $url = 'https://'.$this->_getHost().':9200';
        $client = $this->_getClient(['url' => $url]);
        $connection = $client->getConnection();

        $this->assertEquals($url, $connection->getConfig('url'));
    }

    public function testAddDocumentsEmpty(): void
    {
        $this->expectException(InvalidException::class);

        $client = $this->_getClient();
        $client->addDocuments([]);
    }

    public function testConfigValue(): void
    {
        $config = [
            'level1' => [
                'level2' => [
                    'level3' => 'value3',
                ],
                'level21' => 'value21',
            ],
            'level11' => 'value11',
        ];
        $client = $this->_getClient($config);

        $this->assertNull($client->getConfigValue('level12'));
        $this->assertFalse($client->getConfigValue('level12', false));
        $this->assertEquals(10, $client->getConfigValue('level12', 10));

        $this->assertEquals('value11', $client->getConfigValue('level11'));
        $this->assertNotNull($client->getConfigValue('level11'));
        $this->assertNotEquals(false, $client->getConfigValue('level11', false));
        $this->assertNotEquals(10, $client->getConfigValue('level11', 10));

        $this->assertEquals('value3', $client->getConfigValue(['level1', 'level2', 'level3']));
        $this->assertIsArray($client->getConfigValue(['level1', 'level2']));
    }

    public function testAddHeader(): void
    {
        $client = $this->_getClient();

        $client->addHeader('foo', 'bar');
        $this->assertEquals(['foo' => 'bar'], $client->getConfigValue('headers'));
    }

    public function testRemoveHeader(): void
    {
        $client = $this->_getClient();

        $client->addHeader('first', 'first value');
        $client->addHeader('second', 'second value');

        $client->removeHeader('second');
        $this->assertEquals(['first' => 'first value'], $client->getConfigValue('headers'));
    }

    public function testPassBigIntSettingsToConnectionConfig(): void
    {
        $client = new Client(['bigintConversion' => true]);

        $this->assertTrue($client->getConnection()->getConfig('bigintConversion'));
    }

    public function testClientConnectWithConfigSetByMethod(): void
    {
        $client = new Client();
        $client->setConfigValue('host', $this->_getHost());
        $client->setConfigValue('port', $this->_getPort());

        $client->connect();
        $this->assertTrue($client->hasConnection());

        $connection = $client->getConnection();
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals($this->_getHost(), $connection->getHost());
        $this->assertEquals($this->_getPort(), $connection->getPort());
    }
}
