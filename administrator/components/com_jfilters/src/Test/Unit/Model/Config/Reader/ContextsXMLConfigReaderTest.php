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
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsXMLConfigReader;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\Registry\Registry;
use PHPUnit\Framework\TestCase;

class ContextsXMLConfigReaderTest extends TestCase
{
    /**
     * @var ComponentConfig
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
     * @return ContextsXMLConfigReader
     * @since 1.0.0
     */
    protected function createTestObject($xmlFile)
    {
        $object     = new ContextsXMLConfigReader($this->componentConfig, $xmlFile);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $object->setLogger($loggerMock);

        return $object;
    }

    /**
     * @param $xml
     * @param $count
     *
     * @throws \Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\MissingNodeException
     * @throws \ReflectionException
     * @since        1.0.0
     * @covers       ContextsXMLConfigReader::getContextsConfig
     * @dataProvider getXML
     */
    public function testGetContextsConfig($xmlFile, $xml, $count)
    {
        $object = $this->createTestObject($xmlFile);
        $this->assertEquals($count, count($object->getContextsConfig()));
    }

    /**
     * @param $xmlFile
     *
     * @throws InvalidXMLException
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @covers       ContextsXMLConfigReader::getContextsConfig
     * @dataProvider getXMLInvalid
     * @since        1.0.0
     */
    public function testGetContextsConfigInvalid($xmlFile)
    {
        $object = $this->createTestObject($xmlFile);
        $this->expectException(InvalidXMLException::class);
        $object->getContextsConfig();
    }

    /**
     * @param $xmlFile
     * @param $xml
     *
     * @throws InvalidXMLException
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @covers       ContextsXMLConfigReader::getContextsConfig
     * @dataProvider getXMLNoContextNode
     * @since        1.0.0
     */
    public function testGetContextsConfigNoContextNode($xmlFile, $xml)
    {
        $object = $this->createTestObject($xmlFile);
        $this->expectException(MissingNodeException::class);
        $object->getContextsConfig();
    }

    /**
     * provides the xml config
     *
     * @return array
     * @since 1.0.0
     */
    public function getXML()
    {
        $path =JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Unit/config/contextXmls/contexts.xml';
        ob_start();
        require_once $path;
        $xml = ob_get_contents();
        ob_end_flush();

        $simpleXML1 = simplexml_load_string($xml);

        return [
            ['withNoErrors' => $path, $simpleXML1, 2]
        ];
    }

    /**
     * Provides an xml config, missing the <filters> node
     *
     * @return array
     * @since 1.0.0
     */
    public function getXMLNoContextNode()
    {
        $path = JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Unit/config/contextXmls/contextsExceptionNoContext.xml';
        ob_start();
        require_once $path;
        $xml = ob_get_contents();
        ob_end_clean();

        $simpleXML1 = simplexml_load_string($xml);

        return [
            ['withOutContextNode' => $path, $simpleXML1]
        ];
    }

    /**
     * Provides an xml config, missing the <filters> node
     *
     * @return array
     * @since 1.0.0
     */
    public function getXMLInvalid()
    {
        $path = JPATH_COMPONENT_ADMINISTRATOR_TEST. '/Unit/config/contextXmls/contextsExceptionInvalid.xml';
        ob_start();
        require_once $path;
        $xml = ob_get_contents();
        ob_end_clean();

        $simpleXML1 = @simplexml_load_string($xml);

        return [
            ['InvalidXML' => $path, $simpleXML1]
        ];
    }
}
