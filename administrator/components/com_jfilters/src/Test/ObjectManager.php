<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test;

\defined('_JEXEC') or die();

class ObjectManager
{
    /**
     * @var ObjectManager
     */
    protected static $instance;

    /**
     * @return ObjectManager
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ObjectManager();
        }
        return self::$instance;
    }

    /**
     * Get class instance
     *
     * @param $className
     * @param array $arguments
     * @return object
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getObject($className, array $arguments = [])
    {
        $constructArguments = $this->getConstructArguments($className, $arguments);
        $reflectionClass = new \ReflectionClass($className);
        $newObject = $reflectionClass->newInstanceArgs($constructArguments);

        foreach (array_diff_key($arguments, $constructArguments) as $key => $value) {
            $propertyReflectionClass = $reflectionClass;
            while ($propertyReflectionClass) {
                if ($propertyReflectionClass->hasProperty($key)) {
                    $reflectionProperty = $propertyReflectionClass->getProperty($key);
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($newObject, $value);
                    break;
                }
                $propertyReflectionClass = $propertyReflectionClass->getParentClass();
            }
        }
        return $newObject;
    }

    /**
     * Retrieve associative array of arguments that used for new object instance creation
     *
     * @param $className
     * @param array $arguments
     * @return array
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getConstructArguments($className, array $arguments = [])
    {
        $constructArguments = [];
        $object = null;
        if (!method_exists($className, '__construct')) {
            return $constructArguments;
        }
        $method = new \ReflectionMethod($className, '__construct');

        foreach ($method->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $argClassName = null;
            $defaultValue = null;

            if (array_key_exists($parameterName, $arguments)) {
                $constructArguments[$parameterName] = $arguments[$parameterName];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $defaultValue = $parameter->getDefaultValue();
            }

            try {
                if ($parameter->getClass()) {
                    $argClassName = $parameter->getClass()->getName();
                }
                $object = $this->_getMockObject($argClassName, $arguments);
            } catch (\ReflectionException $e) {
                $parameterString = $parameter->__toString();
                $firstPosition = strpos($parameterString, '<required>');
                if ($firstPosition !== false) {
                    $parameterString = substr($parameterString, $firstPosition + 11);
                    $parameterString = substr($parameterString, 0, strpos($parameterString, ' '));
                    $object = $this->_testObject->getMockBuilder($parameterString)
                        ->disableOriginalConstructor()
                        ->disableOriginalClone()
                        ->disableArgumentCloning()
                        ->disallowMockingUnknownTypes()
                        ->getMock();
                }
            }

            $constructArguments[$parameterName] = null === $object ? $defaultValue : $object;
        }
        return $constructArguments;
    }
}
