<?php
namespace OldElastica\Test\Query;

use OldElastica\Query\Nested;
use OldElastica\Query\QueryString;
use OldElastica\Test\Base as BaseTest;

class NestedTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testSetQuery()
    {
        $nested = new Nested();
        $path = 'test1';

        $queryString = new QueryString('test');
        $this->assertInstanceOf('Elastica\Query\Nested', $nested->setQuery($queryString));
        $this->assertInstanceOf('Elastica\Query\Nested', $nested->setPath($path));
        $expected = array(
            'nested' => array(
                'query' => $queryString->toArray(),
                'path' => $path,
            ),
        );

        $this->assertEquals($expected, $nested->toArray());
    }
}
