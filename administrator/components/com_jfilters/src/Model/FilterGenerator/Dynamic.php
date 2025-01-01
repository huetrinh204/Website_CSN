<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\DynamicInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Table\FilterTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Class Dynamic
 * Generate dynamic filters based on the configuration
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator
 */
class Dynamic
{
    /**
     * @var FilterInterface
     * @since 1.0.0
     */
    protected $filterConfig;

    /**
     * @var FilterTable
     * @since 1.0.0
     */
    protected $mainTable;

    /**
     * @var Collection
     * @since 1.0.0
     */
    protected $contextConfigCollection;

    /**
     * @var DynamicFilterCollection
     * @since 1.0.0
     */
    protected $dynamicFilterCollection;

    /**
     * Dynamic constructor.
     *
     * @param   FilterInterface  $filterConfig
     * @param   FilterTable  $table
     * @param   Collection  $contextConfigCollection
     * @param   DynamicFilterCollection  $dynamicFilterCollection
     */
    public function __construct(
        FilterInterface $filterConfig,
        FilterTable $table,
        Collection $contextConfigCollection,
        DynamicFilterCollection $dynamicFilterCollection
    ) {
        $this->filterConfig = $filterConfig;
        $this->mainTable = $table;
        $this->contextConfigCollection = $contextConfigCollection;
        $this->dynamicFilterCollection = $dynamicFilterCollection;
    }

    /**
     * Return the dynamic filters reading the database table of the config
     *
     * @return array|mixed
     * @throws \Exception
     * @since 1.3.0
     */
    public function generate()
    {
        $definition = $this->filterConfig->getDefinition();
        $mainTableColumns = $this->mainTable->getMainTableFields(
            [
                'config_name',
                'root',
                'name',
                'context',
                'parent_id'
            ]
        );
        $db = $this->mainTable->getDbo();
        $query = $db->getQuery(true);
        $query->select($mainTableColumns);
        $query->select(
            [
                $db->quote($this->filterConfig->getName()) . ' AS config_name',
                $db->quote((int)$this->filterConfig->isRoot()) . ' AS root',
                $definition->getId()->getValue() ? $db->quote($definition->getId()->getValue()) : $db->quoteName(
                        'def.' . $definition->getId()->getDbColumn()
                    ) . 'AS parent_id',
                $definition->getTitle()->getValue() ? $db->quote($definition->getTitle()->getValue()) : $db->quoteName(
                        'def.' . $definition->getTitle()->getDbColumn()
                    ) . 'AS name',
                $definition->getType()->getValue() ? $db->quote($definition->getType()->getValue()) : $db->quoteName(
                        'def.' . $definition->getType()->getDbColumn()
                    ) . 'AS type',
                $definition->getContext()->getValue() ? $db->quote(
                    $definition->getContext()->getValue()
                ) : $db->quoteName('def.' . $definition->getContext()->getDbColumn()) . 'AS context',
            ]
        )->from($query->quoteName($definition->getDbTable()) . ' AS def')
              ->leftJoin(
                  $this->mainTable->getTableName() . ' AS main ON main.parent_id=def.' . $definition->getId(
                  )->getDbColumn()
                  . ' AND main.config_name=' . $query->quote($this->filterConfig->getName())
              );

        // Check for conditions based on which the filters are generated.
        if ($definition->getCondition() !== null && $definition->getCondition()->getDbColumn() !== null && $definition->getCondition()->getValue() !== null) {
            $query->where($db->quoteName('def.' . $definition->getCondition()->getDbColumn()) . '=' . $db->quote($definition->getCondition()->getValue()));
        }

        if ($definition->getLanguage() !== null) {
            $query->select(
                $definition->getLanguage()->getValue() ? $db->quote(
                    $definition->getLanguage()->getValue()
                ) : $db->quoteName('def.' . $definition->getLanguage()->getDbColumn()) . 'AS language'
            );
        }

        // Get filters by specific ids if specified in the config
        if (!$definition->getId()->getValue() && $definition->getId()->getDbColumn() !== null && $definition->getId()->getIncluded()) {
            $query->whereIn(
                $db->quoteName('def.' . $definition->getId()->getDbColumn()),
                $definition->getId()->getIncluded(),
                ParameterType::INTEGER
            );
        }

        // Get filters only based on the supported contexts.
        if ($definition->getContext()->getDbColumn() !== null) {
            $contexts = [];
            if (empty($definition->getContext()->getValue())) {
                /** @var ContextInterface $contextItem */
                foreach ($this->contextConfigCollection as $contextItem) {
                    $contexts [] = $contextItem->getName();
                }
            } else {
                $contexts [] = $definition->getContext()->getValue();
            }
            if ($contexts) {
                $query->whereIn(
                    $db->quoteName('def.' . $definition->getContext()->getDbColumn()),
                    $contexts,
                    ParameterType::STRING
                );
            }
        }

        // Exclude/Include specific types as declared in config.
        if (!$definition->getType()->getValue() && $definition->getType()->getDbColumn() != null) {
            $excluded = $this->getNonFilterableTypes();
            if ($definition->getType()->getExcluded()) {
                $excluded = array_merge($excluded, $definition->getType()->getExcluded());
            }

            if ($excluded) {
                $query->whereNotIn(
                    $db->quoteName('def.' . $definition->getType()->getDbColumn()),
                    $excluded,
                    ParameterType::STRING
                );
            }

            if ($definition->getType()->getIncluded()) {
                $query->whereIn(
                    $db->quoteName('def.' . $definition->getType()->getDbColumn()),
                    $definition->getType()->getIncluded(),
                    ParameterType::STRING
                );
            }
        }

        // Get also the params of the parent field. They may contain valuable data.
        if ($definition->getParams() && !$definition->getParams()->getValue() && $definition->getParams()->getDbColumn() != null) {
            $query->select($db->quoteName($definition->getParams()->getDbColumn(), 'params'));
        }

        $db->setQuery($query);
        $filters = $db->loadObjectList();

        // Load the jfilters plugins, perhaps they want to do something
        PluginHelper::importPlugin('jfilters');
        Factory::getApplication()->triggerEvent('onFiltersAfterGenerate', [$this->filterConfig, &$filters]);
        $this->setDefaults($filters);

        return $filters;
    }

