<?php
namespace Elastica2\Test\Aggregation;

use Elastica2\Aggregation\Min;
use Elastica2\Aggregation\Nested;
use Elastica2\Document;
use Elastica2\Query;
use Elastica2\Type\Mapping;

class NestedTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(new Mapping(null, array(
            'resellers' => array(
                'type' => 'nested',
                'properties' => array(
                    'name' => array('type' => 'string'),
                    'price' => array('type' => 'double'),
                ),
            ),
        )));

        $type->addDocuments(array(
            new Document(1, array(
                'resellers' => array(
                    'name' => 'spacely sprockets',
                    'price' => 5.55,
                ),
            )),
            new Document(2, array(
                'resellers' => array(
                    'name' => 'cogswell cogs',
                    'price' => 4.98,
                ),
            )),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testNestedAggregation()
    {
        $agg = new Nested('resellers', 'resellers');
        $min = new Min('min_price');
        $min->setField('price');
        $agg->addAggregation($min);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('resellers');

        $this->assertEquals(4.98, $results['min_price']['value']);
    }
}
