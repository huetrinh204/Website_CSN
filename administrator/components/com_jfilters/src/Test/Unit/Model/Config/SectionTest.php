<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Config;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FieldFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Section;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SectionTest
 *
 * @covers Section
 * @package Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Config
 */
class SectionTest extends TestCase
{
    /**
     * @var Field|MySection
     */
    protected $section;

    /**
     * @var Field|MockObject
     */
    protected $fieldName;

    /**
     * @var MockObject
     */
    protected $fieldAddress;

    /**
     * SetUp the dependencies
     */
    protected function setUp() : void
    {
        parent::setUp();
        $fieldFactory = $this->createMock(FieldFactory::class);
        $this->fieldName = $this->createMock(Field::class, array('getName', 'setName', 'getValue', 'setValue'));
        $this->fieldAddress = $this->createMock(Field::class, array('getName', 'setName', 'getValue', 'setValue'));
        $fieldFactory->expects($this->at(0))->method('create')->willReturn($this->fieldName);
        $fieldFactory->expects($this->at(1))->method('create')->willReturn($this->fieldAddress);
        $this->section = new MySection($fieldFactory);
    }

    /**
     * @covers Section::setFields
     * @param $xmlElement
     * @dataProvider dataProvider
     */
    public function testSetFields($xmlElement)
    {
        $this->fieldName->expects($this->any())->method('getValue')->willReturn((string)$xmlElement->children['name']);
        $this->fieldName->expects($this->any())->method('getName')->willReturn('name');

        $this->fieldAddress->expects($this->any())->method('getValue')->willReturn((string)$xmlElement->children['address']);
        $this->fieldAddress->expects($this->any())->method('getName')->willReturn('address');

        $this->section->setFields($xmlElement);
        $this->assertInstanceOf(Field::class, $this->section->getName());
        $this->assertInstanceOf(Field::class, $this->section->getAddress());

        $this->assertEquals($this->section->getName()->getValue(), $this->fieldName->getValue());
        $this->assertEquals($this->section->getName()->getName(), $this->fieldName->getName());

        $this->assertEquals($this->section->getAddress()->getValue(), $this->fieldAddress->getValue());
        $this->assertEquals($this->section->getAddress()->getName(), $this->fieldAddress->getName());
    }

    public function dataProvider()
    {
        /*
         * The xml will have 2 nodes (name, address)
         * Each node will have a set of attributes
         * Relevant functions for the Section and the Field mock should exist
         * as those 2 use magic sets based on the nodes and their attributes
         */
        $xmlElement = new \SimpleXMLElement("<item></item>");
        $xmlElement->addChild('name', 'Sakis Terzis');
        $xmlElement->addChild('address', 'Big Avenue');

        return [
            ['xml' => $xmlElement]
        ];
    }
}
