<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\MissingNodeException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersConfigReaderInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;

/**
 * Class AbstractCollection
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $itemObjectClass = FilterInterface::class;

    /**
     * @var FiltersConfigReaderInterface|null
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
     *
     * @param   FiltersConfigReaderInterface|null  $configReader
     * @param   LoggerInterface                    $logger
     */
    public function __construct(?FiltersConfigReaderInterface $configReader, LoggerInterface $logger)
    {
        parent::__construct();
        $this->configReader = $configReader;
        $this->logger       = $logger;
    }

    /**
     * Return a filter configuration by its name
     *
     * @param   string  $filterName
     *
     * @return FilterInterface
     * @since 1.0.0
     */
    public function getByNameAttribute(string $filterName): FilterInterface
    {
        $filter = $this->getByAttribute('name', $filterName);

        if ($filter === null) {
            $exception = new \RuntimeException(sprintf('The configuration for the filter with the name: \'%s\', is either absent or invalid',
                $filterName));
            $this->logger->critical($exception);
            throw $exception;
        }

        return $filter;
    }

    /**
     * @return AbstractCollection
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function loadWithFilters(): AbstractCollection
    {
        $filtersConfig = $this->configReader->getFiltersConfig();
        foreach ($filtersConfig as $filterConfig) {
            if (empty($filterConfig['name'])) {
                throw new \RuntimeException('The `name` attribute is missing from the filter\'s configuration');
            }
            $name = (string)$filterConfig['name'];
            try {
                $filterConfigItem = $this->setConfigObject($filterConfig);
            } catch (MissingNodeException $e) {
                $this->logger->critical($e);
                continue;
            }

            $this->items[$name] = $filterConfigItem;
        }

        return parent::loadWithFilters();
    }

    /**
     * Creates and returns a config object, with all it's sections
     *
     * @param   array  $data
     *
     * @return FilterInterface
     * @throws MissingNodeException
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function setConfigObject($data = [])
    {
        /** @var FilterInterface $filterConfig */
        $filterConfig = $this->getEmptyItem();

        if (!empty($data['name'])) {
            $filterConfig->setName($data['name']);
        }

        if (!empty($data['label'])) {
            $filterConfig->setLabel($data['label']);
        }

        $isDynamic = false;
        if (!empty($data['dynamic']) && $data['dynamic'] == 'true') {
            $isDynamic = true;
        }
        $filterConfig->setIsDynamic($isDynamic);

        $isRoot = false;
        if (!empty($data['root']) && $data['root'] == 'true') {
            $isRoot = true;
        }
        $filterConfig->setIsRoot($isRoot);

        $filterConfig->setDefinition($filterConfig->generateDefinition($data));
        $filterConfig->setValue($filterConfig->generateValue($data));
        $filterConfig->setValueItemRef($filterConfig->generateValueItemRef($data));

        return $filterConfig;
    }
}
