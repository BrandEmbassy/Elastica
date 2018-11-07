<?php
namespace OldElastica\Connection\Strategy;

use OldElastica\Exception\ClientException;

/**
 * Description of SimpleStrategy.
 *
 * @author chabior
 */
class Simple implements StrategyInterface
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
        foreach ($connections as $connection) {
            if ($connection->isEnabled()) {
                return $connection;
            }
        }

        throw new ClientException('No enabled connection');
    }
}
