<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Field\DisplaytypesField;
use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;

/**
 * Class Validator
 * Validates the requested values by the set rules
 */
class PropertyHandler
{
    /**
     * @var ComponentConfig
     */
    protected $componentConfig;

    /**
     * Parameter rules
     *
     * @var array[]
     * @todo Validate further by the accepted values for each property
     */
    protected $paramsConfig = [
        'toggle_state' => [
            'type' => 'cmd',
            'default' => 'expanded',
            'value' => [
                'collapsed' => 'expanded'
            ]
        ],
        'list_search' => [
            'type' => 'int',
            'default' => 0,
            'value' => [
                1 => 0
            ]
        ],
        'display' => [
            'type' => 'cmd',
            'default' => DisplaytypesField::DEFAULT_DISPLAY_TYPE,
            // Map the PRO edition values (key) to the Free available (value), in case of downgrade or violation of the locked fields (e.g. through the db).
            'value' => [
                'list' => 'links',
                'radios' => 'links',
                'buttons_single' => 'links',
                'buttons_multi' => 'checkboxes'
            ]
        ],
        'options_sort_by' => [
            'type' => 'cmd',
            'default' => 'label',
            'value' => [
                'ordering' => 'label'
            ]
        ],
        'options_sort_direction' => [
            'type' => 'cmd',
            'default' => 'asc',
        ],
        'show_option_counter' => [
            'type' => 'int',
            'default' => 0,
            'value' => [
                1 => 0
            ]
        ],
        'scrollbar_after' => [
            'type' => 'float',
            'default' => '',
            'value' => [
                '*' => ''
            ]
        ]
    ];

    public function __construct(ComponentConfig $componentConfig)
    {
        $this->componentConfig = $componentConfig;
    }

    /**
     * Get the array after applying the rules at each element
     *
     * @param   array  $vars  format $varName => $varValue
     *
     * @return array
     * @since 1.0.0
     */
    public function getArray(array $vars): array
    {
        $arrayTmp = [];
        foreach ($vars as $varName => $varValue) {
            $arrayTmp[$varName] = $this->get($varName, $varValue);
        }

        return $arrayTmp;
    }

    /**
     * Get the proper value, after applying the rules.
     *
     * @param   string  $keyName
     * @param   mixed   $value
     *
     * @return mixed
     * @since 1.0.0
     */
    public function get(string $keyName, $value)
    {
        if ($value === null) {
            $value = $this->getDefault($keyName);
        }

        return $this->getValueByEdition($keyName, $value);
    }

    /**
     * Get the default value.
     *
     * @param   string  $keyName
     *
     * @return mixed|null
     * @since 1.0.0
     */
    protected function getDefault(string $keyName)
    {
        $value = null;
        if (isset($this->paramsConfig[$keyName]['default'])) {
            $value = $this->paramsConfig[$keyName]['default'];
        }

        return $value;
    }

    /**
     * Gets a value based on the edition.
     *
     * @param   string|null  $keyName
     * @param   scalar       $value
     *
     * @return scalar
     * @since 1.0.0
     */
    public function getValueByEdition(string $keyName, $value)
    {
        if (isset($value) && isset($this->paramsConfig[$keyName]) && !$this->componentConfig->get('isPro')) {
            if (isset($this->paramsConfig[$keyName]['value'][$value])) {
                $value = $this->paramsConfig[$keyName]['value'][$value];
            } // Asterisk (*) applies for all the values.
            elseif (isset($this->paramsConfig[$keyName]['value']['*'])) {
                $value = $this->paramsConfig[$keyName]['value']['*'];
            }
        }

        return $value;
    }
}