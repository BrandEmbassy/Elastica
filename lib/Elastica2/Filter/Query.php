<?php
namespace Elastica2\Filter;

use Elastica2\Exception\InvalidException;
use Elastica2\Query\AbstractQuery;

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
     * @param array|\Elastica2\Query\AbstractQuery $query
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
     * @param array|\Elastica2\Query\AbstractQuery $query
     *
     * @throws \Elastica2\Exception\InvalidException If parameter is invalid
     *
     * @return $this
     */
    public function setQuery($query)
    {
        if (!$query instanceof AbstractQuery && !is_array($query)) {
            throw new InvalidException('expected an array or instance of Elastica2\Query\AbstractQuery');
        }

        $this->_query = $query;

        return $this;
    }

    /**
     * @see \Elastica2\Param::_getBaseName()
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
     * @see \Elastica2\Param::toArray()
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
