<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Field\DisplaytypesField;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilterInterface;
use Joomla\CMS\Form\Form;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


class DisplaytypesFieldTest extends TestCase
{
    /**
     * @var \SimpleXMLElement
     * @since 1.0.0
     */
    protected $displayFieldXML;

    /**
     * @var DynamicFilterInterface|MockObject
     * @since 1.0.0
     */
    protected $dynamicFilterMock;

    /**
     * @var DisplaytypesField
     * @since 1.0.0
     */
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();

        $displayFieldXMLFile = JPATH_COMPONENT_ADMINISTRATOR . '/src/Test/Unit/config/form_filter.xml';
        $this->displayFieldXML = simplexml_load_file($displayFieldXMLFile);
        $this->dynamicFilterMock = $this->createMock(DynamicFilterInterface::class);
        $form = new Form('com_jfilters.filter', ['control' => 'jform']);
        $this->model = new DisplaytypesField($form);
        $this->model->setFilter($this->dynamicFilterMock);

    }

    /**
     * @covers DisplaytypesField::isMultiSelect()
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function testIsMultiSelect()
    {
        $display = 'color_button_multi';
        $this->dynamicFilterMock->expects($this->any())->method('getDisplay')->willReturn($display);
        $this->dynamicFilterMock->expects($this->any())->method('getType')->willReturn('color');
        $this->model->setup($this->displayFieldXML, $display);
        $this->assertEquals(true, $this->model->isMultiSelect());
    }

    /**
     * @covers DisplaytypesField::getOptions()
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function testGetOptions()
    {
        $display = 'color_button_multi';
        $this->dynamicFilterMock->expects($this->any())->method('getDisplay')->willReturn($display);
        $this->dynamicFilterMock->expects($this->any())->method('getType')->willReturn('color');
        $this->model->setup($this->displayFieldXML, $display);
        $options = $this->model->getOptions();
        $this->assertCount(3, $options);
        // test also the option values
        $this->assertEquals('links', $options[0]->value);
        $this->assertEquals('color_button', $options[1]->value);
        $this->assertEquals('color_button_multi', $options[2]->value);
    }

    /**
     * @covers DisplaytypesField::isMultiSelect()
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function testIsMultiSelectNoType()
    {
        $display = 'links';
        $this->dynamicFilterMock->expects($this->any())->method('getDisplay')->willReturn($display);
        $this->model->setup($this->displayFieldXML, $display);
        $this->assertEquals(false, $this->model->isMultiSelect());
    }

    /**
     * @covers DisplaytypesField::isMultiSelect()
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function testIsMultiSelectNativeType()
    {
        $display = 'checkboxes';
        $this->dynamicFilterMock->expects($this->any())->method('getDisplay')->willReturn($display);
        $this->model->setup($this->displayFieldXML, $display);
        $this->assertEquals(true, $this->model->isMultiSelect());
    }
}
