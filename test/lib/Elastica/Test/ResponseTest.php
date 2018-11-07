<?php
namespace Elastica2\Test;

use Elastica2\Document;
use Elastica2\Facet\DateHistogram;
use Elastica2\Query;
use Elastica2\Query\MatchAll;
use Elastica2\Request;
use Elastica2\Response;
use Elastica2\Test\Base as BaseTest;
use Elastica2\Type\Mapping;

class ResponseTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testClassHierarchy()
    {
        $facet = new DateHistogram('dateHist1');
        $this->assertInstanceOf('Elastica\Facet\Histogram', $facet);
        $this->assertInstanceOf('Elastica\Facet\AbstractFacet', $facet);
        unset($facet);
    }

    /**
     * @group functional
     */
    public function testResponse()
    {
        $index = $this->_createIndex();
        $type = $index->getType('helloworld');

        $mapping = new Mapping($type, array(
            'name' => array('type' => 'string', 'store' => 'no'),
            'dtmPosted' => array('type' => 'date', 'store' => 'no', 'format' => 'yyyy-MM-dd HH:mm:ss'),
        ));
        $type->setMapping($mapping);

        $type->addDocuments(array(
            new Document(1, array('name' => 'nicolas ruflin', 'dtmPosted' => '2011-06-23 21:53:00')),
            new Document(2, array('name' => 'raul martinez jr', 'dtmPosted' => '2011-06-23 09:53:00')),
            new Document(3, array('name' => 'rachelle clemente', 'dtmPosted' => '2011-07-08 08:53:00')),
            new Document(4, array('name' => 'elastica search', 'dtmPosted' => '2011-07-08 01:53:00')),
        ));

        $query = new Query();
        $query->setQuery(new MatchAll());
        $index->refresh();

        $resultSet = $type->search($query);

        $engineTime = $resultSet->getResponse()->getEngineTime();
        $shardsStats = $resultSet->getResponse()->getShardsStatistics();

        $this->assertInternalType('int', $engineTime);
        $this->assertTrue(is_array($shardsStats));
        $this->assertArrayHasKey('total', $shardsStats);
        $this->assertArrayHasKey('successful', $shardsStats);
    }

    /**
     * @group functional
     */
    public function testIsOk()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'ruflin'));
        $response = $type->addDocument($doc);

        $this->assertTrue($response->isOk());
    }

    /**
     * @group functional
     */
    public function testIsOkMultiple()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $docs = array(
            new Document(1, array('name' => 'ruflin')),
            new Document(2, array('name' => 'ruflin')),
        );
        $response = $type->addDocuments($docs);

        $this->assertTrue($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsOkBulkWithErrorsField()
    {
        $response = new Response(json_encode(array(
            'took' => 213,
            'errors' => false,
            'items' => array(
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200)),
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 200)),
            ),
        )));

        $this->assertTrue($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsNotOkBulkWithErrorsField()
    {
        $response = new Response(json_encode(array(
            'took' => 213,
            'errors' => true,
            'items' => array(
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200)),
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 200)),
            ),
        )));

        $this->assertFalse($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsOkBulkItemsWithOkField()
    {
        $response = new Response(json_encode(array(
            'took' => 213,
            'items' => array(
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'ok' => true)),
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'ok' => true)),
            ),
        )));

        $this->assertTrue($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsNotOkBulkItemsWithOkField()
    {
        $response = new Response(json_encode(array(
            'took' => 213,
            'items' => array(
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'ok' => true)),
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'ok' => false)),
            ),
        )));

        $this->assertFalse($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsOkBulkItemsWithStatusField()
    {
        $response = new Response(json_encode(array(
            'took' => 213,
            'items' => array(
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200)),
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 200)),
            ),
        )));

        $this->assertTrue($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsNotOkBulkItemsWithStatusField()
    {
        $response = new Response(json_encode(array(
            'took' => 213,
            'items' => array(
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200)),
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 301)),
            ),
        )));

        $this->assertFalse($response->isOk());
    }

    /**
     * @group unit
     */
    public function testDecodeResponseWithBigIntSetToTrue()
    {
        $response = new Response(json_encode(array(
            'took' => 213,
            'items' => array(
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200)),
                array('index' => array('_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 200)),
            ),
        )));
        $response->setJsonBigintConversion(true);

        $this->assertTrue(is_array($response->getData()));
    }

    /**
     * @group functional
     */
    public function testGetDataEmpty()
    {
        $index = $this->_createIndex();

        $response = $index->request(
            'non-existent-type/_mapping',
            Request::GET
        )->getData();

        $this->assertEquals(0, count($response));
    }
}
