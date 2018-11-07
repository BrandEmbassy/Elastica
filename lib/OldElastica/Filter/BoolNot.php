<?php
namespace OldElastica\Filter;

/**
 * Not Filter.
 *
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-not-filter.html
 */
class BoolNot extends AbstractFilter
{
    /**
     * Creates Not filter query.
     *
     * @param \OldElastica\Filter\AbstractFilter $filter Filter object
     */
    public function __construct(AbstractFilter $filter)
    {
        $this->setFilter($filter);
    }

    /**
     * Set filter.
     *
     * @param \OldElastica\Filter\AbstractFilter $filter
     *
     * @return $this
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->setParam('filter', $filter);
    }

    /**
     * @return string
     */
    protected function _getBaseName()
    {
        return 'not';
    }
}
