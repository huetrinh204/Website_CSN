<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\SortingRule;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\BaseObject;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as filterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Joomla\String\StringHelper;

/**
 * The Sorting Rule Condition
 *
 * @since 1.16.0
 */
class Condition extends BaseObject
{
    /**
     * The 'contain' condition operator
     * @since 1.16.0
     */
    public const OPERATOR_CONTAIN = 'contain';

    /**
     * The 'notContain' condition operator
     * @since 1.16.0
     */
    public const OPERATOR_NOT_CONTAIN = 'notContain';

    /**
     * @var string|null
     * @since 1.16.0
     */
    protected ?string $conditionOperator = null;

    /**
     * @var string|null
     * @since 1.16.0
     */
    protected ?string $conditionFilters = null;

    /**
     * @var filterCollection|null
     * @since 1.16.0
     */
    protected ?filterCollection $filterCollection = null;

    /**
     * @var bool|null
     * @since 1.16.0
     */
    protected ?bool $isValid = null;

    /**
     * @param filterCollection $filterCollection
     * @since 1.16.0
     */
    public function __construct(
        filterCollection $filterCollection
    )
    {
        parent::__construct();
        $this->filterCollection = $filterCollection;
        $this->filterCollection->addCondition('filter.state', [1, 2]);
    }

    /**
     * @param string $conditionOperator
     * @return $this
     * @since 1.16.0
     */
    public function setConditionOperator(string $conditionOperator)
    {
        $conditionOperator = in_array($conditionOperator, [self::OPERATOR_CONTAIN, self::OPERATOR_NOT_CONTAIN]) ? $conditionOperator : self::OPERATOR_CONTAIN;
        $this->conditionOperator = $conditionOperator;
        // Init dependent var
        $this->isValid = null;
        return $this;
    }

    /**
     * @return string|null
     * @since 1.16.0
     */
    public function getConditionOperator() : string
   {
        return $this->conditionOperator ?? self::OPERATOR_CONTAIN;
   }

    /**
     * @param string $conditionFilters
     * @return $this
     * @since 1.16.0
     */
    public function setConditionFilters(string $conditionFilters)
    {
        $this->conditionFilters = trim($conditionFilters);
        // Init dependent var
        $this->isValid = null;
        return $this;
    }

    /**
     * @return string|null
     * @since 1.16.0
     */
    public function getConditionFilters() : string
    {
        return $this->conditionFilters ?? '';
    }

    /**
     * Is the condition valid based on the set filters condition, compared to the page's filters
     *
     * @return bool
     * @throws \Exception
     * @since 1.16.0
     */
    public function isValid() : bool
    {
        if ($this->isValid === null) {
            // Applicable to all
            if (empty($this->getConditionFilters())) {
                $this->isValid = $this->getConditionOperator() === self::OPERATOR_CONTAIN;
                return $this->isValid;
            }
            /*
             * We want it to be valid when any value in a set filter is found.
             * But should be at least 1 value matched in each set filter.
             *
             * E.g. If we have this set filter: category[]=1&category[]=2&tag[]=1&tag[]=2
             * If the page contains a category=1&tag=1 OR category=1&tag=2, etc. it wil be valid
             */

            $found = false;
            $url = 'https://example.com?' . $this->conditionFilters;
            $urlComponents = parse_url($url);
            if ($urlComponents && isset($urlComponents['query'])) {
                parse_str($urlComponents['query'], $queryParams);
                foreach ($queryParams as $paramName => $paramValue) {
                    $paramValue = is_scalar($paramValue) ? [$paramValue] : $paramValue;
                    /** @var FilterInterface $filter */
                    $filter = $this->filterCollection->getByAttribute('alias', $paramName);
                    if ($filter) {
                        foreach ($paramValue as $value) {
                            // Do not get fooled by the letter case. `in_array` is case-sensitive.
                            $found = in_array(StringHelper::strtolower($value), array_map([StringHelper::class, 'strtolower'], $filter->getRequest()));
                            if ($found === true) {
                                break 2;
                            }
                        }
                    }
                }
            }

            $this->isValid = ($this->getConditionOperator() === self::OPERATOR_CONTAIN && $found) || ($this->getConditionOperator() === self::OPERATOR_NOT_CONTAIN && !$found);
        }

        return $this->isValid;
    }
}