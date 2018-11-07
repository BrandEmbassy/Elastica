<?php
namespace Elastica2\Query;

use Elastica2\Exception\InvalidException;
use Elastica2\Filter\AbstractFilter;

/**
 * Filtered query. Needs a query and a filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-filtered-query.html
 */
class Filtered extends AbstractQuery
{
    /**
     * Constructs a filtered query.
     *
     * @param \Elastica2\Query\AbstractQuery   $query  OPTIONAL Query object
     * @param \Elastica2\Filter\AbstractFilter $filter OPTIONAL Filter object
     */
    public function __construct(AbstractQuery $query = null, AbstractFilter $filter = null)
    {
        $this->setQuery($query);
        $this->setFilter($filter);
    }

    /**
     * Sets a query.
     *
     * @param \Elastica2\Query\AbstractQuery $query Query object
     *
     * @return $this
     */
    public function setQuery(AbstractQuery $query = null)
    {
        return $this->setParam('query', $query);
    }

    /**
     * Sets the filter.
     *
     * @param \Elastica2\Filter\AbstractFilter $filter Filter object
     *
     * @return $this
     */
    public function setFilter(AbstractFilter $filter = null)
    {
        return $this->setParam('filter', $filter);
    }

    /**
     * Gets the filter.
     *
     * @return \Elastica2\Filter\AbstractFilter
     */
    public function getFilter()
    {
        return $this->getParam('filter');
    }

    /**
     * Gets the query.
     *
     * @return \Elastica2\Query\AbstractQuery
     */
    public function getQuery()
    {
        return $this->getParam('query');
    }

    /**
     * Converts query to array.
     *
     * @return array Query array
     *
     * @see \Elastica2\Query\AbstractQuery::toArray()
     */
    public function toArray()
    {
        $filtered = array();

        if ($this->hasParam('query') && $this->getParam('query') instanceof AbstractQuery) {
            $filtered['query'] = $this->getParam('query')->toArray();
        }

        if ($this->hasParam('filter') && $this->getParam('filter') instanceof AbstractFilter) {
            $filtered['filter'] = $this->getParam('filter')->toArray();
        }

        if (empty($filtered)) {
            throw new InvalidException('A query and/or filter is required');
        }

        return array('filtered' => $filtered);
    }
}
