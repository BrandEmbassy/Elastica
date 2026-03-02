<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Percentiles;
use Elastica\Document;
use Elastica\Query;
use Iterator;

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
     * @return Iterator<string, array{expectedValue: float, percentileKey: string}>
     */
    public function actualWorkDataProvider(): Iterator
    {
        yield '1st percentile' => [
            'expectedValue' => 100.0,
            'percentileKey' => '1.0',
        ];

        yield '5th percentile' => [
            'expectedValue' => 100.0,
            'percentileKey' => '5.0',
        ];

        yield '25th percentile' => [
            'expectedValue' => 300.0,
            'percentileKey' => '25.0',
        ];

        yield '50th percentile' => [
            'expectedValue' => 550.0,
            'percentileKey' => '50.0',
        ];

        yield '75th percentile' => [
            'expectedValue' => 800.0,
            'percentileKey' => '75.0',
        ];

        yield '95th percentile' => [
            'expectedValue' => 1000.0,
            'percentileKey' => '95.0',
        ];

        yield '99th percentile' => [
            'expectedValue' => 1000.0,
            'percentileKey' => '99.0',
        ];
    }


    /**
     * @group functional
     * @dataProvider actualWorkDataProvider
     */
    public function testActualWork(float $expectedValue, string $percentileKey): void
    {
        // prepare
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

        // execute
        $query = new Query();
        $query->addAggregation(new Percentiles('price_percentile', 'price'));

        $aggResult = $index->search($query)->getAggregation('price_percentile');

        $this->assertEqualsWithDelta($expectedValue, $aggResult['values'][$percentileKey], 50.0);
    }

    /**
     * @return Iterator<string, array{expectedKey: float, expectedValue: float, index: int}>
     */
    public function keyedDataProvider(): Iterator
    {
        yield '1st percentile' => [
            'expectedKey'   => 1.0,
            'expectedValue' => 100.0,
            'index'         => 0,
        ];

        yield '5th percentile' => [
            'expectedKey'   => 5.0,
            'expectedValue' => 100.0,
            'index'         => 1,
        ];

        yield '25th percentile' => [
            'expectedKey'   => 25.0,
            'expectedValue' => 300.0,
            'index'         => 2,
        ];

        yield '50th percentile' => [
            'expectedKey'   => 50.0,
            'expectedValue' => 550.0,
            'index'         => 3,
        ];

        yield '75th percentile' => [
            'expectedKey'   => 75.0,
            'expectedValue' => 800.0,
            'index'         => 4,
        ];

        yield '95th percentile' => [
            'expectedKey'   => 95.0,
            'expectedValue' => 1000.0,
            'index'         => 5,
        ];

        yield '99th percentile' => [
            'expectedKey'   => 99.0,
            'expectedValue' => 1000.0,
            'index'         => 6,
        ];
    }


    /**
     * @group functional
     * @dataProvider keyedDataProvider
     */
    public function testKeyed(float $expectedKey, float $expectedValue, int $index): void
    {
        // prepare
        $esIndex = $this->_createIndex();
        $esIndex->addDocuments([
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
        $esIndex->refresh();

        // execute
        $agg = (new Percentiles('price_percentile', 'price'))
            ->setKeyed(false)
        ;

        $query = new Query();
        $query->addAggregation($agg);

        $aggResult = $esIndex->search($query)->getAggregation('price_percentile');

        $this->assertEqualsWithDelta($expectedKey, $aggResult['values'][$index]['key'], 0.01);
        $this->assertEqualsWithDelta($expectedValue, $aggResult['values'][$index]['value'], 50.0);
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
