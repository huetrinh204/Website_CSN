<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as ConfigFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\LanguageHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator\Declarative;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator\Dynamic;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * Class FilterGenerator
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
class FilterGenerator
{
    /**
     * @var ObjectManager
     * @since 1.0.0
     */
    protected $objectManager;

    /**
     * @var ConfigFilterCollection
     * @since 1.0.0
     */
    protected $configFilterCollection;

    /**
     * @var AdminModel
     * @since 1.0.0
     */
    protected $resourceModel;

    /**
     * @var LoggerInterface
     * @since 1.0.0
     */
    protected $logger;

    /**
     * FilterGenerator constructor.
     * @param ObjectManager $objectManager
     * @param ConfigFilterCollection $configFilterCollection
     * @param AdminModel $resourceModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectManager $objectManager,
        ConfigFilterCollection $configFilterCollection,
        AdminModel $resourceModel,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->configFilterCollection = $configFilterCollection;
        $this->resourceModel = $resourceModel;
        $this->logger = $logger;
    }

    /**
     * Returns all the generated filters
     *
     * @return array
     * @throws \Exception
     * @since 1.0.0
     */
    public function getFilters(): array
    {
        $filters = $this->generate();
        foreach ($filters as $filter) {
            if (empty($filter->label)) {
                $filter->label = $filter->name;
            }
            if (empty($filter->language)) {
                $filter->language = '*';
            }
        }
        $this->logger->info(sprintf('%d filter/s generated', count($filters)));
        return $filters;
    }

    /**
     * Generates the filters
     *
     * @return array
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function generate(): array
    {
        $generatedFilters = [];

        /** @var  FilterInterface $filterConfig */
        foreach ($this->configFilterCollection as $filterConfig) {
            if ($filterConfig->isDynamic()) {
                $contextConfigCollection = $this->objectManager->getObject(Collection::class);
                $dynamicFiltersConfigCollection = $this->objectManager->getObject(DynamicFilterCollection::class);

                /** @var  Dynamic $dynamic */
                $dynamic = $this->objectManager->createObject(Dynamic::class,
                    [$filterConfig, $this->resourceModel->getTable(), $contextConfigCollection, $dynamicFiltersConfigCollection]);
                $generatedFilters = array_merge($generatedFilters, $dynamic->generate());
                continue;
            }
            //non-dynamic - single filter
            /** @var  Declarative $declarative */
            $languageHelper = new LanguageHelper($filterConfig, $this->resourceModel->getTable()->getDbo());
            $declarative = $this->objectManager->createObject(Declarative::class,
                [$filterConfig, $languageHelper]);
            $generatedFilters = array_merge($generatedFilters, $declarative->generate());
        }
        return $generatedFilters;
    }
}
