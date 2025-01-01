<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Resolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\TypeResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\AttributesResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Registry;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class FilterTest
 *
 * @covers \Bluecoder\Component\Jfilters\Administrator\Model\Filter
 */
class FilterTest extends TestCase
{
    /**
     * @var Field|MockObject
     */
    protected $fieldMock;

    /**
     * @var ValueInterface|MockObject
     * @since 1.0.0
     */
    protected $valueConfigSectionMock;

    /**
     * @var FilterInterface|MockObject
     * @since 1.0.0
     */
    protected $filterConfigMock;

    /**
     * @var \Joomla\Input\Input
     */
    protected $input;

    /**
     * @var Resolver|MockObject
     * @since 1.0.0
     */
    protected $configResolverMock;

    /**
     * @var Filter\Option\CollectionFactory
     * @since 1.0.0
     */
    protected $optionCollectionFactory;

    /**
     * @var Filter\Option\Collection|MockObject
     */
    protected $optionCollectionMock;

    /**
     * @var TypeResolver|MockObject
     */
    protected $typeResolverMock;

    /**
     * @var FormFactoryInterface|(FormFactoryInterface&object&MockObject)|(FormFactoryInterface&MockObject)|(object&MockObject)|MockObject
     */
    protected $formFactory;

    /**
     * @var Filter
     */
    protected $model;

    /**
     * Setup basic vars
     */
    protected function setUp(): void
    {
        $this->filterConfigMock = $this->createMock(FilterInterface::class);
        $this->input = Factory::getApplication()->getInput();
        $request = new Filter\Request($this->input);
        $this->configResolverMock = $this->createMock(Resolver::class);
        $this->configResolverMock->expects($this->any())->method('getFilterConfig')->willReturn($this->filterConfigMock);
        $this->optionCollectionFactory = $this->createMock(Filter\Option\CollectionFactory::class);
        $this->optionCollectionMock = $this->createMock(Filter\Option\Collection::class);

        $this->typeResolverMock = $this->createMock(TypeResolver::class);
        // The class of each option. If empty resolves to the default.
        $this->typeResolverMock->expects($this->any())->method('getTypeClass')->willReturn('');

        $loggerMock = $this->createMock(LoggerInterface::class);
        $form = new Form('com_jfilters.filter', ['control' => 'jform']);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->formFactory->expects($this->any())->method('createForm')->willReturn($form);
        $attributeResolverMock = $this->createMock(AttributesResolver::class);
        $this->model = new Filter($request, $this->configResolverMock, $this->typeResolverMock,
            $this->optionCollectionFactory, $this->formFactory, $attributeResolverMock, $loggerMock);
    }

    /**
     * @param $filterId
     * @param $type
     * @param $values
     * @throws \Exception
     * @since 1.0.0
     * @covers Filter::getRequest()
     * @dataProvider dataProvider
     */
    public function testGetRequest($filterId, $type, $values)
    {
        $this->model->setId($filterId);
        $this->model->setName('filter' . $filterId);
        $this->model->setAlias('filter' . $filterId);
        $this->model->setConfigName('');
        $this->input->set('filter' . $filterId, $values);

        $this->optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn($type);
        $this->model->setOptions($this->optionCollectionMock);
        $this->model->setDisplay('checkboxes');
        $this->assertEquals($values, $this->model->getRequest());
    }

    /**
     * @throws \Exception
     * @since 1.0.0
     * @covers Filter::getRequest()
     */
    public function testGetRequestFilteredString()
    {
        $filterId = 5;
        $values = ['sakis&#xA9;', 'nikos<;', 'money&#x3E;'];
        $expected_values = ['sakis©', 'nikos', 'money>'];
        $this->model->setId($filterId);
        $this->model->setName('filter' . $filterId);
        $this->model->setAlias('filter' . $filterId);
        $this->model->setConfigName('');
        $this->input->set('filter' . $filterId, $values);
        // We do not set the type, as happens in the other functions. This should reset to "string" type.
        $this->optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn('');
        $this->model->setOptions($this->optionCollectionMock);
        $this->model->setDisplay('checkboxes');
        $this->assertEquals($expected_values, $this->model->getRequest());
    }

    /**
     * @throws \Exception
     * @since 1.0.0
     * @covers Filter::getRequest()
     */
    public function testGetRequestFilteredInteger()
    {
        $filterId = 5;
        $type = 'INT';
        $values = ['10a', 'abc20', 'xx15x'];
        $expected_values = ['10', '20', '15'];
        $this->model->setId($filterId);
        $this->model->setName('filter' . $filterId);
        $this->model->setAlias('filter' . $filterId);
        $this->model->setConfigName('');
        $this->input->set('filter' . $filterId, $values);

        $this->optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn($type);
        $this->model->setOptions($this->optionCollectionMock);
        $this->model->setDisplay('checkboxes');
        $this->assertEquals($expected_values, $this->model->getRequest());
    }

