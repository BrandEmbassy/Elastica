<?php
namespace OldElastica\Test\Facet;

use OldElastica\Document;
use OldElastica\Facet\Query as FacetQuery;
use OldElastica\Query;
use OldElastica\Query\Term;
use OldElastica\Test\Base as BaseTest;

class QueryTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testFilter()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $type->addDocument(new Document(1, array('color' => 'red')));
        $type->addDocument(new Document(2, array('color' => 'green')));
        $type->addDocument(new Document(3, array('color' => 'blue')));

        $index->refresh();

        $termQuery = new Term(array('color' => 'red'));

        $facet = new FacetQuery('test');
        $facet->setQuery($termQuery);

        $query = new Query();
        $query->addFacet($facet);

        $resultSet = $type->search($query);

        $facets = $resultSet->getFacets();

        $this->assertEquals(1, $facets['test']['count']);
    }
}
