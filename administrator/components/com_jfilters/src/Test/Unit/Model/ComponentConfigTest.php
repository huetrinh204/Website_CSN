<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\Filter\InputFilter;
use Joomla\Registry\Registry;
use PHPUnit\Framework\TestCase;

class ComponentConfigTest extends TestCase
{
    /**
     * @var ComponentConfig
     * @since 1.0.0
     */
    protected $object;

    protected function setUp() : void
    {
        $inputFilter = new InputFilter;
        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->object = new ComponentConfig($inputFilter, $loggerMock);
    }

    /**
     * @since 1.0.0
     * @dataProvider dataProvider
     */
    public function testGet($paramName, $paramValue, $default)
    {
        $params = new Registry([$paramName=>$paramValue]);
        $this->object->setParamsRegistry($params);
        $value = $paramValue??$default;
        $this->assertEquals($value, $this->object->get($paramName, $default));
    }

    /**
     * @since        1.0.0
     * @dataProvider dataProviderDeclared
     */
    public function testSetDeclared($paramName, $paramValue, $dependsOn, $expected)
    {
        if ($dependsOn) {
            list($depdendentParamName, $depdendentParamOperator, $depdendentParamValue) = $dependsOn;
            $this->object->set($depdendentParamName, $depdendentParamValue);
        }
        // If we set the dependent param (e.g. 'edit_filters_config_file_path' = 1), we can set the value and test using an invalid value.
        if ($depdendentParamName && $depdendentParamValue) {
            $this->expectException(\UnexpectedValueException::class);
            $this->object->set($paramName, $paramValue);
        } else {
            // If we do not set the dependent param, it will set nothing and the function will just return the class object.
            $this->assertEquals($this->object, $this->object->set($paramName, $paramValue));
        }
    }

    /**
     * @since 1.0.0
     * @dataProvider dataProviderDeclared
     */
    public function testGetDeclared($paramName, $paramValue, $dependsOn, $expected)
    {
        $this->assertEquals($expected, $this->object->get($paramName));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        /*
         * Each of the array elements represents a filter
         * The 3rd attribute represents the values/request of the filter
         */
        return [
            [
                'name' => 'Var1',
                'value' => '20',
                'default'=>'BlaBla'
            ],
            [
                'name' => 'Var2',
                'value' => 'Hahaha',
                'default'=>'Hoho'
            ],
            [
                'name' => 'Var3',
                'value' => null,
                'default'=>5
            ],
        ];
    }


    /**
     * @return array
     */
    public function dataProviderDeclared()
    {
        return [
            [
                'name' => 'filters_config_file_path',
                'value'=> 'somevalue',
                'dependsOn' => ['edit_filters_config_file_path', '=', '1'],
                'expected' => ComponentConfig::FILTERS_XML_DEFAULT_FILENAME
            ],
            [
                'name' => 'filters_config_file_path',
                'value'=> 'somevalue',
                'dependsOn' => ['edit_filters_config_file_path', '=', '0'],
                'expected' => ComponentConfig::FILTERS_XML_DEFAULT_FILENAME
            ]
        ];
    }
}
