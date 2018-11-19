<?php
namespace Elastica\Query;

use Elastica\Script\AbstractScript;

/**
 * Class FunctionScore.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-function-score-query.html
 */
class FunctionScore extends AbstractQuery
{
    const BOOST_MODE_MULTIPLY = 'multiply';
    const BOOST_MODE_REPLACE = 'replace';
    const BOOST_MODE_SUM = 'sum';
    const BOOST_MODE_AVERAGE = 'avg';
    const BOOST_MODE_MAX = 'max';
    const BOOST_MODE_MIN = 'min';

    const SCORE_MODE_MULTIPLY = 'multiply';
    const SCORE_MODE_SUM = 'sum';
    const SCORE_MODE_AVERAGE = 'avg';
    const SCORE_MODE_FIRST = 'first';
    const SCORE_MODE_MAX = 'max';
    const SCORE_MODE_MIN = 'min';

    const DECAY_GAUSS = 'gauss';
    const DECAY_EXPONENTIAL = 'exp';
    const DECAY_LINEAR = 'linear';

    const FIELD_VALUE_FACTOR_MODIFIER_NONE = 'none';
    const FIELD_VALUE_FACTOR_MODIFIER_LOG = 'log';
    const FIELD_VALUE_FACTOR_MODIFIER_LOG1P = 'log1p';
    const FIELD_VALUE_FACTOR_MODIFIER_LOG2P = 'log2p';
    const FIELD_VALUE_FACTOR_MODIFIER_LN = 'ln';
    const FIELD_VALUE_FACTOR_MODIFIER_LN1P = 'ln1p';
    const FIELD_VALUE_FACTOR_MODIFIER_LN2P = 'ln2p';
    const FIELD_VALUE_FACTOR_MODIFIER_SQUARE = 'square';
    const FIELD_VALUE_FACTOR_MODIFIER_SQRT = 'sqrt';
    const FIELD_VALUE_FACTOR_MODIFIER_RECIPROCAL = 'reciprocal';

    const MULTI_VALUE_MODE_MIN = 'min';
    const MULTI_VALUE_MODE_MAX = 'max';
    const MULTI_VALUE_MODE_AVG = 'avg';
    const MULTI_VALUE_MODE_SUM = 'sum';

    const RANDOM_SCORE_FIELD_ID = '_id';
    const RANDOM_SCORE_FIELD_SEQ_NO = '_seq_no';

    protected $_functions = [];

    /**
     * Set the child query for this function_score query.
     *
     * @param AbstractQuery $query
     *
     * @return $this
     */
    public function setQuery(AbstractQuery $query)
    {
        return $this->setParam('query', $query);
    }

    /**
     * Add a function to the function_score query.
     *
     * @param string        $functionType   valid values are DECAY_* constants and script_score
     * @param array|float   $functionParams the body of the function. See documentation for proper syntax.
     * @param AbstractQuery $filter         optional filter to apply to the function
     * @param float         $weight         function weight
     *
     * @return $this
     */
    public function addFunction($functionType, $functionParams, AbstractQuery $filter = null, $weight = null)
    {
        $function = [
            $functionType => $functionParams,
        ];

        if (!is_null($filter)) {
            $function['filter'] = $filter;
        }

        if ($weight !== null) {
            $function['weight'] = $weight;
        }

        $this->_functions[] = $function;

        return $this;
    }

    /**
     * Add a script_score function to the query.
     *
     * @param \Elastica\Script\AbstractScript $script a Script object
     * @param AbstractQuery                   $filter an optional filter to apply to the function
     * @param float                           $weight the weight of the function
     *
     * @return $this
     */
    public function addScriptScoreFunction(AbstractScript $script, AbstractQuery $filter = null, $weight = null)
    {
        return $this->addFunction('script_score', $script, $filter, $weight);
    }

