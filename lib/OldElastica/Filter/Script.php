<?php
namespace OldElastica\Filter;

use OldElastica;

/**
 * Script filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-script-filter.html
 */
class Script extends AbstractFilter
{
    /**
     * Query object.
     *
     * @var array|\OldElastica\Query\AbstractQuery
     */
    protected $_query = null;

    /**
     * Construct script filter.
     *
     * @param array|string|\OldElastica\Script $script OPTIONAL Script
     */
    public function __construct($script = null)
    {
        if ($script) {
            $this->setScript($script);
        }
    }

    /**
     * Sets script object.
     *
     * @param \OldElastica\Script|string|array $script
     *
     * @return $this
     */
    public function setScript($script)
    {
        return $this->setParam('script', OldElastica\Script::create($script));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = parent::toArray();

        if (isset($array['script'])) {
            $array['script'] = $array['script']['script'];
        }

        return $array;
    }
}
