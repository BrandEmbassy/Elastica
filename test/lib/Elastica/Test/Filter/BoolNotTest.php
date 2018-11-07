<?php
namespace OldElastica\Test\Filter;

use OldElastica\Filter\BoolNot;
use OldElastica\Filter\Ids;
use OldElastica\Test\Base as BaseTest;

class BoolNotTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $idsFilter = new Ids();
        $idsFilter->setIds(12);
        $filter = new BoolNot($idsFilter);

        $expectedArray = array(
            'not' => array(
                'filter' => $idsFilter->toArray(),
            ),
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
