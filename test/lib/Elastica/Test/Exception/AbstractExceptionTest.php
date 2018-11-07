<?php
namespace Elastica2\Test\Exception;

use Elastica2\Test\Base as BaseTest;

abstract class AbstractExceptionTest extends BaseTest
{
    protected function _getExceptionClass()
    {
        $reflection = new \ReflectionObject($this);

        // Elastica2\Test\Exception\RuntimeExceptionTest => Elastica2\Exception\RuntimeExceptionTest
        $name = preg_replace('/^Elastica2\\\\Test/', 'Elastica2', $reflection->getName());

        // Elastica2\Exception\RuntimeExceptionTest => Elastica2\Exception\RuntimeException
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
