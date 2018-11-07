<?php
namespace OldElastica\Filter;

use OldElastica\Exception\InvalidException;
use OldElastica\Query\AbstractQuery;

/**
 * Query filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-filter.html
 */
class Query extends AbstractFilter
{
    /**
     * Query.
     *
     * @var array
     */
    protected $_query;

    /**
     * Construct query filter.
     *
     * @param array|\OldElastica\Query\AbstractQuery $query
     */
    public function __construct($query = null)
    {
        if (!is_null($query)) {
            $this->setQuery($query);
        }
    }

    /**
     * Set query.
     *
     * @param array|\OldElastica\Query\AbstractQuery $query
     *
     * @throws \OldElastica\Exception\InvalidException If parameter is invalid
     *
     * @return $this
     */
    public function setQuery($query)
    {
        if (!$query instanceof AbstractQuery && !is_array($query)) {
            throw new InvalidException('expected an array or instance of OldElastica\Query\AbstractQuery');
        }

        $this->_query = $query;

        return $this;
    }

    /**
     * @see \OldElastica\Param::_getBaseName()
     */
    protected function _getBaseName()
    {
        if (empty($this->_params)) {
            return 'query';
        } else {
            return 'fquery';
        }
    }

    /**
     * @see \OldElastica\Param::toArray()
     */
    public function toArray()
    {
        $data = parent::toArray();

        $name = $this->_getBaseName();
        $filterData = $data[$name];

        if (empty($filterData)) {
            $filterData = $this->_query;
        } else {
            $filterData['query'] = $this->_query;
        }

        $data[$name] = $filterData;

        return $this->_convertArrayable($data);
    }
}