    /**
     * Get those types for which no filter will be generated
     *
     * @return array
     * @since 1.0.0
     */
    protected function getNonFilterableTypes()
    {
        $nonFilterableTypes = [];
        $nonFilterableFilters = $this->dynamicFilterCollection->getByAttribute('generateFilter', false, false);

        // the collection maybe null
        if ($nonFilterableFilters) {
            /** @var DynamicInterface $dynamicFilterConfig */
            foreach ($nonFilterableFilters as $nonFilterableFilter) {
                $nonFilterableTypes[] = $nonFilterableFilter->getName();
            }
        }

        return $nonFilterableTypes;
    }

    /**
     * Set default properties for the results, taken by the dynamic filters configuration
     *
     * @param $results
     *
     * @return mixed
     * @since 1.0.0
     */
    protected function setDefaults($results)
    {
        foreach ($results as $filter) {
            if (!empty($filter->type)) {
                // we store the dynamic filter's type in the attributes. This way we spare db queries.
                $attributes = [];
                $attributes['type'] = $filter->type;
                if ($this->filterConfig->getValue()->getIsTree()) {
                    $attributes['isTree'] = 1;
                }

                $filter->attribs = new Registry($attributes);
                $filterConfigItem = $this->dynamicFilterCollection->getByNameAttribute($filter->type);

                if($filterConfigItem && $filterConfigItem->getDataType()) {
                    $filter->attribs->set('dataType', $filterConfigItem->getDataType());
                }

                if ($filter->type == 'text' && !empty($filter->params)) {
                    $parentFieldParams = new Registry($filter->params);
                    if ($parentFieldParams->get('filter') && $parentFieldParams->get('filter') == 'integer' || $parentFieldParams->get('filter') == 'float') {
                        $filter->attribs->set('dataType', 'int');
                    }
                }

                if (empty($filter->display) && $filterConfigItem && $filterConfigItem->getDisplays()) {
                    $displays = $filterConfigItem->getDisplays();
                    /** @var Field $displayField */
                    $displayField = reset($displays);
                    $filter->display = $displayField->getValue();
                }
            }
        }

        return $results;
    }
}
