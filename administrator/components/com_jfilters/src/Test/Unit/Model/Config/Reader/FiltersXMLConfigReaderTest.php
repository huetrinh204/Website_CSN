<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Config\Reader;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\InvalidXMLException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\MissingNodeException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\Registry\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


class FiltersXMLConfigReaderTest extends TestCase
{
    /**
     * @var MockObject
     * @since 1.0.0
     */
    protected $componentConfig;

    /**
     * @since 1.0.0
     */
    protected function setUp(): void
    {
        $params                = new Registry();
        $this->componentConfig = $this->createMock(ComponentConfig::class);
        $this->componentConfig->method('get')->willReturn($params);
    }

    /**
     * @return FiltersXMLConfigReader
     * @since 1.0.0
     */
    protected function createTestObject($xmlFile)
    {
        $object     = new FiltersXMLConfigReader($this->componentConfig, $xmlFile);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $object->setLogger($loggerMock);

        return $object;
    }

    /**
     * @param $xmlFile
     * @param $xml
     * @param $count
     *
     * @throws InvalidXMLException
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @covers       FiltersXMLConfigReader::getFiltersConfig
     * @dataProvider getXML
     * @since        1.0.0
     */
    public function testGetFiltersConfig($xmlFile, $xml, $count)
    {
        $object = $this->createTestObject($xmlFile);
        $this->assertEquals($count, count($object->getFiltersConfig()));
    }

    /**
     * @param $xmlFile
     * @param $xml
     *
     * @throws InvalidXMLException
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @covers       FiltersXMLConfigReader::getFiltersConfig
     * @dataProvider getXMLInvalid
     * @since        1.0.0
     */
    public function testGetFiltersConfigInvalid($xmlFile, $xml)
    {
        $object = $this->createTestObject($xmlFile);
        $this->expectException(InvalidXMLException::class);
        $object->getFiltersConfig();
    }

    /**
     * @param $xmlFile
     * @param $xml
     *
     * @throws InvalidXMLException
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @covers       FiltersXMLConfigReader::getFiltersConfig
     * @dataProvider getXMLNoFilterNode
     * @since        1.0.0
     */
    public function testGetFiltersConfigNoFilterNode($xmlFile, $xml)
    {
        $object = $this->createTestObject($xmlFile);
        $this->expectException(MissingNodeException::class);
        $object->getFiltersConfig();
    }

    /**
     * provides the xml config
     *
     * @return array
     * @since 1.0.0
     */
    public function getXML()
    {
        $path = JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Unit/config/filterXmls/filters.xml';
        ob_start();
        require_once $path;
        $xml = ob_get_contents();
        ob_end_flush();

        $simpleXML1 = simplexml_load_string($xml);

        return [
            ['withNoErrors' => realpath($path), $simpleXML1, $count = 2]
        ];
    }

    /**
     * Provides an invalid xml config
     *
     * @return array
     * @since 1.0.0
     */
    public function getXMLInvalid()
    {
        $path = JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Unit/config/filterXmls/filtersInvalid.xml';
        ob_start();
        require_once $path;
        $xml = ob_get_contents();
        ob_end_clean();

        $simpleXML1 = @simplexml_load_string($xml);

        return [
            ['withOutFiltersNode' => realpath($path), $simpleXML1]
        ];
    }

    /**
     * Provides an xml config, missing a <filter> node
     *
     * @return array
     * @since 1.0.0
     */
    public function getXMLNoFilterNode()
    {
        $path = JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Unit/config/filterXmls/filtersException2.xml';
        ob_start();
        require_once $path;
        $xml = ob_get_contents();
        ob_end_clean();

        $simpleXML1 = simplexml_load_string($xml);

        return [
            ['withOutFilterNode' => realpath($path), $simpleXML1]
        ];
    }
}
