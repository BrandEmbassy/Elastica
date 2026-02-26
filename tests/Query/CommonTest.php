<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Common;
use Elastica\Test\Base as BaseTest;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class CommonTest extends BaseTest
{
    use ExpectDeprecationTrait;

    /**
     * @group unit
     * @group legacy
     */
    public function testToArray(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: The "Elastica\Query\Common" class is deprecated, use "Elastica\Query\MatchQuery" instead. It will be removed in 8.0.');

        $query = new Common('body', 'test query', .001);
        $query->setLowFrequencyOperator(Common::OPERATOR_AND);

        $expected = [
            'common' => [
                'body' => [
                    'query' => 'test query',
                    'cutoff_frequency' => .001,
                    'low_freq_operator' => 'and',
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group functional
     * @group legacy
     */
    public function testQuery(): void
    {
        $this->markTestSkipped('The "common" query was removed in ES 8.x. Use MatchQuery with operator instead.');
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testSetHighFrequencyOperator(): void
    {
        $value = 'OPERATOR_TEST';
        $query = new Common('body', 'test query', .001);
        $query->setHighFrequencyOperator($value);

        $this->assertEquals($value, $query->toArray()['common']['body']['high_frequency_operator']);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testSetBoost(): void
    {
        $value = .02;
        $query = new Common('body', 'test query', .001);
        $query->setBoost($value);

        $this->assertEquals($value, $query->toArray()['common']['body']['boost']);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testSetAnalyzer(): void
    {
        $analyzer = 'standard';
        $query = new Common('body', 'test query', .001);
        $query->setBoost(.02);
        $query->setAnalyzer($analyzer);

        $this->assertEquals($analyzer, $query->toArray()['common']['body']['analyzer']);
    }
}
