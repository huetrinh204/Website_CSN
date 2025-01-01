<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersConfigReaderInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use PHPUnit\Framework\TestCase;

class FiltersConfigTest extends TestCase
{
    /**
     * @var \Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection
     */
    protected $model;

    /**
     * Initialize the vars
     * @param int $filtersCounter
     */
    protected function initConfig($filtersCounter)
    {
        $config = [];
        for ($i = 0; $i < $filtersCounter; $i++) {
            $name = 'filter' . $i;
            $config[$name] = $this->createXmlElement($name);
        }
        $configReader = $this->createMock(FiltersConfigReaderInterface::class);
        $configReader->expects($this->any())->method('getFiltersConfig')->will($this->returnValue($config));
        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->model = new Collection($configReader, $loggerMock);
    }

    /**
     * Create a an xml element of type filter, like those used by the jfilters.xml
     *
     * @param $name
     * @return \SimpleXMLElement
     */
    protected function createXmlElement($name)
    {
        $simpleXML = new \SimpleXMLElement('<filter></filter>');
        $simpleXML->addAttribute('name', $name);
        $simpleXML->addChild('definition');
        $simpleXML->addChild('value');
        $simpleXML->addChild('valueRefItem');
        return $simpleXML;
    }

    /**
     * @covers \Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection::getItems
     */
    public function testGetConfigItems()
    {
        $filtersCounter = 3;
        $this->initConfig($filtersCounter);
        $this->assertCount($filtersCounter, $this->model->getItems());
    }
}
