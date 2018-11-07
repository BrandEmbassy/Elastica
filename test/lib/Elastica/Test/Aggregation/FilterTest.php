<?php
namespace Elastica2\Test\Aggregation;

use Elastica2\Aggregation\Avg;
use Elastica2\Aggregation\Filter;
use Elastica2\Document;
use Elastica2\Filter\Range;
use Elastica2\Filter\Term;
use Elastica2\Query;

class FilterTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('test')->addDocuments(array(
            new Document(1, array('price' => 5, 'color' => 'blue')),
            new Document(2, array('price' => 8, 'color' => 'blue')),
            new Document(3, array('price' => 1, 'color' => 'red')),
            new Document(4, array('price' => 3, 'color' => 'green')),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $expected = array(
            'filter' => array('range' => array('stock' => array('gt' => 0))),
            'aggs' => array(
                'avg_price' => array('avg' => array('field' => 'price')),
            ),
        );

        $agg = new Filter('in_stock_products');
        $agg->setFilter(new Range('stock', array('gt' => 0)));
        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group functional
     */
    public function testFilterAggregation()
    {
        $agg = new Filter('filter');
        $agg->setFilter(new Term(array('color' => 'blue')));
        $avg = new Avg('price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('filter');
        $results = $results['price']['value'];

        $this->assertEquals((5 + 8) / 2.0, $results);
    }

    /**
     * @group functional
     */
    public function testFilterNoSubAggregation()
    {
        $agg = new Avg('price');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('price');
        $results = $results['value'];

        $this->assertEquals((5 + 8 + 1 + 3) / 4.0, $results);
    }

    /**
     * @group unit
     */
    public function testConstruct()
    {
        $agg = new Filter('foo', new Term(array('color' => 'blue')));

        $expected = array(
            'filter' => array(
                'term' => array(
                    'color' => 'blue',
                ),
            ),
        );

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     */
    public function testConstructWithoutFilter()
    {
        $agg = new Filter('foo');
        $this->assertEquals('foo', $agg->getName());
    }
}
