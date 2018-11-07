<?php
namespace Elastica2\Test\Facet;

use Elastica2\Document;
use Elastica2\Facet\Terms;
use Elastica2\Query;
use Elastica2\Query\MatchAll;
use Elastica2\Test\Base as BaseTest;

class TermsTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testQuery()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('name' => 'nicolas ruflin'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'ruflin test'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'nicolas helloworld'));
        $type->addDocument($doc);

        $facet = new Terms('test');
        $facet->setField('name');

        $query = new Query();
        $query->addFacet($facet);
        $query->setQuery(new MatchAll());

        $index->refresh();

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(3, count($facets['test']['terms']));
    }

    /**
     * @group functional
     */
    public function testFacetScript()
    {
        $this->_checkScriptInlineSetting();
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('name' => 'rodolfo', 'last_name' => 'moraes'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'jose', 'last_name' => 'honjoya'));
        $type->addDocument($doc);

        $facet = new Terms('test');
        $facet->setField('name');
        $facet->setScript('term + " "+doc["last_name"].value');

        $query = new Query();
        $query->addFacet($facet);
        $query->setQuery(new MatchAll());

        $index->refresh();

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(2, count($facets['test']['terms']));
    }
}
