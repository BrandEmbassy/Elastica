<?php
namespace OldElastica\Test\Filter;

use OldElastica\Filter\Term;
use OldElastica\Test\Base as BaseTest;

class TermTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new Term();
        $key = 'name';
        $value = 'ruflin';
        $query->setTerm($key, $value);

        $data = $query->toArray();

        $this->assertInternalType('array', $data['term']);
        $this->assertEquals(array($key => $value), $data['term']);
    }
}
