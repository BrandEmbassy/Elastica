<?php
namespace Elastica2\Connection\Strategy;

/**
 * Description of RoundRobin.
 *
 * @author chabior
 */
class RoundRobin extends Simple
{
    /**
     * @param array|\Elastica2\Connection[] $connections
     *
     * @throws \Elastica2\Exception\ClientException
     *
     * @return \Elastica2\Connection
     */
    public function getConnection($connections)
    {
        shuffle($connections);

        return parent::getConnection($connections);
    }
}
