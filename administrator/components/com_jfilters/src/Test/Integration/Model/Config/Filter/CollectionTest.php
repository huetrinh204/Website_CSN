<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model\Config\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersConfigReaderInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class FilterGeneratorTest
 *
 * Testing Category against xml configs
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Test\Integration\Model
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     * @since 1.0.0
     */
    protected $model;

    /**
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = ObjectManager::getInstance();
        /** @var  ComponentConfig $componentConfig */
        $componentConfig = $objectManager->getObject(ComponentConfig::class);
        /*
         * We are based in the fact that the ObjectManager uses singletons, to set vars used by the classes we will use, later.
         * In that case, we are changing our default xml config files.
         */
        $componentConfig->set('edit_filters_config_file_path', true);
        $componentConfig->set('filters_config_file_path', realpath(__DIR__ . '/../../../config/filters.xml'));
        $componentConfig->set('edit_contexts_config_file_path', true);
        $componentConfig->set('contexts_config_file_path', realpath(__DIR__ . '/../../../config/contexts.xml'));

        $filtersConfigXML = $objectManager->getObject(FiltersConfigReaderInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->model = $objectManager->getObject(Collection::class, [$filtersConfigXML, $loggerMock]);
    }

    /**
     * @since 1.0.0
     */
    public function testGetByNameAttribute()
    {
        $filter = $this->model->getByNameAttribute('category');
        $this->assertEquals('category', $filter->getName());
        $this->assertEquals(true, $filter->isRoot());
    }


    /**
     * @since 1.0.0
     */
    public function testGetByNameAttribute2()
    {
        $filter = $this->model->getByNameAttribute('fields');
        $this->assertEquals('fields', $filter->getName());
        $this->assertEquals(false, $filter->isRoot());
        $this->assertEquals(true, $filter->isDynamic());
    }
}
