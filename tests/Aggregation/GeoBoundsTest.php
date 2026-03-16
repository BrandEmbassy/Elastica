<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\GeoBounds;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;

/**
 * @internal
 */
class GeoBoundsTest extends BaseAggregationTest
{
    /**
     * @group functional
     *
     * @dataProvider geoBoundsDataProvider
     */
    public function testGeoBoundsAggregation(float $expectedValue, string $position, string $coordinate): void
    {
        $agg = new GeoBounds('viewport', 'location');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->getIndexForTest()->search($query)->getAggregation('viewport');

        $this->assertEqualsWithDelta($expectedValue, $results['bounds'][$position][$coordinate], 0.000001);
    }

    /**
     * @return \Iterator<string, array{expectedValue: float, position: string, coordinate: string}>
     */
    public function geoBoundsDataProvider(): \Iterator
    {
        yield 'top left latitude' => [
            'expectedValue' => 37.782438984141,
            'position' => 'top_left',
            'coordinate' => 'lat',
        ];

        yield 'top left longitude' => [
            'expectedValue' => -122.39256000146,
            'position' => 'top_left',
            'coordinate' => 'lon',
        ];

        yield 'bottom right latitude' => [
            'expectedValue' => 32.798319971189,
            'position' => 'bottom_right',
            'coordinate' => 'lat',
        ];

        yield 'bottom right longitude' => [
            'expectedValue' => -117.24664804526,
            'position' => 'bottom_right',
            'coordinate' => 'lon',
        ];
    }

    private function getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'location' => ['type' => 'geo_point'],
        ]));

        $index->addDocuments([
            new Document('1', ['location' => ['lat' => 32.849437, 'lon' => -117.271732]]),
            new Document('2', ['location' => ['lat' => 32.798320, 'lon' => -117.246648]]),
            new Document('3', ['location' => ['lat' => 37.782439, 'lon' => -122.392560]]),
        ]);

        $index->refresh();

        return $index;
    }
}
