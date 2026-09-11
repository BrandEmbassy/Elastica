<?php

namespace Elastica\Aggregation\Traits;

trait MissingTrait
{
    /**
     * Defines how documents that are missing a value should be treated.
     *
     * @return $this
     */
    public function setMissing($missing): self
    {
        return $this->setParam('missing', $missing);
    }
}
