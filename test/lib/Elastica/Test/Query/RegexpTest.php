<?php
namespace OldElastica\Test\Query;

use OldElastica\Query\Regexp;
use OldElastica\Test\Base as BaseTest;

class RegexpTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $field = 'name';
        $value = 'ruf';
        $boost = 2;

        $query = new Regexp($field, $value, $boost);

        $expectedArray = array(
            'regexp' => array(
                $field => array(
                    'value' => $value,
                    'boost' => $boost,
                ),
            ),
        );

        $this->assertequals($expectedArray, $query->toArray());
    }
}
