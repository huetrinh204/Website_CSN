<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\Filters;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\InvalidXMLException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\MissingNodeException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ConfigReaderInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersConfigReaderInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;

/**
 * Class DynamicConfigReader
 *
 * Reads and parses the dynamic  filters configuration file, converting it to a php assoc. array.
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\Filters
 */
class DynamicConfigReader implements DynamicConfigReaderInterface
{
    /**
     * @var array
     * @since 1.0.0
     */
    protected $filters;

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
            if($componentConfig->get('edit_dynamic_filters_config_file_path', false)) {
                try {
                    $xmlfile = $componentConfig->get('dynamic_filters_config_file_path', ComponentConfig::FILTERS_DYNAMIC_XML_DEFAULT_FILENAME);
                }
                catch (\UnexpectedValueException $e) {
                    //Suck it. We can live with that setting invalid.
                }
            }
            // Otherwise use the default one
            if(empty($xmlfile) || !file_exists($xmlfile)) {
                $xmlfile = ComponentConfig::FILTERS_DYNAMIC_XML_DEFAULT_FILENAME;
            }
        }

        $this->xmlFile = $xmlfile;
        if(!file_exists($this->xmlFile)) {
            throw new \UnexpectedValueException(Text::sprintf('Not found: %s', $this->xmlFile));
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
            $this->logger = ObjectManager::getInstance()->getObject(LoggerInterface::class);
        }
        return $this->logger;
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
                throw new InvalidXMLException('There is an error in the dynamic filters\' xml document. Check the logs for more details.');
            }
            if ($xml === false) {
                $this->getLogger()->critical('There is an error in the dynamic filters\' xml document');
                throw new InvalidXMLException('There is an error in the dynamic filters\' xml document. Check the logs for more details.');
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
    protected function parse(): array
    {
        $contextFound = false;
        $filters = [];
        foreach ($this->xml->children() as $nodeName => $filter) {
            if($nodeName != 'filter') {
                continue;
            }
            $name = (string)$filter['name'];
            $filters[$name] = $filter;
            $contextFound = true;
        }
        // no filter node found
        if($contextFound === false) {
            $exception = new MissingNodeException('The \'filters\' node does not contain any \'filter\' node, in the dynamic filters xml.');
            $this->getLogger()->critical($exception);
            throw $exception;
        }
        return $filters;
    }

    public function getDynamicFiltersConfig(): array
    {
        if($this->filters === null) {
            $this->loadConfigFromXML();
            $this->filters = $this->parse();
        }
        return $this->filters;
    }
}
