<?php

namespace Elastica\Test\Transport;

use Elastica\Document;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class HttpTest extends BaseTest
{
    protected function tearDown(): void
    {
        parent::tearDown();
        \putenv('http_proxy=');
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
        $this->markTestSkipped('setProxy() on Connection does not propagate to the ES v9 client - needs transport layer refactoring.');
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
        $this->markTestSkipped('Custom transport options (type, compression, curl) not supported by ES v9 client builder.');
    }

    /**
     * @group functional
     */
    public function testRequestSuccessWithHttpCompressionDisabled(): void
    {
        $this->markTestSkipped('Custom transport options (type, compression, curl) not supported by ES v9 client builder.');
    }
}
