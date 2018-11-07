<?php
namespace Elastica2;

/**
 * Elastica2 searchable interface.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface SearchableInterface
{
    /**
     * Searches results for a query.
     *
     * TODO: Improve sample code
     * {
     *     "from" : 0,
     *     "size" : 10,
     *     "sort" : {
     *          "postDate" : {"reverse" : true},
     *          "user" : { },
     *          "_score" : { }
     *      },
     *      "query" : {
     *          "term" : { "user" : "kimchy" }
     *      }
     * }
     *
     * @param string|array|\Elastica2\Query $query   Array with all query data inside or a Elastica2\Query object
     * @param null                         $options
     *
     * @return \Elastica2\ResultSet with all results inside
     */
    public function search($query = '', $options = null);

    /**
     * Counts results for a query.
     *
     * If no query is set, matchall query is created
     *
     * @param string|array|\Elastica2\Query $query Array with all query data inside or a Elastica2\Query object
     *
     * @return int number of documents matching the query
     */
    public function count($query = '');

    /**
     * @param \Elastica2\Query|string $query
     * @param array                  $options
     *
     * @return \Elastica2\Search
     */
    public function createSearch($query = '', $options = null);
}
