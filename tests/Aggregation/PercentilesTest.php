<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Percentiles;
use Elastica\Document;
use Elastica\Query;

/**
 * @internal
 */
class PercentilesTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testConstruct(): void
    {
        $agg = new Percentiles('price_percentile');
        $this->assertSame('price_percentile', $agg->getName());

        $agg = new Percentiles('price_percentile', 'price');
        $this->assertSame('price', $agg->getParam('field'));
    }

    /**
     * @group functional
     */
    public function testSetField(): void
    {
        $agg = (new Percentiles('price_percentile'))
            ->setField('price')
        ;

        $this->assertSame('price', $agg->getParam('field'));
    }

    /**
     * @group unit
     */
    public function testCompression(): void
    {
        $expected = [
            'percentiles' => [
                'field' => 'price',
                'keyed' => false,
                'tdigest' => [
                    'compression' => 100,
                ],
            ],
        ];

        $agg = (new Percentiles('price_percentile', 'price'))
            ->setKeyed(false)
            ->setCompression(100)
        ;

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     */
    public function testHdr(): void
    {
        $expected = [
            'percentiles' => [
                'field' => 'price',
                'keyed' => false,
                'hdr' => [
                    'number_of_significant_value_digits' => 2.0,
                ],
            ],
        ];

        $agg = (new Percentiles('price_percentile', 'price'))
            ->setKeyed(false)
            ->setHdr('number_of_significant_value_digits', 2)
        ;

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group functional
     */
    public function testSetPercents(): void
    {
        $percents = [1, 2, 3];
        $agg = (new Percentiles('price_percentile'))
            ->setPercents($percents)
        ;

        $this->assertSame($percents, $agg->getParam('percents'));
    }

    /**
     * @group functional
     */
    public function testAddPercent(): void
    {
        $percents = [1, 2, 3];
        $agg = (new Percentiles('price_percentile'))
            ->setPercents($percents)
        ;

        $this->assertEquals($percents, $agg->getParam('percents'));

        $agg->addPercent($percents[] = 4);
        $this->assertEquals($percents, $agg->getParam('percents'));
    }

    /**
     * @group functional
     */
    public function testSetScript(): void
    {
        $script = 'doc["load_time"].value / 20';
        $agg = (new Percentiles('price_percentile'))
            ->setScript($script)
        ;

        $this->assertEquals($script, $agg->getParam('script'));
    }

    /**
     * @group functional
     */
    public function testActualWork(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['price' => 100]),
            new Document('2', ['price' => 200]),
            new Document('3', ['price' => 300]),
            new Document('4', ['price' => 400]),
            new Document('5', ['price' => 500]),
            new Document('6', ['price' => 600]),
            new Document('7', ['price' => 700]),
            new Document('8', ['price' => 800]),
            new Document('9', ['price' => 900]),
            new Document('10', ['price' => 1000]),
        ]);
        $index->refresh();

        $query = new Query();
        $query->addAggregation(new Percentiles('price_percentile', 'price'));

        $resultSet = $index->search($query);
        $aggResult = $resultSet->getAggregation('price_percentile');

        $this->assertEqualsWithDelta(100.0, $aggResult['values']['1.0'], 50.0);
        $this->assertEqualsWithDelta(100.0, $aggResult['values']['5.0'], 50.0);
        $this->assertEqualsWithDelta(300.0, $aggResult['values']['25.0'], 50.0);
        $this->assertEqualsWithDelta(550.0, $aggResult['values']['50.0'], 50.0);
        $this->assertEqualsWithDelta(800.0, $aggResult['values']['75.0'], 50.0);
        $this->assertEqualsWithDelta(1000.0, $aggResult['values']['95.0'], 50.0);
        $this->assertEqualsWithDelta(1000.0, $aggResult['values']['99.0'], 50.0);
    }

    /**
     * @group functional
     */
    public function testKeyed(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['price' => 100]),
            new Document('2', ['price' => 200]),
            new Document('3', ['price' => 300]),
            new Document('4', ['price' => 400]),
            new Document('5', ['price' => 500]),
            new Document('6', ['price' => 600]),
            new Document('7', ['price' => 700]),
            new Document('8', ['price' => 800]),
            new Document('9', ['price' => 900]),
            new Document('10', ['price' => 1000]),
        ]);
        $index->refresh();

        $agg = (new Percentiles('price_percentile', 'price'))
            ->setKeyed(false)
        ;

        $query = new Query();
        $query->addAggregation($agg);

        $resultSet = $index->search($query);
        $aggResult = $resultSet->getAggregation('price_percentile');

        $expected = [
            ['key' => 1.0, 'value' => 100.0],
            ['key' => 5.0, 'value' => 100.0],
            ['key' => 25.0, 'value' => 300.0],
            ['key' => 50.0, 'value' => 550.0],
            ['key' => 75.0, 'value' => 800.0],
            ['key' => 95.0, 'value' => 1000.0],
            ['key' => 99.0, 'value' => 1000.0],
        ];

        $this->assertCount(\count($expected), $aggResult['values']);
        foreach ($expected as $i => $expectedEntry) {
            $this->assertEqualsWithDelta($expectedEntry['key'], $aggResult['values'][$i]['key'], 0.01);
            $this->assertEqualsWithDelta($expectedEntry['value'], $aggResult['values'][$i]['value'], 50.0);
        }
    }

    /**
     * @group unit
     */
    public function testMissing(): void
    {
        $expected = [
            'percentiles' => [
                'field' => 'price',
                'keyed' => false,
                'hdr' => [
                    'number_of_significant_value_digits' => 2.0,
                ],
                'missing' => 10,
            ],
        ];

        $agg = (new Percentiles('price_percentile', 'price'))
            ->setKeyed(false)
            ->setHdr('number_of_significant_value_digits', 2)
            ->setMissing(10)
        ;

        $this->assertEquals($expected, $agg->toArray());
    }
}
