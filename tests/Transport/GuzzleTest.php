<?php

namespace Elastica\Test\Transport;

use Elastica\Exception\Connection\GuzzleException;
use Elastica\Test\Base as BaseTest;
use function class_exists;
use function putenv;

/**
 * @internal
 */
class GuzzleTest extends BaseTest
{
    public static function setUpbeforeClass(): void
    {
        if (!class_exists('GuzzleHttp\Client')) {
            self::markTestSkipped('guzzlehttp/guzzle package should be installed to run guzzle transport tests');
        }
    }

    protected function setUp(): void
    {
        \putenv('http_proxy=');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithEnvironmentalProxy(): void
    {
        putenv('http_proxy='.$this->_getProxyUrl().'/');

        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        $client->getConnection()->setProxy(null); // will not change anything
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithEnabledEnvironmentalProxy(): void
    {
        putenv('http_proxy='.$this->_getProxyUrl403().'/');

        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(403, $transferInfo['http_code']);

        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $client->getConnection()->setProxy('');
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithProxy(): void
    {
        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $client->getConnection()->setProxy($this->_getProxyUrl());

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testWithoutProxy(): void
    {
        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $client->getConnection()->setProxy('');

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testBodyReuse(): void
    {
        $this->markTestSkipped('ApiVersion::API_VERSION_9 - body reuse via raw request() not supported in ES v9 - type-based URL path is rejected.');
    }

    /**
     * @group unit
     */
    public function testInvalidConnection(): void
    {
        $this->expectException(GuzzleException::class);

        $client = $this->_getClient(['transport' => 'Guzzle', 'port' => 4500, 'persistent' => false]);
        $client->request('_stats', 'GET');
    }
}
