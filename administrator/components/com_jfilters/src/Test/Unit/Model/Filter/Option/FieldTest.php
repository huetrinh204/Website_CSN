<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilter\FieldsFilter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandlerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\Registry\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    /**
     * @var Registry
     * @since 1.0.0
     */
    protected $params;

    /**
     * @var FieldsFilter|MockObject
     * @since 1.0.0
     */
    protected $filterMock;

    /**
     * @var ComponentConfig
     */
    protected $componentConfig;

    /**
     * @var Field
     * @since 1.0.0
     */
    protected $model;

    protected function setUp(): void
    {
        $uriHandlerInterfaceMock = $this->createMock(UriHandlerInterface::class);
        $this->params = new Registry(
            [
                'options' =>
                    [
                        ['value' => 'sakiTer', 'name' => 'Sakis Terzis'],
                        ['value' => 'S', 'name' => 'Science'],
                        ['value' => 'ABC', 'name' => 'xavara katranemia ileo ileo nama nama nama']
                    ]
            ]
        );
        $this->filterMock = $this->createMock(FieldsFilter::class);
        $this->filterMock->expects($this->any())->method('getParams')->willReturn($this->params);
        $dynamicConfigCollectionMock = $this->createMock(DynamicConfigCollection::class);
        /** @var  ComponentConfig $componentConfig */
        $this->componentConfig = ObjectManager::getInstance()->getObject(ComponentConfig::class);
        $this->componentConfig->set('max_option_label_length', 15);
        $this->componentConfig->set('max_option_value_length', 15);
        $this->model = new Field($uriHandlerInterfaceMock, $this->componentConfig, $dynamicConfigCollectionMock);
        $this->model->setParentFilter($this->filterMock);
    }

    /**
     * The function should set the option's label from the params (if exist)
     *
     * @covers Field::getLabel
     *
     * @since 1.0.0
     */
    public function testGetLabel()
    {
        $paramOptions = $this->params->get('options');
        $this->model->setValue($paramOptions[0]['value']);
        $maxStrLength = $this->componentConfig->get('max_option_label_length');
        $expectedLabel = $paramOptions[0]['name'];

        // Test also the stripped ones
        if(mb_strlen($paramOptions[0]['name']) > $maxStrLength) {
            $expectedLabel = $this->stripString($expectedLabel, $maxStrLength);
            $expectedLabel.='...';
        }
        // this should be overwritten
        $this->model->setLabel('Hahaha');
        $this->assertEquals($expectedLabel, $this->model->getLabel());
    }

    protected function stripString($string,$maxStrLength)
    {
        $strLength = mb_strlen($string);
        if(!is_numeric($string) && $strLength > $maxStrLength) {
            $string = mb_substr($string, 0, -1*($strLength-$maxStrLength));
        }
        return $string;
    }

    /**
     * The function should set the option's label from the params (if exist).
     * If does not exist, keep the label.
     *
     * @covers Field::getLabel
     * @since 1.0.0
     */
    public function testGetLabelNoParamFound()
    {
        $optionValue = 'Something I like';
        $expectedLabel = $optionValue;
        $expectedValue = $optionValue;
        $maxStrLength = $this->componentConfig->get('max_option_label_length');
        // Test also the stripped ones
        if(mb_strlen($optionValue) > $maxStrLength) {
            $expectedLabel = $this->stripString($optionValue, $maxStrLength);
            $expectedValue = $expectedLabel;
            $expectedLabel.='...';
        }
        $this->model->setValue($optionValue);
        $this->model->setLabel($optionValue);
        $this->assertEquals($expectedLabel, $this->model->getLabel());
        $this->assertEquals($expectedValue, $this->model->getValue());
    }
}
