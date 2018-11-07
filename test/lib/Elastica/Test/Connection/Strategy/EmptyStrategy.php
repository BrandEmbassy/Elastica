<?php
namespace OldElastica\Test\Connection\Strategy;

use OldElastica\Connection\Strategy\StrategyInterface;

/**
 * Description of EmptyStrategy.
 *
 * @author chabior
 */
class EmptyStrategy implements StrategyInterface
{
    public function getConnection($connections)
    {
        return;
    }
}
