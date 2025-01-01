<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Logger;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\DI\Container;
use Joomla\Filter\InputFilter;

/**
 * Class ObjectManager
 * Dependency injector for the classes
 *
 * @package Bluecoder\Component\Jfilters\Administrator
 */
class ObjectManager
{
    /**
     * @var ObjectManager
     * @since 1.0.0
     */
    protected static $instance;

    /**
     * @var \Joomla\DI\Container
     * @since 1.0.0
     */
    protected $container;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $preferences = [];

    /**
     * ObjectManager constructor.
     */
    public function __construct()
    {
        /*
         * Setup the dependencies for the ComponentConfig object.
         * We cannot instantiate with the Object Manager in that case.
         */
        $inputFilter = new InputFilter();
        $logger = new Logger();
        $componentConfig = new ComponentConfig($inputFilter, $logger);
        
        // Since this __construct function is the entry point of the requests (backend, frontend), we declare that global constant here.
        \define('JF_PROFILING', $componentConfig->get('profiling', false));
        
        if($componentConfig->get('edit_preferences_file_path', false)) {
            try {
                $preferenceFile = $componentConfig->get('preferences_file_path', ComponentConfig::JFILTERS_PREFERENCE_FILE);
            }
            catch (\UnexpectedValueException $e) {
                //Suck it. We can live with that setting invalid.
            }
        }
        // Otherwise use the default one
        if(empty($preferenceFile) || !file_exists($preferenceFile)) {
            $preferenceFile = ComponentConfig::JFILTERS_PREFERENCE_FILE;
        }
        $preferences = [];
        $preferencesFile = realpath($preferenceFile);
        if(!file_exists($preferencesFile)) {
            throw new \UnexpectedValueException(sprintf("The supplied preferences file:%s , does not exist, or is not accessible.", $preferencesFile));
        }
        require_once $preferencesFile;
        $this->container = Factory::getContainer();
        $this->preferences = $preferences;
    }

    /**
     * Get singleton of ObjectManager
     *
     * @return ObjectManager
     * @since 1.0.0
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ObjectManager();
        }
        return self::$instance;
    }

    /**
     * @return Container
     * @since 1.0.0
     */
    public function getContainer() : Container
    {
        return $this->container;
    }

    /**
     * Create a new object, resolving constructor dependencies
     *
     * @param $interfaceName
     * @param array $params
     * @return object
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function createObject($interfaceName, $params = [])
    {
        $className = $this->getPreference($interfaceName);
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        //no constructor no params. Piece of cake
        if ($constructor === null) {
            return new $className;
        }

        $_params = $constructor->getParameters();

        //not all params passed, lets detect them
        if (empty($params) || count($_params)>count($params)) {
            foreach ($_params as $param) {
                $key = $param->getName();
                $reflectionType = $param->getType();
                if($reflectionType !== null) {
                    $paramClass = $reflectionType->getName();

                    /*
                     * scalar types no need instantiation, neither to be stored in the container
                     */
                    if(in_array($paramClass, ['string', 'int', 'integer', 'float', 'bool', 'boolean']) || $paramClass == 'array') {
                        if($param->isOptional()) {
                            $params[$key] = $param->getDefaultValue();
                        }
                        else {
                            if ($paramClass == 'array') {
                                $params[$key] = [];
                            }
                            else {
                                $params[$key] = '';
                            }
                        }
                        continue ;
                    }
                    try {
                        $newObject = $this->getObject($paramClass);
                        $params[$key] = $newObject;
                    }catch (\Exception $e) {
                        throw $e;
                    }
                }
            }
        }
        return $reflection->newInstanceArgs($params);
    }

    /**
     * Get singleton object
     *
     * @param string $interfaceName
     * @param array $arguments
     * @return mixed|object
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getObject(string $interfaceName, array $arguments = [])
    {
        if ($this->container->has($interfaceName)) {
            return $this->container->get($interfaceName);
        }
        //resolve the interface
        $className = $this->getPreference($interfaceName);
        $className = ltrim($className, '\\');

        //maybe it is stored using the actual class name (Not the Interface)
        if ($this->container->has($className)) {
            return $this->container->get($className);
        }

        $object = $this->createObject($interfaceName, $arguments);
        if(!$object instanceof $interfaceName) {
            throw new \RuntimeException(sprintf('The object is either not generated, or is not of type: %s', $interfaceName));
        }
        $this->container->alias($interfaceName, $className);
        $this->container->set($className, $object, true);
        return $object;
    }

    /**
     * Get preference class for an Interface
     * or the class itself if it's not Interface
     *
     * @param string $interfaceName
     * @return mixed|string
     * @since 1.0.0
     */
    protected function getPreference(string $interfaceName)
    {
        $interfaceName = trim($interfaceName);
        $interfaceName = '\\' . $interfaceName;

        //It's a class and not an interface
        if (class_exists($interfaceName)) {
            return $interfaceName;
        }
        if (!isset($this->preferences[$interfaceName])) {
            throw new \RuntimeException(Text::sprintf('Preference for the interface: %s, cannot be found',
                $interfaceName));
        }
        return $this->preferences[$interfaceName];
    }
}
