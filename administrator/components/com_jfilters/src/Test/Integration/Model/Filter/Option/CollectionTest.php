<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as FilterConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Resolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection as OptionCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Registry;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Bluecoder\Component\Jfilters\Administrator\Test\Integration\DbHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionTest
 * @covers \Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection
 */
class CollectionTest extends TestCase
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
     * @var Filtered
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
        $loggerMock = $this->createMock(LoggerInterface::class);

        /** @var  ComponentConfig $componentConfig */
        $this->componentConfig = ObjectManager::getInstance()->getObject(ComponentConfig::class);
        $this->componentConfig->set('max_option_label_length', 55);
        $this->componentConfig->set('max_option_value_length', 35);
        $contextsXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/contexts.xml');
        $configReader = new ContextsXMLConfigReader($this->componentConfig, $contextsXML);
        $contextConfigCollection = new ContextConfigCollection($configReader, $loggerMock);
        $filter = $this->getFilter();
        $this->filterAttributes = new Registry(['options_sort_by' => 'label', 'options_sort_direction' => 'ASC']);
        $filter->expects($this->any())->method('getAttributes')->willReturn($this->filterAttributes);
        $dynamicFiltersConfigCollection = new DynamicFilterCollection(null, $loggerMock);
        $db = self::$databaseHelper->getDbo();
        $this->model = new OptionCollection($db, $contextConfigCollection, $dynamicFiltersConfigCollection, $loggerMock);
        $this->model->setFilterItem($filter);
    }

    /**
     * Test the returned items based on the set ordering (counter ASC)
     *
     * Do note: Sort by counter sorts 1st by counter and 2nd by label
     *
     * @since 1.3.0
     */
    public function testGetItemsSortByCountAsc()
    {
        $this->filterAttributes->set('options_sort_by', 'count');
        $this->filterAttributes->set('options_sort_direction', 'ASC');
        $items = $this->model->getItems();

        // The values should be sorted by counter and label
        $this->assertEquals('100', $items[0]->getLabel());
        $this->assertEquals('2', $items[1]->getLabel());
        $this->assertEquals('3', $items[2]->getLabel());
        $this->assertEquals('Aba', $items[3]->getLabel());
        $this->assertEquals('Abé', $items[4]->getLabel());
        $this->assertEquals('Baba', $items[5]->getLabel());
        $this->assertEquals('Cava', $items[6]->getLabel());
        $this->assertEquals('βάκης', $items[7]->getLabel());
        $this->assertEquals('Λάκης', $items[8]->getLabel());
        $this->assertEquals('Βάκος', $items[9]->getLabel());
        $this->assertEquals('Τάκης', $items[10]->getLabel());
    }

    /**
     * Test the returned items based on the set ordering (counter DESC)
     *
     * Do note: Sort by counter sorts 1st by counter and 2nd by label
     *
     * @since 1.3.0
     */
    public function testGetItemsSortByCountDesc()
    {
        $this->filterAttributes->set('options_sort_by', 'count');
        $this->filterAttributes->set('options_sort_direction', 'DESC');
        $items = $this->model->getItems();

        // The values should be sorted by counter and label
        $this->assertEquals('Βάκος', $items[0]->getLabel());
        $this->assertEquals('Τάκης', $items[1]->getLabel());
        $this->assertEquals('100', $items[2]->getLabel());
        $this->assertEquals('2', $items[3]->getLabel());
        $this->assertEquals('3', $items[4]->getLabel());
        $this->assertEquals('Aba', $items[5]->getLabel());
        $this->assertEquals('Abé', $items[6]->getLabel());
        $this->assertEquals('Baba', $items[7]->getLabel());
        $this->assertEquals('Cava', $items[8]->getLabel());
        $this->assertEquals('βάκης', $items[9]->getLabel());
        $this->assertEquals('Λάκης', $items[10]->getLabel());
    }

    /**
     * Test the returned items based on the set ordering (label ASC)
     *
     * @since 1.3.0
     */
    public function testGetItemsSortByLabelAsc()
    {
        $this->filterAttributes->set('options_sort_by', 'label');
        $this->filterAttributes->set('options_sort_direction', 'ASC');
        $items = $this->model->getItems();

        // The values should be sorted by their label. Numbers are always 1st
        $this->assertEquals('2', $items[0]->getLabel());
        $this->assertEquals('3', $items[1]->getLabel());
        $this->assertEquals('100', $items[2]->getLabel());
        $this->assertEquals('Aba', $items[3]->getLabel());
        $this->assertEquals('Abé', $items[4]->getLabel());
        $this->assertEquals('Baba', $items[5]->getLabel());
        $this->assertEquals('Cava', $items[6]->getLabel());
        $this->assertEquals('βάκης', $items[7]->getLabel());
        $this->assertEquals('Βάκος', $items[8]->getLabel());
        $this->assertEquals('Λάκης', $items[9]->getLabel());
        $this->assertEquals('Τάκης', $items[10]->getLabel());
    }

    /**
     * Test the returned items based on the set ordering (label DESC)
     *
     * @since 1.3.0
     */
    public function testGetItemsSortByLabelDesc()
    {
        $this->filterAttributes->set('options_sort_by', 'label');
        $this->filterAttributes->set('options_sort_direction', 'DESC');
        $items = $this->model->getItems();

        // The values should be sorted by their label DESC. Numbers are always last
        $this->assertEquals('Τάκης', $items[0]->getLabel());
        $this->assertEquals('Λάκης', $items[1]->getLabel());
        $this->assertEquals('Βάκος', $items[2]->getLabel());
        $this->assertEquals('βάκης', $items[3]->getLabel());
        $this->assertEquals('Cava', $items[4]->getLabel());
        $this->assertEquals('Baba', $items[5]->getLabel());
        $this->assertEquals('Abé', $items[6]->getLabel());
        $this->assertEquals('Aba', $items[7]->getLabel());
        $this->assertEquals('100', $items[8]->getLabel());
        $this->assertEquals('3', $items[9]->getLabel());
        $this->assertEquals('2', $items[10]->getLabel());
    }

    /**
     * Generate a filter
     *
     * @return FilterInterface|\PHPUnit\Framework\MockObject\MockObject
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

        $filterItemMock = $this->createMock(FilterInterface::class);
        $filterItemMock->expects($this->any())->method('getId')->willReturn(5);
        $filterItemMock->expects($this->any())->method('getParentId')->willReturn(4);
        $filterItemMock->expects($this->any())->method('getContext')->willReturn('com_content.article');
        $filterItemMock->expects($this->any())->method('getConfigName')->willReturn('fields');
        $filterItemMock->expects($this->any())->method('getConfig')->willReturn($configResolver->getFilterConfig($filterItemMock));

        return $filterItemMock;
    }

    public static function tearDownAfterClass(): void
    {
        self::$databaseHelper->dropTables();
    }
}