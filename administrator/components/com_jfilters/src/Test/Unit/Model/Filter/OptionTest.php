<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandlerInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    /**
     * @var FilterInterface|MockObject
     * @since 1.0.0
     */
    protected $filterMock;

    /**
     * @var int
     * @since 1.0.0
     */
    protected int $maxCharLenth = 10;

    /**
     * @var ComponentConfig
     * @since 1.0.0
     */
    protected $componentConfig;

    /**
     * @var Option
     * @since 1.0.0
     */
    protected $model;

    /**
     * Setup basic vars
     */
    protected function setUp(): void
    {
        $this->filterMock = $this->createMock(FilterInterface::class);
        $routeMock = $this->createMock(UriHandlerInterface::class);
        /** @var  ComponentConfig $componentConfig */
        $this->componentConfig = ObjectManager::getInstance()->getObject(ComponentConfig::class);
        $this->componentConfig->set('max_option_label_length', $this->maxCharLenth);
        $this->componentConfig->set('max_option_value_length', $this->maxCharLenth);
        $this->model = new Option($routeMock, $this->componentConfig);
        $this->model->setParentFilter($this->filterMock);
    }

    /**
     * @covers Option::isSelected
     * @since 1.0.0
     */
    public function testIsSelectedTrue()
    {
        $this->filterMock->expects($this->any())->method('getRequest')->willReturn(['a', 'b', 'c']);
        $this->model->setValue('a');
        $this->assertEquals(true, $this->model->isSelected());
    }

    /**
     * @covers Option::isSelected
     * @since 1.0.0
     */
    public function testIsSelectedFalse()
    {
        $this->filterMock->expects($this->any())->method('getRequest')->willReturn(['a', 'b', 'c']);
        $this->model->setValue('d');
        $this->assertEquals(false, $this->model->isSelected());
    }

    /**
     * @covers Option::isSelected
     * @since 1.0.0
     */
    public function testIsSelectedCamelCase()
    {
        $this->filterMock->expects($this->any())->method('getRequest')->willReturn(['a', 'BcD', 'c']);
        $this->model->setValue('bCD');
        $this->assertEquals(true, $this->model->isSelected());
    }

    /**
     * Test string stripping after a certain length.
     *
     * @covers Option::setLabel
     * @since 1.0.0
     */
    public function testSetLabelWillBeStripped()
    {
        $value = 'xavara katranemia ileo ileo nama nama nama nama nama|xavara katranemia ileo ileo nama nama nama nama nama';
        $expected = mb_substr($value, 0, -1 * (mb_strlen($value) - $this->maxCharLenth));
        // We add 3 dots at the end of the stripped labels
        $expected .='...';
        $this->model->setLabel($value);
        $this->assertEquals($expected, $this->model->getLabel());
    }

    /**
     * Test string stripping after a certain length.
     *
     * @covers Option::setValue
     * @since 1.0.0
     */
    public function testSetValueWillBeStripped()
    {
        $value = 'xavara katranemia ileo ileo nama nama nama nama nama|xavara katranemia ileo ileo nama nama nama nama nama';
        $expected = mb_substr($value, 0, -1 * (mb_strlen($value) - $this->maxCharLenth));
        $this->model->setValue($value);
        $this->assertEquals($expected, $this->model->getValue());
    }
}
