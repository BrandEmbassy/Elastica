<?php
namespace Elastica2\Test\Query;

use Elastica2\Document;
use Elastica2\Index;
use Elastica2\Query\QueryString;
use Elastica2\Test\Base as BaseTest;
use Elastica2\Type;
use Elastica2\Util;

class EscapeStringTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSearch()
    {
        $index = $this->_createIndex();
        $index->getSettings()->setNumberOfReplicas(0);

        $type = new Type($index, 'helloworld');

        $doc = new Document(1, array(
            'email' => 'test@test.com', 'username' => 'test 7/6 123', 'test' => array('2', '3', '5'), )
        );
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $queryString = new QueryString(Util::escapeTerm('test 7/6'));
        $resultSet = $type->search($queryString);

        $this->assertEquals(1, $resultSet->count());
    }
}
