<?php
namespace OldElastica\Query;

use OldElastica\Exception\InvalidException;
use OldElastica\Filter\AbstractFilter;

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
     * @param \OldElastica\Query\AbstractQuery   $query  OPTIONAL Query object
     * @param \OldElastica\Filter\AbstractFilter $filter OPTIONAL Filter object
     */
    public function __construct(AbstractQuery $query = null, AbstractFilter $filter = null)
    {
        $this->setQuery($query);
        $this->setFilter($filter);
    }

    /**
     * Sets a query.
     *
     * @param \OldElastica\Query\AbstractQuery $query Query object
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
     * @param \OldElastica\Filter\AbstractFilter $filter Filter object
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
     * @return \OldElastica\Filter\AbstractFilter
     */
    public function getFilter()
    {
        return $this->getParam('filter');
    }

    /**
     * Gets the query.
     *
     * @return \OldElastica\Query\AbstractQuery
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
     * @see \OldElastica\Query\AbstractQuery::toArray()
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
