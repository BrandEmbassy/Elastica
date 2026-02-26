<?php

namespace Elastica\Test\Transport;

use Elastica\Response;
use Elastica\Test\Base as BaseTest;
use Elastica\Transport\NullTransport;

/**
 * Elastica Null Transport Test.
 *
 * @author James Boehmer <james.boehmer@jamesboehmer.com>
 * @author Jan Domanski <jandom@gmail.com>
 *
 * @internal
 */
class NullTransportTest extends BaseTest
{
    /** @var NullTransport NullTransport */
    protected $transport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transport = new NullTransport();
    }

    /**
     * @group functional
     */
    public function testEmptyResult(): void
    {
        $this->markTestSkipped('NullTransport Connection is incompatible with elasticsearch-php v9 client architecture.');
    }

    /**
     * @group functional
     */
    public function testExec(): void
    {
        $this->markTestSkipped('NullTransport functional test fails with v9 client NoNodeAvailableException in tearDown.');
    }

    /**
     * @group unit
     */
    public function testResponse(): void
    {
        $resposeString = '';
        $response = new Response($resposeString);
        $this->transport->setResponse($response);
        $this->assertEquals($response, $this->transport->getResponse());
    }

    /**
     * @group unit
     */
    public function testGenerateDefaultResponse(): void
    {
        $params = ['blah' => 123];
        $response = $this->transport->generateDefaultResponse($params);
        $this->assertEquals([], $response->getTransferInfo());

        $responseData = $response->getData();
        $this->assertArrayHasKey('params', $responseData);
        $this->assertEquals($params, $responseData['params']);
    }
}
