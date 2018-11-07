<?php
namespace OldElastica\Multi;

use OldElastica\Client;
use OldElastica\JSON;
use OldElastica\Request;
use OldElastica\Search as BaseSearch;

/**
 * OldElastica multi search.
 *
 * @author munkie
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-multi-search.html
 */
class Search
{
    /**
     * @var array|\OldElastica\Search[]
     */
    protected $_searches = array();

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var \OldElastica\Client
     */
    protected $_client;

    /**
     * Constructs search object.
     *
     * @param \OldElastica\Client $client Client object
     */
    public function __construct(Client $client)
    {
        $this->setClient($client);
    }

    /**
     * @return \OldElastica\Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @param \OldElastica\Client $client
     *
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->_client = $client;

        return $this;
    }

    /**
     * @return $this
     */
    public function clearSearches()
    {
        $this->_searches = array();

        return $this;
    }

    /**
     * @param \OldElastica\Search $search
     * @param string           $key    Optional key
     *
     * @return $this
     */
    public function addSearch(BaseSearch $search, $key = null)
    {
        if ($key) {
            $this->_searches[$key] = $search;
        } else {
            $this->_searches[] = $search;
        }

        return $this;
    }

    /**
     * @param array|\OldElastica\Search[] $searches
     *
     * @return $this
     */
    public function addSearches(array $searches)
    {
        foreach ($searches as $key => $search) {
            $this->addSearch($search, $key);
        }

        return $this;
    }

    /**
     * @param array|\OldElastica\Search[] $searches
     *
     * @return $this
     */
    public function setSearches(array $searches)
    {
        $this->clearSearches();
        $this->addSearches($searches);

        return $this;
    }

    /**
     * @return array|\OldElastica\Search[]
     */
    public function getSearches()
    {
        return $this->_searches;
    }

    /**
     * @param string $searchType
     *
     * @return $this
     */
    public function setSearchType($searchType)
    {
        $this->_options[BaseSearch::OPTION_SEARCH_TYPE] = $searchType;

        return $this;
    }

    /**
     * @return \OldElastica\Multi\ResultSet
     */
    public function search()
    {
        $data = $this->_getData();

        $response = $this->getClient()->request(
            '_msearch',
            Request::POST,
            $data,
            $this->_options
        );

        return new ResultSet($response, $this->getSearches());
    }

    /**
     * @return string
     */
    protected function _getData()
    {
        $data = '';
        foreach ($this->getSearches() as $search) {
            $data .= $this->_getSearchData($search);
        }

        return $data;
    }

    /**
     * @param \OldElastica\Search $search
     *
     * @return string
     */
    protected function _getSearchData(BaseSearch $search)
    {
        $header = $this->_getSearchDataHeader($search);
        $header = (empty($header)) ? new \stdClass() : $header;
        $query = $search->getQuery();

        $data = JSON::stringify($header)."\n";
        $data .= JSON::stringify($query->toArray())."\n";

        return $data;
    }

    /**
     * @param \OldElastica\Search $search
     *
     * @return array
     */
    protected function _getSearchDataHeader(BaseSearch $search)
    {
        $header = $search->getOptions();

        if ($search->hasIndices()) {
            $header['index'] = $search->getIndices();
        }

        if ($search->hasTypes()) {
            $header['types'] = $search->getTypes();
        }

        return $header;
    }
}
