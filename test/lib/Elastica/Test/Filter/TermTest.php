<?php
namespace Elastica2\Test\Filter;

use Elastica2\Filter\Term;
use Elastica2\Test\Base as BaseTest;

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
