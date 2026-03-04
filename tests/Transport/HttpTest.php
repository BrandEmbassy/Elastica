<?php

namespace Elastica\Test\Transport;

use Elastica\Document;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class HttpTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        \putenv('http_proxy=');
    }

    protected function tearDown(): void
    {
        \putenv('http_proxy=');
        parent::tearDown();
    }

    /**
     * @group functional
     */
    public function testCurlNobodyOptionIsResetAfterHeadRequest(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('curl_test');
        $index->create([], [
            'recreate' => true,
        ]);
        $this->_waitForAllocation($index);

        // Force HEAD request to set CURLOPT_NOBODY = true
        $index->exists();

        $id = '1';
        $data = ['id' => $id, 'name' => 'Item 1'];
        $doc = new Document($id, $data);

        $index->addDocument($doc);

        $index->refresh();

        $doc = $index->getDocument($id);

        // Document should be retrieved correctly
        $this->assertSame($data, $doc->getData());
        $this->assertEquals($id, $doc->getId());
    }

    /**
     * @group functional
     */
    public function testUnicodeData(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('curl_test');
        $index->create([], [
            'recreate' => true,
        ]);
        $this->_waitForAllocation($index);

        // Force HEAD request to set CURLOPT_NOBODY = true
        $index->exists();

        $id = '22';
        $data = ['id' => $id, 'name' => '
            Сегодня, я вижу, особенно грустен твой взгляд, /
            И руки особенно тонки, колени обняв. /
            Послушай: далеко, далеко, на озере Чад /
            Изысканный бродит жираф.'];

        $doc = new Document($id, $data);

        $index->addDocument($doc);

        $index->refresh();

        $doc = $index->getDocument($id);

        // Document should be retrieved correctly
        $this->assertSame($data, $doc->getData());
        $this->assertEquals($id, $doc->getId());
    }

    /**
     * @group functional
     */
    public function testWithEnvironmentalProxy(): void
    {
        \putenv('http_proxy='.$this->_getProxyUrl().'/');

        $client = $this->_getClient();
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        $client->getConnection()->setProxy(null); // will not change anything
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        \putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithEnabledEnvironmentalProxy(): void
    {
        \putenv('http_proxy='.$this->_getProxyUrl403().'/');
        $client = $this->_getClient();
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(403, $transferInfo['http_code']);
        $client = $this->_getClient();
        $client->getConnection()->setProxy('');
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
        \putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithProxy(): void
    {
        $client = $this->_getClient();
        $client->getConnection()->setProxy($this->_getProxyUrl());

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testWithoutProxy(): void
    {
        $client = $this->_getClient();
        $client->getConnection()->setProxy('');

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testBodyReuse(): void
    {
        $this->markTestSkipped('ApiVersion::API_VERSION_9 - body reuse via raw request() not supported in ES v9 client.');
    }

    /**
     * @group functional
     */
    public function testRequestSuccessWithHttpCompressionEnabled(): void
    {
        $client = $this->_getClient(['transport' => ['type' => 'Http', 'compression' => true], 'curl' => [\CURLINFO_HEADER_OUT => true]]);

        $response = $client->request('/_nodes');
        $transferInfo = $response->getTransferInfo();

        if (\method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/Accept-Encoding:\ (gzip|deflate)/', $transferInfo['request_header']);
        } else {
            $this->assertRegExp('/Accept-Encoding:\ (gzip|deflate)/', $transferInfo['request_header']);
        }
    }

    /**
     * @group functional
     */
    public function testRequestSuccessWithHttpCompressionDisabled(): void
    {
        $client = $this->_getClient(['transport' => ['type' => 'Http', 'compression' => false], 'curl' => [\CURLINFO_HEADER_OUT => true]]);

        $response = $client->request('/_nodes');
        $transferInfo = $response->getTransferInfo();

        if (\method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/Accept-Encoding:\ (gzip|deflate)/', $transferInfo['request_header']);
        } else {
            $this->assertRegExp('/Accept-Encoding:\ (gzip|deflate)/', $transferInfo['request_header']);
        }
    }
}
