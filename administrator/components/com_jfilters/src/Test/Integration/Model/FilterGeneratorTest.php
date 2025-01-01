<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Bluecoder\Component\Jfilters\Administrator\Table\FilterTable;
use Bluecoder\Component\Jfilters\Administrator\Test\Integration\DbHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use PHPUnit\Framework\TestCase;

class FilterGeneratorTest extends TestCase
{
    /**
     * @var DbHelper
     * @since 1.0.0
     */
    protected static $databaseHelper;

    /**
     * @var FilterGenerator
     * @since 1.0.0
     */
    protected $model;

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
        $objectManager = ObjectManager::getInstance();
        $componentConfigMock = $this->createMock(ComponentConfig::class);
        $filtersXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/filters.xml');
        $configReader = new FiltersXMLConfigReader($componentConfigMock, $filtersXML);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $filterConfigCollection = new Collection($configReader, $loggerMock);
        $adminModelMock = $this->createMock(AdminModel::class);
        $tableMock = $this->createMock(FilterTable::class);
        $db = self::$databaseHelper->getDbo();
        $db->select(JTEST_DB_NAME);
        $tableMock->expects($this->any())->method('getDbo')->willReturn($db);
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
        $tableMock->expects($this->any())->method('getTableName')->willReturn('#__jfilters_filters');
        $adminModelMock->expects($this->any())->method('getTable')->willReturn($tableMock);
        $this->model = new FilterGenerator($objectManager, $filterConfigCollection, $adminModelMock, $loggerMock);
    }

    public function testGenerate()
    {
        $generated = $this->model->generate();
        $this->assertEquals(9, count($generated));
        //check the data sql (for dynamic)  or the xml (for non-dynamic) for the expected values
        $this->assertEquals('category', $generated[0]->config_name);
        $this->assertEquals('Category', $generated[0]->name);
        $this->assertEquals('*', $generated[0]->language);

        $this->assertEquals('category', $generated[1]->config_name);
        $this->assertEquals('Category', $generated[1]->name);
        $this->assertEquals('de-DE', $generated[1]->language);

        $this->assertEquals('category', $generated[2]->config_name);
        $this->assertEquals('Category', $generated[2]->name);
        $this->assertEquals('en-GB', $generated[2]->language);

        $this->assertEquals('fields', $generated[3]->config_name);
        $this->assertEquals('Science', $generated[3]->name);
        $this->assertEquals('fields', $generated[4]->config_name);
        $this->assertEquals('Level', $generated[4]->name);
    }

    public static function tearDownAfterClass(): void
    {
        self::$databaseHelper->dropTables();
    }
}
