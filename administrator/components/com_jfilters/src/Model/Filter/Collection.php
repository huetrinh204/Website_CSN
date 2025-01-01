<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\TypeResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface as configFilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\Console\Application;
use Joomla\Session\SessionInterface;

/**
 * Class Collection
 * Filter Collection class
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     * @since 1.0.0
     * @internal
     */
    protected $itemObjectClass = FilterInterface::class;

    /**
     * @var TypeResolver
     * @since 1.0.0
     */
    protected $typeResolver;

    /**
     * Collection constructor.
     * @param TypeResolver $typeResolver
     * @throws \Exception
     * @since 1.0.0
     */
    public function __construct(TypeResolver $typeResolver)
    {
        try {
            $app = Factory::getApplication();
        } catch (\Exception $exception) {
            /*
             * The application does not exist, in cli (e.g. unit tests)
             * We have to create it.
             */
            $container = Factory::getContainer();
            $container->alias('session', 'session.cli')
                ->alias(Session::class, 'session.cli')
                ->alias(SessionInterface::class, 'session.cli');
            $app = Factory::getContainer()->get(Application::class);
            Factory::$application = $app;
        }
        $this->resourceModel = $app->bootComponent('com_jfilters')
            ->getMVCFactory()->createModel('Filters', 'Administrator', ['ignore_request' => true]);
        $this->typeResolver = $typeResolver;
        parent::__construct();
    }

    /**
     * We have to re-create the Joomla model.
     * Otherwise, it keeps the old conditions (states),
     * no matter if we clear the collection's conditions
     *
     * @return $this
     * @throws \Exception
     * @since 1.0.0
     */
    public function clear(): Collection
    {
        parent::clear();
        $app = Factory::getApplication();
        $this->resourceModel = $app->bootComponent('com_jfilters')
            ->getMVCFactory()->createModel('Filters', 'Administrator', ['ignore_request' => true]);

        return $this;
    }

    /**
     * Get the default ordering/sorting field of the collection
     *
     * @return array|string
     * @since 1.0.0
     */
    public function getOrderField()
    {
        return $this->resourceModel->getDefaultOrderingField();
    }

    /**
     * Get the filters that have selection/input
     *
     * @return array
     * @throws \Exception
     * @since 1.0.0
     */
    public function getSelectedItems(): array
    {
        $selected = [];
        /** @var FilterInterface $filter */
        foreach ($this as $filter) {
            if (!empty($filter->getRequest())) {
                $selected[] = $filter;
            }
        }
        return $selected;
    }

    /**
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function loadWithFilters(): AbstractCollection
    {
        $itemsRaw = $this->resourceModel->getItems();
        $itemsRaw = $this->setItemsClass($itemsRaw);
        $this->setMappedItems($itemsRaw);
        return parent::loadWithFilters();
    }

    /**
     * Set the itemObjectClass per filter as different filters can use a different classes
     *
     * This allows to have flexible collections with different types of filters
     * which though implement the FilterInterface
     *
     * @param array $itemsRaw
     *
     * @return array
     * @since 1.0.0
     */
    public function setItemsClass($itemsRaw): array
    {
        foreach ($itemsRaw as &$itemRaw) {
            $itemClass = $this->typeResolver->getTypeClass($itemRaw, configFilterInterface::SECTION_DEFINITION_NAME);
            if ($itemClass && is_subclass_of($itemClass, FilterInterface::class)) {
                $itemRaw->objectClass = $itemClass;
            }
        }
        return $itemsRaw;
    }
}
