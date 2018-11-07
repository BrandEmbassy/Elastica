<?php
namespace OldElastica\Test\Query;

use OldElastica\Query\Simple;
use OldElastica\Test\Base as BaseTest;

class SimpleTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $testQuery = array('hello' => array('world'), 'name' => 'ruflin');
        $query = new Simple($testQuery);

        $this->assertEquals($testQuery, $query->toArray());
    }
}
