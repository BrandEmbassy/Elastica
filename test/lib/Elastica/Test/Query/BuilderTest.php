<?php
namespace OldElastica\Test\Query;

use OldElastica\Query\Builder;
use OldElastica\Test\Base as BaseTest;

class BuilderTest extends BaseTest
{
    /**
     * @group unit
     * @covers \OldElastica\Query\Builder::factory
     * @covers \OldElastica\Query\Builder::__construct
     */
    public function testFactory()
    {
        $this->assertInstanceOf(
            'Elastica\Query\Builder',
            Builder::factory('some string')
        );
    }

    public function getQueryData()
    {
        return array(
            array('allowLeadingWildcard', false, '{"allow_leading_wildcard":"false"}'),
            array('allowLeadingWildcard', true, '{"allow_leading_wildcard":"true"}'),
            array('analyzeWildcard', false, '{"analyze_wildcard":"false"}'),
            array('analyzeWildcard', true, '{"analyze_wildcard":"true"}'),
            array('analyzer', 'someAnalyzer', '{"analyzer":"someAnalyzer"}'),
            array('autoGeneratePhraseQueries', true, '{"auto_generate_phrase_queries":"true"}'),
            array('autoGeneratePhraseQueries', false, '{"auto_generate_phrase_queries":"false"}'),
            array('boost', 2, '{"boost":"2"}'),
            array('boost', 4.2, '{"boost":"4.2"}'),
            array('defaultField', 'fieldName', '{"default_field":"fieldName"}'),
            array('defaultOperator', 'OR', '{"default_operator":"OR"}'),
            array('defaultOperator', 'AND', '{"default_operator":"AND"}'),
            array('enablePositionIncrements', true, '{"enable_position_increments":"true"}'),
            array('enablePositionIncrements', false, '{"enable_position_increments":"false"}'),
            array('explain', true, '{"explain":"true"}'),
            array('explain', false, '{"explain":"false"}'),
            array('from', 42, '{"from":"42"}'),
            array('fuzzyMinSim', 4.2, '{"fuzzy_min_sim":"4.2"}'),
            array('fuzzyPrefixLength', 2, '{"fuzzy_prefix_length":"2"}'),
            array('gt', 10, '{"gt":"10"}'),
            array('gte', 11, '{"gte":"11"}'),
            array('lowercaseExpandedTerms', true, '{"lowercase_expanded_terms":"true"}'),
            array('lt', 10, '{"lt":"10"}'),
            array('lte', 11, '{"lte":"11"}'),
            array('minimumNumberShouldMatch', 21, '{"minimum_number_should_match":"21"}'),
            array('phraseSlop', 6, '{"phrase_slop":"6"}'),
            array('size', 7, '{"size":"7"}'),
            array('tieBreakerMultiplier', 7, '{"tie_breaker_multiplier":"7"}'),
            array('matchAll', 1.1, '{"match_all":{"boost":"1.1"}}'),
            array('fields', array('age', 'sex', 'location'), '{"fields":["age","sex","location"]}'),
        );
    }

