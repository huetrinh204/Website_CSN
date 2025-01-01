<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Config\Reader\Filters;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\InvalidXMLException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\MissingNodeException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\Filters\DynamicConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\Registry\Registry;
use PHPUnit\Framework\TestCase;

class DynamicConfigReaderTest extends TestCase
{
    /**
     * @var ComponentConfig
     * @since 1.0.0
     */
    protected $componentConfig;

    /**
     * @since 1.0.0
     */
    protected function setUp() : void
    {
        $params = new Registry();
        $this->componentConfig = $this->createMock(ComponentConfig::class);
        $this->componentConfig->method('get')->willReturn($params);
    }

    /**
     * @return DynamicConfigReader
     * @since 1.0.0
     */
    protected function createTestObject($xmlFile)
    {
        $object = new DynamicConfigReader($this->componentConfig, $xmlFile);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $object->setLogger($loggerMock);
        return $object;
    }

    /**
     * @param $xmlFile
     * @param $xml
     * @param $count
     * @covers DynamicConfigReader::getDynamicFiltersConfig
     * @dataProvider getXML
     * @since 1.0.0
     */
    public function testGetDynamicFiltersConfig($xmlFile, $xml, $count)
    {
        $object = $this->createTestObject($xmlFile);
        $this->assertEquals($count, count($object->getDynamicFiltersConfig()));
    }

    /**
     * @param $xmlFile
     * @param $xml
     * @throws InvalidXMLException
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @covers DynamicConfigReader::getDynamicFiltersConfig
     * @dataProvider getXMLNoFilterNode
     * @since 1.0.0
     */
    public function testGetDynamicFiltersConfigNoFilterNode($xmlFile, $xml)
    {
        $object = $this->createTestObject($xmlFile);
        $this->expectException(MissingNodeException::class);
        $object->getDynamicFiltersConfig();
    }

    /**
     * provides the xml config
     *
     * @return array
     * @since 1.0.0
     */
    public function getXML()
    {
        $path = JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Unit/config/filterXmls/dynamic.xml';
        ob_start();
        require_once $path;
        $xml = ob_get_contents();
        ob_end_flush();

        $simpleXML1 = simplexml_load_string($xml);
        return [
            ['withNoErrors' => $path, $simpleXML1, 4]
        ];
    }

    /**
     * Provides an xml config, missing the <filters> node
     *
     * @return array
     * @since 1.0.0
     */
    public function getXMLNoFilterNode()
    {
        $path = JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Unit/config/filterXmls/dynamicNoFilter.xml';
        ob_start();
        require_once $path;
        $xml = ob_get_contents();
        ob_end_clean();

        $simpleXML1 = simplexml_load_string($xml);
        return [
            ['withOutFilterNode' => $path, $simpleXML1]
        ];
    }
}
