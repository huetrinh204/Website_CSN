<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Resolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\TypeResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\AttributesResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\CollectionFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Request;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Registry\Registry;
use PHPUnit\Framework\TestCase;

class DynamicFilterTest extends TestCase
{
    protected $fieldMock;

    protected $valueConfigSectionMock;

    protected $filterConfigMock;

    /**
     * @var \Joomla\Input\Input
     * @since 1.16.0
     */
    protected $input;

    protected $configResolverMock;

    protected $optionCollectioFactorynMock;

    protected $typeResolverMock;

    protected $formFactory;

    /**
     * @var DynamicFilter
     */
    protected $model;

    /**
     * Setup basic vars
     */
    protected function setUp(): void
    {
        $this->fieldMock = $this->createMock(Field::class);
        $this->valueConfigSectionMock = $this->createMock(ValueInterface::class);
        $this->valueConfigSectionMock->expects($this->any())->method('getValue')->willReturn($this->fieldMock);
        $this->filterConfigMock = $this->createMock(FilterInterface::class);
        $this->filterConfigMock->expects($this->any())->method('getValue')->willReturn($this->valueConfigSectionMock);
        $this->input = Factory::getApplication()->getInput();
        $request = new Request($this->input);
        $this->configResolverMock = $this->createMock(Resolver::class);
        $this->configResolverMock->expects($this->any())->method('getFilterConfig')->willReturn($this->filterConfigMock);
        $this->optionCollectioFactorynMock = $this->createMock(CollectionFactory::class);
        $this->typeResolverMock = $this->createMock(TypeResolver::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $form = new Form('com_jfilters.filter', ['control' => 'jform']);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->formFactory->expects($this->any())->method('createForm')->willReturn($form);
        $attributeResolverMock = $this->createMock(AttributesResolver::class);
        $this->model = new DynamicFilter($request, $this->configResolverMock, $this->typeResolverMock,
            $this->optionCollectioFactorynMock, $this->formFactory, $attributeResolverMock, $loggerMock);
    }

    /**
     * @covers DynamicFilter::getType
     * @since 1.0.0
     */
    public function testGetType()
    {
        $params = new Registry(['type' => 'color']);
        $this->model->setAttributes($params);
        $this->assertEquals($params->get('type'), $this->model->getType());
    }

    /**
     * @covers DynamicFilter::getType
     * @since 1.0.0
     */
    public function testGetNullType()
    {
        $this->assertEquals(null, $this->model->getType());
    }
}
