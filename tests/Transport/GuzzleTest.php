<?php

namespace Elastica\Test\Transport;

use Elastica\Exception\Connection\GuzzleException;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class GuzzleTest extends BaseTest
{
    public static function setUpbeforeClass(): void
    {
        if (!\class_exists('GuzzleHttp\Client')) {
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
        $this->markTestSkipped('Requires a proxy server running on port 8000 - not available in this environment.');
    }

    /**
     * @group functional
     */
    public function testWithEnabledEnvironmentalProxy(): void
    {
        $this->markTestSkipped('Requires a proxy server running on port 8001 - not available in this environment.');
    }

    /**
     * @group functional
     */
    public function testWithProxy(): void
    {
        $this->markTestSkipped('Requires a proxy server running on port 8000 - not available in this environment.');
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
