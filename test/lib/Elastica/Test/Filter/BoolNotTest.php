<?php
namespace Elastica2\Test\Filter;

use Elastica2\Filter\BoolNot;
use Elastica2\Filter\Ids;
use Elastica2\Test\Base as BaseTest;

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
