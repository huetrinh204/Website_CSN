<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\PropertyHandler;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use PHPUnit\Framework\TestCase;

class PropertyHandlerTest extends TestCase
{
    /**
     * @var ComponentConfig
     */
    protected $componentConfig;

    /**
     * @var PropertyHandler
     */
    protected $model;

    /**
     * Setup basic vars
     */
    protected function setUp(): void
    {
        $this->componentConfig = ObjectManager::getInstance()->getObject(ComponentConfig::class);
        $this->model = new PropertyHandler($this->componentConfig);
    }

    /**
     * @param array $passedArray
     * @param array $expectedArrayFree
     * @param array $expectedArrayPro
     *
     * @dataProvider dataProviderArray
     * @covers PropertyHandler::getArray
     * @since 1.0.0
     */
    public function testGetArray($passedArray, $expectedArrayFree, $expectedArrayPro)
    {
        // Free
        $this->setIsPro(false);
        $returnedArray = $this->model->getArray($passedArray);
        $expected = $expectedArrayFree;
        $this->assertEquals($expected, $returnedArray);

        // PRO
        $this->setIsPro(true);
        $returnedArray = $this->model->getArray($passedArray);
        $expected = $expectedArrayPro;
        $this->assertEquals($expected, $returnedArray);
    }

    /**
     * @param string $varName
     * @param $passedValue
     * @param $expectedValueFree What we should get on the Free edition
     * @param $expectedValuePro We should get on the Pro edition
     *
     * @dataProvider dataProviderScalar
     * @covers PropertyHandler::get
     * @since 1.0.0
     */
    public function testGet($varName, $passedValue, $expectedValueFree, $expectedValuePro)
    {
        // Free
        $this->setIsPro(false);
        $returnedValue = $this->model->get($varName, $passedValue);
        $expectedValue = $expectedValueFree;
        $this->assertEquals($expectedValue, $returnedValue);

        // PRO
        $this->setIsPro(true);
        $returnedValue = $this->model->get($varName, $passedValue);
        $expectedValue = $expectedValuePro;
        $this->assertEquals($expectedValue, $returnedValue);
    }

    /**
     * Set the edition
     *
     * @param   bool  $isPro
     *
     * @return ComponentConfig
     * @since 1.0.0
     */
    protected function setIsPro(bool $isPro = false)
    {
        return $this->componentConfig->set('isPro', $isPro);
    }

    /**
     * @return array
     */
    public function dataProviderScalar()
    {
        /*
         * Each of the array elements represents a filter parameter
         * The 'actualValue' is what we pass as input and the 'expectedValueFree' what should return when we are on a free version and use a PRO allowed value.
         */
        return [
            [
                'paramName' => 'toggle_state',
                'actualValue' => 'collapsed',
                'expectedValueFree' => 'expanded',
                'expectedValuePro' => 'collapsed'
            ],
            [
                'paramName' => 'display',
                'actualValue' => 'list',
                'expectedValueFree' => 'links',
                'expectedValuePro' => 'list'
            ],
            [
                'paramName' => 'display',
                'actualValue' => 'radios',
                'expectedValueFree' => 'links',
                'expectedValuePro' => 'radios'
            ],
            [
                'paramName' => 'display',
                'actualValue' => 'checkboxes',
                'expectedValueFree' => 'checkboxes',
                'expectedValuePro' => 'checkboxes'
            ],
            [
                'paramName' => 'display',
                'actualValue' => 'buttons_single',
                'expectedValueFree' => 'links',
                'expectedValuePro' => 'buttons_single'
            ],
            [
                'paramName' => 'display',
                'actualValue' => 'buttons_multi',
                'expectedValueFree' => 'checkboxes',
                'expectedValuePro' => 'buttons_multi'
            ],
            // Try a value not set in the PropertyHandler::$paramsConfig. Should return the passed value unmodified.
            [
                'paramName' => 'display',
                'actualValue' => 'customDisplay',
                'expectedValueFree' => 'customDisplay',
                'expectedValuePro' => 'customDisplay'
            ],
            [
                'paramName' => 'scrollbar_after',
                'actualValue' => 100,
                'expectedValueFree' => '',
                'expectedValuePro' => 100
            ],
            [
                'paramName' => 'scrollbar_after',
                'actualValue' => 'aaa',
                'expectedValueFree' => '',
                // Change that when you sanitize data by data type
                'expectedValuePro' => 'aaa'
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderArray()
    {
        return [
            [
                [
                    'display' => 'radios',
                    'display' => 'buttons_single',
                    'display' =>  'checkboxes',
                    'scrollbar_after' => 100,
                    'myCustomParam' => 'myCustomValue'
                ],
                // Expected returned array for Free version
                [
                    'display' => 'links',
                    'display' => 'links',
                    'display' =>  'checkboxes',
                    'scrollbar_after' => '',
                    'myCustomParam' => 'myCustomValue'
                ],
                // Expected returned array for PRO version
                [
                    'display' => 'radios',
                    'display' => 'buttons_single',
                    'display' =>  'checkboxes',
                    'scrollbar_after' => 100,
                    'myCustomParam' => 'myCustomValue'
                ],
            ]
        ];
    }
}