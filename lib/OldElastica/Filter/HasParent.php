<?php
namespace OldElastica\Filter;

/**
 * Returns child documents having parent docs matching the query.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-parent-filter.html
 */
class HasParent extends AbstractFilter
{
    /**
     * Construct HasParent filter.
     *
     * @param string|\OldElastica\Query|\OldElastica\Filter\AbstractFilter $query Query string or a Query object or a filter
     * @param string|\OldElastica\Type                                  $type  Parent document type
     */
    public function __construct($query, $type)
    {
        if ($query instanceof AbstractFilter) {
            $this->setFilter($query);
        } else {
            $this->setQuery($query);
        }
        $this->setType($type);
    }

    /**
     * Sets query object.
     *
     * @param string|\OldElastica\Query|\OldElastica\Query\AbstractQuery $query
     *
     * @return $this
     */
    public function setQuery($query)
    {
        return $this->setParam('query', \OldElastica\Query::create($query));
    }

    /**
     * Sets filter object.
     *
     * @param \OldElastica\Filter\AbstractFilter $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        return $this->setParam('filter', $filter);
    }

    /**
     * Set type of the parent document.
     *
     * @param string|\OldElastica\Type $type Parent document type
     *
     * @return $this
     */
    public function setType($type)
    {
        if ($type instanceof \OldElastica\Type) {
            $type = $type->getName();
        }

        return $this->setParam('type', (string) $type);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        if (isset($array[$baseName]['query'])) {
            $array[$baseName]['query'] = $array[$baseName]['query']['query'];
        }

        return $array;
    }
}
