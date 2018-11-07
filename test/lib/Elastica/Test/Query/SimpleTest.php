<?php
namespace Elastica2\Test\Query;

use Elastica2\Query\Simple;
use Elastica2\Test\Base as BaseTest;

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
