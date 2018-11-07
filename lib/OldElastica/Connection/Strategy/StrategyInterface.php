<?php
namespace OldElastica\Connection\Strategy;

/**
 * Description of AbstractStrategy.
 *
 * @author chabior
 */
interface StrategyInterface
{
    /**
     * @param array|\OldElastica\Connection[] $connections
     *
     * @return \OldElastica\Connection
     */
    public function getConnection($connections);
}
