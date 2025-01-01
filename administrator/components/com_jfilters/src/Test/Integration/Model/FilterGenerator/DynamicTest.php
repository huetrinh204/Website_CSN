<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model\FilterGenerator;

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FieldFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator\Dynamic;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\Table\FilterTable;
use Bluecoder\Component\Jfilters\Administrator\Test\Integration\DbHelper;
use PHPUnit\Framework\TestCase;

\defined('_JEXEC') or die();

class DynamicTest extends TestCase
{
    /**
     * @var DbHelper
     * @since 1.0.0
     */
    protected static $databaseHelper;

    /**
     * @var Dynamic
     * @since 1.0.0
     */
    protected $model;

    /**
     * @var FilterInterface
     * @since 1.0.0
     */
    protected $filterFieldConfig;

    /**
     * @var Collection
     * @since 1.0.0
     */
    protected $filterConfigCollection;

    public static function setUpBeforeClass() : void
    {
        self::$databaseHelper = new DbHelper();
        self::$databaseHelper->createTables();
        //insert data
        $dataFile = 'data1.sql';
        self::$databaseHelper->executeSqlFile($dataFile);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $componentConfigMock = $this->createMock(ComponentConfig::class);
        $filtersXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/filters.xml');
        $configFilterReader = new FiltersXMLConfigReader($componentConfigMock, $filtersXML);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->filterConfigCollection = new Collection($configFilterReader, $loggerMock);

        $contextsXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/contexts.xml');
        $configContextReader = new ContextsXMLConfigReader($componentConfigMock, $contextsXML);
        $contextConfigCollection = new ContextConfigCollection($configContextReader, $loggerMock);

        $tableMock = $this->createMock(FilterTable::class);
        $db = self::$databaseHelper->getDbo();
        $db->select(JTEST_DB_NAME);
        //the table columns to be returned from the 'fields' table
        $mainTableFields = [
            "main.id"
            ,"main.label"
            ,"main.alias"
            ,"main.display"
            ,"main.state"
            ,"main.access"
            ,"main.ordering"
            ,"main.attribs"
            ,"main.checked_out"
            ,"main.checked_out_time"
            ,"main.created_time"
            ,"main.updated_time"
            ,"main.language"
        ];
        $tableMock->expects($this->any())->method('getMainTableFields')->willReturn($mainTableFields);
        $tableMock->expects($this->any())->method('getDbo')->willReturn($db);
        $tableMock->expects($this->any())->method('getTableName')->willReturn('#__jfilters_filters');

        $dynamicFiltersConfigCollection = new DynamicFilterCollection(null, $loggerMock);

        /** @var  FilterInterface $filterConfig */
        foreach ($this->filterConfigCollection as $filterConfig) {
            if($filterConfig->getName() == 'fields') {
                $this->filterFieldConfig = $filterConfig;
                break;
            }
        }

        $this->model = new Dynamic($this->filterFieldConfig, $tableMock, $contextConfigCollection, $dynamicFiltersConfigCollection);
    }

    /**
     * Generate based on the config xml
     *
     * @throws \Exception
     * @since 1.3.0
     */
    public function testGenerate()
    {
        // Generate all the dynamic filters
        $generated = $this->model->generate();
        $this->assertCount(5, $generated);
    }

    /**
     * Generate with a condition
     *
     * @throws \ReflectionException
     * @since 1.3.0
     */
    public function testGenerateWithCondition()
    {
        $fieldFactory = new FieldFactory();
        /* We set a condition based on the state. Unpublished filters should be generated */
        $condition = $fieldFactory->create(['dbColumn' => 'state', 'value' => 1]);
        $this->filterFieldConfig->getDefinition()->setCondition($condition);
        $generated = $this->model->generate();
        $this->assertCount(4, $generated);
    }

    public static function tearDownAfterClass(): void
    {
        self::$databaseHelper->dropTables();
    }
}