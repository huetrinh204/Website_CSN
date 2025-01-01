<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

use Bluecoder\Component\Jfilters\Administrator\BaseObject;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Condition;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Field;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die();

/**
 * Sorting Rule class that hansles the result sorting in the front-end
 * @since 1.16.0
 */
class SortingRule extends BaseObject
{
    /**
     * The default sorting direction for filtering
     * @since 1.16.0
     */
    public const DEFAULT_SORTING_FILTERING_DIR = 'ASC';

    /**
     * The default sorting field for filtering
     * @since 1.16.0
     */
    public const DEFAULT_SORTING_FILTERING_FIELD = 'l.title';

    /**
     * The default sorting direction for search
     * @since 1.16.0
     */
    public const DEFAULT_SORTING_SEARCH_DIR = 'DESC';

    /**
     * The default sorting field for search
     * @since 1.16.0
     */
    public const DEFAULT_SORTING_SEARCH_FIELD = 'relevance';

    /**
     * @var Field|null
     * @since 1.16.0
     */
    protected ?Field $sortField = null;

    /**
     * @var string|null
     * @since 1.16.0
     */
    protected ?string $sortDirection = null;

    /**
     * @var bool|null
     * @since 1.16.0
     */
    protected ?bool $useOnSearch = null;

    /**
     * @var bool|null
     * @since 1.16.0
     */
    protected ?bool $useOnFiltering = null;

    /**
     * @var Condition|null
     * @since 1.16.0
     */
    protected ?Condition $condition = null;

    /**
     * @var bool|null
     * @since 1.16.0
     */
    protected ?bool $isActive = null;

    /**
     * @param string $sortField
     * @return $this
     * @since 1.16.0
     */
    public function setSortField(string $sortField) : SortingRule
    {
        $sortField = trim($sortField);
        try {
            /** @var Field $field */
            $field = $this->objectManager->createObject(Field::class);
            $field->setField($sortField);
        }
        catch (\Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->objectManager->getObject(LoggerInterface::class);
            $logger->error('Cannot create SortingRule\Field.' . $exception->getMessage());
        }
        $this->sortField = $field;

        return $this;
    }

    /**
     * Returns the sort field
     *
     * @return Field|null
     * @since 1.16.0
     */
    public function getSortField() : ?Field
    {
        return $this->sortField;
    }


    /**
     * Returns the sorting direction (ASC, DESC)
     *
     * @param string $sortDirection
     * @return $this
     * @since 1.16.0
     */
    public function setSortDirection(string $sortDirection) : SortingRule
    {
        $sortDirection = strtoupper($sortDirection);
        $sortDirection = in_array($sortDirection, ['ASC', 'DESC']) ? $sortDirection : self::DEFAULT_SORTING_FILTERING_DIR;
        $this->sortDirection = $sortDirection;
        return $this;
    }

    /**
     * @return string
     * @since 1.16.0
     */
    public function getSortDirection() : string
    {
        return $this->sortDirection ?? self::DEFAULT_SORTING_FILTERING_DIR;
    }

    /**
     * Get the label of the sorting (translated)
     *
     * @return null | string
     * @throws \Exception
     * @since 1.16.0
     */
    public function getLabel(): ?string
    {
        $fieldLabel = $this->getSortField()->getLabel(false);
        /**
         * We give 2 ways to translate a field label.
         * 1. Using the constant that contains the direction. e.g. `COM_JFILTERS_SORT_FIELD_MODIFIED_DATE_ASC`
         * The user may want to translate that like 'Recent first'.
         *
         * 2. Translating the field and the direction separately.
         * In that case we have 2 language constants:
         * A. The one returned by the field->label
         * B. The ASC/DESC lang constants
         */
        if ($fieldLabel) {
            $fieldLabelLangConstant = 'COM_JFILTERS_SORT_FIELD_' . strtoupper($fieldLabel) . '_' . $this->getSortDirection();
            $label = Text::_($fieldLabelLangConstant);
            // If no translation found for the 1st way, use the 2nd.
            $label = $label != $fieldLabelLangConstant ? $label : Text::_($this->getSortField()->getLabel()) . ' ' . Text::_('COM_JFILTERS_' . $this->getSortDirection() . '_LABEL');
        }

        return $label ?? $fieldLabel;
    }

    public function setUseOnSearch(string $useOnSearch) : SortingRule
    {
        $this->useOnSearch = (bool)$useOnSearch;
        return $this;
    }

    /**
     * @return bool|null
     * @since 1.16.0
     */
    public function getUseOnSearch() : ?bool
    {
        return $this->useOnSearch;
    }

    public function setUseOnFiltering(string $useOnFiltering) : SortingRule
    {
        $this->useOnFiltering = (bool)$useOnFiltering;
        return $this;
    }

    /**
     * @return bool|null
     * @since 1.16.0
     */
    public function getUseOnFiltering() : ?bool
    {
        return $this->useOnFiltering;
    }

    public function setCondition(Condition $condition) : SortingRule
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @return Condition|null
     * @since 1.16.0
     */
    public function getCondition() : ?Condition
    {
        return $this->condition;
    }

    /**
     * Check if this rule is currently active, based on the input
     *
     * @return bool
     * @throws \Exception
     * @since 1.16.0
     */
    public function isActive() : bool
    {
        if ($this->isActive === null) {
            $this->isActive = false;
            $inputOrder = Factory::getApplication()->getInput()->getString('o', '');
            $inputOrderDir = Factory::getApplication()->getInput()->getString('od', 'asc');
            if ($inputOrder == $this->getSortField()->getFieldName() && strtoupper($inputOrderDir) == $this->getSortDirection()) {
                $this->isActive = true;
            }
        }

        return $this->isActive;
    }
}