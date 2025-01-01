<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as FilterConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Resolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\TypeResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\AttributesResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Request;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Input\Input;
use PHPUnit\Framework\TestCase;

/**
 * Class FilterTest
 *
 * @covers \Bluecoder\Component\Jfilters\Administrator\Model\Filter
 */
class FilterTest extends TestCase
{
    /**
     * @var Input
     * @since 1.0.0
     */
    protected $input;

    /**
     * @var Filter
     * @since 1.0.0
     */
    protected $model;

    /**
     * Setup basic vars
     * @since 1.0.0
     */
    protected function setUp(): void
    {
        $this->input = Factory::getApplication()->getInput();
        $request = new Request($this->input);
        $componentConfigMock = $this->createMock(ComponentConfig::class);
        $filtersXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/filters.xml');
        $configReader = new FiltersXMLConfigReader($componentConfigMock, $filtersXML);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $filterConfigCollection = new FilterConfigCollection($configReader, $loggerMock);

        $contextsXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/contexts.xml');
        $configReader = new ContextsXMLConfigReader($componentConfigMock, $contextsXML);
        $contextConfigCollection = new ContextConfigCollection($configReader, $loggerMock);
        $configResolver = new Resolver($filterConfigCollection, $contextConfigCollection);

        $typeResolverMock = $this->createMock(TypeResolver::class);
        $optionCollectionFactory = new Filter\Option\CollectionFactory();
        $loggerMock = $this->createMock(LoggerInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $attributeResolverMock = $this->createMock(AttributesResolver::class);

        $this->model = new Filter($request, $configResolver, $typeResolverMock,
            $optionCollectionFactory, $formFactory, $attributeResolverMock, $loggerMock);
    }

    /**
     * Test getRequest with a var in another extension
     *
     * @param $filterId
     * @param $type
     * @param $values
     * @throws \Exception
     * @since 1.0.0
     * @covers Filter::getRequest()
     */
    public function testGetRequest()
    {
        $filterId = 1;
        $this->model->setId($filterId);
        $this->model->setConfigName('category');
        $this->model->setName('filter' . $filterId);
        $this->model->setContext('com_content.article');
        $this->input->set('option', 'com_content');
        $this->input->set('view', 'category');
        $this->input->set('id', 1);
        $this->model->setDisplay('checkboxes');
        $this->assertEquals([1], $this->model->getRequest());
    }
}
