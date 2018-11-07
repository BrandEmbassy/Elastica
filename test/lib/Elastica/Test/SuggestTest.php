<?php
namespace OldElastica\Test;

use OldElastica\Exception\NotImplementedException;
use OldElastica\Filter\BoolFilter;
use OldElastica\Suggest;
use OldElastica\Test\Base as BaseTest;

class SuggestTest extends BaseTest
{
    /**
     * Create self test.
     *
     * @group functional
     */
    public function testCreateSelf()
    {
        $suggest = new Suggest();

        $selfSuggest = Suggest::create($suggest);

        $this->assertSame($suggest, $selfSuggest);
    }

    /**
     * Create with suggest test.
     *
     * @group functional
     */
    public function testCreateWithSuggest()
    {
        $suggest1 = new Suggest\Term('suggest1', '_all');

        $suggest = Suggest::create($suggest1);

        $this->assertTrue($suggest->hasParam('suggestion'));
    }

    /**
     * Create with non suggest test.
     *
     * @group functional
     */
    public function testCreateWithNonSuggest()
    {
        try {
            Suggest::create(new BoolFilter());
            $this->fail();
        } catch (NotImplementedException $e) {
        }
    }
}
