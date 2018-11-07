<?php
namespace Elastica2\Connection\Strategy;

/**
 * Description of AbstractStrategy.
 *
 * @author chabior
 */
interface StrategyInterface
{
    /**
     * @param array|\Elastica2\Connection[] $connections
     *
     * @return \Elastica2\Connection
     */
    public function getConnection($connections);
}
