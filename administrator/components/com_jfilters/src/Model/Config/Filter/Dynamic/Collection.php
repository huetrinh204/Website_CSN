<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\MissingNodeException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\DynamicInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\Filters\DynamicConfigReaderInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $itemObjectClass = DynamicInterface::class;

    /**
     * @var DynamicConfigReaderInterface|null
     * @since 1.0.0
     */
    protected $configReader;

    /**
     * @var LoggerInterface
     * @since 1.0.0
     */
    protected $logger;

    /**
     * Collection constructor.
     * @param DynamicConfigReaderInterface|null $configReader
     * @param LoggerInterface $logger
     */
    public function __construct(?DynamicConfigReaderInterface $configReader, LoggerInterface $logger)
    {
        parent::__construct();
        $this->configReader = $configReader;
        $this->logger = $logger;
    }

    /**
     * Return a filter configuration by it's name
     *
     * @param string $filterName
     * @return DynamicInterface|null
     * @since 1.0.0
     */
    public function getByNameAttribute(string $filterName): ?DynamicInterface
    {
        return $this->getByAttribute('name', $filterName);;
    }

    /**
     * @return AbstractCollection
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function loadWithFilters(): AbstractCollection
    {
        if ($this->configReader) {
            $filtersConfig = $this->configReader->getDynamicFiltersConfig();
            foreach ($filtersConfig as $filterConfig) {
                if (empty($filterConfig['name'])) {
                    throw new MissingNodeException('The "name" attribute is missing from the dynamic filter\'s configuration');
                }

                try {
                    $name = (string)$filterConfig['name'];
                    $filterConfigItem = $this->setConfigObject($filterConfig);
                    $this->items[$name] = $filterConfigItem;
                } catch (MissingNodeException $e) {
                    $this->logger->critical($e);
                    continue;
                }
            }
        }
        return parent::loadWithFilters();
    }

    /**
     * Create the collection object and set it's parameters from the config reader.
     *
     * @param   array  $data
     *
     * @return DynamicInterface
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function setConfigObject($data = [])
    {
        /** @var DynamicInterface $filterConfig */
        $filterConfig = $this->getEmptyItem();

        if (!empty($data['name'])) {
            $filterConfig->setName($data['name']);
        } else {
            throw new MissingNodeException('The "name" attribute is missing from the dynamic filter\'s configuration');
        }

        if (!empty($data['generateFilter'])) {
            $filterConfig->setGenerateFilter($data['generateFilter'] == 'true' ? true : false);
        }

        if (!empty($data['type'])) {
            $filterConfig->setDataType($data['type']);
        }

        // Generate the display params
        $filterConfig->generateDisplays($data);

        // Generate the additional order field params
        $filterConfig->generateAdditionalSortFields($data);

        return $filterConfig;
    }
}
