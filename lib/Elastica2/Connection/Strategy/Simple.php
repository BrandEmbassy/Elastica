<?php
namespace Elastica2\Connection\Strategy;

use Elastica2\Exception\ClientException;

/**
 * Description of SimpleStrategy.
 *
 * @author chabior
 */
class Simple implements StrategyInterface
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
        foreach ($connections as $connection) {
            if ($connection->isEnabled()) {
                return $connection;
            }
        }

        throw new ClientException('No enabled connection');
    }
}
