<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\InvalidXMLException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\MissingNodeException;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Language\Text;

/**
 * Class ContextsXMLConfigReader
 *
 * Reads and parses the contexts configuration file, converting it to a php assoc. array.
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader
 */
class ContextsXMLConfigReader implements ContextsConfigReaderInterface
{
    /**
     * @var array
     * @since 1.0.0
     */
    protected $contexts;

    /**
     * @var \SimpleXmlElement
     * @since 1.0.0
     */
    protected $xml;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $xmlFile;

    /**
     * @var LoggerInterface
     * @since 1.0.0
     */
    protected $logger;

    /**
     * ContextsXMLConfigReader constructor.
     * @param ComponentConfig $componentConfig
     * @param string $xmlfile
     */
    public function __construct(ComponentConfig $componentConfig, $xmlfile = '')
    {
        // Check if there is a file set, through the component's configuration
        if (empty($xmlfile)) {
            $xmlfile = $componentConfig->get('contexts_config_file_path');
        }

        $this->xmlFile = $xmlfile;
        if(!file_exists($this->xmlFile)) {
            throw new \UnexpectedValueException(Text::sprintf('Not found %s', $this->xmlFile));
        }
    }

    /**
     * Sets the SimpleXMLElement to the class
     *
     * @param \SimpleXMLElement $xml
     * @return ConfigReaderInterface
     * @since 1.0.0
     */
    public function setXML(\SimpleXMLElement $xml): ConfigReaderInterface
    {
        $this->xml = $xml;
        return $this;
    }

    /**
     * @return $this
     * @throws InvalidXMLException
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function loadConfigFromXML()
    {
        if ($this->xml === null) {
            try {
                $xml = simplexml_load_file($this->xmlFile);
            }
                // The exception is thrown during unit tests, but not in runtime
            catch (\Exception $e) {
                $this->getLogger()->critical($e);
                throw new InvalidXMLException('There is an error in the contexts\' xml document. Check the logs for more details.');
            }
            if ($xml === false) {
                $this->getLogger()->critical('There is an error in the contexts\' xml document');
                throw new InvalidXMLException('There is an error in the contexts\' xml document. Check the logs for more details.');
            }
            $this->setXML($xml);
        }
        return $this;
    }

    /**
     * Parse the context section of the xml
     *
     * @return array
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function parseContexts(): array
    {
        $contextFound = false;
        $contexts = [];
        foreach ($this->xml->children() as $nodeName => $context) {
            if($nodeName != 'context') {
                continue;
            }
            $name = (string)$context['name'];
            $contexts[$name] = $context;
            $contextFound = true;
        }
        // no context node found
        if($contextFound === false) {
            $exception = new MissingNodeException('The \'contexts\' node does not contain any \'context\' node, in the filters xml.');
            $this->getLogger()->critical($exception);
            throw $exception;
        }
        return $contexts;
    }

    /**
     * @return array
     * @throws InvalidXMLException
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getContextsConfig(): array
    {
        if ($this->contexts == null) {
            $this->loadConfigFromXML();
            $this->contexts = $this->parseContexts();
        }
        return $this->contexts;
    }

    /**
     * @param LoggerInterface $logger
     * @return FiltersConfigReaderInterface
     * @since 1.0.0
     */
    public function setLogger(LoggerInterface $logger) : ConfigReaderInterface
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface|mixed|object
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getLogger()
    {
        if($this->logger === null) {
            /** @var  LoggerInterface logger */
            $this->logger = ObjectManager::getInstance()->getObject(LoggerInterface::class);
        }
        return $this->logger;
    }
}