    /**
     * @group unit
     * @dataProvider getQueryData
     * @covers \OldElastica\Query\Builder::__toString
     * @covers \OldElastica\Query\Builder::allowLeadingWildcard
     * @covers \OldElastica\Query\Builder::analyzeWildcard
     * @covers \OldElastica\Query\Builder::analyzer
     * @covers \OldElastica\Query\Builder::autoGeneratePhraseQueries
     * @covers \OldElastica\Query\Builder::boost
     * @covers \OldElastica\Query\Builder::defaultField
     * @covers \OldElastica\Query\Builder::defaultOperator
     * @covers \OldElastica\Query\Builder::enablePositionIncrements
     * @covers \OldElastica\Query\Builder::explain
     * @covers \OldElastica\Query\Builder::from
     * @covers \OldElastica\Query\Builder::fuzzyMinSim
     * @covers \OldElastica\Query\Builder::fuzzyPrefixLength
     * @covers \OldElastica\Query\Builder::gt
     * @covers \OldElastica\Query\Builder::gte
     * @covers \OldElastica\Query\Builder::lowercaseExpandedTerms
     * @covers \OldElastica\Query\Builder::lt
     * @covers \OldElastica\Query\Builder::lte
     * @covers \OldElastica\Query\Builder::minimumNumberShouldMatch
     * @covers \OldElastica\Query\Builder::phraseSlop
     * @covers \OldElastica\Query\Builder::size
     * @covers \OldElastica\Query\Builder::tieBreakerMultiplier
     * @covers \OldElastica\Query\Builder::matchAll
     * @covers \OldElastica\Query\Builder::fields
     */
    public function testAllowLeadingWildcard($method, $argument, $result)
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->$method($argument));
        $this->assertSame($result, (string) $builder);
    }

    public function getQueryTypes()
    {
        return array(
            array('bool', 'bool'),
            array('constantScore', 'constant_score'),
            array('disMax', 'dis_max'),
            array('facets', 'facets'),
            array('filter', 'filter'),
            array('filteredQuery', 'filtered'),
            array('must', 'must'),
            array('mustNot', 'must_not'),
            array('prefix', 'prefix'),
            array('query', 'query'),
            array('queryString', 'query_string'),
            array('range', 'range'),
            array('should', 'should'),
            array('sort', 'sort'),
            array('term', 'term'),
            array('textPhrase', 'text_phrase'),
            array('wildcard', 'wildcard'),
        );
    }

    /**
     * @group unit
     * @dataProvider getQueryTypes
     * @covers \OldElastica\Query\Builder::fieldClose
     * @covers \OldElastica\Query\Builder::close
     * @covers \OldElastica\Query\Builder::bool
     * @covers \OldElastica\Query\Builder::boolClose
     * @covers \OldElastica\Query\Builder::constantScore
     * @covers \OldElastica\Query\Builder::constantScoreClose
     * @covers \OldElastica\Query\Builder::disMax
     * @covers \OldElastica\Query\Builder::disMaxClose
     * @covers \OldElastica\Query\Builder::facets
     * @covers \OldElastica\Query\Builder::facetsClose
     * @covers \OldElastica\Query\Builder::filter
     * @covers \OldElastica\Query\Builder::filterClose
     * @covers \OldElastica\Query\Builder::filteredQuery
     * @covers \OldElastica\Query\Builder::filteredQueryClose
     * @covers \OldElastica\Query\Builder::must
     * @covers \OldElastica\Query\Builder::mustClose
     * @covers \OldElastica\Query\Builder::mustNot
     * @covers \OldElastica\Query\Builder::mustNotClose
     * @covers \OldElastica\Query\Builder::prefix
     * @covers \OldElastica\Query\Builder::prefixClose
     * @covers \OldElastica\Query\Builder::query
     * @covers \OldElastica\Query\Builder::queryClose
     * @covers \OldElastica\Query\Builder::queryString
     * @covers \OldElastica\Query\Builder::queryStringClose
     * @covers \OldElastica\Query\Builder::range
     * @covers \OldElastica\Query\Builder::rangeClose
     * @covers \OldElastica\Query\Builder::should
     * @covers \OldElastica\Query\Builder::shouldClose
     * @covers \OldElastica\Query\Builder::sort
     * @covers \OldElastica\Query\Builder::sortClose
     * @covers \OldElastica\Query\Builder::term
     * @covers \OldElastica\Query\Builder::termClose
     * @covers \OldElastica\Query\Builder::textPhrase
     * @covers \OldElastica\Query\Builder::textPhraseClose
     * @covers \OldElastica\Query\Builder::wildcard
     * @covers \OldElastica\Query\Builder::wildcardClose
     */
    public function testQueryTypes($method, $queryType)
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->$method()); // open
        $this->assertSame($builder, $builder->{$method.'Close'}()); // close
        $this->assertSame('{"'.$queryType.'":{}}', (string) $builder);
    }

    /**
     * @group unit
     * @covers \OldElastica\Query\Builder::fieldOpen
     * @covers \OldElastica\Query\Builder::fieldClose
     * @covers \OldElastica\Query\Builder::open
     * @covers \OldElastica\Query\Builder::close
     */
    public function testFieldOpenAndClose()
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->fieldOpen('someField'));
        $this->assertSame($builder, $builder->fieldClose());
        $this->assertSame('{"someField":{}}', (string) $builder);
    }

    /**
     * @group unit
     * @covers \OldElastica\Query\Builder::sortField
     */
    public function testSortField()
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->sortField('name', true));
        $this->assertSame('{"sort":{"name":{"reverse":"true"}}}', (string) $builder);
    }

    /**
     * @group unit
     * @covers \OldElastica\Query\Builder::sortFields
     */
    public function testSortFields()
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->sortFields(array('field1' => 'asc', 'field2' => 'desc', 'field3' => 'asc')));
        $this->assertSame('{"sort":[{"field1":"asc"},{"field2":"desc"},{"field3":"asc"}]}', (string) $builder);
    }

    /**
     * @group unit
     * @covers \OldElastica\Query\Builder::queries
     */
    public function testQueries()
    {
        $queries = array();

        $builder = new Builder();
        $b1 = clone $builder;
        $b2 = clone $builder;

        $queries[] = $b1->term()->field('age', 34)->termClose();
        $queries[] = $b2->term()->field('name', 'christer')->termClose();

        $this->assertSame($builder, $builder->queries($queries));
        $this->assertSame('{"queries":[{"term":{"age":"34"}},{"term":{"name":"christer"}}]}', (string) $builder);
    }

    public function getFieldData()
    {
        return array(
            array('name', 'value', '{"name":"value"}'),
            array('name', true, '{"name":"true"}'),
            array('name', false, '{"name":"false"}'),
            array('name', array(1, 2, 3), '{"name":["1","2","3"]}'),
            array('name', array('foo', 'bar', 'baz'), '{"name":["foo","bar","baz"]}'),
        );
    }

    /**
     * @group unit
     * @dataProvider getFieldData
     * @covers \OldElastica\Query\Builder::field
     */
    public function testField($name, $value, $result)
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->field($name, $value));
        $this->assertSame($result, (string) $builder);
    }

    /**
     * @group unit
     * @expectedException \OldElastica\Exception\InvalidException
     * @expectedExceptionMessage The produced query is not a valid json string : "{{}"
     * @covers \OldElastica\Query\Builder::toArray
     */
    public function testToArrayWithInvalidData()
    {
        $builder = new Builder();
        $builder->open('foo');
        $builder->toArray();
    }

    /**
     * @group unit
     * @covers \OldElastica\Query\Builder::toArray
     */
    public function testToArray()
    {
        $builder = new Builder();
        $builder->query()->term()->field('category.id', array(1, 2, 3))->termClose()->queryClose();
        $expected = array(
            'query' => array(
                'term' => array(
                    'category.id' => array(1, 2, 3),
                ),
            ),
        );
        $this->assertEquals($expected, $builder->toArray());
    }
}
