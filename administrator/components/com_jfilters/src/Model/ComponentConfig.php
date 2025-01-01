<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filter\InputFilter;
use Joomla\Registry\Registry;

/**
 * Class ComponentConfig
 *
 * The main reason for the existence of that class is that the Joomla\CMS\Component\ComponentHelper, mainly uses static methods,
 * which cannot be stubbed in our tests. Moreover we can extend the default functionality, by adding validation rules.
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
class ComponentConfig
{
    /**
     * the file that defines the preferences for the Interfaces
     * @since 1.0.0
     */
    const JFILTERS_PREFERENCE_FILE = JPATH_ADMINISTRATOR . '/components/com_jfilters/config_presets/preferences.php';

    /**
     * The default xml file for the contexts
     * @since 1.0.0
     */
    const CONTEXTS_XML_DEFAULT_FILENAME = JPATH_ADMINISTRATOR . '/components/com_jfilters/config_presets/contexts.xml';

    /**
     * The default xml file for the filters
     * @since 1.0.0
     */
    const FILTERS_XML_DEFAULT_FILENAME = JPATH_ADMINISTRATOR . '/components/com_jfilters/config_presets/filters.xml';

    /**
     * The default xml file for the dynamic filters
     * @since 1.0.0
     */
    const FILTERS_DYNAMIC_XML_DEFAULT_FILENAME = JPATH_ADMINISTRATOR . '/components/com_jfilters/config_presets/filters/dynamic.xml';

    /**
     * @var Registry
     * @since 1.0.0
     */
    protected $paramsRegistry;

    /**
     *
     * @var InputFilter
     * @since 1.0.0
     */
    protected $filter;

    /**
     * @var LoggerInterface
     * @since 1.0.0
     */
    protected $logger;

    /**
     * Stores the params
     *
     * @var array
     * @since 1.0.0
     */
    protected $params = [];

    /**
     * Set additional parameters for the config params
     *
     * @var array
     * @since 1.0.0
     */
    protected $paramsConfig = [        
        'use_canonical' => [
            'default' => false,
            /* Type is required in all the params */
            'type' => 'boolean',
            'depends' => ['root', '=', '1']
        ],
        'max_option_label_length' => [
            'type' => 'int',
            'default' => 55,
        ],
        'max_option_value_length' => [
            'type' => 'int',
            'default' => 35,
        ],
        // Module settings
        'ajax_mode' => [
            'default' => false,
            'type' => 'boolean'
        ],
        'submit_filters_using_button' => [
            'default' => false,
            'type' => 'boolean'
        ],
        'filters_config_file_path' => [
            'type' => 'path',
            'validationRule' => '/[A-Za-z0-9_\/-]+\.xml/',
            'default' => self::FILTERS_XML_DEFAULT_FILENAME,
            'depends' => ['edit_filters_config_file_path', '=', '1']
        ],
        'contexts_config_file_path' => [
            'type' => 'path',
            'validationRule' => '/[A-Za-z0-9_\/-]+\.xml/',
            'default' => self::CONTEXTS_XML_DEFAULT_FILENAME,
            'depends' => ['edit_contexts_config_file_path', '=', '1']
        ],
        'dynamic_filters_config_file_path' => [
            'type' => 'path',
            'validationRule' => '/[A-Za-z0-9_\/-]+\.xml/',
            'default' => self::FILTERS_DYNAMIC_XML_DEFAULT_FILENAME,
            'depends' => ['edit_dynamic_filters_config_file_path', '=', '1']
        ],
        'preferences_file_path' => [
            'type' => 'path',
            'validationRule' => '[A-Za-z0-9_\/-]+\.php',
            'default' => self::FILTERS_DYNAMIC_XML_DEFAULT_FILENAME,
            'depends' => ['edit_preferences_file_path', '=', '1']
        ]
    ];

    /**
     * ComponentConfig constructor.
     *
     * @param InputFilter $filter
     * @param LoggerInterface $logger
     */
    public function __construct(InputFilter $filter, LoggerInterface $logger)
    {
        $this->filter = $filter;
        $this->logger = $logger;
        $this->init();
    }

    /**
     * Initialize externally fetched params
     *
     * @since 1.0.0
     */
    public function init()
    {
        $proEdition = false;
        $version = '';

        // Get the app vars and use them to generate parameters.
        $evnVars = @include __DIR__ . '/../../env.php';

        if (is_array($evnVars) && isset($evnVars['version']) && isset($evnVars['md5Hash'])) {
            $proVersionMd5Hash = md5($evnVars['version'] . 'PRO');
            $currentVersionHash = trim($evnVars['md5Hash'], " -");
            $version = (string)$evnVars['version'];

            /*
             * Is Pro if the hash is valid or we use placeholders (dev mode).
             * Do not use entire HASH placeholder or will be replaced during build.
             */
            if ($proVersionMd5Hash === $currentVersionHash || strpos($evnVars['md5Hash'],  '##HASH#') === 0) {
                $proEdition = true;
            }
        }

        $this->set('version', $version);
        $this->set('isPro', $proEdition);
    }

    /**
     * Set a component param
     *
     * @param   string  $keyName
     * @param   mixed   $value
     *
     * @return $this
     * @since 1.0.0
     */
    public function set(string $keyName, $value) : ComponentConfig
    {
        $value = $this->validate($keyName, $value);
        $this->params[$keyName] = $value;
        return $this;
    }

    /**
     * Get a component param
     *
     * @param string $keyName
     * @param null $default
     *
     * @return mixed
     * @throws \UnexpectedValueException
     * @since 1.0.0
     */
    public function get(string $keyName, $default = null)
    {
        if (!isset($this->params[$keyName])) {
            $default = $default === null && isset($this->paramsConfig[$keyName]['default']) ? $this->paramsConfig[$keyName]['default'] : $default;
            $paramValue = $this->getParamsRegistry()->get($keyName, $default);
            $paramValue = $this->validate($keyName, $paramValue);
            if (!isset($paramValue)) {
                $paramValue = $default;
            }
            $this->params[$keyName] = $paramValue;
        }

        return $this->params[$keyName];
    }

    /**
     * Return the component's params
     *
     * @return Registry
     * @since 1.0.0
     */
    public function getParamsRegistry(): Registry
    {
        if ($this->paramsRegistry === null) {
            try {
                $this->paramsRegistry = ComponentHelper::getParams('com_jfilters');
            } catch (\Exception $exception) {
                /*
                 * It fails when we call it through CLI (e.g. Unit tests).
                 * Hence suck it.
                 */
                $this->paramsRegistry = new Registry();
            }
        }

        return $this->paramsRegistry;
    }

    /**
     * @param Registry $paramsRegistry
     *
     * @return ComponentConfig
     * @since 1.0.0
     */
    public function setParamsRegistry(Registry $paramsRegistry): ComponentConfig
    {
        $this->paramsRegistry = $paramsRegistry;

        return $this;
    }

    /**
     * Validate a param value by it's rules
     *
     * @param string $keyName
     * @param mixed $value
     * @return array|bool|float|int|mixed|string|null
     * @since 1.0.0
     */
    protected function validate(string $keyName, $value)
    {
        if (!$this->isValidByDependency($keyName)) {
            $value = null;
        }
        if (isset($value) && isset($this->paramsConfig[$keyName])) {
            $type = $this->paramsConfig[$keyName]['type'];
            $validationPattern = $this->paramsConfig[$keyName]['validationRule'] ?? '';
            $value = $this->filter->clean($value, $type);

            // additional validation
            if (!empty($validationPattern)) {
                preg_match($validationPattern, $value, $matches);
                if (!empty($matches[0])) {
                    $value = $matches[0];
                } else {
                    $e = new \UnexpectedValueException('The supplied value for the configuration setting \'%s\', is not valid. Please check again, that configuration setting.');
                    $this->logger->error($e->getMessage());
                    throw $e;
                }
            }

            if ($type === 'path' && !file_exists($value)) {
                // maybe is not real path
                $value = realpath(JPATH_ROOT . DIRECTORY_SEPARATOR . $value);
                if (!file_exists($value)) {
                    $value = null;
                }
            }
        }
        return $value;
    }

    /**
     * Validate if the field's value should be taken into account, in case there are dependencies
     *
     * @param string $keyName
     * @return bool|mixed
     * @since 1.0.0
     */
    protected function isValidByDependency(string $keyName)
    {
        $result = true;
        if (isset($this->paramsConfig[$keyName]['depends'])) {
            $result = false;
            $dependencyRule = $this->paramsConfig[$keyName]['depends'];
            $parentParamValue = $this->get($dependencyRule[0]);
            $operator = in_array($dependencyRule[1], ['=', '!=']) ? $dependencyRule[1] . '=' : null;
            $expression = isset($parentParamValue) && isset($operator) && isset($dependencyRule[2]) ? $parentParamValue . $operator . $dependencyRule[2] . ';' : false;
            $result = $expression ? eval("return $expression") : false;
        }
        return $result;
    }
}
