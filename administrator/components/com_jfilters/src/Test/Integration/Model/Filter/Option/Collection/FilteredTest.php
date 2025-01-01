<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model\Filter\Option\Collection;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as FilterConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Resolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection as OptionCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Registry;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Bluecoder\Component\Jfilters\Administrator\Test\Integration\DbHelper;
use Joomla\CMS\Application\CMSApplication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilteredTest extends TestCase
{

    /**
     * @var DbHelper
     * @since 1.0.0
     */
    protected static $databaseHelper;

    /**
     * @var ComponentConfig
     */
    protected $componentConfig;

    /** @var MockObject[] */
    protected $filterMocks;

    /**
     * @var Filtered
     * @since 1.0.0
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

        $db = self::$databaseHelper->getDbo();
        /** @var Collection $filtersCollection */
        $filtersCollection = ObjectManager::getInstance()->getObject(FilterCollection::class);
        $this->filterMocks = $this->filtersProvider();
        $applicationMock = $this->createMock(CMSApplication::class);

        foreach ($this->filterMocks as &$filterMock) {
            $filtersCollection->addItem($filterMock);
        }
        $dynamicFiltersConfigCollection = new DynamicFilterCollection(null, $loggerMock);
        $this->model = new Filtered($db, $contextConfigCollection, $dynamicFiltersConfigCollection, $loggerMock, $filtersCollection, $applicationMock, $this->componentConfig);
    }

    /**
     * Test the options of Categories (while root), against selections in a category.
     * The Categories should remain intact, when they are root
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function testCategoriesWithCategorySelected()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRoot')->willReturn(true);
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([10]);

        // test a category filter
        $this->model->setFilterItem($this->filterMocks[0]);

        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        $this->assertEquals('Programming', $items[0]->getLabel());
        $this->assertEquals(5, $items[0]->getCount());
        $this->assertEquals('PHP', $items[1]->getLabel());
        $this->assertEquals(1, $items[1]->getCount());
        $this->assertEquals('Manufacturing', $items[3]->getLabel());
        $this->assertEquals(2, $items[3]->getCount());
    }


    /**
     * Test the options of Categories, against selections in a field.
     * The Categories should change, when they are not root
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function testCategoriesNoRootWithFieldSelected()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRoot')->willReturn(false);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[3]->expects($this->any())->method('getRequest')->willReturn([]);

        // test a category filter
        $this->model->setFilterItem($this->filterMocks[0]);

        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        $this->assertEquals('Programming', $items[0]->getLabel());
        $this->assertEquals(5, $items[0]->getCount());
    }

    /**
     * Test the options of a field, against selections in a category
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function testFieldOptionCollectionWithCategorySelected()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([10]);
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[3]->expects($this->any())->method('getRequest')->willReturn([]);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[1]);
        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        $this->assertEquals('Computer-Science', $items[0]->getLabel());
        $this->assertEquals('Computer-Science', $items[0]->getValue());
        $this->assertEquals(1, $items[0]->getCount());
    }

    /**
     * Test the options of a field, against selections in a field filter and a category
     * In that case the selected field value is cut.
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function testFieldOptionCollectionWithBigFieldValueAndCategorySelected()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([10]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn(['Computer-Science']);
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([]);

        // Set the selected value for a field filter (4th)
        $storedValue = 'Ζαβαρακατρανεμια ζαβαρακατρανεμια αληλουια αληλουια αληλουια ζαβαρακατρανεμια ηλεος ηλεος λαμα λαμα ναμα ναμα νενια αληλουια αληλουια ηλεος ηλεος ηλεος';
        $requestValue = mb_substr($storedValue, 0, -1 * (mb_strlen($storedValue) - $this->componentConfig->get('max_option_value_length')));
        $this->filterMocks[3]->expects($this->any())->method('getRequest')->willReturn([$requestValue]);
        // test a field filter
        $this->model->setFilterItem($this->filterMocks[1]);
        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        $this->assertEquals('Computer-Science', $items[0]->getLabel());
        $this->assertEquals('Computer-Science', $items[0]->getValue());
        $this->assertEquals(1, $items[0]->getCount());
    }

    /**
     * Test the options of a field, against selections in a field filter and a category
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function testFieldOptionCollectionWithFieldAndCategorySelected()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([10]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn(['Computer-Science']);
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[3]->expects($this->any())->method('getRequest')->willReturn([]);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[2]);
        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        $this->assertEquals('1', $items[0]->getLabel());
        $this->assertEquals('1', $items[0]->getValue());
        $this->assertEquals(1, $items[0]->getCount());
    }

    /**
     * Test the counter of an option, against selections in a field filter pointing to the same item.
     *
     * There was a bug when 2 (or more) options of a filter assigned to the same item, were selected.
     * The counter was wrongly counting as many as the selected options, despite both pointing to a single item.
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function testFieldOptionCollectionWithMultipleFieldSelected()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[4]->expects($this->any())->method('getRequest')->willReturn(['Aba', 'Baba']);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[3]);

        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        $this->assertEquals(1, $items[0]->getCount());
    }

    /**
     * Test the options of a field, against selections in a field filter.
     *
     * There was a bug when 2 field filters have a similar value.
     * When that value was selected in 1 of the filters it was showing (wrongly) in the other.
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function testFieldOptionCollectionWithFieldSelected()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn(['3']);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[3]);

        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        $this->assertNull(isset($items[1]) ? $items[1] : null);
    }

    /**
     * Test that returns cut value/label with a category selected
     *
     * @since 1.0.0
     */
    public function testFieldOptionValueCutWithFieldAndCategorySelected()
    {
        // test a field filter that has an option with very long value
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([10]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn(['Computer-Science']);
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([]);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[3]);
        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        $item = reset($items);
        $storedValue = 'Ζαβαρακατρανεμια ζαβαρακατρανεμια αληλουια αληλουια αληλουια ζαβαρακατρανεμια ηλεος ηλεος λαμα λαμα ναμα ναμα νενια αληλουια αληλουια ηλεος ηλεος ηλεος';
        $expectedValue = mb_substr($storedValue, 0, -1 * (mb_strlen($storedValue) - $this->componentConfig->get('max_option_value_length')));
        $expectedLabel = mb_substr($storedValue, 0, -1 * (mb_strlen($storedValue) - $this->componentConfig->get('max_option_label_length')));
        // We add 3 dots at the end of the cut labels
        $expectedLabel.='...';
        $this->assertEquals($expectedValue, $item->getValue());
        $this->assertEquals($expectedLabel, $item->getLabel());
    }

    /**
     * Test date ranges
     *
     * @since 1.9.0
     */
    public function testFieldOptionCollectionWithDateRange()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([]);
        $attributes = new Registry(['calendar_mode' => 'range']);
        $optionCollectionMock = $this->createMock(OptionCollection::class);
        $optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn('date');
        $this->filterMocks[5]->expects($this->any())->method('getOptions')->willReturn($optionCollectionMock);
        $this->filterMocks[5]->expects($this->any())->method('getDisplay')->willReturn('calendar');
        $this->filterMocks[5]->expects($this->any())->method('getIsRange')->willReturn(true);
        $this->filterMocks[5]->expects($this->any())->method('getAttributes')->willReturn($attributes);

        // These 2 dates are our range. It should be translated as BETWEEN '2023-04-22' AND '2023-04-29'
        $this->filterMocks[5]->expects($this->any())->method('getRequest')->willReturn(['2023-04-22', '2023-04-29']);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[2]);

        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();
        $this->assertEquals(2, count($items));
    }

    /**
     * Test numerical ranges (using both min and max) against other field filters
     *
     * @since 1.9.0
     */
    public function testFieldOptionCollectionWithNumericalRange()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn([]);

        $optionCollectionMock = $this->createMock(OptionCollection::class);
        $optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn('int');
        $this->filterMocks[2]->expects($this->any())->method('getOptions')->willReturn($optionCollectionMock);
        $this->filterMocks[2]->expects($this->any())->method('getDisplay')->willReturn('range_inputs');
        $this->filterMocks[2]->expects($this->any())->method('getIsRange')->willReturn(true);

        // These 2 values are our range.
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([1, 3]);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[1]);

        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();

        // Counts the items/options of the filter
        $this->assertEquals(2, count($items));

        // Check the counter of the 1st option ('Computer-Science')
        $this->assertEquals(4, $items[0]->getCount());
    }

    /**
     * Test numerical ranges against other field filters, using 1 var (min)
     *
     * @since 1.16.4
     * @return void
     */
    public function testFieldOptionCollectionWithNumericalRangeOnlyMin()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn([]);

        $optionCollectionMock = $this->createMock(OptionCollection::class);
        $optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn('int');
        $this->filterMocks[2]->expects($this->any())->method('getOptions')->willReturn($optionCollectionMock);
        $this->filterMocks[2]->expects($this->any())->method('getDisplay')->willReturn('range_inputs');
        $this->filterMocks[2]->expects($this->any())->method('getIsRange')->willReturn(true);

        // This value is our min value of the range. We leave the max null
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([1, null]);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[1]);

        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();

        // Counts the items/options of the filter
        $this->assertEquals(2, count($items));

        // Check the counter of the 1st option ('Computer-Science')
        $this->assertEquals(6, $items[0]->getCount());
    }

    /**
     * Test numerical ranges against other field filters, using 1 var (max)
     *
     * @since 1.16.4
     * @return void
     */
    public function testFieldOptionCollectionWithNumericalRangeOnlyMax()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn([]);

        $optionCollectionMock = $this->createMock(OptionCollection::class);
        $optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn('int');
        $this->filterMocks[2]->expects($this->any())->method('getOptions')->willReturn($optionCollectionMock);
        $this->filterMocks[2]->expects($this->any())->method('getDisplay')->willReturn('range_inputs');
        $this->filterMocks[2]->expects($this->any())->method('getIsRange')->willReturn(true);

        // This value is our max value of the range. We leave the min null
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([null, 3]);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[1]);

        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();

        // Counts the items/options of the filter
        $this->assertEquals(2, count($items));

        // Check the counter of the 1st option ('Computer-Science')
        $this->assertEquals(4, $items[0]->getCount());
    }

    /**
     * Test numerical ranges, with big max value,  against other field filters
     *
     * @since 1.9.0
     */
    public function testFieldOptionCollectionWithNumericalRangeWithBigMaxValue()
    {
        $this->filterMocks[0]->expects($this->any())->method('getRequest')->willReturn([]);
        $this->filterMocks[1]->expects($this->any())->method('getRequest')->willReturn([]);

        $optionCollectionMock = $this->createMock(OptionCollection::class);
        $optionCollectionMock->expects($this->any())->method('getOptionDataType')->willReturn('int');
        $this->filterMocks[2]->expects($this->any())->method('getOptions')->willReturn($optionCollectionMock);
        $this->filterMocks[2]->expects($this->any())->method('getDisplay')->willReturn('range_inputs');
        $this->filterMocks[2]->expects($this->any())->method('getIsRange')->willReturn(true);

        // These 2 values are our range.
        $this->filterMocks[2]->expects($this->any())->method('getRequest')->willReturn([1, 500]);

        // test a field filter
        $this->model->setFilterItem($this->filterMocks[1]);

        /** @var OptionInterface[] $items */
        $items = $this->model->getItems();

        // Counts the items/options of the filter
        $this->assertEquals(2, count($items));

        // Check the counter of the 1st option ('Computer-Science')
        $this->assertEquals(6, $items[0]->getCount());
    }

    /**
     * Data Provider
     *
     * @return array
     * @since 1.0.0
     */
    public function filtersProvider()
    {
        $filtersXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/filters.xml');
        $configReader = new FiltersXMLConfigReader($this->componentConfig, $filtersXML);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $filterConfigCollection = new FilterConfigCollection($configReader, $loggerMock);

        $contextsXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/contexts.xml');
        $configReader = new ContextsXMLConfigReader($this->componentConfig, $contextsXML);
        $contextConfigCollection = new ContextConfigCollection($configReader, $loggerMock);
        $configResolver = new Resolver($filterConfigCollection, $contextConfigCollection);

        // create the filter mocks
        $filterItemMock1 = $this->createMock(FilterInterface::class);
        $filterItemMock1->expects($this->any())->method('getId')->willReturn(1);
        $filterItemMock1->expects($this->any())->method('getParentId')->willReturn(1);
        $filterItemMock1->expects($this->any())->method('getContext')->willReturn('com_content.article');
        // set the selected option
        $filterItemMock1->expects($this->any())->method('getConfigName')->willReturn('category');
        $filterItemMock1->expects($this->any())->method('getConfig')->willReturn($configResolver->getFilterConfig($filterItemMock1));

        $filterItemMock2 = $this->createMock(FilterInterface::class);
        $filterItemMock2->expects($this->any())->method('getId')->willReturn(2);
        $filterItemMock2->expects($this->any())->method('getParentId')->willReturn(1);
        $filterItemMock2->expects($this->any())->method('getContext')->willReturn('com_content.article');
        // set the selected option
        $filterItemMock2->expects($this->any())->method('getConfigName')->willReturn('fields');
        $filterItemMock2->expects($this->any())->method('getConfig')->willReturn($configResolver->getFilterConfig($filterItemMock2));

        $filterItemMock3 = $this->createMock(FilterInterface::class);
        $filterItemMock3->expects($this->any())->method('getId')->willReturn(3);
        $filterItemMock3->expects($this->any())->method('getParentId')->willReturn(2);
        $filterItemMock3->expects($this->any())->method('getContext')->willReturn('com_content.article');
        $filterItemMock3->expects($this->any())->method('getConfigName')->willReturn('fields');
        $filterItemMock3->expects($this->any())->method('getConfig')->willReturn($configResolver->getFilterConfig($filterItemMock3));

        $filterItemMock4 = $this->createMock(FilterInterface::class);
        $filterItemMock4->expects($this->any())->method('getId')->willReturn(4);
        $filterItemMock4->expects($this->any())->method('getParentId')->willReturn(3);
        $filterItemMock4->expects($this->any())->method('getContext')->willReturn('com_content.article');
        $filterItemMock4->expects($this->any())->method('getConfigName')->willReturn('fields');
        $filterItemMock4->expects($this->any())->method('getConfig')->willReturn($configResolver->getFilterConfig($filterItemMock4));

        $filterItemMock5 = $this->createMock(FilterInterface::class);
        $filterItemMock5->expects($this->any())->method('getId')->willReturn(5);
        $filterItemMock5->expects($this->any())->method('getParentId')->willReturn(4);
        $filterItemMock5->expects($this->any())->method('getContext')->willReturn('com_content.article');
        $filterItemMock5->expects($this->any())->method('getConfigName')->willReturn('fields');
        $filterItemMock5->expects($this->any())->method('getConfig')->willReturn($configResolver->getFilterConfig($filterItemMock5));

        $filterItemMock6 = $this->createMock(FilterInterface::class);
        $filterItemMock6->expects($this->any())->method('getId')->willReturn(6);
        $filterItemMock6->expects($this->any())->method('getParentId')->willReturn(5);
        $filterItemMock6->expects($this->any())->method('getContext')->willReturn('com_content.article');
        $filterItemMock6->expects($this->any())->method('getConfigName')->willReturn('fields');
        $filterItemMock6->expects($this->any())->method('getConfig')->willReturn($configResolver->getFilterConfig($filterItemMock6));

        return [$filterItemMock1, $filterItemMock2, $filterItemMock3, $filterItemMock4, $filterItemMock5, $filterItemMock6];
    }

    public static function tearDownAfterClass(): void
    {
        self::$databaseHelper->dropTables();
    }
}
