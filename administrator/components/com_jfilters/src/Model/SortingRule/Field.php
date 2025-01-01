<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\SortingRule;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as contextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as filterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\MenuItemTrait;
use Joomla\CMS\Factory;

/**
 * The sorting rule field
 * @since 2.16.0
 */
class Field
{
    use MenuItemTrait;

    /**
     * Store the labels as used in the respective setting in the menu item's Sorting Rules
     * Any change here should be reflected to the Sorting Rules setting.
     *
     * @var array|string[]
     * @since 2.16.0
     */
    protected array $labels = [
        'relevance' => 'COM_JFILTERS_SORT_FIELD_RELEVANCE',
        'l.title' => 'JGLOBAL_TITLE',
        'l.start_date' => 'JGLOBAL_FIELD_CREATED_LABEL',
        '{context}.modified_date'  => 'JGLOBAL_FIELD_MODIFIED_LABEL',
        '{context}.publish_start_date' => 'JGLOBAL_FIELD_PUBLISH_UP_LABEL',
        '{context}.ordering' => 'JGLOBAL_FIELD_FIELD_ORDERING_LABEL',
        'l.list_price' => 'COM_JFILTERS_SORT_FIELD_PRICE'
    ];

    /**
     * @var string|null
     * @since 2.16.0
     */
    protected ?string $fieldValue = null;

    /**
     * @var string|null
     * @since 1.16.0
     */
    protected ?string $fieldName = null;

    /**
     * @var bool|null
     * @since 1.16.0
     */
    protected ?bool $isFilter = null;

    /**
     * @var contextCollection
     * @since 1.16.0
     */
    protected contextCollection $contextCollection;

    /**
     * @var string|null
     * @since 1.16.0
     */
    protected ?string $dbTableName = null;

    /**
     * @var ContextInterface|null
     * @since 1.16.0
     */
    protected ?ContextInterface $context = null;

    /**
     * @var filterCollection|null
     * @since 1.16.0
     *
     */
    protected ?filterCollection $filtersCollection = null;

    public function __construct(
        contextCollection $contextCollection,
        filterCollection $filtersCollection
    )
    {
        $this->contextCollection = $contextCollection;
        $this->filtersCollection = $filtersCollection;
        $this->filtersCollection->addCondition('filter.state', [1, 2]);
    }

    public function setField(string $field) : Field
    {
        $this->fieldValue = $field;
        // Reset vars generated from that
        $this->isFilter = null;
        $this->fieldName = null;
        $this->context = null;
        $this->dbTableName = null;

        return $this;
    }

    /**
     * Is it a filter or not
     *
     * @return bool
     * @since 1.16.0
     */
    public function getIsFilter() : bool
    {
        if ($this->isFilter === null) {
            $this->isFilter = preg_match('/^\d+$/', $this->fieldValue);
        }

        return $this->isFilter;
    }

    /**
     * The field that will be used in the db query for sorting.
     * Just the filter id, when dealing with filters.
     *
     * @return string|null
     * @throws \Exception
     * @since 1.16.0
     */
    public function getFieldName(): ?string
    {
        if ($this->fieldName === null) {
            // The field has the format 'dbTable.column' or just a number (when it is a filter)
            $parts = explode('.', $this->fieldValue);
            $this->fieldName = count($parts)>1 ? $parts[1] : $this->fieldValue;

            preg_match('/\{(context)\}/', reset($parts), $matches);
            // It's a context field. Get it from the context configuration
            if (count($parts)>1 && !empty($matches[0])) {
                $context = $this->getContext();
                if ($context) {
                    $contextItem = $context->getItem();

                    $fieldName = strtolower($this->fieldName);
                    $parts = explode('_', $fieldName);
                    $varName = '';
                    foreach ($parts as $part) {
                        $varName.= ucfirst($part);
                    }
                    $fnName = 'get' . ucfirst($varName);
                    if(method_exists($contextItem, $fnName)) {
                        /** @var  \Bluecoder\Component\Jfilters\Administrator\Model\Config\Field $field */
                        $field = $contextItem->{$fnName}();
                        $this->fieldName = $field ? $field->getDbColumn() : '';
                    }
                }

            }
        }

        return $this->fieldName;
    }

    /**
     * Get the page's context
     *
     * @return ContextInterface|null
     * @throws \Exception
     * @since 1.16.0
     */
    protected function getContext() : ?ContextInterface
    {
        if ($this->context === null) {
            // In our unit tests, there is no `ConsoleApplication::getMenu()`
            $app = Factory::getApplication();
            $menuItem = method_exists($app, 'getMenu') ? $this->getMenuItem() : null;
            $contextName = $menuItem ? $menuItem->getParams()->get('contextType', 'com_content.article') : 'com_content.article';
            /** @var ContextInterface $context */
            $this->context = $this->contextCollection->getByAttribute('name', $contextName);
        }
        return $this->context;
    }

    /**
     * Get the label/string that will be used in the drop-down.
     * If there is a lang constant. This will be returned instead. So it has to be translated. (using: Text::_($label))
     * If it's a filter, the filter label will be returned as is.
     *
     * @param bool $langConstants
     * @return string
     * @throws \Exception
     * @since 1.16.0
     */
    public function getLabel(bool $langConstants = true) : string
    {
        $label = '';
        if ($this->getIsFilter()) {
            /** @var FilterInterface $filter */
            $filter =  $this->filtersCollection->getByAttribute('id', (int)$this->getFieldName());
            if ($filter) {
                $label = $filter->getLabel();
            }
        }else {
            $label = $langConstants && $this->labels[$this->fieldValue] ? $this->labels[$this->fieldValue] : $this->getFieldName();

            // No translation found
            if (strpos($label, '.')) {
                $labelParts = explode('.', $this->getFieldName());
                $label = count($labelParts) > 1 ? $labelParts[1] : $this->getFieldName();
            }
        }

        return $label;
    }

    /**
     * Get the db table name used by the field.
     * This works only for string fields (Not for filters)
     *
     * @return string
     * @throws \Exception
     * @since 1.16.0
     */
    public function getDbTableName() : string
    {
        if ($this->dbTableName === null) {
            $parts = explode('.', $this->fieldValue);
            if (!$this->getIsFilter() && count($parts) > 1) {
                $this->dbTableName = reset($parts);
                preg_match('/\{(context)\}/', $this->dbTableName, $matches);

                // It contains the {context} placeholder
                if (!empty($matches[0])) {
                    $context = $this->getContext();
                    $this->dbTableName = $context ? $context->getItem()->getDbTable() : '';
                }
            }
            else {
                $this->dbTableName = '';
            }
        }

        return $this->dbTableName;
    }
}