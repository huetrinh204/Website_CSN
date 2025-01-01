<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Context;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsConfigReaderInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;

/**
 * Class Collection
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Context
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $itemObjectClass = ContextInterface::class;

    /**
     * @var ContextsConfigReaderInterface
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
     * @param   ContextsConfigReaderInterface|null  $configReader
     * @param   LoggerInterface                     $logger
     */
    public function __construct(ContextsConfigReaderInterface $configReader, LoggerInterface $logger)
    {
        parent::__construct();
        $this->configReader = $configReader;
        $this->logger       = $logger;
    }

    /**
     * Return an item configuration by it's name
     *
     * @param   string  $contextName
     *
     * @return  null|ContextInterface
     * @since 1.0.0
     */
    public function getByNameAttribute(string $contextName): ?ContextInterface
    {
        $this->load();
        $toBeReturned = null;
        foreach ($this->items as $key => $object) {
            if ($contextName == $key) {
                $toBeReturned = $object;
                break;
            }
        }

        return $toBeReturned;
    }

    /**
     * @return AbstractCollection
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function loadWithFilters(): AbstractCollection
    {
        $contextsConfig = $this->configReader->getContextsConfig();
        foreach ($contextsConfig as $contextConfig) {
            if (empty($contextConfig['name'])) {
                throw new \RuntimeException('The name is missing from the context\'s configuration');
            }
            $name              = (string)$contextConfig['name'];
            /** @var ContextInterface $contextConfigItem */
            $contextConfigItem = $this->getEmptyItem();
            $this->setSections($contextConfigItem, $contextConfig);
            $this->items[$name] = $contextConfigItem;
        }

        return parent::loadWithFilters();
    }

    /**
     * @param   ContextInterface  $contextConfigItem
     * @param                     $data
     *
     * @return $this
     * @since 1.0.0
     */
    public function setSections(ContextInterface $contextConfigItem, $data)
    {
        if (!empty($data['name'])) {
            $contextConfigItem->setName((string)$data['name']);
        }

        if (!empty($data['alias'])) {
            $contextConfigItem->setAlias((string)$data['alias']);
        }

        if (!empty($data['typeId'])) {
            $contextConfigItem->setTypeId((int)$data['typeId']);
        }

        // set the item section
        $contextConfigItem->setItem($contextConfigItem->generateConfigItem($data));

        return $this;
    }
}
