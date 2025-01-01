<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model\Filter\Option\Collection\Filtered;

// Used to print the collection's db query to string
define('JFTESTDEBUG', true);

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as FilterConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Resolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered\Nested;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\CollectionFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Registry;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Bluecoder\Component\Jfilters\Administrator\Test\Integration\DbHelper;
use Joomla\CMS\Application\CMSApplication;
use PHPUnit\Framework\TestCase;

class NestedTest extends TestCase
{
    /**
     * @var DbHelper
     * @since 1.0.0
     */
    protected static $databaseHelper;

    /**
     * @var Nested
     * @since 1.0.0
     */
    protected $model;

    public static function setUpBeforeClass() : void
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
        $db = self::$databaseHelper->getDbo();
        $componentConfigMock = $this->createMock(ComponentConfig::class);
        // create the contexts config
        $contextsXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/contexts.xml');
        $configReader = new ContextsXMLConfigReader($componentConfigMock, $contextsXML);
        $contextConfigCollection = new ContextConfigCollection($configReader, $loggerMock);

        $filtersCollection = $this->createMock(FilterCollection::class);
        $collectionFactory = ObjectManager::getInstance()->getObject(CollectionFactory::class);
        $applicationMock = $this->createMock(CMSApplication::class);
        $dynamicFiltersConfigCollection = new DynamicFilterCollection(null, $loggerMock);
        $this->model = new Nested($db, $contextConfigCollection, $dynamicFiltersConfigCollection, $loggerMock, $filtersCollection, $applicationMock, $componentConfigMock, $collectionFactory);

        // create the filters config
        $filtersXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/filters.xml');
        $configReader = new FiltersXMLConfigReader($componentConfigMock, $filtersXML);
        $filterConfigCollection = new FilterConfigCollection($configReader, $loggerMock);
        $configResolver = new Resolver($filterConfigCollection, $contextConfigCollection);
        $attributes = new Registry();

        $filterItemMock = $this->createMock(FilterInterface::class);
        $filterItemMock->expects($this->any())->method('getRoot')->willReturn(true);
        $filterItemMock->expects($this->any())->method('getParentId')->willReturn(1);
        $filterItemMock->expects($this->any())->method('getContext')->willReturn('com_content.article');
        $filterItemMock->expects($this->any())->method('getConfigName')->willReturn('category');
        $filterItemMock->expects($this->any())->method('getConfig')->willReturn($configResolver->getFilterConfig($filterItemMock));
        $filterItemMock->expects($this->any())->method('getAttributes')->willReturn($attributes);

        $this->model->setFilterItem($filterItemMock);
    }

    public function testGetItems()
    {
        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();

        // Count checks if the nesting is done properly and if unpublished categories are included.
        $this->assertCount(3, $items);

        // check the expected values from our data file.
        $this->assertEquals('Programming', $items[0]->getLabel());
        $this->assertEquals('Manufacturing', $items[1]->getLabel());

        // This is a parent category without articles assigned. Tests a former bug (not loading it).
        $this->assertEquals('Parent', $items[2]->getLabel());
    }

    public function testGetItemsTestChildrenWithCounter()
    {
        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        // check the expected values from our data file.
        $this->assertEquals(null, $items[1]->getParentOption());
        /**
         * Direct children of 'Programming'
         * @var OptionInterface[] $children
         */
        $children = $items[0]->getChildren()->getItems();
        // test with label (title) and value (id)
        $this->assertEquals('PHP', $children[0]->getLabel());
        $this->assertEquals('9', $children[0]->getValue());

        /**
         * Direct children of 'PHP'
         * @var OptionInterface[] $children2
         */
        $children2 = $children[0]->getChildren()->getItems();

        // test with label (title) and value (id)
        $this->assertEquals('PHP compile', $children2[0]->getLabel());
        $this->assertEquals('10', $children2[0]->getValue());
        $this->assertEquals(1, $children2[0]->getCount());
    }

    public function testGetItemsTestChildrenWithEmptyParent()
    {
        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        // check the expected values from our data file.
        $this->assertEquals(null, $items[2]->getParentOption());
        /**
         * Direct children of 'Programming'
         * @var OptionInterface[] $children
         */
        $children = $items[2]->getChildren()->getItems();
        // test with label (title) and value (id)
        $this->assertEquals('child', $children[0]->getLabel());
        $this->assertEquals(15, $children[0]->getValue());
        $this->assertEquals(1, $children[0]->getCount());
    }

    public function testGetItemsTestChildrenWithoutCounter()
    {
        /** @var OptionInterface[] $items */
        $this->model->setJoinItemValueTable(false);
        $items = $this->model->getItems();
        // check the expected values from our data file.
        $this->assertEquals(null, $items[1]->getParentOption());
        /**
         * Direct children of 'Programming'
         * @var OptionInterface[] $children
         */
        $children = $items[2]->getChildren()->getItems();
        // test with label (title) and value (id)
        $this->assertEquals('PHP', $children[0]->getLabel());
        $this->assertEquals('9', $children[0]->getValue());

        /**
         * Direct children of 'PHP'
         * @var OptionInterface[] $children2
         */
        $children2 = $children[0]->getChildren()->getItems();

        // test with label (title) and value (id)
        $this->assertEquals('PHP compile', $children2[0]->getLabel());
        $this->assertEquals('10', $children2[0]->getValue());

        // test with label (title) and value (id)
        $this->assertEquals('Php Type hinting', $children2[1]->getLabel());
        $this->assertEquals('13', $children2[1]->getValue());
    }

    public function testGetItemsTestParentReturnsChildren()
    {
        // Set the setting to count sub-nodes on parents
        $this->model->getFilterItem()->getAttributes()->set('show_sub_node_contents_on_parent', 1);
        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();

        // Count checks if the nesting is done properly and if unpublished categories are included.
        $this->assertCount(3, $items);

        /*
         * Check the expected values from our data file.
         * In that, the parents should also include the sum of the counter
         * of their child nodes.
         */
        $this->assertEquals(7, $items[0]->getCount());
        $this->assertEquals(2, $items[1]->getCount());
        $this->assertEquals(1, $items[2]->getCount());
    }

    public static function tearDownAfterClass(): void
    {
        self::$databaseHelper->dropTables();
    }
}
