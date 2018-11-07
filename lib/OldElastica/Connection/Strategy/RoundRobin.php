<?php
namespace OldElastica\Connection\Strategy;

/**
 * Description of RoundRobin.
 *
 * @author chabior
 */
class RoundRobin extends Simple
{
    /**
     * @param array|\OldElastica\Connection[] $connections
     *
     * @throws \OldElastica\Exception\ClientException
     *
     * @return \OldElastica\Connection
     */
    public function getConnection($connections)
    {
        shuffle($connections);

        return parent::getConnection($connections);
    }
}
