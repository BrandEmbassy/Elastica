<?php
namespace OldElastica\Test\Filter;

use OldElastica\Filter\MatchAll;
use OldElastica\Test\Base as BaseTest;

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