    /**
     * Cmd allows [A-Z0-9_\.-]/i
     *
     * @throws \Exception
     * @since 1.0.0
     * @covers Filter::getRequest()
     */
    public function testGetRequestFilteredCmd()
    {
        $filterId = 5;
        $type = 'cmd';
        $values = ['10 a #', '^50**', '_abc d'];
        $expected_values = ['10a', '50', '_abcd'];
        $this->model->setId($filterId);
        $this->model->setName('filter' . $filterId);
        $this->model->setAlias('filter' . $filterId);
        $this->model->setConfigName('');
        $this->input->set('filter' . $filterId, $values);
        $this->optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn($type);
        $this->model->setOptions($this->optionCollectionMock);
        $this->model->setDisplay('checkboxes');
        $this->assertEquals($expected_values, $this->model->getRequest());
    }

    /**
     * Test invalid dates.
     * The request should ignore them.
     *
     * @throws \Exception
     * @since 1.0.0
     * @covers Filter::getRequest()
     */
    public function testGetRequestDatesInvalid()
    {
        $filterId = 5;
        $type = 'date';
        $values = ['blabla', 'abc20', 'xx15x'];
        $expected_values = [];
        $this->model->setId($filterId);
        $this->model->setName('filter' . $filterId);
        $this->model->setAlias('filter' . $filterId);
        $this->model->setConfigName('');
        $this->input->set('filter' . $filterId, $values);

        $this->optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn($type);
        $this->model->setOptions($this->optionCollectionMock);
        $this->model->setDisplay('calendar');
        $this->assertEquals($expected_values, $this->model->getRequest());
    }

    /**
     * Test valid and invalid dates (show time = disabled)
     * Should only return 1 date (without time), since it is not range
     *
     * @throws \Exception
     * @since 1.0.0
     * @covers Filter::getRequest()
     */
    public function testGetRequestDates()
    {
        $filterId = 5;
        $type = 'date';
        $values = ['1970-10-23', '2015-6-30 23:55:40', 'xx15x'];
        $expected_values = ['1970-10-23', '2015-06-30'];
        $this->model->setId($filterId);
        $this->model->setName('filter' . $filterId);
        $this->model->setAlias('filter' . $filterId);
        $this->model->setConfigName('');
        $this->input->set('filter' . $filterId, $values);
        $attributes = new Registry(['show_time'=> 0]);
        $this->optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn($type);
        $this->model->setOptions($this->optionCollectionMock);
        $this->model->setDisplay('calendar');
        $this->model->setAttributes($attributes);
        $this->assertEquals($expected_values, $this->model->getRequest());
    }

    /**
     * Range dates should include 2 dates in the request.
     * More values than these should be ignored
     *
     * @throws \Exception
     * @since 1.0.0
     * @covers Filter::getRequest()
     */
    public function testGetRequestDatesRange()
    {
        $filterId = 5;
        $type = 'date';
        $values = ['1970-10-23', '2015/6/30 23:55:40', '1982/06/14'];
        $expected_values = ['1970-10-23', '2015-06-30'];
        $this->model->setId($filterId);
        $this->model->setName('filter' . $filterId);
        $this->model->setAlias('filter' . $filterId);
        $this->model->setConfigName('');
        $this->input->set('filter' . $filterId, $values);
        $attributes = new Registry(['show_time'=> 0, 'calendar_mode' => 'range']);
        $this->optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn($type);
        $this->model->setOptions($this->optionCollectionMock);
        $this->model->setDisplay('calendar');
        $this->model->setAttributes($attributes);
        $this->assertEquals($expected_values, $this->model->getRequest());
    }

    /**
     * @since 1.0.0
     * @covers Filter::getIsMultiSelect()
     */
    public function testGetIsMultiSelect()
    {
        $this->model->setDisplay('checkboxes');
        $this->assertEquals(true, $this->model->getIsMultiSelect());

        $this->model->setDisplay('a');
        $this->assertEquals(false, $this->model->getIsMultiSelect());
    }

    /**
     * @since 1.0.0
     * @covers Filter::getAttributes()
     * @dataProvider dataProviderAttributes
     */
    public function testGetAttributes($attributes, $attributeNamesToBeReturned, $registryObjectToBeReturned)
    {
        $this->model->setAttributes($attributes);
        $this->assertEquals($registryObjectToBeReturned, $this->model->getAttributes($attributeNamesToBeReturned));
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
                'id' => 1,
                'type' => 'INT',
                'filter1' => [1, 2, 3]
            ],
            [
                'id' => 2,
                'type' => 'INT',
                'filter2' => [3, 4]
            ],
            [
                'id' => 3,
                'type' => 'STRING',
                'filter2' => ['sakis', 'lakis']
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderAttributes()
    {
        $attributes = ['fName' => 'MyName', 'lName' => 'A last name', 'age' => 100];
        $attributesToGet = ['fName', 'lName'];
        $attributesToReturn = [];
        foreach ($attributes as $key => $attributeValue) {
            if (in_array($key, $attributesToGet)) {
                $attributesToReturn[$key] = $attributeValue;
            }
        }
        $attributesToReturn['isPro'] = null;

        /*
         * 1st element the attributes of the filters to be set.
         * 2nd the attribute names to be returned
         * 3rd the actual object to be returned
         */
        return [
            [
                'attributes' => new Registry($attributes),
                'attributesToGet' => $attributesToGet,
                'attributesToReturn' => new Registry($attributesToReturn),
            ],
            [
                'attributes' => new Registry($attributes),
                'attributesToGet' => [],
                'attributesToReturn' => new Registry($attributes),
            ],
        ];
    }
}