    /**
     * Add a decay function to the query.
     *
     * @param string        $function       see DECAY_* constants for valid options
     * @param string        $field          the document field on which to perform the decay function
     * @param string        $origin         the origin value for this decay function
     * @param string        $scale          a scale to define the rate of decay for this function
     * @param string        $offset         If defined, this function will only be computed for documents with a distance from the origin greater than this value
     * @param float         $decay          optionally defines how documents are scored at the distance given by the $scale parameter
     * @param float         $weight         optional factor by which to multiply the score at the value provided by the $scale parameter
     * @param AbstractQuery $filter         a filter associated with this function
     * @param string        $multiValueMode see MULTI_VALUE_MODE_* constants for valid options
     *
     * @return $this
     */
    public function addDecayFunction(
        $function,
        $field,
        $origin,
        $scale,
        $offset = null,
        $decay = null,
        $weight = null,
        AbstractQuery $filter = null,
        $multiValueMode = null
    ) {
        $functionParams = [
            $field => [
                'origin' => $origin,
                'scale' => $scale,
            ],
        ];
        if (!is_null($offset)) {
            $functionParams[$field]['offset'] = $offset;
        }
        if (!is_null($decay)) {
            $functionParams[$field]['decay'] = (float) $decay;
        }

        if (null !== $multiValueMode) {
            $functionParams['multi_value_mode'] = $multiValueMode;
        }

        return $this->addFunction($function, $functionParams, $filter, $weight);
    }

    public function addFieldValueFactorFunction(
        $field,
        $factor = null,
        $modifier = null,
        $missing = null,
        $weight = null,
        AbstractQuery $filter = null
    ) {
        $functionParams = [
            'field' => $field,
        ];

        if (!is_null($factor)) {
            $functionParams['factor'] = $factor;
        }

        if (!is_null($modifier)) {
            $functionParams['modifier'] = $modifier;
        }

        if (!is_null($missing)) {
            $functionParams['missing'] = $missing;
        }

        return $this->addFunction('field_value_factor', $functionParams, $filter, $weight);
    }

    /**
     * @param float         $weight the weight of the function
     * @param AbstractQuery $filter a filter associated with this function
     *
     * @return $this
     */
    public function addWeightFunction($weight, AbstractQuery $filter = null)
    {
        return $this->addFunction('weight', $weight, $filter);
    }

    /**
     * Add a random_score function to the query.
     *
     * @param number        $seed   the seed value
     * @param AbstractQuery $filter a filter associated with this function
     * @param float         $weight an optional boost value associated with this function
     * @param string        $field  the field to be used for random number generation
     *
     * @return $this
     */
    public function addRandomScoreFunction($seed, AbstractQuery $filter = null, $weight = null, $field = null)
    {
        $functionParams = [
            'seed' => $seed,
        ];

        if (null !== $field) {
            $functionParams['field'] = $field;
        }

        return $this->addFunction('random_score', $functionParams, $filter, $weight);
    }

    /**
     * Set an overall boost value for this query.
     *
     * @param float $boost
     *
     * @return $this
     */
    public function setBoost($boost)
    {
        return $this->setParam('boost', (float) $boost);
    }

    /**
     * Restrict the combined boost of the function_score query and its child query.
     *
     * @param float $maxBoost
     *
     * @return $this
     */
    public function setMaxBoost($maxBoost)
    {
        return $this->setParam('max_boost', (float) $maxBoost);
    }

    /**
     * The boost mode determines how the score of this query is combined with that of the child query.
     *
     * @param string $mode see BOOST_MODE_* constants for valid options. Default is multiply.
     *
     * @return $this
     */
    public function setBoostMode($mode)
    {
        return $this->setParam('boost_mode', $mode);
    }

    /**
     * If set, this query will return results in random order.
     *
     * @param int $seed Set a seed value to return results in the same random order for consistent pagination.
     *
     * @return $this
     */
    public function setRandomScore($seed = null)
    {
        $seedParam = new \stdClass();
        if (!is_null($seed)) {
            $seedParam->seed = $seed;
        }

        return $this->setParam('random_score', $seedParam);
    }

    /**
     * Set the score method.
     *
     * @param string $mode see SCORE_MODE_* constants for valid options. Default is multiply.
     *
     * @return $this
     */
    public function setScoreMode($mode)
    {
        return $this->setParam('score_mode', $mode);
    }

    /**
     * Set min_score option.
     *
     * @param float $minScore
     *
     * @return $this
     */
    public function setMinScore($minScore)
    {
        return $this->setParam('min_score', (float) $minScore);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (sizeof($this->_functions)) {
            $this->setParam('functions', $this->_functions);
        }

        return parent::toArray();
    }
}
