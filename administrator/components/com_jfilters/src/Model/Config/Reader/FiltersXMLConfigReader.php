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
 * Class FiltersXMLConfigReader
 *
 * Reads and parses the filters configuration file, converting it to a php assoc. array.
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader
 */
class FiltersXMLConfigReader implements FiltersConfigReaderInterface
{
    /**
     * @var array
     * @since 1.0.0
     */
    protected $filters = [];

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
     * FiltersXMLConfigReader constructor.
     * @param ComponentConfig $componentConfig
     * @param string $xmlfile
     */
    public function __construct(ComponentConfig $componentConfig, $xmlfile = '')
    {
        // Check if there is a file set, through the component's configuration
        if (empty($xmlfile)) {
            $xmlfile = $componentConfig->get('filters_config_file_path');
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
                throw new InvalidXMLException('There is an error in the filters\' xml document. Check the logs for more details.');
            }
            if ($xml === false) {
                $exception = new InvalidXMLException('There is an error in the filters\' xml document. Check the logs for more details.');
                $this->getLogger()->critical($exception->getMessage());
                throw $exception;
            }
            $this->setXML($xml);
        }
        return $this;
    }

    /**
     * Parse the filters section of the xml
     *
     * @return array
     * @throws \ReflectionException
     * @throws MissingNodeException
     * @since 1.0.0
     */
    protected function parseFilters(): array
    {
        $filterFound = false;
        $filters = [];
        foreach ($this->xml->children() as $nodeName => $filter) {
            if($nodeName != 'filter') {
                continue;
            }
            $name = (string)$filter['name'];
            $filters[$name] = $filter;
            $filterFound = true;
        }

        if($filterFound === false) {
            $exception = new MissingNodeException('The \'filters\' node does not contain any \'filter\' node, in the filters xml.');
            $this->getLogger()->critical($exception);
            throw $exception;
        }
        return $filters;
    }

    /**
     * @return array
     * @throws InvalidXMLException
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getFiltersConfig(): array
    {
        if ($this->filters == null) {
            $this->loadConfigFromXML();
            $this->filters = $this->parseFilters();
        }
        return $this->filters;
    }

    /**
     * @param LoggerInterface $logger
     * @return ConfigReaderInterface
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
