<?php
namespace Elastica2\Test\Filter;

use Elastica2\Filter\MatchAll;
use Elastica2\Test\Base as BaseTest;

class MatchAllTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $filter = new MatchAll();

        $expectedArray = array('match_all' => new \stdClass());

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
