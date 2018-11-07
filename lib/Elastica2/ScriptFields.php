<?php
namespace Elastica2;

use Elastica2\Exception\InvalidException;

/**
 * Container for scripts as fields.
 *
 * @author Sebastien Lavoie <github@lavoie.sl>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-script-fields.html
 */
class ScriptFields extends Param
{
    /**
     * @param \Elastica2\Script[]|array $scripts OPTIONAL
     */
    public function __construct(array $scripts = array())
    {
        if ($scripts) {
            $this->setScripts($scripts);
        }
    }

    /**
     * @param string           $name   Name of the Script field
     * @param \Elastica2\Script $script
     *
     * @throws \Elastica2\Exception\InvalidException
     *
     * @return $this
     */
    public function addScript($name, Script $script)
    {
        if (!is_string($name) || !strlen($name)) {
            throw new InvalidException('The name of a Script is required and must be a string');
        }
        $this->setParam($name, $script);

        return $this;
    }

    /**
     * @param \Elastica2\Script[]|array $scripts Associative array of string => Elastica2\Script
     *
     * @return $this
     */
    public function setScripts(array $scripts)
    {
        $this->_params = array();
        foreach ($scripts as $name => $script) {
            $this->addScript($name, $script);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->_convertArrayable($this->_params);
    }
}
