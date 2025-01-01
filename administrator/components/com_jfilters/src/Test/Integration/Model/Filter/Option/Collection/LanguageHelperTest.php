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
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\LanguageHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\Test\Integration\DbHelper;
use PHPUnit\Framework\TestCase;

class LanguageHelperTest extends TestCase
{
    /**
     * @var DbHelper
     * @since 1.0.0
     */
    protected static $databaseHelper;

    /**
     * @var Collection
     * @since 1.0.0
     */
    protected $filterConfigCollection;

    public static function setUpBeforeClass(): void
    {
        self::$databaseHelper = new DbHelper();
        self::$databaseHelper->createTables();
        //insert data
        $dataFile = 'data1.sql';
        self::$databaseHelper->executeSqlFile($dataFile);
    }

    protected function setUp(): void
    {
        $componentConfigMock = $this->createMock(ComponentConfig::class);
        $filtersXML = realpath(JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Integration/config/filters.xml');
        $configReader = new FiltersXMLConfigReader($componentConfigMock, $filtersXML);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->filterConfigCollection = new Collection($configReader, $loggerMock);
    }

    public function testGetLanguages()
    {
        $categoryConfig = $this->filterConfigCollection->getByNameAttribute('category');
        $model = new LanguageHelper($categoryConfig, self::$databaseHelper->getDbo());
        $languages = $model->getLanguages();
        $this->assertEquals(3, count($languages));
    }

    public static function tearDownAfterClass(): void
    {
        self::$databaseHelper->dropTables();
    }
}
