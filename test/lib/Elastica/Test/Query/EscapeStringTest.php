<?php
namespace OldElastica\Test\Query;

use OldElastica\Document;
use OldElastica\Index;
use OldElastica\Query\QueryString;
use OldElastica\Test\Base as BaseTest;
use OldElastica\Type;
use OldElastica\Util;

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
