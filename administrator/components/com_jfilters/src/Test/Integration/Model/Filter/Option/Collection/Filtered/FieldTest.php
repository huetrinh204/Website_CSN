<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model\Filter\Option\Collection\Filtered;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as FilterConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Resolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilter\FieldsFilter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered\Field as OptionFieldCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Registry;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Bluecoder\Component\Jfilters\Administrator\Test\Integration\DbHelper;
use Joomla\CMS\Application\CMSApplication;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionTest
 * @covers \Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered\Field
 */
class FieldTest extends TestCase
{

    /**
     * @var DbHelper
     * @since 1.3.0
     */
    protected static $databaseHelper;

    /**
     * @var ComponentConfig
     */
    protected $componentConfig;

    /**
     * @var Registry
     */
    protected $filterAttributes;

    /**
     * @var OptionFieldCollection
     * @since 1.3.0
     */
    protected $model;

    public static function setUpBeforeClass(): void
    {
        self::$databaseHelper = new DbHelper();
        self::$databaseHelper->dropTables();
        self::$databaseHelper->createTables();
        //insert data
        $dataFile = 'data1.sql';
        self::$databaseHelper->executeSqlFile($dataFile);
    }

    protected function setUp(): void
    {
        parent::setUp();
        /*
         * We need that.
         * Otherwise we get an exception from the application, when trying to parse the apps uri (http://localhost/someFolder/phpunit/phpunit/phpunit)
         */
        $_SERVER['SCRIPT_NAME'] = 'http://localhost';
        $loggerMock = $this->createMock(LoggerInterface::class);

        /** @var  ComponentConfig $componentConfig */
        $this->componentConfig = ObjectManager::getInstance()->getObject(ComponentConfig::class);
        $this->componentConfig->set('max_option_label_length', 55);
        $this->componentConfig->set('max_option_value_length', 35);
        $contextsXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/contexts.xml');
        $configReaderContexts = new ContextsXMLConfigReader($this->componentConfig, $contextsXML);
        $contextConfigCollection = new ContextConfigCollection($configReaderContexts, $loggerMock);
        $filter = $this->getFilter();
        $this->filterAttributes = new Registry(['options_sort_by' => 'label', 'options_sort_direction' => 'ASC']);
        $filter->expects($this->any())->method('getAttributes')->willReturn($this->filterAttributes);
        $filtersCollection = ObjectManager::getInstance()->getObject(FilterCollection::class);
        $applicationMock = $this->createMock(CMSApplication::class);
        $db = self::$databaseHelper->getDbo();

        $dynamicFiltersConfigCollection = new DynamicFilterCollection(null, $loggerMock);

        $this->model = new OptionFieldCollection($db, $contextConfigCollection, $dynamicFiltersConfigCollection, $loggerMock, $filtersCollection, $applicationMock, $this->componentConfig);
        $this->model->setFilterItem($filter);
    }

    /**
     * Test the returned items based on the set ordering (ordering ASC)
     *
     * @since 1.3.0
     */
    public function testGetItemsSortByOrderingAsc()
    {
        $this->filterAttributes->set('options_sort_by', 'ordering');
        $this->filterAttributes->set('options_sort_direction', 'ASC');
        // Do not cross-filter with the other filters at this test
        $this->model->setUseOtherSelectionsAsConditions(false);
        $items = $this->model->getItems();

        // The values should be sorted by the defined order see line: 134
        $this->assertEquals('Aba', $items[0]->getLabel());
        $this->assertEquals('Baba', $items[1]->getLabel());
        $this->assertEquals('Cava', $items[2]->getLabel());
        $this->assertEquals('2', $items[3]->getLabel());
        $this->assertEquals('3', $items[4]->getLabel());
        $this->assertEquals('100', $items[5]->getLabel());
        $this->assertEquals('Λάκης', $items[6]->getLabel());
        $this->assertEquals('βάκης', $items[7]->getLabel());
        $this->assertEquals('Τάκης', $items[8]->getLabel());
        $this->assertEquals('Βάκος', $items[9]->getLabel());
        $this->assertEquals('Abé', $items[10]->getLabel());
    }

    /**
     * Generate a filter
     *
     * @return FieldsFilter|\PHPUnit\Framework\MockObject\MockObject
     * @since 1.3.0
     */
    public function getFilter()
    {
        $filtersXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/filters.xml');
        $configReader = new FiltersXMLConfigReader($this->componentConfig, $filtersXML);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $filterConfigCollection = new FilterConfigCollection($configReader, $loggerMock);

        $contextsXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/contexts.xml');
        $configReader = new ContextsXMLConfigReader($this->componentConfig, $contextsXML);
        $contextConfigCollection = new ContextConfigCollection($configReader, $loggerMock);
        $configResolver = new Resolver($filterConfigCollection, $contextConfigCollection);

        $fieldParams = new \Joomla\Registry\Registry('{"options":{"options0":{"name":"Aba","value":"Aba"},"options1":{"name":"Baba","value":"Baba"},"options2":{"name":"Cava","value":"Cava"},"options3":{"name":"2","value":"2"},"options4":{"name":"3","value":"3"},"options5":{"name":"100","value":"100"}, "options6":{"name":"Λάκης","value":"Λάκης"},"options7":{"name":"βάκης","value":"βάκης"}, "options8":{"name":"Τάκης","value":"Τάκης"}, "options9":{"name":"Βάκος","value":"Βάκος"}, "options10":{"name":"Abé","value":"Abé"}}}');

        $filterItemMock = $this->createMock(FieldsFilter::class);
        $filterItemMock->expects($this->any())->method('getId')->willReturn(5);
        $filterItemMock->expects($this->any())->method('getParentId')->willReturn(4);
        $filterItemMock->expects($this->any())->method('getContext')->willReturn('com_content.article');
        $filterItemMock->expects($this->any())->method('getConfigName')->willReturn('fields');
        $filterItemMock->expects($this->any())->method('getConfig')->willReturn($configResolver->getFilterConfig($filterItemMock));
        $filterItemMock->expects($this->any())->method('getParams')->willReturn($fieldParams);

        return $filterItemMock;
    }

    public static function tearDownAfterClass(): void
    {
        self::$databaseHelper->dropTables();
    }
}