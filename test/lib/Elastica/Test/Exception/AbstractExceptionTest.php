<?php
namespace OldElastica\Test\Exception;

use OldElastica\Test\Base as BaseTest;

abstract class AbstractExceptionTest extends BaseTest
{
    protected function _getExceptionClass()
    {
        $reflection = new \ReflectionObject($this);

        // OldElastica\Test\Exception\RuntimeExceptionTest => OldElastica\Exception\RuntimeExceptionTest
        $name = preg_replace('/^OldElastica\\\\Test/', 'OldElastica', $reflection->getName());

        // OldElastica\Exception\RuntimeExceptionTest => OldElastica\Exception\RuntimeException
        $name = preg_replace('/Test$/', '', $name);

        return $name;
    }

    /**
     * @group unit
     */
    public function testInheritance()
    {
        $className = $this->_getExceptionClass();
        $reflection = new \ReflectionClass($className);
        $this->assertTrue($reflection->isSubclassOf('Exception'));
        $this->assertTrue($reflection->implementsInterface('Elastica\Exception\ExceptionInterface'));
    }
}
